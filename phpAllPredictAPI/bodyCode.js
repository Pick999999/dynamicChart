// ฟังก์ชันแปลงเปอร์เซ็นต์เป็นรหัสแบบสัดส่วนที่รวมกันได้ 10
function percentToProportionalCode(upperPercent, bodyPercent, lowerPercent) {
    // แปลงเป็น scale 0-10 แล้วปัดลง
    let upper = Math.floor((upperPercent / 100) * 10);
    let body = Math.floor((bodyPercent / 100) * 10);
    let lower = Math.floor((lowerPercent / 100) * 10);

    // คำนวณส่วนเกิน
    let total = upper + body + lower;
    let remainder = 10 - total;

    // กระจายส่วนเกินไปให้ส่วนที่มีค่าสูงสุดก่อน
    if (remainder > 0) {
        // สร้าง array ของ [value, index] แล้วเรียงจากมากไปน้อย
        const parts = [
            {value: upperPercent, code: 'upper', index: 0},
            {value: bodyPercent, code: 'body', index: 1},
            {value: lowerPercent, code: 'lower', index: 2}
        ].sort((a, b) => b.value - a.value);

        // กระจายส่วนเกิน
        for (let i = 0; i < remainder && i < parts.length; i++) {
            if (parts[i].code === 'upper') upper++;
            else if (parts[i].code === 'body') body++;
            else if (parts[i].code === 'lower') lower++;
        }
    }

    return {
        upper: upper.toString(),
        body: body.toString(),
        lower: lower.toString()
    };
}

// ฟังก์ชันคำนวณ candleCode
function calculateCandleCode(candleData) {
    const { open, high, low, close, thisColor } = candleData;

    // คำนวณขนาดของ candle (high - low = 100%)
    const totalRange = high - low;

    // ป้องกันการหารด้วย 0
    if (totalRange === 0) {
        return "0-10-0-" + (thisColor === "Red" ? "R" : "G");
    }

    // หาค่า max และ min ของ open/close เพื่อคำนวณ body
    const bodyTop = Math.max(open, close);
    const bodyBottom = Math.min(open, close);

    // คำนวณขนาดของแต่ละส่วน
    const upperWickSize = high - bodyTop;
    const bodySize = bodyTop - bodyBottom;
    const lowerWickSize = bodyBottom - low;

    // คำนวณเปอร์เซ็นต์ของแต่ละส่วน
    const upperWickPercent = (upperWickSize / totalRange) * 100;
    const bodyPercent = (bodySize / totalRange) * 100;
    const lowerWickPercent = (lowerWickSize / totalRange) * 100;

    // แปลงเป็นรหัสแบบสัดส่วนที่รวมกันได้ 10
    const codes = percentToProportionalCode(upperWickPercent, bodyPercent, lowerWickPercent);

    // กำหนดรหัสสี
    const colorCode = thisColor === "Red" ? "R" : "G";

    // สร้าง candleCode ในรูปแบบ UpperWick-Body-LowerWick-Color
    return `${codes.upper}-${codes.body}-${codes.lower}-${colorCode}`;
}

// ฟังก์ชันหลักสำหรับเพิ่ม candleCode ให้กับ candle data
function addCandleCode(candleData) {
    const result = { ...candleData };
    result.candleCode = calculateCandleCode(candleData);
    return result;
}

// ฟังก์ชันสำหรับประมวลผล array ของ candle data
function processCandleArray(candleArray) {
    return candleArray.map(candle => addCandleCode(candle));
}

/*

// ตัวอย่างการใช้งาน
const sampleData = {
    "time": 1750737720,
    "open": 109760.9659,
    "high": 109778.6756,
    "low": 109507.1193,
    "close": 109521.3281,
    "thisColor": "Red"
};

// เรียกใช้ฟังก์ชัน
const result = addCandleCode(sampleData);
console.log(result);



// ตัวอย่างการใช้กับ array
// const candleArray = [sampleData];
// const processedArray = processCandleArray(candleArray);
*/