/**
 * ============================================================================
 * DynamicChart.js
 * ============================================================================
 * Standalone JavaScript class ที่ครอบ (wrap) Lightweight Charts v4.2
 * สามารถ <script src="dynamicChart.js"> แล้วเรียกใช้ได้ทันทีโดยไม่ต้อง build step
 *
 * คุณสมบัติหลัก:
 * - แสดง Candlestick chart
 * - สร้างเส้น indicator แบบ dynamic (EMA / HMA / WMA / Bollinger Band / Horizontal Line)
 * - อ่านโครงสร้าง JSON แล้วสร้างกราฟ + indicator ทั้งหมดอัตโนมัติ
 * - เพิ่ม/ลบ/toggle เส้นระหว่างใช้งานผ่าน API (ไม่ต้อง reload)
 * - ใช้การคำนวณ indicator จาก FullAnalysisEngine.js
 *
 * Dependencies:
 * - Lightweight Charts v4.2.x (ต้อง load ก่อน)
 * - FullAnalysisEngine.js (ต้อง load ก่อน)
 *
 * @version 1.0.0
 * @license MIT
 */

class DynamicChart {
    /**
     * @param {Object} jsonConfig - JSON configuration object (ดูรูปแบบใน README)
     */
    constructor(jsonConfig) {
        /** @type {Object} Lightweight Charts chart instance */
        this._chart = null;

        /** @type {Object} CandlestickSeries instance */
        this._candleSeries = null;

        /** @type {Map<string, Object>} Map เก็บ indicator ทั้งหมด: id → { config, series[], priceLine? } */
        this._indicators = new Map();

        /** @type {FullAnalysisEngine} Engine instance สำหรับคำนวณ indicator */
        this._engine = new FullAnalysisEngine();

        /** @type {Array} Raw OHLC candle data ปัจจุบัน */
        this._candleData = [];

        /** @type {Array} Cached analysis data for tooltip rendering */
        this._cachedAnalysisData = [];

        /** @type {string} Container element ID */
        this._containerId = null;

        /** @type {Object} Current chart JSON config */
        this._config = null;

        /** @type {Array} Custom markers (e.g. trade Win/Loss markers) */
        this._customMarkers = [];

        /** @type {BackgroundColorZonesPlugin} Custom highlight plugin for clicked trade */
        this._customHighlightPlugin = null;

        /** @type {Array} PriceLines drawn on CandlestickSeries */
        this._customPriceLines = [];

        // ถ้าส่ง config มาตั้งแต่ตอน construct → สร้างกราฟทันที
        if (jsonConfig) {
            this.loadConfig(jsonConfig);
        }
    }

    // ========================================================================
    //  PUBLIC API
    // ========================================================================

    /**
     * อ่าน JSON config → สร้างกราฟ + indicator ทั้งหมดอัตโนมัติ
     * ถ้ามีกราฟอยู่แล้วจะ destroy แล้วสร้างใหม่
     * @param {Object} jsonConfig
     */
    loadConfig(jsonConfig) {
        // ทำลาย chart เก่า (ถ้ามี)
        this.destroy();

        this._config = jsonConfig;
        const chartCfg = jsonConfig.chart || {};
        const candleCfg = jsonConfig.candle || {};
        const indicatorsCfg = Array.isArray(jsonConfig.indicators) ? jsonConfig.indicators : [];

        // 1. สร้าง Chart
        this._containerId = chartCfg.container || 'chart-container';
        const container = document.getElementById(this._containerId);
        if (!container) {
            console.error(`[DynamicChart] Container element "${this._containerId}" not found.`);
            return;
        }

        // เคลียร์ container ก่อนสร้าง chart ใหม่ (สำคัญเมื่อ reload)
        container.innerHTML = '';

        container.style.position = 'relative';

        // สร้าง tooltip element
        const tooltip = document.createElement('div');
        tooltip.id = 'chart-custom-tooltip';
        tooltip.style.position = 'absolute';
        tooltip.style.display = 'none';
        tooltip.style.padding = '8px 12px';
        tooltip.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.4)';
        tooltip.style.background = 'rgba(15, 22, 41, 0.92)';
        tooltip.style.border = '1px solid rgba(56, 189, 248, 0.4)';
        tooltip.style.borderRadius = '6px';
        tooltip.style.color = '#e2e8f0';
        tooltip.style.fontFamily = 'Arial, sans-serif';
        tooltip.style.fontSize = '12px';
        tooltip.style.lineHeight = '1.5';
        tooltip.style.zIndex = '50';
        tooltip.style.pointerEvents = 'none';
        container.appendChild(tooltip);
        this._tooltip = tooltip;

        // Deep merge chart options
        const defaultOpts = {
            width: chartCfg.width || container.clientWidth || 800,
            height: chartCfg.height || container.clientHeight || 500,
            layout: {
                background: { color: '#1a1a2e' },
                textColor: '#e0e0e0',
            },
            rightPriceScale: {
                scaleMargins: {
                    top: 0.15,
                    bottom: 0.15,
                },
                autoScale: true,
            },
            grid: {
                vertLines: { color: 'rgba(255,255,255,0.04)' },
                horzLines: { color: 'rgba(255,255,255,0.04)' },
            },
            timeScale: {
                timeVisible: true,
                secondsVisible: false,
            },
            crosshair: {
                mode: LightweightCharts.CrosshairMode.Normal,
            },
        };
        const chartOptions = this._deepMerge(defaultOpts, chartCfg.options || {});

        this._chart = LightweightCharts.createChart(container, chartOptions);

        this._chart.subscribeClick((param) => {
            if (param && param.time) {
                const containerEl = document.getElementById(this._containerId);
                if (containerEl) {
                    const event = new CustomEvent('chart-click', {
                        detail: { time: param.time }
                    });
                    containerEl.dispatchEvent(event);
                }
            }
        });

        this._chart.subscribeCrosshairMove((param) => {
            const chkShowTooltip = document.getElementById('chk-show-tooltip');
            const showTooltip = chkShowTooltip ? chkShowTooltip.checked : false;

            if (!showTooltip || !param.point || !param.time || !this._candleSeries || !this._tooltip) {
                if (this._tooltip) this._tooltip.style.display = 'none';
                return;
            }

            // ดึงข้อมูลสำหรับจุดเวลานี้
            const dataPoint = param.seriesData.get(this._candleSeries);
            if (!dataPoint) {
                this._tooltip.style.display = 'none';
                return;
            }

            // ค้นหาข้อมูลวิเคราะห์ใน Cache
            if (!Array.isArray(this._cachedAnalysisData)) {
                this._tooltip.style.display = 'none';
                return;
            }

            const time = param.time;
            const analysisItem = this._cachedAnalysisData.find(item => item.candletime === time);

            if (analysisItem) {
                const code12 = analysisItem.ageCutCandleCode12 || '-';
                const code123 = analysisItem.ageCutCandleCode123 || '-';
                
                const macd12 = typeof analysisItem.macd_12 === 'number' ? analysisItem.macd_12.toFixed(5) : '-';
                const macd23 = typeof analysisItem.macd_23 === 'number' ? analysisItem.macd_23.toFixed(5) : '-';
                const emaConvergenceType = analysisItem.ema_convergence_type || '-';
                const convColor = emaConvergenceType === 'convergence' ? '#34d399' : 
                                  emaConvergenceType === 'divergence' ? '#ef5350' : '#fbbf24';

                this._tooltip.innerHTML = `
                    <div style="font-weight: bold; color: #38bdf8; margin-bottom: 4px;">Age Cut Info</div>
                    <div>Code 12: <span style="color: #34d399; font-family: monospace;">${code12}</span></div>
                    <div>Code 123: <span style="color: #fbbf24; font-family: monospace;">${code123}</span></div>
                    <div>macd_12: <span style="color: #38bdf8; font-family: monospace;">${macd12}</span></div>
                    <div>macd_23: <span style="color: #c084fc; font-family: monospace;">${macd23}</span></div>
                    <div>ema_convergence_type: <span style="color: ${convColor}; font-family: monospace;">${emaConvergenceType}</span></div>
                `;

                const toolWidth = 260;
                const toolHeight = 125;
                let left = param.point.x + 15;
                let top = param.point.y + 15;

                const containerWidth = container.clientWidth;
                const containerHeight = container.clientHeight;
                if (left + toolWidth > containerWidth) {
                    left = param.point.x - toolWidth - 15;
                }
                if (top + toolHeight > containerHeight) {
                    top = param.point.y - toolHeight - 15;
                }

                this._tooltip.style.left = left + 'px';
                this._tooltip.style.top = top + 'px';
                this._tooltip.style.display = 'block';
            } else {
                this._tooltip.style.display = 'none';
            }
        });

        // 2. สร้าง Candlestick series
        const defaultCandleOpts = {
            upColor: '#26a69a',
            downColor: '#ef5350',
            borderVisible: false,
            wickUpColor: '#26a69a',
            wickDownColor: '#ef5350',
        };
        const candleOptions = this._deepMerge(defaultCandleOpts, candleCfg.options || {});

        this._candleSeries = this._chart.addCandlestickSeries(candleOptions);

        // 3. Set candle data (ถ้ามี)
        if (Array.isArray(candleCfg.data) && candleCfg.data.length > 0) {
            this._candleData = candleCfg.data;
            this._candleSeries.setData(this._candleData);
        }

        this._updateCachedAnalysis();

        // 4. สร้าง Indicators ทั้งหมดจาก config
        indicatorsCfg.forEach(cfg => {
            this.addIndicator(cfg);
        });

        // 5. Fit content
        this._chart.timeScale().fitContent();

        // 6. Auto-resize observer
        this._setupResizeObserver(container);
    }

    /**
     * เปลี่ยน/อัพเดตข้อมูลแท่งเทียนทั้งหมด + recalc ทุก indicator
     * @param {Array} data - Array ของ { time, open, high, low, close }
     */
    setCandleData(data) {
        this._candleData = data;
        if (this._candleSeries) {
            this._candleSeries.setData(data);
        }
        // Recalculate ทุก indicator
        this._recalcAllIndicators();
        this._updateCachedAnalysis();
        if (this._chart) {
            this._chart.timeScale().fitContent();
        }
    }

    /**
     * เพิ่มแท่งเทียนทีละแท่ง (สำหรับ real-time streaming)
     * @param {Object} candle - { time, open, high, low, close }
     */
    appendCandle(candle) {
        this._candleData.push(candle);
        if (this._candleSeries) {
            this._candleSeries.update(candle);
        }
        // Recalculate ทุก indicator ด้วยข้อมูลใหม่
        this._recalcAllIndicators();
        this._updateCachedAnalysis();
    }

    /**
     * เพิ่ม indicator ใหม่จาก config object
     * @param {Object} cfg - Indicator config (ต้องมี id, type)
     * @returns {string|null} id ของ indicator ที่เพิ่ม หรือ null ถ้าเพิ่มไม่สำเร็จ
     */
    addIndicator(cfg) {
        if (!cfg || !cfg.id || !cfg.type) {
            console.error('[DynamicChart] Indicator config ต้องมี id และ type');
            return null;
        }

        // ถ้า id ซ้ำ → ลบตัวเก่าก่อน
        if (this._indicators.has(cfg.id)) {
            this.removeIndicator(cfg.id);
        }

        const type = cfg.type.toLowerCase();
        const entry = { config: Object.assign({}, cfg), series: [], priceLine: null, priceLines: [], markers: [] };

        try {
            if (type === 'smc') {
                this._createSMC(entry);
            } else if (type === 'horizontalline') {
                this._createHorizontalLine(entry);
            } else if (type === 'bb') {
                this._createBollingerBands(entry);
            } else if (['ema', 'hma', 'wma', 'sma'].includes(type)) {
                this._createMovingAverage(entry);
            } else {
                console.warn(`[DynamicChart] Unknown indicator type: "${type}"`);
                return null;
            }
        } catch (err) {
            console.error(`[DynamicChart] Error creating indicator "${cfg.id}":`, err);
            return null;
        }

        this._indicators.set(cfg.id, entry);
        this._updateCachedAnalysis();
        return cfg.id;
    }

    /**
     * ลบ indicator ออกจาก chart
     * @param {string} id - Indicator ID
     * @returns {boolean} สำเร็จหรือไม่
     */
    removeIndicator(id) {
        const entry = this._indicators.get(id);
        if (!entry) return false;

        // ลบ priceLine และ priceLines ออกจาก chart
        if (entry.priceLine && this._candleSeries) {
            try { this._candleSeries.removePriceLine(entry.priceLine); } catch (e) {}
        }
        if (Array.isArray(entry.priceLines) && this._candleSeries) {
            entry.priceLines.forEach(pl => {
                try { this._candleSeries.removePriceLine(pl); } catch (e) {}
            });
        }
        entry.series.forEach(s => {
            try {
                this._chart.removeSeries(s);
            } catch (e) {
                // series อาจถูกลบไปแล้ว
            }
        });

        entry.markers = [];
        entry.bgZones = [];
        this._indicators.delete(id);
        this._updateAllMarkers();
        this._updateAllBgZones();
        this._updateCachedAnalysis();
        return true;
    }

    /**
     * เปลี่ยน property ของ indicator แล้ว recalculate
     * @param {string} id - Indicator ID
     * @param {Object} props - Properties ที่ต้องการเปลี่ยน (เช่น { period: 30, color: '#ff0000' })
     * @returns {boolean}
     */
    updateIndicator(id, props) {
        const entry = this._indicators.get(id);
        if (!entry) return false;

        // Merge props เข้า config
        Object.assign(entry.config, props);

        // ลบ priceLines, series, markers & bgZones เก่า
        if (entry.priceLine && this._candleSeries) {
            try { this._candleSeries.removePriceLine(entry.priceLine); } catch (e) {}
            entry.priceLine = null;
        }
        if (Array.isArray(entry.priceLines) && this._candleSeries) {
            entry.priceLines.forEach(pl => {
                try { this._candleSeries.removePriceLine(pl); } catch (e) {}
            });
        }
        entry.priceLines = [];
        entry.markers = [];
        entry.bgZones = [];
        entry.series.forEach(s => {
            try { this._chart.removeSeries(s); } catch (e) {}
        });
        entry.series = [];

        // สร้างใหม่ด้วย config ใหม่
        const type = entry.config.type.toLowerCase();
        if (type === 'smc') {
            this._createSMC(entry);
        } else if (type === 'horizontalline') {
            this._createHorizontalLine(entry);
        } else if (type === 'bb') {
            this._createBollingerBands(entry);
        } else if (['ema', 'hma', 'wma', 'sma'].includes(type)) {
            this._createMovingAverage(entry);
        }

        // อัปเดต Markers และ BgZones เสมอ เพื่อให้การ toggle visible สะอาดหมดจด
        this._updateAllMarkers();
        this._updateAllBgZones();
        this._updateCachedAnalysis();

        return true;
    }

    /**
     * สลับ visible ของ indicator
     * @param {string} id - Indicator ID
     * @returns {boolean} สถานะ visible ใหม่
     */
    toggleIndicator(id) {
        const entry = this._indicators.get(id);
        if (!entry) return false;

        const newVisible = !entry.config.visible;
        this.updateIndicator(id, { visible: newVisible });
        return newVisible;
    }

    /**
     * ดึง config + series ของ indicator ตาม id
     * @param {string} id
     * @returns {Object|null}
     */
    getIndicator(id) {
        const entry = this._indicators.get(id);
        if (!entry) return null;
        return {
            id: entry.config.id,
            config: Object.assign({}, entry.config),
            visible: entry.config.visible !== false,
            seriesCount: entry.series.length,
        };
    }

    /**
     * ดึงรายการ indicator ทั้งหมด
     * @returns {Array<Object>}
     */
    getAllIndicators() {
        const result = [];
        this._indicators.forEach((entry, id) => {
            result.push({
                id: id,
                type: entry.config.type,
                visible: entry.config.visible !== false,
                config: Object.assign({}, entry.config),
            });
        });
        return result;
    }

    /**
     * ตั้งค่า Custom Markers บน CandlestickSeries (เช่น แสดง Win/Loss จากข้อมูลการเทรด)
     * @param {Array<Object>} markers - Array ของ { time, position, color, shape, text }
     * @param {string|null} category - หมวดหมู่ของ marker เพื่อแยกแยะไม่ให้ทับซ้อนกัน
     */
    setCustomMarkers(markers, category = null) {
        if (category) {
            if (!this._customMarkersMap) this._customMarkersMap = {};
            this._customMarkersMap[category] = Array.isArray(markers) ? markers : [];
        } else {
            this._customMarkers = Array.isArray(markers) ? markers : [];
        }
        this._updateAllMarkers();
    }

    /**
     * เคลียร์ Custom Markers ทั้งหมด หรือเฉพาะหมวดหมู่ที่ระบุ
     * @param {string|null} category - หมวดหมู่ของ marker ที่ต้องการลบ (ถ้าไม่ใส่จะลบทั้งหมด)
     */
    clearCustomMarkers(category = null) {
        if (category) {
            if (this._customMarkersMap) {
                delete this._customMarkersMap[category];
            }
        } else {
            this._customMarkers = [];
            this._customMarkersMap = {};
        }
        this._updateAllMarkers();
    }


    getFullAnalysisData() {
        if (!this._candleData || this._candleData.length === 0) return [];
        
        const emas = [];
        this._indicators.forEach(ind => {
            if (ind.config.type === 'ema' && ind.config.visible !== false) {
                emas.push(Number(ind.config.period));
            }
        });
        emas.sort((a, b) => a - b);

        const config = {
            ema: {
                short: { period: emas[0] || 9, type: 'ema' },
                medium: { period: emas[1] || 21, type: 'ema' },
                long: { period: emas[2] || 50, type: 'ema' }
            },
            indicators: {
                adxPeriod: 14,
                atrPeriod: 7,
                atrMulti: 1.3,
                bbPeriod: 20,
                ciPeriod: 14,
                smcPeriod: 50
            }
        };

        return this._engine.performAnalysis(this._candleData, config);
    }

    /**
     * Set a vertical shaded zone and horizontal price lines for a trade
     * @param {Object} trade - { startTime, endTime, isCall, entryPrice, exitPrice }
     */
    setTradeHighlight(trade) {
        console.log('[DynamicChart] setTradeHighlight called with:', trade);
        this.clearTradeHighlight();

        if (!trade) {
            console.warn('[DynamicChart] setTradeHighlight: trade is null/undefined');
            return;
        }
        if (!this._candleSeries) {
            console.warn('[DynamicChart] setTradeHighlight: _candleSeries is null');
            return;
        }

        // 1. Draw vertical shaded zone using BackgroundColorZonesPlugin
        try {
            const bgPlugin = this._getCustomHighlightPlugin();
            console.log('[DynamicChart] bgPlugin:', bgPlugin ? 'OK' : 'NULL');
            if (bgPlugin) {
                const color = trade.isCall ? 'rgba(52, 211, 153, 0.18)' : 'rgba(239, 83, 80, 0.18)';
                const borderColor = trade.isCall ? 'rgba(52, 211, 153, 0.5)' : 'rgba(239, 83, 80, 0.5)';
                
                let exactStart = this._findClosestCandleTime(trade.startTime);
                let exactEnd = this._findClosestCandleTime(trade.endTime);

                if (exactStart === exactEnd) {
                    const idx = this._candleData.findIndex(c => c.time === exactStart);
                    if (idx !== -1 && idx + 1 < this._candleData.length) {
                        exactEnd = this._candleData[idx + 1].time;
                    }
                }

                const zoneData = [{
                    startTime: exactStart,
                    endTime: exactEnd,
                    color: color,
                    borderColor: borderColor,
                    borderWidth: 2,
                    label: trade.isCall ? 'CALL' : 'PUT',
                    labelColor: '#ffffff',
                    labelBackgroundColor: trade.isCall ? 'rgba(52, 211, 153, 0.9)' : 'rgba(239, 83, 80, 0.9)'
                }];
                console.log('[DynamicChart] Setting zones with exact times:', JSON.stringify(zoneData));
                bgPlugin.setZones(zoneData);
            }
        } catch (err) {
            console.error('[DynamicChart] Error drawing shaded zone:', err);
        }

        // 2. Draw horizontal price lines for entry/exit spot
        try {
            if (typeof trade.entryPrice === 'number' && !isNaN(trade.entryPrice)) {
                const plEntry = this._candleSeries.createPriceLine({
                    price: trade.entryPrice,
                    color: '#38bdf8',
                    lineWidth: 2,
                    lineStyle: LightweightCharts.LineStyle.Solid,
                    axisLabelVisible: true,
                    title: `Entry: ${trade.entryPrice.toFixed(2)}`,
                });
                this._customPriceLines.push(plEntry);
                console.log('[DynamicChart] Created Entry price line at:', trade.entryPrice);
            }
        } catch (err) {
            console.error('[DynamicChart] Error creating Entry price line:', err);
        }

        try {
            if (typeof trade.exitPrice === 'number' && !isNaN(trade.exitPrice)) {
                const plExit = this._candleSeries.createPriceLine({
                    price: trade.exitPrice,
                    color: '#fbbf24',
                    lineWidth: 2,
                    lineStyle: LightweightCharts.LineStyle.Dashed,
                    axisLabelVisible: true,
                    title: `Exit: ${trade.exitPrice.toFixed(2)}`,
                });
                this._customPriceLines.push(plExit);
                console.log('[DynamicChart] Created Exit price line at:', trade.exitPrice);
            }
        } catch (err) {
            console.error('[DynamicChart] Error creating Exit price line:', err);
        }
    }

    /**
     * Clear the vertical shaded zone and horizontal price lines
     */
    clearTradeHighlight() {
        // Remove price lines
        if (Array.isArray(this._customPriceLines) && this._candleSeries) {
            this._customPriceLines.forEach(pl => {
                try { this._candleSeries.removePriceLine(pl); } catch (e) {}
            });
        }
        this._customPriceLines = [];

        // Clear shaded zones
        if (this._customHighlightPlugin) {
            try {
                this._customHighlightPlugin.clearZones();
            } catch (e) {
                console.warn('[DynamicChart] Error clearing highlight zones:', e);
            }
        }
    }

    /**
     * ดึงหรือสร้าง BackgroundColorZonesPlugin สำหรับ custom highlight (trade shading)
     */
    _getCustomHighlightPlugin() {
        if (!this._customHighlightPlugin && typeof BackgroundColorZonesPlugin !== 'undefined' && this._candleSeries) {
            console.log('[DynamicChart] Creating new BackgroundColorZonesPlugin for trade highlight');
            this._customHighlightPlugin = new BackgroundColorZonesPlugin([]);
            try {
                this._candleSeries.attachPrimitive(this._customHighlightPlugin);
                console.log('[DynamicChart] BackgroundColorZonesPlugin attached successfully');
            } catch (err) {
                console.error('[DynamicChart] Failed to attach CustomHighlightPlugin:', err);
                this._customHighlightPlugin = null;
            }
        }
        return this._customHighlightPlugin;
    }

    /**
     * ค้นหาเวลาแท่งเทียนที่ใกล้เคียงที่สุดในกราฟ
     */
    _findClosestCandleTime(targetEpoch) {
        if (!this._candleData || this._candleData.length === 0) return targetEpoch;

        const getEpoch = (t) => {
            if (typeof t === 'number') return t > 1e11 ? Math.floor(t / 1000) : t;
            if (typeof t === 'string') {
                if (/^\d+$/.test(t)) {
                    const num = parseInt(t, 10);
                    return num > 1e11 ? Math.floor(num / 1000) : num;
                }
                const d = new Date(t.replace(' ', 'T'));
                if (!isNaN(d.getTime())) return Math.floor(d.getTime() / 1000);
                const d2 = new Date(t);
                if (!isNaN(d2.getTime())) return Math.floor(d2.getTime() / 1000);
            }
            if (t && typeof t === 'object' && t.year && t.month && t.day) {
                return Math.floor(new Date(t.year, t.month - 1, t.day).getTime() / 1000);
            }
            return 0;
        };

        let closestTime = this._candleData[0].time;
        let minDiff = Math.abs(getEpoch(closestTime) - targetEpoch);

        for (let i = 1; i < this._candleData.length; i++) {
            const cTime = this._candleData[i].time;
            const cEpoch = getEpoch(cTime);
            const diff = Math.abs(cEpoch - targetEpoch);
            if (diff < minDiff) {
                minDiff = diff;
                closestTime = cTime;
            }
        }
        return closestTime;
    }

    /**
     * ทำลาย chart instance ทั้งหมด
     */
    destroy() {
        if (this._resizeObserver) {
            this._resizeObserver.disconnect();
            this._resizeObserver = null;
        }
        if (this._chart) {
            try { this._chart.remove(); } catch (e) {}
            this._chart = null;
        }
        this._candleSeries = null;
        this._indicators.clear();
        this._customMarkers = [];
        this._candleData = [];
        this._customPriceLines = [];
        this._customHighlightPlugin = null;
        // เคลียร์ container DOM
        if (this._containerId) {
            const container = document.getElementById(this._containerId);
            if (container) container.innerHTML = '';
        }
    }

    /**
     * Resize chart ให้เต็ม container
     */
    resize() {
        if (!this._chart || !this._containerId) return;
        const container = document.getElementById(this._containerId);
        if (container) {
            this._chart.resize(container.clientWidth, container.clientHeight);
        }
    }

    // ========================================================================
    //  INTERNAL HELPERS
    // ========================================================================

    /**
     * สร้าง Moving Average series (EMA/HMA/WMA/SMA)
     */
    _createMovingAverage(entry) {
        const cfg = entry.config;
        const type = cfg.type.toLowerCase();
        const period = cfg.period || 20;
        const source = (cfg.source || 'close').toLowerCase();
        const color = cfg.color || '#2196F3';
        const lineWidth = cfg.lineWidth || 1;
        const visible = cfg.visible !== false;

        // คำนวณ
        const prices = this._extractSourceData(source);
        if (prices.length === 0) return;

        let values;
        switch (type) {
            case 'ema': values = this._engine.computeEMA(prices, period); break;
            case 'hma': values = this._engine.computeHMA(prices, period); break;
            case 'wma': values = this._engine.computeWMA(prices, period); break;
            case 'sma': values = this._engine.computeSMA(prices, period); break;
            default:    values = this._engine.computeEMA(prices, period); break;
        }

        // สร้าง data series
        const lineData = this._valuesToLineData(values, period);
        if (!Array.isArray(lineData) || lineData.length === 0) return;

        // สร้าง LineSeries
        const series = this._chart.addLineSeries({
            color: color,
            lineWidth: lineWidth,
            visible: visible,
            lastValueVisible: false,
            priceLineVisible: false,
            crosshairMarkerVisible: true,
            crosshairMarkerRadius: 3,
        });
        series.setData(lineData);
        entry.series.push(series);
    }

    /**
     * สร้าง Bollinger Bands (3 เส้น: upper, middle, lower)
     */
    _createBollingerBands(entry) {
        const cfg = entry.config;
        const period = cfg.period || 20;
        const stdDev = cfg.stdDev || 2;
        const source = (cfg.source || 'close').toLowerCase();
        const colors = cfg.colors || {};
        const upperColor = colors.upper || '#9C27B0';
        const middleColor = colors.middle || '#607D8B';
        const lowerColor = colors.lower || '#9C27B0';
        const lineWidth = cfg.lineWidth || 1;
        const visible = cfg.visible !== false;

        // คำนวณ
        const prices = this._extractSourceData(source);
        if (prices.length === 0) return;

        const bbResult = this._engine.computeBollingerBands(prices, period, stdDev);

        // สร้าง data สำหรับ 3 เส้น
        const upperData = [];
        const middleData = [];
        const lowerData = [];

        for (let i = period - 1; i < this._candleData.length; i++) {
            if (bbResult[i].middle === 0) continue;
            const time = this._candleData[i].time;
            upperData.push({ time, value: bbResult[i].upper });
            middleData.push({ time, value: bbResult[i].middle });
            lowerData.push({ time, value: bbResult[i].lower });
        }

        // Guard: ข้อมูลต้องเป็น array ที่มีสมาชิก
        if (upperData.length === 0) return;

        // Upper band
        const upperSeries = this._chart.addLineSeries({
            color: upperColor,
            lineWidth: lineWidth,
            visible: visible,
            lineStyle: LightweightCharts.LineStyle.Dashed,
            lastValueVisible: false,
            priceLineVisible: false,
        });
        upperSeries.setData(upperData);
        entry.series.push(upperSeries);

        // Middle band
        const middleSeries = this._chart.addLineSeries({
            color: middleColor,
            lineWidth: lineWidth,
            visible: visible,
            lastValueVisible: false,
            priceLineVisible: false,
        });
        middleSeries.setData(middleData);
        entry.series.push(middleSeries);

        // Lower band
        const lowerSeries = this._chart.addLineSeries({
            color: lowerColor,
            lineWidth: lineWidth,
            visible: visible,
            lineStyle: LightweightCharts.LineStyle.Dashed,
            lastValueVisible: false,
            priceLineVisible: false,
        });
        lowerSeries.setData(lowerData);
        entry.series.push(lowerSeries);
    }

    /**
     * สร้าง Horizontal Line (ใช้ createPriceLine ของ CandlestickSeries)
     */
    _createHorizontalLine(entry) {
        const cfg = entry.config;
        const value = cfg.value || 0;
        const color = cfg.color || '#F44336';
        const lineWidth = cfg.lineWidth || 1;
        const lineStyle = this._mapLineStyle(cfg.lineStyle || 'dashed');
        const visible = cfg.visible !== false;

        if (!visible) return;

        if (!this._candleSeries) return;

        const priceLine = this._candleSeries.createPriceLine({
            price: value,
            color: color,
            lineWidth: lineWidth,
            lineStyle: lineStyle,
            axisLabelVisible: true,
            title: cfg.id || '',
        });

        entry.priceLine = priceLine;
    }

    /**
     * สร้าง SMC (Smart Money Concepts) Indicator
     * คำนวณ CHoCH, BOS, Swing Points (HH, HL, LH, LL), Order Blocks, FVG, EQH/EQL, Premium/Discount
     */
    _createSMC(entry) {
        const cfg = entry.config;
        if (cfg.visible === false) return;
        if (typeof SMCIndicator === 'undefined') {
            console.error('[DynamicChart] SMCIndicator class is not loaded.');
            return;
        }

        if (!this._candleData || this._candleData.length === 0) return;

        const smcConfig = {
            swingLength: cfg.swingLength || 50,
            internalLength: cfg.internalLength || 5,
            showInternalStructure: cfg.showChoch !== false || cfg.showBOS !== false,
            showSwingStructure: cfg.showChoch !== false || cfg.showBOS !== false,
            showOrderBlocks: cfg.showOrderBlocks !== false,
            showFVG: cfg.showFVG !== false,
            showEqualHL: cfg.showEqualHL !== false,
            showPremiumDiscount: cfg.showPremiumDiscount !== false,
        };

        const smc = new SMCIndicator(smcConfig);
        smc.calculate(this._candleData);

        entry.priceLines = [];
        entry.markers = [];
        entry.bgZones = [];
        const latestTime = this._candleData.length > 0 ? this._candleData[this._candleData.length - 1].time : null;

        const showPriceLines = cfg.showPriceLines !== false;

        // 1. CHoCH & BOS (Market Structure)
        if (cfg.showChoch !== false || cfg.showBOS !== false) {
            const structures = smc.getStructures();
            structures.forEach(s => {
                const isChoch = s.type === 'CHoCH';
                const isBOS = s.type === 'BOS';

                if ((isChoch && cfg.showChoch !== false) || (isBOS && cfg.showBOS !== false)) {
                    const color = s.direction === 'bullish' ? '#26a69a' : '#ef5350';
                    if (showPriceLines && this._candleSeries) {
                        const pl = this._candleSeries.createPriceLine({
                            price: s.price,
                            color: color,
                            lineWidth: 1,
                            lineStyle: LightweightCharts.LineStyle.Dashed,
                            axisLabelVisible: false,
                            title: `${s.level.toUpperCase()} ${s.type}`,
                        });
                        entry.priceLines.push(pl);
                    }

                    entry.markers.push({
                        time: s.time,
                        position: s.direction === 'bullish' ? 'belowBar' : 'aboveBar',
                        color: color,
                        shape: s.direction === 'bullish' ? 'arrowUp' : 'arrowDown',
                        text: `${s.type}`,
                    });
                }
            });
        }

        // 2. Swing Points (HH, HL, LH, LL)
        if (cfg.showSwingPoints !== false) {
            const swingPoints = smc.getSwingPoints();
            swingPoints.forEach(sp => {
                const isHigh = sp.swing === 'high';
                const color = isHigh ? '#fbbf24' : '#38bdf8';
                entry.markers.push({
                    time: sp.time,
                    position: isHigh ? 'aboveBar' : 'belowBar',
                    color: color,
                    shape: 'circle',
                    text: sp.type,
                });
            });
        }

        // 3. Order Blocks (OB)
        if (cfg.showOrderBlocks !== false) {
            const obs = smc.getOrderBlocks({ mitigated: false });
            obs.slice(-6).forEach(ob => {
                const isBull = ob.bias === 'bullish';
                const color = isBull ? 'rgba(38, 166, 154, 0.8)' : 'rgba(239, 83, 80, 0.8)';
                
                // Add Background Shaded Zone for OB (Horizontal Price-Bounded Box)
                if (latestTime) {
                    entry.bgZones.push({
                        startTime: ob.time,
                        endTime: ob.mitigatedTime || latestTime,
                        topPrice: ob.high,
                        bottomPrice: ob.low,
                        color: isBull ? 'rgba(38, 166, 154, 0.25)' : 'rgba(239, 83, 80, 0.25)',
                        borderColor: isBull ? 'rgba(38, 166, 154, 0.8)' : 'rgba(239, 83, 80, 0.8)',
                        borderWidth: 1,
                        label: `OB ${ob.bias.toUpperCase()}`,
                        labelColor: '#ffffff',
                        labelBackgroundColor: isBull ? 'rgba(38, 166, 154, 0.85)' : 'rgba(239, 83, 80, 0.85)'
                    });
                }

                if (showPriceLines && this._candleSeries) {
                    const plHigh = this._candleSeries.createPriceLine({
                        price: ob.high,
                        color: color,
                        lineWidth: 1,
                        lineStyle: LightweightCharts.LineStyle.Solid,
                        axisLabelVisible: false,
                        title: `OB ${ob.bias.toUpperCase()} Top`,
                    });
                    const plLow = this._candleSeries.createPriceLine({
                        price: ob.low,
                        color: color,
                        lineWidth: 1,
                        lineStyle: LightweightCharts.LineStyle.Dotted,
                        axisLabelVisible: false,
                        title: `OB ${ob.bias.toUpperCase()} Bot`,
                    });
                    entry.priceLines.push(plHigh);
                    entry.priceLines.push(plLow);
                }
            });
        }

        // 4. Fair Value Gaps (FVG)
        if (cfg.showFVG !== false) {
            const fvgs = smc.getFairValueGaps({ filled: false });
            fvgs.slice(-4).forEach(fvg => {
                const isBull = fvg.bias === 'bullish';
                const color = isBull ? 'rgba(167, 139, 250, 0.8)' : 'rgba(251, 191, 36, 0.8)';

                // Add Background Shaded Zone for FVG (Horizontal Price-Bounded Box)
                if (latestTime) {
                    entry.bgZones.push({
                        startTime: fvg.time,
                        endTime: fvg.filledTime || latestTime,
                        topPrice: fvg.top,
                        bottomPrice: fvg.bottom,
                        color: isBull ? 'rgba(167, 139, 250, 0.25)' : 'rgba(251, 191, 36, 0.25)',
                        borderColor: isBull ? 'rgba(167, 139, 250, 0.8)' : 'rgba(251, 191, 36, 0.8)',
                        borderWidth: 1,
                        label: `FVG ${fvg.bias.toUpperCase()}`,
                        labelColor: '#ffffff',
                        labelBackgroundColor: isBull ? 'rgba(167, 139, 250, 0.85)' : 'rgba(251, 191, 36, 0.85)'
                    });
                }

                if (showPriceLines && this._candleSeries) {
                    const plTop = this._candleSeries.createPriceLine({
                        price: fvg.top,
                        color: color,
                        lineWidth: 1,
                        lineStyle: LightweightCharts.LineStyle.Dashed,
                        axisLabelVisible: false,
                        title: `FVG Top`,
                    });
                    const plBot = this._candleSeries.createPriceLine({
                        price: fvg.bottom,
                        color: color,
                        lineWidth: 1,
                        lineStyle: LightweightCharts.LineStyle.Dashed,
                        axisLabelVisible: false,
                        title: `FVG Bot`,
                    });
                    entry.priceLines.push(plTop);
                    entry.priceLines.push(plBot);
                }
            });
        }

        // 5. Equal Highs / Lows (EQH/EQL)
        if (cfg.showEqualHL !== false) {
            const eqhl = smc.equalHighsLows || [];
            eqhl.forEach(eq => {
                const color = eq.type === 'EQH' ? '#ef5350' : '#26a69a';
                if (showPriceLines && this._candleSeries) {
                    const pl = this._candleSeries.createPriceLine({
                        price: eq.price,
                        color: color,
                        lineWidth: 1,
                        lineStyle: LightweightCharts.LineStyle.SparseDotted,
                        axisLabelVisible: true,
                        title: eq.type,
                    });
                    entry.priceLines.push(pl);
                }
            });
        }

        // 6. Premium / Discount Zone
        if (cfg.showPremiumDiscount !== false) {
            const zones = smc.premiumDiscountZones;
            if (zones && zones.length > 0) {
                const z = zones[zones.length - 1];
                const startTime = z.startTime || (this._candleData[0] ? this._candleData[0].time : latestTime);
                const endTime = z.endTime || latestTime;

                if (latestTime) {
                    entry.bgZones.push({
                        startTime: startTime,
                        endTime: endTime,
                        topPrice: z.premiumTop,
                        bottomPrice: z.equilibrium,
                        color: 'rgba(239, 83, 80, 0.12)',
                        borderColor: 'rgba(239, 83, 80, 0.4)',
                        label: 'PREMIUM ZONE',
                        labelColor: '#ef5350',
                        labelBackgroundColor: 'rgba(239, 83, 80, 0.2)'
                    });
                    entry.bgZones.push({
                        startTime: startTime,
                        endTime: endTime,
                        topPrice: z.equilibrium,
                        bottomPrice: z.discountBottom,
                        color: 'rgba(38, 166, 154, 0.12)',
                        borderColor: 'rgba(38, 166, 154, 0.4)',
                        label: 'DISCOUNT ZONE',
                        labelColor: '#26a69a',
                        labelBackgroundColor: 'rgba(38, 166, 154, 0.2)'
                    });
                }

                if (showPriceLines && this._candleSeries) {
                    const plEq = this._candleSeries.createPriceLine({
                        price: z.equilibrium,
                        color: '#fbbf24',
                        lineWidth: 1,
                        lineStyle: LightweightCharts.LineStyle.Dashed,
                        axisLabelVisible: true,
                        title: 'Equilibrium (50%)',
                    });
                    entry.priceLines.push(plEq);
                }
            }
        }

        this._updateAllMarkers();
        this._updateAllBgZones();
    }

    /**
     * ดึงหรือสร้าง BackgroundColorZonesPlugin บน _candleSeries
     */
    _getBgZonesPlugin() {
        if (!this._bgZonesPlugin && typeof BackgroundColorZonesPlugin !== 'undefined' && this._candleSeries) {
            this._bgZonesPlugin = new BackgroundColorZonesPlugin([]);
            try {
                this._candleSeries.attachPrimitive(this._bgZonesPlugin);
            } catch (err) {
                console.warn('[DynamicChart] Failed to attach BackgroundColorZonesPlugin:', err);
            }
        }
        return this._bgZonesPlugin;
    }

    /**
     * รวบรวม Background Zones ทั้งหมดจาก SMC indicators แล้วอัปเดตลง Plugin
     */
    _updateAllBgZones() {
        const bgPlugin = this._getBgZonesPlugin();
        if (!bgPlugin) return;

        let allZones = [];
        this._indicators.forEach(entry => {
            if (entry.config.visible !== false && Array.isArray(entry.bgZones)) {
                allZones = allZones.concat(entry.bgZones);
            }
        });
        bgPlugin.setZones(allZones);
    }

    /**
     * รวบรวม Markers ทั้งหมดจาก Indicators และ Custom Markers แล้วอัปเดตลง CandlestickSeries
     */
    _updateAllMarkers() {
        if (!this._candleSeries) return;
        let allMarkers = [];
        this._indicators.forEach((entry) => {
            if (entry.config.visible !== false && Array.isArray(entry.markers)) {
                allMarkers = allMarkers.concat(entry.markers);
            }
        });
        if (Array.isArray(this._customMarkers) && this._customMarkers.length > 0) {
            allMarkers = allMarkers.concat(this._customMarkers);
        }
        if (this._customMarkersMap) {
            Object.values(this._customMarkersMap).forEach(markers => {
                if (Array.isArray(markers)) {
                    allMarkers = allMarkers.concat(markers);
                }
            });
        }
        allMarkers.sort((a, b) => {
            const tA = typeof a.time === 'number' ? a.time : (new Date(a.time).getTime() || 0);
            const tB = typeof b.time === 'number' ? b.time : (new Date(b.time).getTime() || 0);
            return tA - tB;
        });
        try {
            this._candleSeries.setMarkers(allMarkers);
        } catch (err) {
            console.warn('[DynamicChart] setMarkers error:', err);
        }
    }

    /**
     * ดึง price array จาก candleData ตาม source field
     * @param {string} source - 'close', 'open', 'high', 'low'
     * @returns {number[]}
     */
    _extractSourceData(source) {
        return this._candleData.map(c => {
            switch (source) {
                case 'open':  return c.open;
                case 'high':  return c.high;
                case 'low':   return c.low;
                case 'close':
                default:      return c.close;
            }
        });
    }

    /**
     * แปลง computed values array เป็น LineData array
     * กรองเอาเฉพาะค่าที่ valid (ไม่ใช่ 0 และอยู่หลัง warm-up period)
     * @param {number[]} values
     * @param {number} warmupPeriod
     * @returns {Array<{time, value}>}
     */
    _valuesToLineData(values, warmupPeriod) {
        const data = [];
        for (let i = 0; i < values.length; i++) {
            if (i < warmupPeriod - 1 || values[i] === 0) continue;
            data.push({
                time: this._candleData[i].time,
                value: values[i],
            });
        }
        return data;
    }

    /**
     * Recalculate ทุก indicator (เรียกเมื่อ candle data เปลี่ยน)
     */
    _recalcAllIndicators() {
        this._indicators.forEach((entry, id) => {
            const type = entry.config.type.toLowerCase();
            if (type === 'horizontalline') return; // ไม่ต้อง recalc

            // ลบ series เก่า
            entry.series.forEach(s => {
                try { this._chart.removeSeries(s); } catch (e) {}
            });
            entry.series = [];

            // สร้างใหม่
            if (type === 'smc') {
                this._createSMC(entry);
            } else if (type === 'bb') {
                this._createBollingerBands(entry);
            } else if (['ema', 'hma', 'wma', 'sma'].includes(type)) {
                this._createMovingAverage(entry);
            }
        });
    }

    /**
     * Map line style string → LightweightCharts.LineStyle enum
     * @param {string} style - 'solid', 'dashed', 'dotted', 'largeDashed', 'sparseDotted'
     * @returns {number}
     */
    _mapLineStyle(style) {
        const styleMap = {
            'solid':         LightweightCharts.LineStyle.Solid,
            'dashed':        LightweightCharts.LineStyle.Dashed,
            'dotted':        LightweightCharts.LineStyle.Dotted,
            'largedashed':   LightweightCharts.LineStyle.LargeDashed,
            'sparsedotted':  LightweightCharts.LineStyle.SparseDotted,
        };
        return styleMap[(style || 'solid').toLowerCase()] || LightweightCharts.LineStyle.Solid;
    }

    /**
     * Deep merge สอง objects (nested merge แทน shallow Object.assign)
     * @param {Object} target
     * @param {Object} source
     * @returns {Object}
     */
    _deepMerge(target, source) {
        const result = Object.assign({}, target);
        for (const key of Object.keys(source)) {
            if (
                source[key] &&
                typeof source[key] === 'object' &&
                !Array.isArray(source[key]) &&
                target[key] &&
                typeof target[key] === 'object' &&
                !Array.isArray(target[key])
            ) {
                result[key] = this._deepMerge(target[key], source[key]);
            } else {
                result[key] = source[key];
            }
        }
        return result;
    }

    /**
     * อัปเดตข้อมูลวิเคราะห์และแคชเก็บไว้สำหรับเรียกใช้ใน tooltip
     */
    _updateCachedAnalysis() {
        if (!this._candleData || this._candleData.length === 0) {
            this._cachedAnalysisData = [];
            return;
        }
        try {
            const emas = [];
            this._indicators.forEach(ind => {
                if (ind.config.type === 'ema' && ind.config.visible !== false) {
                    emas.push(Number(ind.config.period));
                }
            });
            emas.sort((a, b) => a - b);

            const config = {
                ema: {
                    short: { period: emas[0] || 9, type: 'ema' },
                    medium: { period: emas[1] || 21, type: 'ema' },
                    long: { period: emas[2] || 50, type: 'ema' }
                },
                indicators: {
                    adxPeriod: 14,
                    atrPeriod: 7,
                    atrMulti: 1.3,
                    bbPeriod: 20,
                    ciPeriod: 14,
                    smcPeriod: 50
                }
            };
            this._cachedAnalysisData = this._engine.performAnalysis(this._candleData, config);
        } catch (err) {
            console.error('[DynamicChart] Error recalculating analysis cache:', err);
            this._cachedAnalysisData = [];
        }
    }

    /**
     * Setup ResizeObserver สำหรับ auto-resize chart ตาม container
     */
    _setupResizeObserver(container) {
        if (typeof ResizeObserver === 'undefined') return;
        this._resizeObserver = new ResizeObserver(entries => {
            for (const entry of entries) {
                const { width, height } = entry.contentRect;
                if (this._chart && width > 0 && height > 0) {
                    this._chart.resize(width, height);
                }
            }
        });
        this._resizeObserver.observe(container);
    }
}

// Export สำหรับ Node.js / Module
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DynamicChart;
}

// Export สำหรับ Browser
if (typeof window !== 'undefined') {
    window.DynamicChart = DynamicChart;
}
