<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คู่มือกลยุทธ์การเทรด</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mermaid/10.6.1/mermaid.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 10px;
            background: linear-gradient(90deg, #00d2ff 0%, #3a7bd5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            text-align: center;
            color: #aaa;
            margin-bottom: 40px;
            font-size: 1.2em;
        }

        .section {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .section h2 {
            color: #4a9eff;
            margin-bottom: 20px;
            font-size: 1.8em;
            border-bottom: 2px solid #4a9eff;
            padding-bottom: 10px;
        }

        .section h3 {
            color: #00d2ff;
            margin-top: 25px;
            margin-bottom: 15px;
            font-size: 1.4em;
        }

        .flowchart-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            overflow-x: auto;
        }

        .strategy-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .strategy-card {
            background: rgba(74, 158, 255, 0.1);
            border-left: 4px solid #4a9eff;
            padding: 20px;
            border-radius: 8px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .strategy-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(74, 158, 255, 0.3);
        }

        .strategy-card h4 {
            color: #00d2ff;
            margin-bottom: 10px;
            font-size: 1.2em;
        }

        .signal-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
        }

        .signal-box.buy {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .signal-box.sell {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }

        .indicator-list {
            list-style: none;
            padding-left: 0;
        }

        .indicator-list li {
            padding: 10px;
            margin: 8px 0;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 5px;
            border-left: 3px solid #4a9eff;
        }

        .indicator-list li::before {
            content: "✓ ";
            color: #00d2ff;
            font-weight: bold;
            margin-right: 8px;
        }

        .checklist {
            background: rgba(0, 210, 255, 0.1);
            border: 2px solid #00d2ff;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }

        .checklist h4 {
            color: #00d2ff;
            margin-bottom: 15px;
        }

        .checklist-item {
            padding: 10px;
            margin: 8px 0;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .emoji {
            font-size: 1.5em;
        }

        .warning-box {
            background: rgba(255, 152, 0, 0.1);
            border-left: 4px solid #ff9800;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .warning-box h4 {
            color: #ff9800;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            overflow: hidden;
        }

        th {
            background: rgba(74, 158, 255, 0.3);
            padding: 15px;
            text-align: left;
            color: #00d2ff;
            font-weight: 600;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .step {
            background: linear-gradient(135deg, rgba(74, 158, 255, 0.2) 0%, rgba(0, 210, 255, 0.2) 100%);
            padding: 20px;
            border-radius: 10px;
            margin: 15px 0;
            border-left: 5px solid #4a9eff;
        }

        .step-number {
            display: inline-block;
            background: #4a9eff;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            text-align: center;
            line-height: 35px;
            font-weight: bold;
            margin-right: 15px;
        }

        code {
            background: rgba(0, 0, 0, 0.3);
            padding: 2px 8px;
            border-radius: 4px;
            color: #00d2ff;
            font-family: 'Courier New', monospace;
        }

        .highlight {
            background: linear-gradient(90deg, rgba(255, 215, 0, 0.2) 0%, rgba(255, 152, 0, 0.2) 100%);
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎯 คู่มือกลยุทธ์การเทรด</h1>
        <p class="subtitle">Multiple Timeframe Analysis with Technical Indicators</p>
		<p> Code-->phpAllPredictAPI/clsTradingSignalAnalyzer.js</p>

        <!-- Flowchart หลัก -->
        <div class="section">
            <h2>📊 Flowchart: ขั้นตอนการวิเคราะห์และตัดสินใจเทรด</h2>
            <div class="flowchart-container">
                <div class="mermaid">
                    flowchart TD
                    Start([🚀 เริ่มต้นการวิเคราะห์]) --> TimeframeA[📈 วิเคราะห์ Graph A<br/>Timeframe ใหญ่]
                    
                    TimeframeA --> TrendCheck{ตรวจสอบเทรนด์หลัก}
                    
                    TrendCheck -->|ราคาเหนือ EMA 200| Uptrend[📈 Uptrend<br/>มองหา Buy Setup]
                    TrendCheck -->|ราคาใต้ EMA 200| Downtrend[📉 Downtrend<br/>มองหา Sell Setup]
                    TrendCheck -->|ราคาใกล้ EMA 200| Sideways[↔️ Sideways<br/>ระวัง False Signal]
                    
                    Uptrend --> CheckPivotBuy{ราคาใกล้แนวรับ?}
                    Downtrend --> CheckPivotSell{ราคาใกล้แนวต้าน?}
                    
                    CheckPivotBuy -->|ใกล้ S1/S2 หรือ Fib 38.2-61.8%| SupportZone[✅ อยู่ในโซนรับ]
                    CheckPivotBuy -->|ไม่ใกล้| WaitBuy[⏳ รอสัญญาณ]
                    
                    CheckPivotSell -->|ใกล้ R1/R2 หรือ Fib 38.2-61.8%| ResistanceZone[✅ อยู่ในโซนต้าน]
                    CheckPivotSell -->|ไม่ใกล้| WaitSell[⏳ รอสัญญาณ]
                    
                    SupportZone --> BuyIndicators[🔍 ตรวจสอบ Indicators สำหรับ Buy]
                    ResistanceZone --> SellIndicators[🔍 ตรวจสอบ Indicators สำหรับ Sell]
                    
                    BuyIndicators --> BuyChecklist{Checklist Buy<br/>3/5 เงื่อนไข?}
                    SellIndicators --> SellChecklist{Checklist Sell<br/>3/5 เงื่อนไข?}
                    
                    BuyChecklist -->|ผ่าน 3+ เงื่อนไข| ClickCandleB[👆 คลิกแท่งเทียนใน Graph A]
                    SellChecklist -->|ผ่าน 3+ เงื่อนไข| ClickCandleS[👆 คลิกแท่งเทียนใน Graph A]
                    
                    BuyChecklist -->|ไม่ผ่าน| WaitBuy
                    SellChecklist -->|ไม่ผ่าน| WaitSell
                    
                    ClickCandleB --> GraphB[📊 ดู Graph B<br/>ภายในแท่งที่เลือก]
                    ClickCandleS --> GraphB
                    
                    GraphB --> GraphC[🔮 ดู Graph C<br/>แท่งถัดไป 3-4 แท่ง]
                    
                    GraphC --> ConfirmPattern{ยืนยัน Pattern?}
                    
                    ConfirmPattern -->|Buy: Hammer, Bullish Engulfing<br/>เทรนด์ขาขึ้นใน B&C| ConfirmBuy[✅ ยืนยัน Buy Signal]
                    ConfirmPattern -->|Sell: Shooting Star, Bearish Engulfing<br/>เทรนด์ขาลงใน B&C| ConfirmSell[✅ ยืนยัน Sell Signal]
                    ConfirmPattern -->|Pattern ไม่ชัด| WaitMore[⏳ รอสัญญาณชัดเจนกว่า]
                    
                    ConfirmBuy --> ExecuteBuy[🟢 เข้า BUY<br/>Set Stop Loss & Take Profit]
                    ConfirmSell --> ExecuteSell[🔴 เข้า SELL<br/>Set Stop Loss & Take Profit]
                    
                    ExecuteBuy --> Monitor[👀 ติดตามการเทรด]
                    ExecuteSell --> Monitor
                    
                    Monitor --> End([🏁 จบการวิเคราะห์])
                    
                    WaitBuy --> Start
                    WaitSell --> Start
                    WaitMore --> Start
                    Sideways --> Start
                    
                    style Start fill:#4a9eff,stroke:#fff,stroke-width:3px,color:#fff
                    style ConfirmBuy fill:#38ef7d,stroke:#fff,stroke-width:2px,color:#000
                    style ConfirmSell fill:#f45c43,stroke:#fff,stroke-width:2px,color:#fff
                    style ExecuteBuy fill:#11998e,stroke:#fff,stroke-width:3px,color:#fff
                    style ExecuteSell fill:#eb3349,stroke:#fff,stroke-width:3px,color:#fff
                    style End fill:#667eea,stroke:#fff,stroke-width:3px,color:#fff
                </div>
            </div>
        </div>

        <!-- ขั้นตอนละเอียด -->
        <div class="section">
            <h2>📋 ขั้นตอนการวิเคราะห์แบบละเอียด</h2>
            
            <div class="step">
                <span class="step-number">1</span>
                <strong>วิเคราะห์ Timeframe ใหญ่ (Graph A)</strong>
                <ul class="indicator-list">
                    <li><strong>EMA 200:</strong> กำหนดทิศทางหลัก (เหนือ = Bullish, ใต้ = Bearish)</li>
                    <li><strong>EMA 3 & 5:</strong> ดูโมเมนตัมระยะสั้น (Cross = เปลี่ยนทิศทาง)</li>
                    <li><strong>Pivot Points:</strong> ระบุแนวรับ-ต้านสำคัญ (PP, S1-S3, R1-R3)</li>
                    <li><strong>Fibonacci:</strong> หาโซนที่แข็งแกร่ง (38.2%, 50%, 61.8%)</li>
                    <li><strong>Swing High/Low:</strong> จุดพลิกกลับที่เคยเกิดขึ้น</li>
                </ul>
            </div>

            <div class="step">
                <span class="step-number">2</span>
                <strong>ตรวจสอบ Confluence Zone (โซนทับซ้อน)</strong>
                <p style="margin-top: 10px;">หาจุดที่แนวรับ/ต้านหลายตัวมาบรรจบกัน = <span class="highlight">โอกาสสูง!</span></p>
                <ul class="indicator-list">
                    <li>ตัวอย่าง Buy Zone: S1 + Fib 61.8% + Swing Low + VWAP</li>
                    <li>ตัวอย่าง Sell Zone: R1 + Fib 38.2% + Swing High + BB Upper</li>
                </ul>
            </div>

            <div class="step">
                <span class="step-number">3</span>
                <strong>ยืนยันด้วย Indicators เสริม</strong>
                <ul class="indicator-list">
                    <li><strong>Bollinger Bands:</strong> ราคาแตะขอบบน/ล่าง = Overbought/Oversold</li>
                    <li><strong>ATR:</strong> ความผันผวนสูง = Risk สูง (ปรับ Position Size)</li>
                    <li><strong>Volume:</strong> Volume สูง = ยืนยันการเคลื่อนไหว</li>
                    <li><strong>VWAP:</strong> ราคาเหนือ VWAP = แรงซื้อ, ใต้ = แรงขาย</li>
                </ul>
            </div>

            <div class="step">
                <span class="step-number">4</span>
                <strong>คลิกแท่งเทียนใน Graph A → วิเคราะห์ Graph B & C</strong>
                <p style="margin-top: 10px;"><strong>Graph B (ภายในแท่ง):</strong> ดูรายละเอียดการเคลื่อนไหวภายในแท่ง A</p>
                <ul class="indicator-list">
                    <li>แท่งเทียนเป็นสีเขียวหรือแดงส่วนใหญ่?</li>
                    <li>มี Wick ยาวหรือไม่? (Rejection)</li>
                    <li>เกิด Pattern อะไร? (Hammer, Doji, Engulfing)</li>
                </ul>
                
                <p style="margin-top: 15px;"><strong>Graph C (แท่งถัดไป):</strong> ดูความต่อเนื่อง</p>
                <ul class="indicator-list">
                    <li>ราคายังคงทิศทางเดิมหรือไม่?</li>
                    <li>ทะลุเส้น Reference (ราคาปิด Graph B) หรือไม่?</li>
                    <li>Momentum แข็งแกร่งขึ้นหรือลง?</li>
                </ul>
            </div>

            <div class="step">
                <span class="step-number">5</span>
                <strong>ตัดสินใจเข้าเทรด</strong>
                <p style="margin-top: 10px;">ใช้ <span class="highlight">Checklist</span> ด้านล่างเพื่อยืนยันสัญญาณ</p>
            </div>
        </div>

        <!-- Buy Signal Checklist -->
        <div class="section">
            <h2>🟢 BUY SIGNAL CHECKLIST</h2>
            <div class="signal-box buy">
                <h3 style="color: white; margin: 0;">เงื่อนไขการเข้า Buy (ต้องผ่านอย่างน้อย 3/5)</h3>
            </div>

            <div class="checklist">
                <div class="checklist-item">
                    <span class="emoji">📈</span>
                    <div>
                        <strong>1. Trend อยู่ในทิศทาง Uptrend</strong>
                        <ul style="margin-left: 20px; margin-top: 5px;">
                            <li>ราคาเหนือ EMA 200</li>
                            <li>EMA 3 ตัด EMA 5 ขึ้น (Golden Cross)</li>
                            <li>ราคาเหนือ VWAP</li>
                        </ul>
                    </div>
                </div>

                <div class="checklist-item">
                    <span class="emoji">🎯</span>
                    <div>
                        <strong>2. ราคาอยู่ในโซนรับที่แข็งแกร่ง</strong>
                        <ul style="margin-left: 20px; margin-top: 5px;">
                            <li>แตะ Support Level: S1, S2, PP</li>
                            <li>ตรงกับ Fibonacci: 38.2%, 50%, 61.8%</li>
                            <li>ตรงกับ Swing Low</li>
                            <li>แตะ Bollinger Lower Band</li>
                        </ul>
                    </div>
                </div>

                <div class="checklist-item">
                    <span class="emoji">🕯️</span>
                    <div>
                        <strong>3. Candlestick Pattern บ่งชี้ Reversal ขาขึ้น</strong>
                        <ul style="margin-left: 20px; margin-top: 5px;">
                            <li>Hammer (ค้อน)</li>
                            <li>Bullish Engulfing (กลืนขาขึ้น)</li>
                            <li>Morning Star</li>
                            <li>Pin Bar ขาขึ้น</li>
                        </ul>
                    </div>
                </div>

                <div class="checklist-item">
                    <span class="emoji">📊</span>
                    <div>
                        <strong>4. Volume และ Momentum สนับสนุน</strong>
                        <ul style="margin-left: 20px; margin-top: 5px;">
                            <li>Volume เพิ่มขึ้นตอน Bounce</li>
                            <li>ATR แสดงความผันผวนปานกลาง-สูง</li>
                            <li>ไม่อยู่ในโซน Overbought</li>
                        </ul>
                    </div>
                </div>

                <div class="checklist-item">
                    <span class="emoji">🔮</span>
                    <div>
                        <strong>5. Graph B & C ยืนยันความต่อเนื่อง</strong>
                        <ul style="margin-left: 20px; margin-top: 5px;">
                            <li>Graph B: แท่งสีเขียวมากกว่าแดง</li>
                            <li>Graph C: ราคาทะลุเส้น Reference ขึ้นไป</li>
                            <li>Momentum ยังคงแข็งแกร่ง</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="warning-box">
                <h4>⚠️ Stop Loss & Take Profit สำหรับ Buy</h4>
                <ul style="margin-left: 20px;">
                    <li><strong>Stop Loss:</strong> ใต้ Swing Low หรือ S2 (Risk 1-2%)</li>
                    <li><strong>Take Profit 1:</strong> R1 หรือ Pivot Point (Risk:Reward = 1:1.5)</li>
                    <li><strong>Take Profit 2:</strong> R2 (Risk:Reward = 1:2)</li>
                    <li><strong>Take Profit 3:</strong> R3 หรือ Fib 161.8% (Risk:Reward = 1:3)</li>
                </ul>
            </div>
        </div>

        <!-- Sell Signal Checklist -->
        <div class="section">
            <h2>🔴 SELL SIGNAL CHECKLIST</h2>
            <div class="signal-box sell">
                <h3 style="color: white; margin: 0;">เงื่อนไขการเข้า Sell (ต้องผ่านอย่างน้อย 3/5)</h3>
            </div>

            <div class="checklist">
                <div class="checklist-item">
                    <span class="emoji">📉</span>
                    <div>
                        <strong>1. Trend อยู่ในทิศทาง Downtrend</strong>
                        <ul style="margin-left: 20px; margin-top: 5px;">
                            <li>ราคาใต้ EMA 200</li>
                            <li>EMA 3 ตัด EMA 5 ลง (Death Cross)</li>
                            <li>ราคาใต้ VWAP</li>
                        </ul>
                    </div>
                </div>

                <div class="checklist-item">
                    <span class="emoji">🎯</span>
                    <div>
                        <strong>2. ราคาอยู่ในโซนต้านที่แข็งแกร่ง</strong>
                        <ul style="margin-left: 20px; margin-top: 5px;">
                            <li>แตะ Resistance Level: R1, R2, PP</li>
                            <li>ตรงกับ Fibonacci: 38.2%, 50%, 61.8%</li>
                            <li>ตรงกับ Swing High</li>
                            <li>แตะ Bollinger Upper Band</li>
                        </ul>
                    </div>
                </div>

                <div class="checklist-item">
                    <span class="emoji">🕯️</span>
                    <div>
                        <strong>3. Candlestick Pattern บ่งชี้ Reversal ขาลง</strong>
                        <ul style="margin-left: 20px; margin-top: 5px;">
                            <li>Shooting Star (ดาวตก)</li>
                            <li>Bearish Engulfing (กลืนขาลง)</li>
                            <li>Evening Star</li>
                            <li>Pin Bar ขาลง</li>
                        </ul>
                    </div>
                </div>

                <div class="checklist-item">
                    <span class="emoji">📊</span>
                    <div>
                        <strong>4. Volume และ Momentum สนับสนุน</strong>
                        <ul style="margin-left: 20px; margin-top: 5px;">
                            <li>Volume เพิ่มขึ้นตอน Rejection</li>
                            <li>ATR แสดงความผันผวนปานกลาง-สูง</li>
                            <li>ไม่อยู่ในโซน Oversold</li>
                        </ul>
                    </div>
                </div>

                <div class="checklist-item">
                    <span class="emoji">🔮</span>
                    <div>
                        <strong>5. Graph B & C ยืนยันความต่อเนื่อง</strong>
                        <ul style="margin-left: 20px; margin-top: 5px;">
                            <li>Graph B: แท่งสีแดงมากกว่าเขียว</li>
                            <li>Graph C: ราคาทะลุเส้น Reference ลงมา</li>
                            <li>Momentum ยังคงอ่อนแอ</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="warning-box">
                <h4>⚠️ Stop Loss & Take Profit สำหรับ Sell</h4>
                <ul style="margin-left: 20px;">
                    <li><strong>Stop Loss:</strong> เหนือ Swing High หรือ R2 (Risk 1-2%)</li>
                    <li><strong>Take Profit 1:</strong> S1 หรือ Pivot Point (Risk:Reward = 1:1.5)</li>
                    <li><strong>Take Profit 2:</strong> S2 (Risk:Reward = 1:2)</li>
                    <li><strong>Take Profit 3:</strong> S3 หรือ Fib 161.8% (Risk:Reward = 1:3)</li>
                </ul>
            </div>
        </div>

        <!-- ตารางสรุป -->
        <div class="section">
            <h2>📊 ตารางสรุปสัญญาณ</h2>
            <table>
                <thead>
                    <tr>
                        <th>Indicator</th>
                        <th>Buy Signal 🟢</th>
                        <th>Sell Signal 🔴</th>
                        <th>Neutral ⚪</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>EMA 200</strong></td>
                        <td>ราคาเหนือ EMA 200</td>
                        <td>ราคาใต้ EMA 200</td>
                        <td>ราคาใกล้ EMA 200</td>
                    </tr>
                    <tr>
                        <td><strong>EMA 3/5 Cross</strong></td>
                        <td>EMA 3 ตัด EMA 5 ขึ้น</td>
                        <td>EMA 3 ตัด EMA 5 ลง</td>
                        <td>ไม่มี Cross</td>
                    </tr>
                    <tr>
                        <td><strong>Pivot Points</strong></td>
                        <td>ราคาแตะ S1, S2, S3</td>
                        <td>ราคาแตะ R1, R2, R3</td>
                        <td>ราคาอยู่ใกล้ PP</td>
                    </tr>
                    <tr>
                        <td><strong>Fibonacci</strong></td>
                        <td>ราคาแตะ Fib 38.2-61.8% (Retracement)</td>
                        <td>ราคาแตะ Fib 38.2-61.8% (Rejection)</td>
                        <td>ราคาไม่ใกล้ระดับ Fib</td>
                    </tr>
                    <tr>
                        <td><strong>Bollinger Bands</strong></td>
                        <td>ราคาแตะ Lower Band</td>
                        <td>ราคาแตะ Upper Band</td>
                        <td>ราคาอยู่ใกล้ Middle Band</td>
                    </tr>
                    <tr>
                        <td><strong>VWAP</strong></td>
                        <td>ราคาเหนือ VWAP + Bounce</td>
                        <td>ราคาใต้ VWAP + Rejection</td>
                        <td>ราคาตัด VWAP</td>
                    </tr>
                    <tr>
                        <td><strong>Volume</strong></td>
                        <td>Volume สูงตอน Bounce ขึ้น</td>
                        <td>Volume สูงตอน Rejection ลง</td>
                        <td>Volume ปานกลาง</td>
                    </tr>
                    <tr>
                        <td><strong>ATR</strong></td>
                        <td>ATR เพิ่มขึ้น = Volatility สูง</td>
                        <td>ATR เพิ่มขึ้น = Volatility สูง</td>
                        <td>ATR ต่ำ = ตลาด Quiet</td>
                    </tr>
                    <tr>
                        <td><strong>Candlestick</strong></td>
                        <td>Hammer, Bullish Engulfing</td>
                        <td>Shooting Star, Bearish Engulfing</td>
                        <td>Doji, Small Body</td>
                    </tr>
                    <tr>
                        <td><strong>Graph B & C</strong></td>
                        <td>แท่งเขียว + ทะลุ Reference ขึ้น</td>
                        <td>แท่งแดง + ทะลุ Reference ลง</td>
                        <td>ไม่มีทิศทางชัด</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- กลยุทธ์ขั้นสูง -->
        <div class="section">
            <h2>🚀 กลยุทธ์ขั้นสูง</h2>
            
            <div class="strategy-grid">
                <div class="strategy-card">
                    <h4>💎 Strategy 1: Triple Confluence</h4>
                    <p>หาจุดที่มีแนวรับ/ต้านอย่างน้อย 3 ตัวมาบรรจบ</p>
                    <p style="margin-top: 10px;"><strong>ตัวอย่าง Buy:</strong></p>
                    <ul style="margin-left: 20px; margin-top: 5px;">
                        <li>S1 + Fib 61.8% + Swing Low</li>
                        <li>Volume สูง</li>
                        <li>Hammer Candlestick</li>
                    </ul>
                    <p style="margin-top: 10px; color: #00d2ff;">→ High Probability Setup! 🎯</p>
                </div>

                <div class="strategy-card">
                    <h4>⚡ Strategy 2: Breakout + Retest</h4>
                    <p>รอให้ราคาทะลุแนวต้าน แล้วกลับมาทดสอบ (Retest)</p>
                    <p style="margin-top: 10px;"><strong>ขั้นตอน:</strong></p>
                    <ul style="margin-left: 20px; margin-top: 5px;">
                        <li>ราคาทะลุ R1 พร้อม Volume สูง</li>
                        <li>รอราคากลับมาทดสอบ R1</li>
                        <li>ถ้า Bounce จาก R1 → เข้า Buy</li>
                        <li>Target: R2, R3</li>
                    </ul>
                </div>

                <div class="strategy-card">
                    <h4>🎪 Strategy 3: BB Squeeze + Expansion</h4>
                    <p>ตลาด Consolidate แล้วเกิด Breakout แรง</p>
                    <p style="margin-top: 10px;"><strong>สังเกต:</strong></p>
                    <ul style="margin-left: 20px; margin-top: 5px;">
                        <li>Bollinger Bands แคบมาก</li>
                        <li>ATR ต่ำ (ตลาด Quiet)</li>
                        <li>เมื่อ BB เริ่มขยาย → เตรียมเข้า</li>
                        <li>ทิศทางตาม EMA Cross</li>
                    </ul>
                </div>

                <div class="strategy-card">
                    <h4>🌊 Strategy 4: VWAP Pullback</h4>
                    <p>ราคากลับมาทดสอบ VWAP แล้ว Bounce</p>
                    <p style="margin-top: 10px;"><strong>Buy Setup:</strong></p>
                    <ul style="margin-left: 20px; margin-top: 5px;">
                        <li>Uptrend: ราคาเหนือ VWAP</li>
                        <li>Pullback มาแตะ VWAP</li>
                        <li>เกิด Bullish Candle</li>
                        <li>Volume เพิ่มขึ้น → เข้า Buy</li>
                    </ul>
                </div>

                <div class="strategy-card">
                    <h4>🔄 Strategy 5: Multiple Timeframe Confirmation</h4>
                    <p>ใช้ข้อมูลจาก 3 Timeframes ร่วมกัน</p>
                    <p style="margin-top: 10px;"><strong>ขั้นตอน:</strong></p>
                    <ul style="margin-left: 20px; margin-top: 5px;">
                        <li>Graph A: ระบุทิศทางหลัก</li>
                        <li>Graph B: หาจุด Entry ที่ดี</li>
                        <li>Graph C: ยืนยันความต่อเนื่อง</li>
                        <li>ทั้ง 3 ต้องสอดคล้องกัน!</li>
                    </ul>
                </div>

                <div class="strategy-card">
                    <h4>📈 Strategy 6: ATR-Based Position Sizing</h4>
                    <p>ปรับขนาด Position ตาม Volatility</p>
                    <p style="margin-top: 10px;"><strong>สูตร:</strong></p>
                    <ul style="margin-left: 20px; margin-top: 5px;">
                        <li>ATR สูง → ลด Position Size</li>
                        <li>ATR ต่ำ → เพิ่ม Position Size</li>
                        <li>Stop Loss = 1.5-2 × ATR</li>
                        <li>Risk ต่อเทรดไม่เกิน 1-2%</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ข้อควรระวัง -->
        <div class="section">
            <h2>⚠️ ข้อควรระวังและข้อผิดพลาดที่พบบ่อย</h2>
            
            <div class="warning-box">
                <h4>❌ อย่าทำ:</h4>
                <ul style="margin-left: 20px;">
                    <li><strong>FOMO (Fear of Missing Out):</strong> อย่าเข้าเทรดเพราะกลัวพลาดโอกาส รอสัญญาณที่ชัดเจน</li>
                    <li><strong>Overtrading:</strong> เทรดมากเกินไป = เสียค่าธรรมเนียม + อารมณ์เสีย</li>
                    <li><strong>ไม่ใส่ Stop Loss:</strong> ต้องใส่ทุกครั้ง! ไม่มีข้อยกเว้น</li>
                    <li><strong>เพิกเฉยต่อเทรนด์ใหญ่:</strong> อย่า Counter Trend ถ้าไม่มีประสบการณ์</li>
                    <li><strong>ใช้ Indicator มากเกินไป:</strong> Analysis Paralysis = ตัดสินใจไม่ได้</li>
                    <li><strong>Revenge Trading:</strong> เสียแล้วพยายามคืนทุนทันที = เสียต่อ!</li>
                </ul>
            </div>

            <div class="warning-box" style="border-left-color: #4caf50; background: rgba(76, 175, 80, 0.1);">
                <h4 style="color: #4caf50;">✅ ควรทำ:</h4>
                <ul style="margin-left: 20px;">
                    <li><strong>มีแผนการเทรด:</strong> รู้จุดเข้า, Stop Loss, Take Profit ก่อนเทรด</li>
                    <li><strong>จดบันทึก:</strong> เขียน Trade Journal ทุกครั้ง</li>
                    <li><strong>Risk Management:</strong> เสี่ยงไม่เกิน 1-2% ต่อเทรด</li>
                    <li><strong>รอสัญญาณชัดเจน:</strong> ผ่าน Checklist อย่างน้อย 3/5</li>
                    <li><strong>ฝึกฝนใน Demo:</strong> ก่อนเทรดเงินจริง</li>
                    <li><strong>พักเบรก:</strong> เสียติด 2-3 เทรด = หยุด Review</li>
                </ul>
            </div>
        </div>

        <!-- Risk Management -->
        <div class="section">
            <h2>💰 Risk Management (สำคัญที่สุด!)</h2>
            
            <div class="step">
                <span class="step-number">1</span>
                <strong>กฎ 1% Rule</strong>
                <p style="margin-top: 10px;">อย่าเสี่ยงเกิน 1-2% ของ Account ต่อ 1 เทรด</p>
                <p style="margin-top: 5px;"><code>ตัวอย่าง: Account $10,000 → เสี่ยงไม่เกิน $100-200/เทรด</code></p>
            </div>

            <div class="step">
                <span class="step-number">2</span>
                <strong>Risk:Reward Ratio</strong>
                <p style="margin-top: 10px;">เทรดเฉพาะที่มี R:R อย่างน้อย <span class="highlight">1:1.5</span> แนะนำ <span class="highlight">1:2</span> หรือมากกว่า</p>
                <p style="margin-top: 5px;"><code>ตัวอย่าง: เสี่ยง $100 → ต้องได้กำไร $150-200</code></p>
            </div>

            <div class="step">
                <span class="step-number">3</span>
                <strong>Position Sizing Formula</strong>
                <p style="margin-top: 10px;">คำนวณขนาด Position อย่างถูกต้อง:</p>
                <div style="background: rgba(0,0,0,0.3); padding: 15px; border-radius: 8px; margin-top: 10px;">
                    <code style="display: block; font-size: 1.1em;">
                        Position Size = (Account Size × Risk %) / (Entry - Stop Loss)
                    </code>
                    <p style="margin-top: 10px; color: #aaa;">
                        ตัวอย่าง: Account $10,000, Risk 1%, Entry 1.2000, Stop Loss 1.1950<br>
                        Position Size = ($10,000 × 1%) / (1.2000 - 1.1950) = $100 / 0.005 = 20,000 units
                    </p>
                </div>
            </div>

            <div class="step">
                <span class="step-number">4</span>
                <strong>Maximum Daily Loss</strong>
                <p style="margin-top: 10px;">กำหนดขาดทุนสูงสุดต่อวัน: <span class="highlight">3-5% ของ Account</span></p>
                <p style="margin-top: 5px;">ถ้าถึงลิมิต → หยุดเทรดวันนั้น ไปพักผ่อน Review</p>
            </div>

            <div class="step">
                <span class="step-number">5</span>
                <strong>Take Partial Profits</strong>
                <p style="margin-top: 10px;">ไม่ต้องรอถึง Target สุดท้าย แบ่งขาย:</p>
                <ul style="margin-left: 20px; margin-top: 5px;">
                    <li>50% ที่ TP1 (1:1.5)</li>
                    <li>30% ที่ TP2 (1:2)</li>
                    <li>20% ที่ TP3 (1:3) หรือ Trailing Stop</li>
                </ul>
            </div>
        </div>

        <!-- ตัวอย่างการเทรดจริง -->
        <div class="section">
            <h2>📝 ตัวอย่างการเทรดจริง</h2>
            
            <div class="signal-box buy">
                <h3 style="color: white; margin: 0;">ตัวอย่าง BUY Setup</h3>
            </div>
            
            <div style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 10px; margin-top: 15px;">
                <p><strong>สถานการณ์:</strong> EUR/USD, Timeframe A = 15 นาที, Timeframe B = 5 นาที</p>
                
                <h4 style="color: #00d2ff; margin-top: 15px;">Graph A Analysis:</h4>
                <ul class="indicator-list">
                    <li>✅ ราคา 1.0850 เหนือ EMA 200 (1.0800) → Uptrend</li>
                    <li>✅ EMA 3 ตัด EMA 5 ขึ้นเมื่อ 3 แท่งก่อน</li>
                    <li>✅ ราคาดิ่งลงมาแตะ S1 (1.0840)</li>
                    <li>✅ S1 ตรงกับ Fib 61.8% → Confluence Zone!</li>
                    <li>✅ ราคาแตะ Bollinger Lower Band</li>
                    <li>✅ Volume เพิ่มขึ้นเมื่อราคาแตะ S1</li>
                </ul>

                <h4 style="color: #00d2ff; margin-top: 15px;">คลิกแท่งเทียนที่แตะ S1:</h4>
                <ul class="indicator-list">
                    <li>✅ Graph B: แสดง 3 แท่ง → 2 แท่งสีแดง + 1 แท่งสีเขียวแบบ Hammer</li>
                    <li>✅ Graph C: 3 แท่งถัดไป เป็นสีเขียวทั้งหมด!</li>
                    <li>✅ ราคาทะลุเส้น Reference (ราคาปิดของแท่งสุดท้าย Graph B)</li>
                </ul>

                <h4 style="color: #00d2ff; margin-top: 15px;">Checklist: 5/5 ผ่าน! ✅</h4>

                <div style="background: rgba(56, 239, 125, 0.2); padding: 15px; border-radius: 8px; margin-top: 15px; border-left: 4px solid #38ef7d;">
                    <h4 style="color: #38ef7d; margin-bottom: 10px;">🎯 Trade Plan:</h4>
                    <ul style="margin-left: 20px;">
                        <li><strong>Entry:</strong> 1.0845 (ปิดแท่งที่มี Hammer)</li>
                        <li><strong>Stop Loss:</strong> 1.0835 (ใต้ S1 และ Hammer Low)</li>
                        <li><strong>TP1:</strong> 1.0860 (PP) = 15 pips → R:R = 1:1.5</li>
                        <li><strong>TP2:</strong> 1.0870 (R1) = 25 pips → R:R = 1:2.5</li>
                        <li><strong>TP3:</strong> 1.0885 (R2) = 40 pips → R:R = 1:4</li>
                        <li><strong>Position Size:</strong> Risk $100, เสี่ยง 10 pips = 1 Standard Lot</li>
                    </ul>
                </div>
            </div>

            <div class="signal-box sell" style="margin-top: 30px;">
                <h3 style="color: white; margin: 0;">ตัวอย่าง SELL Setup</h3>
            </div>
            
            <div style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 10px; margin-top: 15px;">
                <p><strong>สถานการณ์:</strong> Volatility 75 Index, Timeframe A = 15 นาที, Timeframe B = 5 นาที</p>
                
                <h4 style="color: #00d2ff; margin-top: 15px;">Graph A Analysis:</h4>
                <ul class="indicator-list">
                    <li>✅ ราคา 850,000 ใต้ EMA 200 (855,000) → Downtrend</li>
                    <li>✅ EMA 3 ตัด EMA 5 ลงเมื่อ 2 แท่งก่อน</li>
                    <li>✅ ราคาพุ่งขึ้นมาแตะ R1 (852,000)</li>
                    <li>✅ R1 ตรงกับ Fib 50% → Confluence Zone!</li>
                    <li>✅ ราคาแตะ Bollinger Upper Band</li>
                    <li>✅ เกิด Shooting Star Candlestick</li>
                </ul>

                <h4 style="color: #00d2ff; margin-top: 15px;">คลิกแท่ง Shooting Star:</h4>
                <ul class="indicator-list">
                    <li>✅ Graph B: แสดง 3 แท่ง → 1 แท่งสีเขียว + 2 แท่งสีแดง</li>
                    <li>✅ Graph C: 4 แท่งถัดไป เป็นสีแดงทั้งหมด!</li>
                    <li>✅ ราคาทะลุเส้น Reference ลงมา</li>
                </ul>

                <h4 style="color: #00d2ff; margin-top: 15px;">Checklist: 5/5 ผ่าน! ✅</h4>

                <div style="background: rgba(244, 92, 67, 0.2); padding: 15px; border-radius: 8px; margin-top: 15px; border-left: 4px solid #f45c43;">
                    <h4 style="color: #f45c43; margin-bottom: 10px;">🎯 Trade Plan:</h4>
                    <ul style="margin-left: 20px;">
                        <li><strong>Entry:</strong> 850,500 (ปิดแท่ง Shooting Star)</li>
                        <li><strong>Stop Loss:</strong> 853,000 (เหนือ R1 และ Shooting Star High)</li>
                        <li><strong>TP1:</strong> 848,000 (PP) = 2,500 points → R:R = 1:1</li>
                        <li><strong>TP2:</strong> 845,000 (S1) = 5,500 points → R:R = 1:2.2</li>
                        <li><strong>TP3:</strong> 840,000 (S2) = 10,500 points → R:R = 1:4.2</li>
                        <li><strong>Position Size:</strong> Risk $200, เสี่ยง 2,500 points = 0.8 Lots</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- สรุป -->
        <div class="section">
            <h2>🎓 สรุป: หลักการเทรดที่ประสบความสำเร็จ</h2>
            
            <div class="step">
                <span class="step-number">📌</span>
                <strong>จำสูตรนี้:</strong>
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 10px; margin-top: 15px; text-align: center;">
                    <h3 style="color: white; font-size: 1.5em; margin-bottom: 10px;">
                        Trend + Confluence Zone + Candlestick Pattern<br>
                        + Volume Confirmation + MTF Validation<br>
                        = High Probability Trade! 🎯
                    </h3>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 30px;">
                <div class="signal-box">
                    <h4 style="margin: 0;">🎯 เป้าหมาย</h4>
                    <p style="margin-top: 10px;">Win Rate 50-60% + R:R 1:2 = Profitable!</p>
                </div>
                
                <div class="signal-box">
                    <h4 style="margin: 0;">🛡️ ป้องกัน</h4>
                    <p style="margin-top: 10px;">Stop Loss ทุกครั้ง + Risk 1-2% เท่านั้น</p>
                </div>
                
                <div class="signal-box">
                    <h4 style="margin: 0;">📚 พัฒนา</h4>
                    <p style="margin-top: 10px;">Trade Journal + Review + ปรับปรุงอย่างต่อเนื่อง</p>
                </div>
            </div>

            <div class="warning-box" style="margin-top: 30px; background: rgba(0, 210, 255, 0.1); border-left-color: #00d2ff;">
                <h4 style="color: #00d2ff;">💡 ข้อความสุดท้าย</h4>
                <p style="font-size: 1.1em; line-height: 1.8;">
                    การเทรดที่ประสบความสำเร็จไม่ได้มาจากการเทรดบ่อย แต่มาจาก<strong style="color: #00d2ff;"> การเทรดที่ถูกต้อง</strong>
                    รอสัญญาณที่ดี มีแผน จัดการความเสี่ยง และ<strong style="color: #00d2ff;"> ควบคุมอารมณ์</strong>
                    <br><br>
                    <strong style="color: #ffd700; font-size: 1.2em;">"Plan Your Trade, Trade Your Plan"</strong>
                </p>
            </div>
        </div>

        <div style="text-align: center; padding: 30px; color: #aaa;">
            <p style="font-size: 0.9em;">© 2025 Multiple Timeframe Analysis Strategy Guide</p>
            <p style="font-size: 0.8em; margin-top: 5px;">สร้างด้วย ❤️ สำหรับเทรดเดอร์ทุกคน</p>
        </div>
    </div>

    <script>
        mermaid.initialize({ 
            startOnLoad: true,
            theme: 'dark',
            flowchart: {
                useMaxWidth: true,
                htmlLabels: true,
                curve: 'basis'
            }
        });
    </script>
</body>
</html>