#!/usr/bin/env python3
"""
AWS Service Helper Script for Vision UI Dashboard
Fetches EC2 status and Cost Explorer billing data using boto3
"""

import sys
import os
import csv
import json
import argparse
import datetime
from typing import Dict, Any, List

def get_credentials(csv_path: str = r"d:\aws\dashboard-billing-reader_accessKeys.csv") -> Dict[str, str]:
    """Reads AWS Access Key and Secret Key from CSV file or environment."""
    if os.path.exists(csv_path):
        with open(csv_path, mode="r", encoding="utf-8") as f:
            reader = csv.reader(f)
            header = next(reader, None)
            row = next(reader, None)
            if row and len(row) >= 2:
                return {
                    "aws_access_key_id": row[0].strip(),
                    "aws_secret_access_key": row[1].strip()
                }
    
    # Fallback to env
    ak = os.environ.get("AWS_ACCESS_KEY_ID")
    sk = os.environ.get("AWS_SECRET_ACCESS_KEY")
    if ak and sk:
        return {"aws_access_key_id": ak, "aws_secret_access_key": sk}

    raise ValueError(f"AWS credentials not found in '{csv_path}' or environment variables.")

def get_ec2_status(instance_id: str, region: str = "ap-southeast-1", creds: Dict[str, str] = None) -> Dict[str, Any]:
    """Fetches real-time status of specified EC2 instance."""
    import boto3
    ec2 = boto3.client(
        "ec2",
        region_name=region,
        aws_access_key_id=creds["aws_access_key_id"],
        aws_secret_access_key=creds["aws_secret_access_key"]
    )
    
    res = ec2.describe_instances(InstanceIds=[instance_id])
    if not res.get("Reservations") or not res["Reservations"][0].get("Instances"):
        return {"error": f"Instance {instance_id} not found in region {region}"}
    
    inst = res["Reservations"][0]["Instances"][0]
    
    # Find Name tag if available
    name = instance_id
    for tag in inst.get("Tags", []):
        if tag.get("Key") == "Name":
            name = tag.get("Value", name)
            break

    state = inst.get("State", {}).get("Name", "unknown")
    launch_time = inst.get("LaunchTime")
    launch_str = launch_time.strftime("%Y-%m-%d %H:%M:%S UTC") if launch_time else None

    # Calculate uptime if running
    uptime_seconds = 0
    if state == "running" and launch_time:
        now_utc = datetime.datetime.now(datetime.timezone.utc)
        uptime_seconds = max(0, int((now_utc - launch_time).total_seconds()))

    return {
        "success": True,
        "instanceId": instance_id,
        "name": name,
        "region": region,
        "state": state,
        "instanceType": inst.get("InstanceType"),
        "publicIp": inst.get("PublicIpAddress"),
        "privateIp": inst.get("PrivateIpAddress"),
        "publicDns": inst.get("PublicDnsName"),
        "launchTime": launch_str,
        "uptimeSeconds": uptime_seconds
    }

def control_ec2_instance(instance_id: str, cmd: str, region: str = "eu-west-2", creds: Dict[str, str] = None) -> Dict[str, Any]:
    """Starts or stops the specified EC2 instance."""
    import boto3
    ec2 = boto3.client(
        "ec2",
        region_name=region,
        aws_access_key_id=creds["aws_access_key_id"],
        aws_secret_access_key=creds["aws_secret_access_key"]
    )
    
    cmd = cmd.lower().strip()
    if cmd == "start":
        res = ec2.start_instances(InstanceIds=[instance_id])
        changes = res.get("StartingInstances", [])
    elif cmd == "stop":
        res = ec2.stop_instances(InstanceIds=[instance_id])
        changes = res.get("StoppingInstances", [])
    else:
        raise ValueError(f"Invalid command '{cmd}'. Must be 'start' or 'stop'.")

    prev_state = "unknown"
    curr_state = "unknown"
    if changes:
        prev_state = changes[0].get("PreviousState", {}).get("Name", "unknown")
        curr_state = changes[0].get("CurrentState", {}).get("Name", "unknown")

    return {
        "success": True,
        "instanceId": instance_id,
        "command": cmd,
        "previousState": prev_state,
        "currentState": curr_state,
        "timestamp": datetime.datetime.now(datetime.timezone.utc).strftime("%Y-%m-%d %H:%M:%S UTC")
    }

CATEGORY_META = {
    "server_runtime": {
        "id": "server_runtime",
        "name": "Server Runtime (EC2 Compute)",
        "nameTh": "ชั่วโมงเปิดเครื่อง (EC2 Compute)",
        "icon": "cpu",
        "color": "#0075FF"
    },
    "public_ipv4": {
        "id": "public_ipv4",
        "name": "Public IPv4 Address",
        "nameTh": "ค่าบริการ Public IP (IPv4)",
        "icon": "globe",
        "color": "#FF9900"
    },
    "storage": {
        "id": "storage",
        "name": "Storage (EBS gp3 Volumes)",
        "nameTh": "พื้นที่จัดเก็บข้อมูล (EBS Disk)",
        "icon": "database",
        "color": "#01B574"
    },
    "bandwidth": {
        "id": "bandwidth",
        "name": "Bandwidth & Data Transfer",
        "nameTh": "ปริมาณการรับ-ส่งข้อมูล (Bandwidth)",
        "icon": "wifi",
        "color": "#7928CA"
    },
    "tax_other": {
        "id": "tax_other",
        "name": "Tax & Fees",
        "nameTh": "ภาษีและค่าบริการอื่นๆ (Tax)",
        "icon": "receipt",
        "color": "#64748B"
    }
}

def categorize_usage(service: str, usage_type: str) -> str:
    """Classifies AWS usage into friendly operational categories."""
    svc = (service or "").lower()
    ut = (usage_type or "").lower()
    if "tax" in svc or "tax" in ut:
        return "tax_other"
    if "publicipv4" in ut:
        return "public_ipv4"
    if "boxusage" in ut or "hostusage" in ut or ("compute" in svc and "usage" in ut):
        return "server_runtime"
    if "ebs" in ut or "volumeusage" in ut or "snapshot" in ut:
        return "storage"
    if "datatransfer" in ut or "out-bytes" in ut or "in-bytes" in ut or "regional-bytes" in ut:
        return "bandwidth"
    return "tax_other"

def get_cost_data(months_back: int = 6, creds: Dict[str, str] = None) -> Dict[str, Any]:
    """Fetches Month-To-Date cost, category breakdown, daily breakdown, and historical monthly trends."""
    import boto3
    from collections import defaultdict

    ce = boto3.client(
        "ce",
        region_name="us-east-1", # Cost Explorer endpoint is always us-east-1
        aws_access_key_id=creds["aws_access_key_id"],
        aws_secret_access_key=creds["aws_secret_access_key"]
    )

    today = datetime.date.today()
    # Month to date: from 1st of current month to tomorrow (AWS CE requires End > Start)
    first_day_of_month = today.replace(day=1)
    tomorrow = today + datetime.timedelta(days=1)

    mtd_start_str = first_day_of_month.strftime("%Y-%m-%d")
    mtd_end_str = tomorrow.strftime("%Y-%m-%d")

    # 1. Current Month Daily Cost grouped by Service & UsageType
    daily_response = ce.get_cost_and_usage(
        TimePeriod={"Start": mtd_start_str, "End": mtd_end_str},
        Granularity="DAILY",
        Metrics=["UnblendedCost", "UsageQuantity"],
        GroupBy=[
            {"Type": "DIMENSION", "Key": "SERVICE"},
            {"Type": "DIMENSION", "Key": "USAGE_TYPE"}
        ]
    )

    total_mtd_amount = 0.0
    currency = "USD"
    category_totals = defaultdict(lambda: {"amount": 0.0, "quantities": defaultdict(float), "items": []})
    service_totals = defaultdict(float)
    daily_breakdown = []

    if daily_response.get("ResultsByTime"):
        for day_res in daily_response["ResultsByTime"]:
            day_str = day_res["TimePeriod"]["Start"]
            day_total = 0.0
            day_cats = defaultdict(float)
            day_items = []

            for group in day_res.get("Groups", []):
                amt = float(group.get("Metrics", {}).get("UnblendedCost", {}).get("Amount", 0.0))
                qty = float(group.get("Metrics", {}).get("UsageQuantity", {}).get("Amount", 0.0))
                unit = group.get("Metrics", {}).get("UsageQuantity", {}).get("Unit", "")
                keys = group.get("Keys", ["Unknown", "Unknown"])
                svc_name = keys[0] if len(keys) > 0 else "Unknown"
                usage_type = keys[1] if len(keys) > 1 else "Unknown"

                if amt > 0.00001:
                    cat = categorize_usage(svc_name, usage_type)
                    day_total += amt
                    day_cats[cat] += amt
                    total_mtd_amount += amt
                    service_totals[svc_name] += amt

                    category_totals[cat]["amount"] += amt
                    if unit:
                        category_totals[cat]["quantities"][unit] += qty
                    category_totals[cat]["items"].append({
                        "date": day_str,
                        "service": svc_name,
                        "usageType": usage_type,
                        "amount": round(amt, 4),
                        "quantity": round(qty, 2),
                        "unit": unit
                    })

                    day_items.append({
                        "service": svc_name,
                        "usageType": usage_type,
                        "category": cat,
                        "amount": round(amt, 4),
                        "quantity": round(qty, 2),
                        "unit": unit
                    })

            daily_breakdown.append({
                "date": day_str,
                "total": round(day_total, 4),
                "server_runtime": round(day_cats["server_runtime"], 4),
                "public_ipv4": round(day_cats["public_ipv4"], 4),
                "storage": round(day_cats["storage"], 4),
                "bandwidth": round(day_cats["bandwidth"], 4),
                "tax_other": round(day_cats["tax_other"], 4),
                "itemCount": len(day_items),
                "items": day_items
            })

    # Prepare formatted Category Breakdown list
    category_breakdown = []
    for cat_id, meta in CATEGORY_META.items():
        cdata = category_totals[cat_id]
        amt = round(cdata["amount"], 4)
        pct = round((amt / total_mtd_amount * 100), 1) if total_mtd_amount > 0 else 0.0
        qty_str = ", ".join(f"{q:.2f} {u}" for u, q in cdata["quantities"].items() if q > 0)
        category_breakdown.append({
            "id": cat_id,
            "name": meta["name"],
            "nameTh": meta["nameTh"],
            "icon": meta["icon"],
            "color": meta["color"],
            "amount": amt,
            "percentage": pct,
            "quantityText": qty_str if qty_str else "-",
            "itemCount": len(cdata["items"])
        })

    # Prepare high-level Service Breakdown list
    service_breakdown = []
    for svc_name, amt in service_totals.items():
        service_breakdown.append({
            "service": svc_name,
            "amount": round(amt, 4),
            "unit": currency
        })
    service_breakdown.sort(key=lambda x: x["amount"], reverse=True)

    # 2. Historical Monthly Cost (past N months)
    hist_year = first_day_of_month.year
    hist_month = first_day_of_month.month - (months_back - 1)
    while hist_month <= 0:
        hist_month += 12
        hist_year -= 1
    hist_start = datetime.date(hist_year, hist_month, 1).strftime("%Y-%m-%d")

    history_response = ce.get_cost_and_usage(
        TimePeriod={"Start": hist_start, "End": mtd_end_str},
        Granularity="MONTHLY",
        Metrics=["UnblendedCost"]
    )

    monthly_history = []
    if history_response.get("ResultsByTime"):
        for res in history_response["ResultsByTime"]:
            month_label = res["TimePeriod"]["Start"][:7] # YYYY-MM
            amt = float(res.get("Total", {}).get("UnblendedCost", {}).get("Amount", 0.0))
            monthly_history.append({
                "month": month_label,
                "amount": round(amt, 4),
                "currency": currency,
                "isCurrent": (month_label == first_day_of_month.strftime("%Y-%m"))
            })

    # Daily burn rate (current month)
    day_of_month = today.day
    daily_burn_rate = round(total_mtd_amount / max(1, day_of_month), 4)
    # Estimated month-end total (projected)
    import calendar
    days_in_current_month = calendar.monthrange(today.year, today.month)[1]
    projected_month_cost = round(daily_burn_rate * days_in_current_month, 4)

    return {
        "success": True,
        "currentMonth": first_day_of_month.strftime("%Y-%m"),
        "totalMtdCost": round(total_mtd_amount, 4),
        "currency": currency,
        "dailyBurnRate": daily_burn_rate,
        "projectedMonthCost": projected_month_cost,
        "categoryBreakdown": category_breakdown,
        "dailyBreakdown": daily_breakdown,
        "serviceBreakdown": service_breakdown,
        "monthlyHistory": monthly_history,
        "lastUpdated": datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    }

def main():
    parser = argparse.ArgumentParser(description="AWS Dashboard Bridge")
    parser.add_argument("--action", choices=["status", "cost", "all", "control"], default="all")
    parser.add_argument("--cmd", choices=["start", "stop"], help="Command for --action control")
    parser.add_argument("--instance-id", default="i-0dfe609694660b8d2")
    parser.add_argument("--region", default="eu-west-2")
    parser.add_argument("--months", type=int, default=6)
    parser.add_argument("--creds-file", default=r"d:\aws\dashboard-billing-reader_accessKeys.csv")

    args = parser.parse_args()

    try:
        creds = get_credentials(args.creds_file)
        result = {}

        if args.action == "control":
            if not args.cmd:
                raise ValueError("--cmd (start|stop) is required when --action is 'control'")
            result["control"] = control_ec2_instance(args.instance_id, args.cmd, args.region, creds)
        else:
            if args.action in ("status", "all"):
                result["instance"] = get_ec2_status(args.instance_id, args.region, creds)

            if args.action in ("cost", "all"):
                result["cost"] = get_cost_data(args.months, creds)

        result["success"] = True
        print(json.dumps(result, ensure_ascii=True, indent=2))

    except Exception as e:
        err_res = {
            "success": False,
            "error": str(e)
        }
        print(json.dumps(err_res, ensure_ascii=True, indent=2))
        sys.exit(1)

if __name__ == "__main__":
    main()
