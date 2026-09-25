# แผนงานการพัฒนา: ระบบสั่ง Start \/ Stop AWS EC2 Instance จาก Dashboard
**เอกสารแผนงานสำหรับรอบการทำงานถัดไป (Project Roadmap & Implementation Plan)**

---

## 1. บทนำและเป้าหมาย (Objective)
เพิ่มความสามารถให้ผู้ใช้งานสามารถ **สั่งเปิดเครื่อง (Start)** หรือ **สั่งปิดเครื่อง (Stop)** ของ AWS EC2 (`nutv99-aws-london` / `i-0dfe609694660b8d2` ใน Region `eu-west-2`) ได้โดยตรงจากหน้า **Vision UI Dashboard** (`billing.php` และ `vps_master.php`) เพื่อความสะดวกและช่วยประหยัดค่าใช้จ่ายชั่วโมงเปิดเครื่อง (EC2 Compute Runtime) ในช่วงเวลาที่ไม่ได้ทำการเทรด

---

## 2. สถาปัตยกรรมการทำงาน (Architecture Flow)

```
[ผู้ใช้งานกดปุ่ม Start / Stop บน Dashboard]
                 │
                 ▼
[Modal ยืนยันความปลอดภัย: ป้องกันการเผลอกดโดน]
                 │ (ยืนยัน)
                 ▼
[JavaScript Fetch POST ไปยัง: api_aws_billing.php?action=control_instance]
                 │
                 ▼
[PHP Backend เรียก: python aws_service.py --action control --cmd start/stop]
                 │
                 ▼
[AWS Boto3 Client สั่ง: ec2.start_instances() หรือ ec2.stop_instances()]
                 │
                 ▼
[AWS Cloud เปลี่ยนสถานะเครื่อง: pending / running หรือ stopping / stopped]
                 │
                 ▼
[Dashboard UI อัปเดตสถานะแบบ Real-time พร้อมแสดง Notification สำเร็จ]
```

---

## 3. สิ่งที่ต้องเตรียมการบน AWS Cloud (Prerequisites)

### 3.1 การเพิ่มสิทธิ์ใน AWS IAM (IAM Policy) ✅ [เรียบร้อยแล้ว]
IAM User (`dashboard-billing-reader`) ได้รับ Policy `EC2-StartStop-nutv99`:

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "AllowEC2StartStopSpecificInstance",
      "Effect": "Allow",
      "Action": [
        "ec2:StartInstances",
        "ec2:StopInstances"
      ],
      "Resource": "arn:aws:ec2:eu-west-2:*:instance/i-0dfe609694660b8d2"
    },
    {
      "Sid": "AllowEC2DescribeStatus",
      "Effect": "Allow",
      "Action": [
        "ec2:DescribeInstances",
        "ec2:DescribeInstanceStatus"
      ],
      "Resource": "*"
    }
  ]
}
```
> [!NOTE]
> ผ่านการทดสอบ DryRun Test เรียบร้อยแล้ว (สิทธิ์ถูกต้อง 100%)

---

## 4. รายละเอียดขั้นตอนการพัฒนาระบบ (Implementation Steps)

### ขั้นตอนที่ 1: ปรับปรุง Python AWS Bridge ([aws_service.py](file:///d:/Rust/dynamicChart/php/aws_service.py))
- เพิ่มฟังก์ชัน `control_ec2_instance(instance_id: str, action: str, region: str, creds)`
  - กรณี `action == "start"`: เรียก `ec2.start_instances(InstanceIds=[instance_id])`
  - กรณี `action == "stop"`: เรียก `ec2.stop_instances(InstanceIds=[instance_id])`
- เพิ่ม Parameter ใน `main()`:
  - `--action control`
  - `--cmd [start|stop]`
- ส่งสถานะการเปลี่ยนแปลงกลับมาในรูปแบบ JSON:
  ```json
  {
    "success": true,
    "action": "start",
    "previousState": "stopped",
    "currentState": "pending"
  }
  ```

---

### ขั้นตอนที่ 2: เพิ่ม Endpoint ใน Backend API ([api_aws_billing.php](file:///d:/Rust/dynamicChart/php/api_aws_billing.php))
- เพิ่มคำสั่ง `action === 'control_instance'`:
  - รับพารามิเตอร์ `command` (`start` หรือ `stop`)
  - ตรวจสอบความถูกต้องและดึง Instance ID ของ AWS จากฐานข้อมูล `vpsMaster`
  - สั่งรันคำสั่งผ่าน Python Bridge
  - อัปเดตสถานะชั่วคราวลงในตาราง `vpsMaster` (เช่น `pending` หรือ `stopping`)
  - ส่ง JSON Response ตอบกลับไปยังหน้าเว็บทันที

---

### ขั้นตอนที่ 3: ปรับปรุง UI หน้า Dashboard ([billing.php](file:///d:/Rust/dynamicChart/dashboard/pages/billing.php) & [vps_master.php](file:///d:/Rust/dynamicChart/dashboard/pages/vps_master.php))
1. **ปุ่มสั่งการทำงาน (Action Buttons):**
   - แสดงในกล่อง **"สถานะ AWS EC2 เครื่องหลัก"** ข้างๆ Badge สถานะ:
     - หากสถานะเป็น `stopped`: แสดงปุ่มสีเขียว **"▶ Start Server"**
     - หากสถานะเป็น `running`: แสดงปุ่มสีแดง **"⏹ Stop Server"**
     - หากสถานะเป็น `pending` หรือ `stopping`: แสดงปุ่มสีเทาและอนิเมชัน Loading
2. **Modal ยืนยันความปลอดภัย (Confirmation Modal):**
   - มีข้อความเตือนอย่างชัดเจน เช่น:
     > *"คำเตือน: หากกด Stop เซิร์ฟเวอร์และโปรแกรมเทรดที่กำลังทำงานอยู่จะหยุดลงทันที ยืนยันที่จะปิดเครื่องหรือไม่?"*
3. **Auto-Polling Status:**
   - หลังจากสั่ง Start หรือ Stop ให้ระบบทำการ Poll ตรวจเช็คสถานะทุก 5 วินาทีจนกว่าจะเปลี่ยนเป็น `running` หรือ `stopped` สำเร็จ แล้วอัปเดต Badge สีบนหน้าจอให้ทันที

---

### ขั้นตอนที่ 4: เชื่อมต่อระบบตั้งเวลาอัตโนมัติ (Auto-Schedule Integration)
- ต่อยอดเข้ากับ `vps_schedule_manager.php`:
  - ตั้งเวลาเปิดเครื่องก่อนตลาดเปิด (เช่น วันจันทร์ 05:00 น.)
  - ตั้งเวลาปิดเครื่องหลังตลาดปิดหรือวันหยุดสุดสัปดาห์ (เช่น วันเสาร์ 06:00 น.)
  - ช่วยลดค่า Server Runtime ได้สูงสุดถึง 25 - 30% ต่อเดือน

---

## 5. ประเด็นสำคัญด้านค่าใช้จ่ายและเทคนิค (Important Considerations)

1. **ค่าบริการที่หยุดคิดเงินเมื่อ Stop เครื่อง:**
   - `BoxUsage:t3.micro` (Compute Runtime) ➔ **หยุดคิดเงินทันที** ($0.00 ในช่วงที่ปิด)
2. **ค่าบริการที่ยังคงมีอยู่แม้จะ Stop เครื่องแล้ว:**
   - `EBS:VolumeUsage.gp3` (Storage Disk) ➔ ยังคงคิดเงินตามขนาดความจุที่จองไว้ (เช่น 30 GB)
3. **การจัดการหมายเลข Public IP เมื่อเปิดเครื่องใหม่:**
   - หากไม่ได้ผูก Elastic IP (Fixed IP) ทุกครั้งที่ Stop แล้ว Start ใหม่ AWS จะเปลี่ยนเลข Public IP ใหม่
   - ระบบ Dashboard จะมีระบบ **Auto-Refresh Public IP** เพื่อนำ IP ใหม่ไปอัปเดตในตาราง `vpsMaster` และลิงก์ Web Service ให้โดยอัตโนมัติ

---

## 6. สรุป Checklist สำหรับเริ่มงาน
- [x] เพิ่มสิทธิ์ `ec2:StartInstances` และ `ec2:StopInstances` ใน AWS IAM
- [x] ทดสอบสิทธิ์จริงผ่าน AWS DryRun API สำเร็จ 100%
- [x] เขียนฟังก์ชัน `control_ec2_instance` ใน `aws_service.py`
- [x] เพิ่ม Action `control_instance` และ `get_status` ใน `api_aws_billing.php`
- [x] ออกแบบปุ่ม Start/Stop และ Confirmation Modal สไตล์ Dark Glassmorphism ใน `billing.php` และ `vps_master.php`
- [x] ทดสอบสั่ง Start และ Stop จริงผ่านระบบ พร้อมบันทึกภาพผลการทำงาน

---

## 7. สรุปเปรียบเทียบค่าบริการ: เปิดตลอด 24 ชม. ทั้งเดือน VS สั่งปิดเครื่องตามรอบ

### 7.1 กรณีเปิดเครื่องทิ้งไว้ตลอด 24 ชม. ทั้งเดือน (30 วัน / 720 ชั่วโมง)
อ้างอิงจากยอดเรียกเก็บจริงของบัญชีในเดือนสิงหาคม 2026 (**$10.38 USD หรือประมาณ 368 บาท**):

| รายการค่าบริการ | อัตราค่าบริการโดยประมาณ | ยอดต่อเดือน (USD) | ยอดต่อเดือน (บาท) |
| :--- | :--- | :--- | :--- |
| **1. ค่าชั่วโมงเปิดเครื่อง (EC2 Compute: `t3.micro`)** | ~$0.0104 / ชั่วโมง | **~$7.50** | ~265 บาท |
| **2. ค่า Public IPv4 Address** | $0.005 / ชั่วโมง | **~$3.60** | ~127 บาท |
| **3. ค่าพื้นที่ Disk (EBS gp3 Storage ~10–20 GB)** | ~$0.08 / GB-Month | **~$0.80 – $1.60** | ~30 – 55 บาท |
| **4. ภาษีมูลค่าเพิ่ม (VAT 7%)** | 7% | **~$0.80** | ~28 บาท |
| **รวมทั้งสิ้นโดยประมาณ** | | **~$10.00 – $13.00** | **~350 – 450 บาท/เดือน** |

*เฉลี่ยตกวันละประมาณ **12 – 15 บาท***

---

### 7.2 ข้อดี-ข้อจำกัด และผลกระทบต่อระบบ (Operational Impact)

| หัวข้อ | เปิดตลอดทั้งเดือน (24/7) | สั่ง Stop / Start เครื่อง |
| :--- | :--- | :--- |
| **ค่าใช้จ่ายต่อเดือน** | ~$10 – $13 (350–450 บาท) | ~$5 – $8 (175–280 บาท) ประหยัดค่า EC2 Compute ได้บางส่วน |
| **ความเสี่ยงการเทรด** | **0%** (บอททำงานต่อเนื่อง ไม่พลาดสัญญาณ) | มีความเสี่ยงหากลืม Start หรือสตาร์ทไม่ทันตลาด |
| **พฤติกรรมของ Public IP** | **IP คงที่เดิมตลอดเวลา** ไม่เปลี่ยน | **IP เปลี่ยนทุกครั้ง** ที่ Stop แล้ว Start ใหม่ |
| **ผลกระทบต่อ Cloudflare (`pkderiv.online`)** | **เข้าเว็บได้ตลอด 24 ชม.** ไม่มี Error | เกิด **Error 522** ทันที หากไม่ได้อัปเดต IP ใหม่ใน Cloudflare DNS |
| **ผลกระทบต่อ WinSCP** | **เข้าได้ตลอดเวลา** ไม่ต้องแก้ IP | ต้องเปลี่ยน Host Name (IP ใหม่) ใน WinSCP ทุกครั้งที่เปิดเครื่อง |

---

### 7.3 แนวทางจัดการเรื่อง IP เมื่อต้องการ Stop/Start เครื่อง
1. **ทางเลือกที่ 1: ผูก Elastic IP (Static IP) บน AWS**
   - **ข้อดี:** IP จะถูกล็อกตายตัวถาวร ไม่เปลี่ยนอีกเลยทั้งสำหรับโดเมน `pkderiv.online` และ WinSCP
   - **ค่าใช้จ่าย:** ฟรีขณะเปิดเครื่อง (ตอนปิดเครื่องมีค่าจอง IP $0.005/ชม. หรือ ~3.60 บาท/วัน)
2. **ทางเลือกที่ 2: ติดตั้ง Cloudflare Tunnel (`cloudflared`)**
   - **ข้อดี:** ฟรี 100% หน้าเว็บ `pkderiv.online` จะชี้เข้าหาเครื่องได้ตลอดเวลาแม้ Public IP จะเปลี่ยนไป
   - **ผลต่อ WinSCP:** WinSCP ยังใช้ได้ปกติผ่าน Public IP (หรือตั้งค่าให้ WinSCP วิ่งผ่าน Tunnel ได้)
3. **ทางเลือกที่ 3: เปิดเครื่องทิ้งไว้ 24/7**
   - **ข้อดี:** ง่ายที่สุด ไม่ต้องติดตั้งหรือตั้งค่าอะไรเพิ่มเติม ค่าใช้จ่ายทั้งเดือนเพียง ~350 - 450 บาท (วันละ 12 บาท)

