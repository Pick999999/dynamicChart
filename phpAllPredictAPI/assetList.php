<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deriv Assets ListBox</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        h1 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 24px;
        }
        
        .status {
            padding: 15px;
            background: #f3f4f6;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
            font-weight: 600;
        }
        
        .status.loading {
            color: #3b82f6;
        }
        
        .status.connected {
            color: #10b981;
        }
        
        .status.error {
            color: #ef4444;
        }
        
        .content {
            padding: 20px;
        }
        
        .market-group {
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .market-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .market-header:hover {
            opacity: 0.9;
        }
        
        .market-count {
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        
        .asset-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .asset-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s;
        }
        
        .asset-item:hover {
            background-color: #f9fafb;
        }
        
        .asset-item:last-child {
            border-bottom: none;
        }
        
        .asset-info {
            flex: 1;
        }
        
        .asset-symbol {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }
        
        .asset-name {
            font-size: 13px;
            color: #6b7280;
        }
        
        .asset-status {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-open {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-closed {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        
        .dot-open {
            background: #10b981;
            animation: pulse 2s infinite;
        }
        
        .dot-closed {
            background: #ef4444;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        
        .summary {
            padding: 20px;
            background: #f9fafb;
            border-top: 2px solid #e5e7eb;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 รายการ Assets ทั้งหมดจาก Deriv</h1>
        <div id="status" class="status loading">กำลังเชื่อมต่อ...</div>
        <div id="content" class="content"></div>
        <div id="summary" class="summary"></div>
    </div>

    <script>
        const APP_ID = '1089'; // ใช้ app_id สำหรับทดสอบ
        let ws = null;
        let allAssets = [];

        function connectWebSocket() {
            const statusEl = document.getElementById('status');
            statusEl.textContent = 'กำลังเชื่อมต่อ...';
            statusEl.className = 'status loading';

            ws = new WebSocket(`wss://ws.derivws.com/websockets/v3?app_id=${APP_ID}`);

            ws.onopen = () => {
                console.log('WebSocket connected');
                statusEl.textContent = 'เชื่อมต่อสำเร็จ - กำลังดึงข้อมูล...';
                statusEl.className = 'status connected';
                
                // ส่ง request เพื่อดึง active symbols
                const request = {
                    active_symbols: 'brief',
                    product_type: 'basic'
                };
                ws.send(JSON.stringify(request));
            };

            ws.onmessage = (event) => {
                const data = JSON.parse(event.data);
                
                if (data.error) {
                    console.error('Error:', data.error.message);
                    statusEl.textContent = `ข้อผิดพลาด: ${data.error.message}`;
                    statusEl.className = 'status error';
                    return;
                }

                if (data.msg_type === 'active_symbols') {
                    allAssets = data.active_symbols;
                    displayAssets(allAssets);
                    displaySummary(allAssets);
                    statusEl.textContent = `แสดงข้อมูล ${allAssets.length} Assets`;
                    statusEl.className = 'status connected';
                }
            };

            ws.onerror = (error) => {
                console.error('WebSocket error:', error);
                statusEl.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
                statusEl.className = 'status error';
            };

            ws.onclose = () => {
                console.log('WebSocket disconnected');
                statusEl.textContent = 'การเชื่อมต่อถูกปิด';
                statusEl.className = 'status error';
            };
        }

        function displayAssets(assets) {
            // จัดกลุ่ม assets ตาม market
            const groupedAssets = {};
            
            assets.forEach(asset => {
                const market = asset.market_display_name || 'อื่นๆ';
                if (!groupedAssets[market]) {
                    groupedAssets[market] = [];
                }
                groupedAssets[market].push(asset);
            });

            // สร้าง HTML
            const contentEl = document.getElementById('content');
            contentEl.innerHTML = '';

            Object.keys(groupedAssets).sort().forEach(market => {
                const marketAssets = groupedAssets[market];
                const openCount = marketAssets.filter(a => a.exchange_is_open === 1).length;
                
                const marketDiv = document.createElement('div');
                marketDiv.className = 'market-group';
                
                const headerDiv = document.createElement('div');
                headerDiv.className = 'market-header';
                headerDiv.innerHTML = `
                    <span>${market}</span>
                    <span class="market-count">${openCount}/${marketAssets.length} เปิด</span>
                `;
                
                const listDiv = document.createElement('div');
                listDiv.className = 'asset-list';
                
                // เรียง assets ตามสถานะ (เปิดก่อน)
                marketAssets.sort((a, b) => b.exchange_is_open - a.exchange_is_open);
                
                marketAssets.forEach(asset => {
                    const isOpen = asset.exchange_is_open === 1;
                    
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'asset-item';
                    itemDiv.innerHTML = `
                        <div class="asset-info">
                            <div class="asset-symbol">${asset.symbol}</div>
                            <div class="asset-name">${asset.display_name}</div>
                        </div>
                        <div class="asset-status">
                            <div class="status-dot ${isOpen ? 'dot-open' : 'dot-closed'}"></div>
                            <span class="status-badge ${isOpen ? 'status-open' : 'status-closed'}">
                                ${isOpen ? 'เปิดเทรด' : 'ปิดเทรด'}
                            </span>
                        </div>
                    `;
                    
                    listDiv.appendChild(itemDiv);
                });
                
                marketDiv.appendChild(headerDiv);
                marketDiv.appendChild(listDiv);
                contentEl.appendChild(marketDiv);
            });
        }

        function displaySummary(assets) {
            const totalAssets = assets.length;
            const openAssets = assets.filter(a => a.exchange_is_open === 1).length;
            const closedAssets = totalAssets - openAssets;
            const markets = new Set(assets.map(a => a.market_display_name)).size;

            const summaryEl = document.getElementById('summary');
            summaryEl.innerHTML = `
                <div class="summary-item">
                    <div class="summary-label">จำนวนตลาด</div>
                    <div class="summary-value">${markets}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Assets ทั้งหมด</div>
                    <div class="summary-value">${totalAssets}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">เปิดเทรด</div>
                    <div class="summary-value" style="color: #10b981">${openAssets}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">ปิดเทรด</div>
                    <div class="summary-value" style="color: #ef4444">${closedAssets}</div>
                </div>
            `;
        }

        // เชื่อมต่อเมื่อโหลดหน้าเว็บ
        window.addEventListener('load', () => {
            connectWebSocket();
        });
    </script>
</body>
</html>