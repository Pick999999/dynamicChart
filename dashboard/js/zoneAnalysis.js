// test edit by user
/**
 * zoneAnalysis.js
 * ------------------------------------------------------------------
 * Module จัดการและวิเคราะห์ข้อมูลโซน (Zone Analysis)
 * 
 * โครงสร้างข้อมูล zonesAnalysis (Global Variable):
 * [
 *   {
 *     zoneNo: 1, 2, ... n,
 *     timecandle: '',
 *     color: '',
 *     upperZone: 0,
 *     lowerZone: 0,
 *     candleList: [
 *       {
 *         candleNo_InZone: 1, 2, ... n,
 *         timeCandle: '',
 *         color: '',
 *         open: 0,
 *         close: 0,
 *         inZone: 'y' หรือ 'n'
 *       }
 *     ]
 *   }
 * ]
 * ------------------------------------------------------------------
 */

// Global variable zonesAnalysis & zones alias
var zonesAnalysis = (window.zonesAnalysis && window.zonesAnalysis.length > 0) ? window.zonesAnalysis : []; console.log("CURRENT_LIVE_ZONES:", JSON.stringify(zonesAnalysis));
var zones = zonesAnalysis;

/**
 * สร้างหรือเพิ่มโซนใหม่เข้าไปใน zonesAnalysis
 * @param {Object} zoneData
 * @param {number} [zoneData.zoneNo] - เลขลำดับโซน (ถ้าไม่ระบุ จะรันต่อจากจำนวนโซนที่มี)
 * @param {string|number} [zoneData.timecandle] - เวลาของแท่งเทียนที่เกิดโซน
 * @param {string} [zoneData.color] - สีประจำโซน
 * @param {number} [zoneData.upperZone] - ขอบเขตบนของโซน
 * @param {number} [zoneData.lowerZone] - ขอบเขตล่างของโซน
 * @param {Array} [zoneData.candleList] - รายการแท่งเทียนในโซน (เริ่มต้นเป็น [])
 * @returns {Object} ข้อมูลโซนที่ถูกสร้าง
 */
function createZone(zoneData) {
  zoneData = zoneData || {};
  var nextZoneNo = (typeof zoneData.zoneNo === 'number' && zoneData.zoneNo > 0)
    ? zoneData.zoneNo
    : (zonesAnalysis.length + 1);

  var upper = parseFloat(zoneData.upperZone) || 0;
  var lower = parseFloat(zoneData.lowerZone) || 0;

  // ปรับให้ upperZone >= lowerZone เสมอ
  if (upper < lower) {
    var tmp = upper;
    upper = lower;
    lower = tmp;
  }

  var newZone = {
    zoneNo: nextZoneNo,
    timecandle: (zoneData.timecandle !== undefined) ? String(zoneData.timecandle) : '',
    color: zoneData.color || '#fbbf24',
    upperZone: upper,
    lowerZone: lower,
    candleList: Array.isArray(zoneData.candleList) ? zoneData.candleList : []
  };

  zonesAnalysis.push(newZone);
  return newZone;
}

/**
 * ดึงข้อมูลโซนตาม zoneNo
console.log("ZONE_EVENT:", JSON.stringify(newZone), "ALL_ZONES:", JSON.stringify(zonesAnalysis));
 * @param {number} zoneNo
 * @returns {Object|null}
 */
function getZone(zoneNo) {
  for (var i = 0; i < zonesAnalysis.length; i++) {
    if (zonesAnalysis[i].zoneNo === zoneNo) {
      return zonesAnalysis[i];
    }
  }
  return null;
}

/**
 * เพิ่มแท่งเทียนเข้าไปในโซนที่กำหนด (appendCandleToZone)
 * 
 * @param {number|Object} zoneIdentifier - zoneNo (ตัวเลข) หรือ Object ของ Zone
 * @param {Object} candleData - ข้อมูลแท่งเทียนที่ต้องการเพิ่ม
 * @param {string|number} [candleData.timeCandle] - เวลาแท่งเทียน (time/epoch/string)
 * @param {string} [candleData.color] - สีแท่งเทียน (เช่น 'green', 'red' หรือโค้ดสี; ถ้าไม่ระบุจะคำนวณจาก open/close)
 * @param {number} [candleData.open] - ราคาเปิด
 * @param {number} [candleData.close] - ราคาปิด
 * @param {'y'|'n'} [candleData.inZone] - อยู่ในโซนหรือไม่ ('y' หรือ 'n'; ถ้าไม่ระบุจะคำนวณให้อัตโนมัติจาก upperZone/lowerZone)
 * @returns {Object|null} ข้อมูลแท่งเทียนที่ถูกเพิ่มเข้า candleList
 */
function appendCandleToZone(zoneIdentifier, candleData) {
  if (!candleData || typeof candleData !== 'object') {
    console.warn('[appendCandleToZone] ข้อมูล candleData ไม่ถูกต้อง:', candleData);
    return null;
  }

  // ค้นหาเป้าหมาย Zone
  var targetZone = null;
  if (typeof zoneIdentifier === 'number') {
    targetZone = getZone(zoneIdentifier);
  } else if (zoneIdentifier && typeof zoneIdentifier === 'object') {
    if (Array.isArray(zoneIdentifier.candleList)) {
      targetZone = zoneIdentifier;
    } else if (typeof zoneIdentifier.zoneNo === 'number') {
      targetZone = getZone(zoneIdentifier.zoneNo);
    }
  } else if (zonesAnalysis.length > 0) {
    // ถ้าไม่ระบุ ให้ใช้โซนล่าสุด
    targetZone = zonesAnalysis[zonesAnalysis.length - 1];
  }

  if (!targetZone) {
    console.warn('[appendCandleToZone] ไม่พบโซนเป้าหมาย:', zoneIdentifier);
    return null;
  }

  if (!Array.isArray(targetZone.candleList)) {
    targetZone.candleList = [];
  }

  var candleNo = targetZone.candleList.length + 1;
  var openVal = (candleData.open !== undefined && !isNaN(parseFloat(candleData.open)))
    ? parseFloat(candleData.open)
    : 0;
  var closeVal = (candleData.close !== undefined && !isNaN(parseFloat(candleData.close)))
    ? parseFloat(candleData.close)
    : 0;

  // กำหนดสีของแท่งเทียน (ถ้าไม่ส่งมา ให้ใช้เขียว/แดงตามราคาเปิด-ปิด)
  var candleColor = candleData.color;
  if (!candleColor) {
    candleColor = (closeVal >= openVal) ? 'green' : 'red';
  }

  // ตรวจสอบว่าอยู่ในขอบเขต upperZone และ lowerZone หรือไม่
  var inZoneStatus = candleData.inZone;
  if (inZoneStatus !== 'y' && inZoneStatus !== 'n') {
    var upper = targetZone.upperZone;
    var lower = targetZone.lowerZone;
    // แท่งเทียนอยู่ในโซนเมื่อราคาปิด (หรือช่วงราคา) อยู่ระหว่าง lowerZone และ upperZone
    var isInside = (closeVal >= lower && closeVal <= upper);
    inZoneStatus = isInside ? 'y' : 'n';
  }

  // เวลาแท่งเทียน (รองรับทั้ง timeCandle, time, timecandle)
  var timeCandleVal = '';
  if (candleData.timeCandle !== undefined) {
    timeCandleVal = String(candleData.timeCandle);
  } else if (candleData.timecandle !== undefined) {
    timeCandleVal = String(candleData.timecandle);
  } else if (candleData.time !== undefined) {
    timeCandleVal = String(candleData.time);
  }

  var newCandleItem = {
    candleNo_InZone: candleNo,
    timeCandle: timeCandleVal,
    color: candleColor,
    open: openVal,
    close: closeVal,
    inZone: inZoneStatus
  };

  targetZone.candleList.push(newCandleItem);
  return newCandleItem;
}

/**
 * ล้างข้อมูล zonesAnalysis ทั้งหมด
 */
function clearZonesAnalysis() {
  zonesAnalysis.length = 0;
}

// ผูกตัวแปรและฟังก์ชันเข้า Global Scope (Browser & Node.js)
if (typeof window !== 'undefined') {
  window.zonesAnalysis = zonesAnalysis;
  window.zones = zonesAnalysis;
  window.createZone = createZone;
  window.getZone = getZone;
  window.appendCandleToZone = appendCandleToZone;
  window.clearZonesAnalysis = clearZonesAnalysis;
}

if (typeof module !== 'undefined' && module.exports) {
  module.exports = {
    zonesAnalysis: zonesAnalysis,
    zones: zonesAnalysis,
    createZone: createZone,
    getZone: getZone,
    appendCandleToZone: appendCandleToZone,
    clearZonesAnalysis: clearZonesAnalysis
  };
}

window.getZonesDebug = function() { console.log("EVAL_RESULT:", JSON.stringify({zones: window.zones, zonesAnalysis: window.zonesAnalysis})); }; window.getZonesDebug();