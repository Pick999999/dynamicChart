<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trading Chart Analysis</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --border-color: #dee2e6;
            --chart-bg: #ffffff;
        }

        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --border-color: #404040;
            --chart-bg: #1e1e1e;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }

        .main-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 15px;
        }

        .control-panel {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .chart-container {
            flex: 1;
            background-color: var(--chart-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .chart-box {
            background-color: var(--chart-bg);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            margin-bottom: 10px;
            min-height: 300px;
        }

        .chart-main {
            flex: 2;
        }

        .chart-indicator {
            flex: 1;
            min-height: 150px;
        }

        .section-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-primary);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-group-custom {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .btn-asset {
            flex: 1;
            min-width: 80px;
        }

        .asset-list {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 10px;
            background-color: var(--bg-primary);
        }

        .asset-item {
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
            transition: background-color 0.2s;
        }

        .asset-item:hover {
            background-color: var(--bg-secondary);
        }

        .asset-item.active {
            background-color: #0d6efd;
            color: white;
        }

        .indicator-panel {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 15px;
            margin-top: 10px;
        }

        .indicator-group {
            margin-bottom: 15px;
        }

        .indicator-inputs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .indicator-input {
            flex: 1;
            min-width: 80px;
        }

        .theme-toggle {
            position: absolute;
            top: 15px;
            right: 15px;
        }

        .form-control, .form-select, .btn {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            border-color: #0d6efd;
        }

        .list-group-item {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .timeframe-list {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            padding: 10px;
            background-color: var(--bg-secondary);
            border-radius: 4px;
        }

        .timeframe-item {
            padding: 5px 15px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .timeframe-item:hover {
            background-color: #0d6efd;
            color: white;
        }

        .timeframe-item.active {
            background-color: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Theme Toggle -->
        <div class="theme-toggle">
            <select class="form-select form-select-sm" id="themeSelector">
                <option value="light">Light Theme</option>
                <option value="dark">Dark Theme</option>
            </select>
        </div>

        <!-- Control Panel -->
        <div class="control-panel">
            <div class="row">
                <!-- Date Pickers -->
                <div class="col-md-3">
                    <div class="section-title">Date Range</div>
                    <div class="mb-2">
                        <label class="form-label small">Start Date</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control datepicker" id="startDate" placeholder="Select start date">
                            <button class="btn btn-outline-secondary" type="button" id="subHourStart">-</button>
                            <button class="btn btn-outline-secondary" type="button" id="addHourStart">+</button>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">End Date</label>
                        <input type="text" class="form-control datepicker" id="endDate" placeholder="Select end date">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="latestCheck">
                        <label class="form-check-label" for="latestCheck">
                            Use Latest Data
                        </label>
                    </div>
                </div>

                <!-- Asset Groups -->
                <div class="col-md-3">
                    <div class="section-title">Asset Groups</div>
                    <div class="btn-group-custom mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm btn-asset" data-group="forex">Forex</button>
                        <button type="button" class="btn btn-outline-primary btn-sm btn-asset" data-group="crypto">Crypto</button>
                        <button type="button" class="btn btn-outline-primary btn-sm btn-asset" data-group="stocks">Stocks</button>
                        <button type="button" class="btn btn-outline-primary btn-sm btn-asset" data-group="commodities">Commodities</button>
                    </div>
                    <div class="asset-list" id="assetList">
                        <div class="text-muted small">Select an asset group</div>
                    </div>
                </div>

                <!-- Connection & Actions -->
                <div class="col-md-2">
                    <div class="section-title">Connection</div>
                    <button type="button" class="btn btn-success w-100 mb-2" id="btnConnect">
                        <i class="bi bi-plug"></i> Connect
                    </button>
                    <button type="button" class="btn btn-primary w-100 mb-2" id="btnLoadData">
                        <i class="bi bi-download"></i> Load Candle Data
                    </button>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="fullscreenCheck">
                        <label class="form-check-label small" for="fullscreenCheck">
                            Fullscreen Mode
                        </label>
                    </div>
                </div>

                <!-- Timeframe Selection -->
                <div class="col-md-4">
                    <div class="section-title">Timeframe</div>
                    <div class="timeframe-list" id="timeframeList">
                        <div class="timeframe-item" data-tf="1m">1m</div>
                        <div class="timeframe-item" data-tf="5m">5m</div>
                        <div class="timeframe-item" data-tf="15m">15m</div>
                        <div class="timeframe-item" data-tf="30m">30m</div>
                        <div class="timeframe-item" data-tf="1h">1h</div>
                        <div class="timeframe-item" data-tf="4h">4h</div>
                        <div class="timeframe-item" data-tf="1d">1D</div>
                        <div class="timeframe-item" data-tf="1w">1W</div>
                        <div class="timeframe-item active" data-tf="1M">1M</div>
                    </div>
                </div>
            </div>

            <!-- Indicator Settings -->
            <div class="indicator-panel">
                <div class="section-title">Technical Indicators</div>
                <div class="row">
                    <!-- EMA Settings -->
                    <div class="col-md-3">
                        <div class="indicator-group">
                            <label class="form-label small fw-bold">EMA (4 Lines)</label>
                            <div class="indicator-inputs">
                                <div class="indicator-input">
                                    <input type="number" class="form-control form-control-sm" placeholder="EMA 1" value="9">
                                </div>
                                <div class="indicator-input">
                                    <input type="number" class="form-control form-control-sm" placeholder="EMA 2" value="21">
                                </div>
                                <div class="indicator-input">
                                    <input type="number" class="form-control form-control-sm" placeholder="EMA 3" value="50">
                                </div>
                                <div class="indicator-input">
                                    <input type="number" class="form-control form-control-sm" placeholder="EMA 4" value="200">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bollinger Bands Settings -->
                    <div class="col-md-3">
                        <div class="indicator-group">
                            <label class="form-label small fw-bold">Bollinger Bands</label>
                            <div class="indicator-inputs">
                                <div class="indicator-input">
                                    <label class="form-label small">Period</label>
                                    <input type="number" class="form-control form-control-sm" placeholder="Period" value="20">
                                </div>
                                <div class="indicator-input">
                                    <label class="form-label small">StdDev</label>
                                    <input type="number" class="form-control form-control-sm" placeholder="Std Dev" value="2" step="0.1">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RSI Settings -->
                    <div class="col-md-3">
                        <div class="indicator-group">
                            <label class="form-label small fw-bold">RSI</label>
                            <div class="indicator-inputs">
                                <div class="indicator-input">
                                    <label class="form-label small">Period</label>
                                    <input type="number" class="form-control form-control-sm" placeholder="Period" value="14">
                                </div>
                                <div class="indicator-input">
                                    <label class="form-label small">Overbought</label>
                                    <input type="number" class="form-control form-control-sm" placeholder="Overbought" value="70">
                                </div>
                                <div class="indicator-input">
                                    <label class="form-label small">Oversold</label>
                                    <input type="number" class="form-control form-control-sm" placeholder="Oversold" value="30">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stochastic Settings -->
                    <div class="col-md-3">
                        <div class="indicator-group">
                            <label class="form-label small fw-bold">Stochastic</label>
                            <div class="indicator-inputs">
                                <div class="indicator-input">
                                    <label class="form-label small">%K Period</label>
                                    <input type="number" class="form-control form-control-sm" placeholder="%K" value="14">
                                </div>
                                <div class="indicator-input">
                                    <label class="form-label small">%D Period</label>
                                    <input type="number" class="form-control form-control-sm" placeholder="%D" value="3">
                                </div>
                                <div class="indicator-input">
                                    <label class="form-label small">Smooth</label>
                                    <input type="number" class="form-control form-control-sm" placeholder="Smooth" value="3">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Container -->
        <div class="chart-container">
            <div class="section-title">Price Chart</div>
            
            <!-- Main Candlestick Chart -->
            <div class="chart-box chart-main" id="candleChart">
                <div class="text-center text-muted p-5">
                    <h5>Candlestick Chart</h5>
                    <p>Load data to display chart</p>
                </div>
            </div>

            <!-- RSI Chart -->
            <div class="chart-box chart-indicator" id="rsiChart">
                <div class="text-center text-muted p-3">
                    <h6>RSI Indicator</h6>
                    <p class="small">Load data to display RSI</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://unpkg.com/lightweight-charts@3.8.0/dist/lightweight-charts.standalone.production.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize date pickers with time
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy hh:ii',
                autoclose: true,
                todayHighlight: true,
                todayBtn: true
            });

            // Set default dates
            const now = new Date();
            const startDate = new Date(now.getTime() - (24 * 60 * 60 * 1000)); // 24 hours ago
            
            $('#startDate').datepicker('setDate', startDate);
            $('#endDate').datepicker('setDate', now);

            // Store dates in a format that includes time
            let currentStartDate = new Date(startDate);
            let currentEndDate = new Date(now);

            // Update display function
            function updateDateDisplay() {
                const formatDate = (date) => {
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    return `${day}/${month}/${year} ${hours}:${minutes}`;
                };

                $('#startDate').val(formatDate(currentStartDate));
                $('#endDate').val(formatDate(currentEndDate));
            }

            // Initialize display
            updateDateDisplay();

            // Add hour to start date
            $('#addHourStart').on('click', function() {
                currentStartDate.setHours(currentStartDate.getHours() + 1);
                currentEndDate.setHours(currentEndDate.getHours() + 1);
                updateDateDisplay();
            });

            // Subtract hour from start date
            $('#subHourStart').on('click', function() {
                currentStartDate.setHours(currentStartDate.getHours() - 1);
                currentEndDate.setHours(currentEndDate.getHours() - 1);
                updateDateDisplay();
            });

            // When start date changes manually
            $('#startDate').on('change', function() {
                const val = $(this).val();
                if (val) {
                    const parts = val.split(' ');
                    if (parts.length === 2) {
                        const dateParts = parts[0].split('/');
                        const timeParts = parts[1].split(':');
                        if (dateParts.length === 3 && timeParts.length === 2) {
                            const newDate = new Date(
                                parseInt(dateParts[2]), 
                                parseInt(dateParts[1]) - 1, 
                                parseInt(dateParts[0]),
                                parseInt(timeParts[0]),
                                parseInt(timeParts[1])
                            );
                            
                            if (!isNaN(newDate.getTime())) {
                                const timeDiff = currentEndDate - currentStartDate;
                                currentStartDate = newDate;
                                currentEndDate = new Date(currentStartDate.getTime() + timeDiff);
                                updateDateDisplay();
                            }
                        }
                    }
                }
            });

            // Theme Switching
            const themeSelector = $('#themeSelector');
            const htmlElement = document.documentElement;

            // Load saved theme or default to light
            const savedTheme = localStorage.getItem('theme') || 'light';
            themeSelector.val(savedTheme);
            if (savedTheme === 'dark') {
                htmlElement.setAttribute('data-theme', 'dark');
            }

            // Theme change handler
            themeSelector.on('change', function() {
                const theme = $(this).val();
                if (theme === 'dark') {
                    htmlElement.setAttribute('data-theme', 'dark');
                } else {
                    htmlElement.removeAttribute('data-theme');
                }
                localStorage.setItem('theme', theme);
            });

            // Asset Group Selection
            $('.btn-asset').on('click', function() {
                $('.btn-asset').removeClass('active');
                $(this).addClass('active');
                
                const group = $(this).data('group');
                loadAssetList(group);
            });

            // Function to load asset list (mock data)
            function loadAssetList(group) {
                const assets = {
                    forex: ['EUR/USD', 'GBP/USD', 'USD/JPY', 'AUD/USD', 'USD/CAD', 'NZD/USD'],
                    crypto: ['BTC/USD', 'ETH/USD', 'BNB/USD', 'XRP/USD', 'ADA/USD', 'SOL/USD'],
                    stocks: ['AAPL', 'GOOGL', 'MSFT', 'AMZN', 'TSLA', 'META'],
                    commodities: ['GOLD', 'SILVER', 'OIL', 'COPPER', 'NATGAS', 'WHEAT']
                };

                const assetList = $('#assetList');
                assetList.empty();

                if (assets[group]) {
                    assets[group].forEach(asset => {
                        assetList.append(`<div class="asset-item" data-asset="${asset}">${asset}</div>`);
                    });

                    // Asset item click handler
                    $('.asset-item').on('click', function() {
                        $('.asset-item').removeClass('active');
                        $(this).addClass('active');
                    });
                }
            }

            // Timeframe Selection
            $('.timeframe-item').on('click', function() {
                $('.timeframe-item').removeClass('active');
                $(this).addClass('active');
            });

            // Fullscreen Toggle
            $('#fullscreenCheck').on('change', function() {
                if ($(this).is(':checked')) {
                    if (document.documentElement.requestFullscreen) {
                        document.documentElement.requestFullscreen();
                    }
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    }
                }
            });

            // Listen for fullscreen changes
            document.addEventListener('fullscreenchange', function() {
                if (!document.fullscreenElement) {
                    $('#fullscreenCheck').prop('checked', false);
                }
            });

            // Connect Button
            $('#btnConnect').on('click', function() {
                const btn = $(this);
                if (btn.text().includes('Connect')) {
                    btn.removeClass('btn-success').addClass('btn-danger').html('<i class="bi bi-plug-fill"></i> Disconnect');
                    console.log('Connected');
                } else {
                    btn.removeClass('btn-danger').addClass('btn-success').html('<i class="bi bi-plug"></i> Connect');
                    console.log('Disconnected');
                }
            });

            // Load Data Button
            $('#btnLoadData').on('click', function() {
                console.log('Loading candle data...');
                console.log('Start Date:', currentStartDate);
                console.log('End Date:', currentEndDate);
                console.log('Selected Asset:', $('.asset-item.active').data('asset'));
                console.log('Selected Timeframe:', $('.timeframe-item.active').data('tf'));
                
                // Get indicator values
                console.log('Indicators:', {
                    ema: [
                        $('input[placeholder="EMA 1"]').val(),
                        $('input[placeholder="EMA 2"]').val(),
                        $('input[placeholder="EMA 3"]').val(),
                        $('input[placeholder="EMA 4"]').val()
                    ],
                    bollinger: {
                        period: $('input[placeholder="Period"]').eq(0).val(),
                        stdDev: $('input[placeholder="Std Dev"]').val()
                    },
                    rsi: {
                        period: $('input[placeholder="Period"]').eq(1).val(),
                        overbought: $('input[placeholder="Overbought"]').val(),
                        oversold: $('input[placeholder="Oversold"]').val()
                    },
                    stochastic: {
                        kPeriod: $('input[placeholder="%K"]').val(),
                        dPeriod: $('input[placeholder="%D"]').val(),
                        smooth: $('input[placeholder="Smooth"]').val()
                    }
                });
                
                alert('Ready to load data. Check console for parameters.');
            });
        });
    </script>
</body>
</html>