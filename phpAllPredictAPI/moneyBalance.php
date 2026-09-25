<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deriv.com API Functions</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .api-section {
            margin-bottom: 40px;
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        
        .api-section:hover {
            transform: translateY(-5px);
        }
        
        .api-section h2 {
            color: #4a5568;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        .input-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2d3748;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        button {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .table-container {
            margin-top: 20px;
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        th {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        tbody tr:hover {
            background: #f7fafc;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .error {
            color: #e53e3e;
            background: #fed7d7;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
        
        .success {
            color: #38a169;
            background: #c6f6d5;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
        
        .status {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            font-weight: 500;
        }
        
        .websocket-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .connected {
            background: #c6f6d5;
            color: #38a169;
        }
        
        .disconnected {
            background: #fed7d7;
            color: #e53e3e;
        }
        
        .debug-log {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Deriv.com API Functions</h1>
        
        <!-- API Token Input -->
        <div class="api-section">
            <h2>API Configuration</h2>
            <div class="input-group">
                <label for="apiToken">API Token:</label>
                <input type="password" id="apiToken" placeholder="Enter your Deriv API token" value='lt5UMO6bNvmZQaR'>
            </div>
            <div class="input-group">
                <label for="appId">App ID:</label>
                <input type="text" id="appId" placeholder="Enter your App ID (default: 1089)" value="66726">
            </div>
            <div class="websocket-status disconnected" id="wsStatus">Disconnected</div>
            <button onclick="connectWebSocket()">Connect WebSocket</button>
            <button onclick="disconnectWebSocket()">Disconnect</button>
            <div id="debugLog" class="debug-log" style="display: none;">Debug Log:</div>
            <button onclick="toggleDebugLog()">Toggle Debug Log</button>
        </div>
        
        <!-- Reality Check API -->
        <div class="api-section">
            <h2>1. Reality Check API</h2>
            <p>ดึงข้อมูลสรุปการซื้อขาย รวมถึงเวลาที่ใช้และกำไร/ขาดทุน</p>
            <button onclick="getRealityCheck()">Get Reality Check</button>
            <div id="realityStatus"></div>
            <div class="table-container">
                <table id="realityTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>Start Time</th>
                            <th>Total Turnover</th>
                            <th>Total Profit/Loss</th>
                            <th>Total Trades</th>
                            <th>Session Duration</th>
                        </tr>
                    </thead>
                    <tbody id="realityTableBody"></tbody>
                </table>
            </div>
        </div>
        
        <!-- Statement API -->
        <div class="api-section">
            <h2>2. Statement API</h2>
            <p>ดึงข้อมูลรายงานบัญชี (Account Statements)</p>
            <div class="input-group">
                <label for="statementAction">Action Type:</label>
                <select id="statementAction">
                    <option value="buy">Buy</option>
                    <option value="sell">Sell</option>
                    <option value="deposit">Deposit</option>
                    <option value="withdrawal">Withdrawal</option>
                    <option value="">All</option>
                </select>
            </div>
            <div class="input-group">
                <label for="statementLimit">Limit:</label>
                <input type="number" id="statementLimit" value="50" min="1" max="999">
            </div>
            <button onclick="getStatement()">Get Statement</button>
            <div id="statementStatus"></div>
            <div class="table-container">
                <table id="statementTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Action</th>
                            <th>Amount</th>
                            <th>Balance</th>
                            <th>Transaction ID</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody id="statementTableBody"></tbody>
                </table>
            </div>
        </div>
        
        <!-- Transaction Stream API -->
        <div class="api-section">
            <h2>3. Transaction Stream API</h2>
            <p>ติดตามการทำรายการแบบ real-time</p>
            <button onclick="subscribeTransactions()">Subscribe to Transactions</button>
            <button onclick="unsubscribeTransactions()">Unsubscribe</button>
            <div id="transactionStatus"></div>
            <div class="table-container">
                <table id="transactionTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Action</th>
                            <th>Amount</th>
                            <th>Balance After</th>
                            <th>Transaction ID</th>
                        </tr>
                    </thead>
                    <tbody id="transactionTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let ws = null;
        let isConnected = false;
        let isAuthorized = false;
        let transactionSubscribed = false;

        // Debug logging
        function addDebugLog(message) {
            const debugLog = document.getElementById('debugLog');
            const timestamp = new Date().toLocaleTimeString();
            debugLog.innerHTML += `<div>${timestamp}: ${message}</div>`;
            debugLog.scrollTop = debugLog.scrollHeight;
        }

        function toggleDebugLog() {
            const debugLog = document.getElementById('debugLog');
            debugLog.style.display = debugLog.style.display === 'none' ? 'block' : 'none';
        }

        // WebSocket connection
        function connectWebSocket() {
            const apiToken = document.getElementById('apiToken').value;
            if (!apiToken) {
                showStatus('wsStatus', 'Please enter API token first', 'error');
                addDebugLog('ERROR: No API token provided');
                return;
            }

            const appId = document.getElementById('appId').value || '1089';
            const wsUrl = `wss://ws.binaryws.com/websockets/v3?app_id=${appId}`;
            
            addDebugLog(`Connecting to: ${wsUrl}`);
            updateWSStatus('disconnected', 'Connecting...');
            
            ws = new WebSocket(wsUrl);
            
            ws.onopen = function() {
                addDebugLog('WebSocket connected successfully');
                updateWSStatus('connected', 'Connected - Authorizing...');
                isConnected = true;
                // Authorize immediately after connection
                authorizeAPI();
            };
            
            ws.onclose = function(event) {
                addDebugLog(`WebSocket closed. Code: ${event.code}, Reason: ${event.reason}`);
                isConnected = false;
                isAuthorized = false;
                updateWSStatus('disconnected', 'Disconnected');
                showStatus('wsStatus', 'WebSocket connection closed', 'error');
            };
            
            ws.onerror = function(error) {
                addDebugLog(`WebSocket error: ${error}`);
                showStatus('wsStatus', 'WebSocket error: ' + error, 'error');
            };
            
            ws.onmessage = function(event) {
                addDebugLog(`Received: ${event.data}`);
                const data = JSON.parse(event.data);
                handleWebSocketMessage(data);
            };
        }
        
        function disconnectWebSocket() {
            if (ws && isConnected) {
                addDebugLog('Disconnecting WebSocket');
                ws.close();
                transactionSubscribed = false;
                isAuthorized = false;
            }
        }
        
        function updateWSStatus(className, text) {
            const statusElement = document.getElementById('wsStatus');
            statusElement.className = `websocket-status ${className}`;
            statusElement.textContent = text;
        }

        // Authorize API
        function authorizeAPI() {
            const apiToken = document.getElementById('apiToken').value;
            
            const request = {
                authorize: apiToken,
                passthrough: {},
                req_id: Date.now()
            };

            addDebugLog(`Sending authorize request with token: ${apiToken.substring(0, 10)}...`);
            ws.send(JSON.stringify(request));
        }

        // Handle WebSocket messages
        function handleWebSocketMessage(data) {
            addDebugLog(`Handling message type: ${data.msg_type || 'unknown'}`);
            
            if (data.msg_type === 'authorize') {
                if (data.error) {
                    addDebugLog(`Authorization failed: ${JSON.stringify(data.error)}`);
                    showStatus('wsStatus', 'Authorization failed: ' + data.error.message, 'error');
                    isAuthorized = false;
                    updateWSStatus('disconnected', 'Auth Failed');
                } else {
                    addDebugLog('Authorization successful');
                    isAuthorized = true;
                    updateWSStatus('connected', 'Authorized ✓');
                    showStatus('wsStatus', 'API authorized successfully! Ready to use.', 'success');
                }
            } else if (data.msg_type === 'reality_check') {
                addDebugLog('Received reality check data');
                displayRealityCheck(data);
            } else if (data.msg_type === 'statement') {
                addDebugLog('Received statement data');
                displayStatement(data);
            } else if (data.msg_type === 'transaction') {
                addDebugLog('Received transaction data');
                displayTransaction(data);
            } else if (data.error) {
                const errorMsg = data.error.message || 'Unknown error';
                addDebugLog(`API Error: ${JSON.stringify(data.error)}`);
                
                // Show error in appropriate status based on request type
                showStatus('realityStatus', 'Error: ' + errorMsg, 'error');
                showStatus('statementStatus', 'Error: ' + errorMsg, 'error');
                showStatus('transactionStatus', 'Error: ' + errorMsg, 'error');
            }
        }

        // 1. Reality Check API
        function getRealityCheck() {
            if (!checkConnection()) return;
            if (!checkAuthorization()) return;

            showStatus('realityStatus', 'Loading reality check data...', 'loading');
            
            const request = {
                reality_check: 1,
                passthrough: { type: 'reality_check' },
                req_id: Date.now()
            };

            addDebugLog(`Sending reality check request: ${JSON.stringify(request)}`);
            ws.send(JSON.stringify(request));
        }

        function displayRealityCheck(data) {
            if (data.error) {
                addDebugLog(`Reality check error: ${JSON.stringify(data.error)}`);
                showStatus('realityStatus', 'Error: ' + data.error.message, 'error');
                return;
            }

            const reality = data.reality_check;
            const tableBody = document.getElementById('realityTableBody');
            const table = document.getElementById('realityTable');
            
            tableBody.innerHTML = '';
            
            const row = tableBody.insertRow();
            row.innerHTML = `
                <td>${new Date(reality.start_time * 1000).toLocaleString('th-TH')}</td>
                <td>$${(reality.turnover || 0).toFixed(2)}</td>
                <td>$${(reality.profit_loss || 0).toFixed(2)}</td>
                <td>${reality.num_contracts || 0}</td>
                <td>${formatDuration(reality.session_duration || 0)}</td>
            `;
            
            table.style.display = 'table';
            showStatus('realityStatus', 'Reality check data loaded successfully', 'success');
        }

        // 2. Statement API
        function getStatement() {
            if (!checkConnection()) return;
            if (!checkAuthorization()) return;

            showStatus('statementStatus', 'Loading statement data...', 'loading');
            
            const action = document.getElementById('statementAction').value;
            const limit = parseInt(document.getElementById('statementLimit').value) || 50;
            
            const request = {
                statement: 1,
                description: 1,
                limit: limit,
                passthrough: { type: 'statement' },
                req_id: Date.now()
            };
            
            if (action) {
                request.action_type = action;
            }

            addDebugLog(`Sending statement request: ${JSON.stringify(request)}`);
            ws.send(JSON.stringify(request));
        }

        function displayStatement(data) {
            if (data.error) {
                addDebugLog(`Statement error: ${JSON.stringify(data.error)}`);
                showStatus('statementStatus', 'Error: ' + data.error.message, 'error');
                return;
            }

			console.log('Statement Data',data);
			

            const statements = data.statement?.transactions || [];
            const tableBody = document.getElementById('statementTableBody');
            const table = document.getElementById('statementTable');
            
            tableBody.innerHTML = '';
            
            statements.forEach(statement => {
                const row = tableBody.insertRow();
                row.innerHTML = `
                    <td>${new Date(statement.transaction_time * 1000).toLocaleString('th-TH')}</td>
                    <td>${statement.action_type || 'N/A'}</td>
                    <td>$${(statement.amount || 0).toFixed(2)}</td>
                    <td>$${(statement.balance_after || 0).toFixed(2)}</td>
                    <td>${statement.transaction_id || 'N/A'}</td>
                    <td>${statement.longcode || statement.shortcode || 'N/A'}</td>
                `;
            });
            
            table.style.display = 'table';
            showStatus('statementStatus', `Statement loaded: ${statements.length} transactions`, 'success');
        }

        // 3. Transaction Stream API
        function subscribeTransactions() {
            if (!checkConnection()) return;
            if (!checkAuthorization()) return;

            if (transactionSubscribed) {
                showStatus('transactionStatus', 'Already subscribed to transactions', 'error');
                return;
            }

            showStatus('transactionStatus', 'Subscribing to transaction stream...', 'loading');
            
            const request = {
                transaction: 1,
                subscribe: 1,
                passthrough: { type: 'transaction' },
                req_id: Date.now()
            };

            addDebugLog(`Sending transaction subscription request: ${JSON.stringify(request)}`);
            ws.send(JSON.stringify(request));
            transactionSubscribed = true;
        }

        function unsubscribeTransactions() {
            if (!checkConnection()) return;
            
            if (!transactionSubscribed) {
                showStatus('transactionStatus', 'Not subscribed to transactions', 'error');
                return;
            }

            const request = {
                forget_all: "transaction",
                passthrough: {},
                req_id: Date.now()
            };

            addDebugLog(`Sending unsubscribe request: ${JSON.stringify(request)}`);
            ws.send(JSON.stringify(request));
            transactionSubscribed = false;
            showStatus('transactionStatus', 'Unsubscribed from transaction stream', 'success');
        }

        function displayTransaction(data) {
            if (data.error) {
                addDebugLog(`Transaction error: ${JSON.stringify(data.error)}`);
                showStatus('transactionStatus', 'Error: ' + data.error.message, 'error');
                return;
            }

            const transaction = data.transaction;
            const tableBody = document.getElementById('transactionTableBody');
            const table = document.getElementById('transactionTable');
            
            // Add new transaction to the top
            const row = tableBody.insertRow(0);
            row.innerHTML = `
                <td>${new Date().toLocaleString('th-TH')}</td>
                <td>${transaction.action || 'N/A'}</td>
                <td>$${(transaction.amount || 0).toFixed(2)}</td>
                <td>$${(transaction.balance_after || 0).toFixed(2)}</td>
                <td>${transaction.transaction_id || 'N/A'}</td>
            `;
            
            // Keep only last 50 transactions
            while (tableBody.rows.length > 50) {
                tableBody.deleteRow(-1);
            }
            
            table.style.display = 'table';
            
            if (!data.subscription) {
                showStatus('transactionStatus', 'Subscribed to transaction stream successfully', 'success');
            }
        }

        // Utility functions
        function checkConnection() {
            if (!isConnected) {
                addDebugLog('ERROR: Not connected to WebSocket');
                alert('Please connect to WebSocket first');
                return false;
            }
            return true;
        }

        function checkAuthorization() {
            if (!isAuthorized) {
                addDebugLog('ERROR: Not authorized');
                alert('Please wait for authorization to complete');
                return false;
            }
            return true;
        }

        function showStatus(elementId, message, type) {
            const element = document.getElementById(elementId);
            element.innerHTML = `<div class="status ${type}">${message}</div>`;
        }

        function formatDuration(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${hours}h ${minutes}m ${secs}s`;
        }
    </script>
</body>
</html>