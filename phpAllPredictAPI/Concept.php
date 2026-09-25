<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="Generator" content="EditPlus®">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <title>Document</title>
 </head>
 <body>
<h1>Concept ในการ เข้าจุดเทรด </h1>
<ol>
 <li>ทุกอย่าง ต้องมีช่วง เวลาเริ่มและสิ้นสุด  </li>
 <li> </li>
 <li> </li>
 <li> </li>
</ol>

ฉันกำลังศึกษา เรือง Multiple timeframe อยู่ ฉันต้องการให้ สร้าง 
graph candle stick 2 อัน โดยให้  
A เป็น timeframe ใหญ่ 
B เป็น timeframe เล็ก 
เมื่อเริ่มแรก ให้ทำการ load data historical จาก  deriv.com โดยมี asset List และ
timeframe A List ให้เลือกตั้งแต่ 3,5,10,15,30 นาที  และ ช่วง วันเวลาจาก dtpicker 2 อัน เมื่อทำการ Load ข้อมูลมาได้ให้ วาดกราฟ A + ema3+ema5 โดย เมือ click ที่แท่งเทียนใน กราฟ A แล้ว ให้วาด marker ในแท่งเทียนที่ลือก และให้ ทำการ วาดกราฟ  Candle Stick ใน
จากกราฟ B โดยมี timeframe ให้เลือก ตัวอย่างเช่น Graph A เป็น timeframe 15 นาที และ เลือก graph B ให้เป็น timeframe 5 นาที ก็จะทำการวาด Candle stick ใน graph b จำนวน 3 แท่ง ที่ข้อมูลตรงกันกับ Candle Stick ใน 
กราฟ A โดยใช้ pure javascript + https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js


 </body>
</html>
