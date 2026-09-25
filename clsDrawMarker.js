/**
 * ============================================================================
 * clsDrawMarker.js
 * ============================================================================
 * Class สำหรับจัดการปุ่มควบคุมและวาด Marker ลงบนกราฟ (Lightweight Charts / DynamicChart)
 * 
 * คุณสมบัติ:
 * 1. รับ Container สำหรับสร้างปุ่มดึง/ล้าง Marker (Win, Loss, All, Clear)
 * 2. ระบุ chartId (ชื่อตัวแปร global หรือ instance) ในการควบคุมกราฟ
 * 3. ระบุ sourceId (ID ของ Textarea/Input) หรือตัวแปร JSON/Function ในการดึงและคัดกรองข้อมูล
 * 4. เมธอดสำหรับล้าง Marker ทั้งหมด
 * 
 * @version 1.0.0
 * @license MIT
 */

class clsDrawMarker {
    /**
     * @param {Object} config
     * @param {string|HTMLElement} config.containerId - ID ของ container หรืออิลิเมนต์ที่ต้องการสร้างปุ่ม
     * @param {string|Object} config.chartId - ชื่อตัวแปร chart ใน window หรือ instance อ็อบเจกต์ตรงๆ
     * @param {string|Object|Function} [config.sourceId] - ID ของ textarea/input แหล่งข้อมูล JSON
     * @param {Object|Array|Function} [config.dataSource] - ตัวเลือกอื่นในการส่งอ็อบเจกต์ JSON หรือฟังก์ชันคืนค่าตรงๆ
     * @param {string|Function} [config.assetFilter] - ชื่อสินทรัพย์ที่ต้องการกรอง หรือฟังก์ชันที่คืนค่าชื่อสินทรัพย์
     * @param {Object} [config.options] - ตัวเลือกอื่นๆ เช่น สไตล์หรือคลาสปุ่ม
     */
    constructor(config) {
        if (!config) {
            console.error('[clsDrawMarker] Configuration is required.');
            return;
        }

        this.containerId = config.containerId;
        this.chartId = config.chartId;
        this.sourceId = config.sourceId || null;
        this.dataSource = config.dataSource || null;
        this.assetFilter = config.assetFilter || null;
        this.options = config.options || {};
        this.category = config.category || (typeof this.containerId === 'string' ? this.containerId : 'trade_markers');

        // คลาส CSS สำหรับปุ่ม
        this.btnClass = this.options.btnClass || 'btn btn-secondary';
        
        this.init();
    }

    /**
     * เริ่มสร้าง UI ปุ่มภายใน Container
     */
    init() {
        let container = null;
        if (typeof this.containerId === 'string') {
            container = document.getElementById(this.containerId);
        } else if (this.containerId instanceof HTMLElement) {
            container = this.containerId;
        }

        if (!container) {
            console.error(`[clsDrawMarker] Container element "${this.containerId}" not found.`);
            return;
        }

        // เคลียร์เนื้อหาเดิม
        container.innerHTML = '';

        // สร้างโครงสร้าง Div wrapper สำหรับปุ่ม
        const wrapper = document.createElement('div');
        wrapper.className = 'draw-marker-wrapper';
        wrapper.style.display = 'flex';
        wrapper.style.alignItems = 'center';
        wrapper.style.justifyContent = 'space-between';
        wrapper.style.flexWrap = 'wrap';
        wrapper.style.gap = '8px';
        wrapper.style.width = '100%';

        // กลุ่มปุ่มคำสั่งวาด
        const btnGroupLeft = document.createElement('div');
        btnGroupLeft.style.display = 'flex';
        btnGroupLeft.style.alignItems = 'center';
        btnGroupLeft.style.gap = '6px';
        btnGroupLeft.style.flexWrap = 'wrap';

        // ป้ายหัวข้อ (ถ้ามีระบุใน options)
        const labelText = this.options.labelText || '📌 Markers:';
        const label = document.createElement('span');
        label.style.fontSize = '12px';
        label.style.color = 'var(--text-secondary, #94a3b8)';
        label.style.fontWeight = '600';
        label.style.marginRight = '4px';
        label.textContent = labelText;
        btnGroupLeft.appendChild(label);

        // 1. ปุ่ม Win
        const btnWin = document.createElement('button');
        btnWin.type = 'button';
        btnWin.className = this.btnClass;
        btnWin.style.cssText = 'font-size:11px; padding:4px 10px; height:28px; gap:4px; border-color:rgba(52, 211, 153, 0.4); color:#34d399; background:rgba(52, 211, 153, 0.1); cursor:pointer;';
        btnWin.innerHTML = '🟢 วาด Marker (Win)';
        btnWin.addEventListener('click', () => this.draw('Win'));
        btnGroupLeft.appendChild(btnWin);

        // 2. ปุ่ม Loss
        const btnLoss = document.createElement('button');
        btnLoss.type = 'button';
        btnLoss.className = this.btnClass;
        btnLoss.style.cssText = 'font-size:11px; padding:4px 10px; height:28px; gap:4px; border-color:rgba(248, 113, 113, 0.4); color:#f87171; background:rgba(248, 113, 113, 0.1); cursor:pointer;';
        btnLoss.innerHTML = '🔴 วาด Marker (Loss)';
        btnLoss.addEventListener('click', () => this.draw('Loss'));
        btnGroupLeft.appendChild(btnLoss);

        // 3. ปุ่ม All
        const btnAll = document.createElement('button');
        btnAll.type = 'button';
        btnAll.className = this.btnClass;
        btnAll.style.cssText = 'font-size:11px; padding:4px 10px; height:28px; gap:4px; border-color:rgba(56, 189, 248, 0.4); color:#38bdf8; background:rgba(56, 189, 248, 0.1); cursor:pointer;';
        btnAll.innerHTML = '📊 วาดทั้งหมด';
        btnAll.addEventListener('click', () => this.draw('All'));
        btnGroupLeft.appendChild(btnAll);

        wrapper.appendChild(btnGroupLeft);

        // 4. ปุ่ม Clear
        const btnClear = document.createElement('button');
        btnClear.type = 'button';
        btnClear.className = this.btnClass;
        btnClear.style.cssText = 'font-size:11px; padding:4px 10px; height:28px; gap:4px; color:var(--text-muted, #64748b); cursor:pointer;';
        btnClear.innerHTML = '🧹 Clear Markers';
        btnClear.addEventListener('click', () => this.clear(true));
        wrapper.appendChild(btnClear);

        container.appendChild(wrapper);
    }

    /**
     * ดึงและประมวลผลข้อมูลจาก Source เพื่อวาด Marker ลงกราฟ
     * @param {string} statusFilter - 'Win', 'Loss', หรือ 'All'
     */
    draw(statusFilter = 'All') {
        const chartInstance = this._resolveChart();
        if (!chartInstance) {
            alert('⚠️ ไม่พบตัวแปรอ็อบเจกต์กราฟ หรือกราฟยังไม่ได้ถูกสร้าง');
            return;
        }

        const rawData = this._resolveData();
        if (!rawData) {
            alert('⚠️ ไม่พบข้อมูลแหล่งอ้างอิง หรือไม่สามารถดึงข้อมูล JSON ได้');
            return;
        }

        let trades = [];
        let rootDateStr = rawData.date || '';

        // คัดกรองและดึงรายการเทรดจากโครงสร้างข้อมูล
        if (Array.isArray(rawData)) {
            trades = rawData;
        } else if (rawData && typeof rawData === 'object') {
            if (rawData.data && typeof rawData.data === 'object') {
                // ค้นหาว่ามีตัวกรอง Asset หรือไม่
                let targetAsset = null;
                if (typeof this.assetFilter === 'function') {
                    targetAsset = this.assetFilter();
                } else if (typeof this.assetFilter === 'string') {
                    targetAsset = this.assetFilter;
                }

                if (targetAsset && rawData.data[targetAsset]) {
                    const assetObj = rawData.data[targetAsset];
                    trades = (assetObj && Array.isArray(assetObj.trades)) ? assetObj.trades : (assetObj.trackOrders || []);
                } else {
                    // หากไม่มีการกรอง รวมข้อมูลจากทุกสินทรัพย์
                    const assetCodes = Object.keys(rawData.data);
                    assetCodes.forEach(code => {
                        const item = rawData.data[code];
                        if (item && Array.isArray(item.trades)) {
                            trades = trades.concat(item.trades);
                        } else if (item && Array.isArray(item.trackOrders)) {
                            trades = trades.concat(item.trackOrders);
                        }
                    });
                }
            } else if (Array.isArray(rawData.trades)) {
                trades = rawData.trades;
            } else if (Array.isArray(rawData.trackOrders)) {
                trades = rawData.trackOrders;
            }
        }

        if (!Array.isArray(trades) || trades.length === 0) {
            alert('⚠️ ไม่พบรายการ Trade (trades หรือ trackOrders) ในข้อมูลที่ระบุ');
            return;
        }

        // กรองตามสถานะ
        const filteredTrades = trades.filter(t => {
            const st = this._getTradeStatus(t);
            if (statusFilter === 'Win') return st === 'Win';
            if (statusFilter === 'Loss') return st === 'Loss';
            return true;
        });

        if (filteredTrades.length === 0) {
            alert(`⚠️ ไม่พบรายการ Trade ที่มีสถานะตรงกับตัวกรอง: "${statusFilter}"`);
            return;
        }

        // ตรวจสอบว่ากราฟมีข้อมูลแท่งเทียนหรือไม่
        if (!chartInstance._candleData || chartInstance._candleData.length === 0) {
            alert('⚠️ กราฟยังไม่มีข้อมูลแท่งเทียนสำหรับการเทียบเวลาเพื่อวาด Marker');
            return;
        }

        const candleData = chartInstance._candleData;
        const candleEpochs = candleData.map(c => this._getCandleEpoch(c.time));

        const newMarkers = [];
        let skippedCount = 0;

        filteredTrades.forEach((t) => {
            const st = this._getTradeStatus(t);
            const isWin = st === 'Win';

            const rawTime = t.epoch || t.date_start || t.purchase_time || t.purchaseTimeDisplay || t.dateStart || t.time || t.sellTimeDisplay || t.sell_time || t.buy_price_time;
            const matchedTime = this._findClosestCandleTime(rawTime, t, candleData, candleEpochs, rootDateStr);

            if (!matchedTime) {
                skippedCount++;
                return;
            }

            let profitVal = parseFloat(t.ThisProfit !== undefined ? t.ThisProfit : (t.profit !== undefined ? t.profit : t.profitLoss));
            let profitStr = !isNaN(profitVal) ? (profitVal >= 0 ? `+$${profitVal.toFixed(2)}` : `-$${Math.abs(profitVal).toFixed(2)}`) : '';

            const action = t.thisAction || t.contract_type || t.tradeCode || '';
            const actionStr = action ? `${action} ` : '';

            const isShort = (this.options && (this.options.format === 'short' || this.options.markerFormat === 'short'));
            const markerText = isShort ? '' : (isWin
                ? `💲 WIN ${actionStr}${profitStr}`.trim()
                : `🛑 LOSS ${actionStr}${profitStr}`.trim());

            newMarkers.push({
                time: matchedTime,
                position: isWin ? 'belowBar' : 'aboveBar',
                color: isWin ? '#34d399' : '#f87171',
                shape: isWin ? 'arrowUp' : 'arrowDown',
                text: markerText,
            });
        });

        if (newMarkers.length === 0) {
            console.error('[clsDrawMarker] Failed to match trade times to candles.', {
                tradesCount: filteredTrades.length,
                firstTrade: filteredTrades[0]
            });
            alert('⚠️ ไม่สามารถจับคู่เวลาของรายการ Trade เข้ากับเวลาของแท่งเทียนในกราฟได้');
            return;
        }

        // วาด Marker บนกราฟ
        if (typeof chartInstance.setCustomMarkers === 'function') {
            chartInstance.setCustomMarkers(newMarkers, this.category);
        } else if (chartInstance._candleSeries && typeof chartInstance._candleSeries.setMarkers === 'function') {
            // กรณีเป็น Lightweight chart series โดยตรง
            chartInstance._candleSeries.setMarkers(newMarkers);
        } else {
            console.error('[clsDrawMarker] Chart instance setMarkers method not found.');
            alert('⚠️ วาดไม่สำเร็จ: ไม่พบฟังก์ชันวาด Marker บนอ็อบเจกต์กราฟ');
            return;
        }

        // แสดงแจ้งเตือนผลลัพธ์ (สามารถปิดได้โดยตั้งใน options.silent)
        if (!this.options.silent) {
            alert(`✅ วาด Marker เรียบร้อยแล้ว (${newMarkers.length} รายการ - ${statusFilter})`);
        }
    }

    /**
     * ล้าง Marker ทั้งหมดบนกราฟ
     * @param {boolean} showNotice - แสดงกล่องข้อความแจ้งเตือนหรือไม่
     */
    clear(showNotice = false) {
        const chartInstance = this._resolveChart();
        if (!chartInstance) {
            if (showNotice) alert('⚠️ ไม่พบตัวแปรอ็อบเจกต์กราฟ');
            return;
        }

        if (typeof chartInstance.clearCustomMarkers === 'function') {
            chartInstance.clearCustomMarkers(this.category);
        } else if (chartInstance._candleSeries && typeof chartInstance._candleSeries.setMarkers === 'function') {
            chartInstance._candleSeries.setMarkers([]);
        }

        if (showNotice && !this.options.silent) {
            alert('🧹 ล้าง Marker ทั้งหมดเรียบร้อยแล้ว!');
        }
    }

    // ========================================================================
    //  INTERNAL HELPERS
    // ========================================================================

    /**
     * ค้นหาและคืนค่า instance ของกราฟ
     */
    _resolveChart() {
        if (typeof this.chartId === 'string') {
            return window[this.chartId] || null;
        }
        return this.chartId; // return object directly
    }

    /**
     * ดึงข้อมูล JSON จากแหล่งที่ระบุ
     */
    _resolveData() {
        // 1. ดึงจาก dataSource (ตรงๆ หรือฟังก์ชัน)
        if (this.dataSource) {
            if (typeof this.dataSource === 'function') {
                return this.dataSource();
            }
            if (typeof this.dataSource === 'object') {
                return this.dataSource;
            }
            if (typeof this.dataSource === 'string') {
                try {
                    return JSON.parse(this.dataSource);
                } catch (e) {
                    console.error('[clsDrawMarker] Failed to parse dataSource string:', e);
                }
            }
        }

        // 2. ดึงจาก sourceId (textarea หรือ input DOM)
        if (this.sourceId) {
            let el = null;
            if (typeof this.sourceId === 'string') {
                el = document.getElementById(this.sourceId) || document.querySelector(this.sourceId);
            } else if (this.sourceId instanceof HTMLElement) {
                el = this.sourceId;
            }

            if (el && (el.value || el.textContent)) {
                try {
                    const text = (el.value || el.textContent).trim();
                    return text ? JSON.parse(text) : null;
                } catch (e) {
                    console.error('[clsDrawMarker] Failed to parse JSON from sourceId:', e);
                }
            }
        }

        return null;
    }

    /**
     * ตรวจสอบสถานะการเทรด (Win / Loss)
     */
    _getTradeStatus(trade) {
        const winStatusStr = (trade.WinStatus || trade.winStatus || trade.status || '').toString().toLowerCase();
        if (['win', 'won', 'success'].includes(winStatusStr)) return 'Win';
        if (['loss', 'lost', 'failed'].includes(winStatusStr)) return 'Loss';

        const profit = parseFloat(trade.ThisProfit !== undefined ? trade.ThisProfit : (trade.profit !== undefined ? trade.profit : trade.profitLoss));
        if (!isNaN(profit)) {
            return profit >= 0 ? 'Win' : 'Loss';
        }

        return 'Unknown';
    }

    /**
     * ค้นหาแท่งเทียนที่ใกล้เคียงเวลาที่ต้องการมากที่สุด
     */
    _findClosestCandleTime(tradeTimeInput, tradeObj, candleData, candleEpochs, rootDateStr) {
        if (!candleData || candleData.length === 0) return null;

        const timeFieldsToTry = [];
        if (tradeTimeInput !== null && tradeTimeInput !== undefined) timeFieldsToTry.push(tradeTimeInput);
        if (tradeObj) {
            if (tradeObj.epoch !== undefined && tradeObj.epoch !== tradeTimeInput) timeFieldsToTry.push(tradeObj.epoch);
            if (tradeObj.date_start !== undefined) timeFieldsToTry.push(tradeObj.date_start);
            if (tradeObj.purchase_time !== undefined) timeFieldsToTry.push(tradeObj.purchase_time);
            if (tradeObj.sell_time !== undefined) timeFieldsToTry.push(tradeObj.sell_time);
            if (tradeObj.date_expiry !== undefined) timeFieldsToTry.push(tradeObj.date_expiry);
            if (tradeObj.buy_price_time !== undefined) timeFieldsToTry.push(tradeObj.buy_price_time);
        }

        let bestTradeEpoch = 0;
        for (const tf of timeFieldsToTry) {
            const ep = this._parseToEpochSeconds(tf, rootDateStr);
            if (ep > 0) {
                bestTradeEpoch = ep;
                break;
            }
        }

        if (!bestTradeEpoch) {
            // ค้นหาโดยการตรงกันของ string ตรงๆ
            const foundDirect = candleData.find(c => String(c.time) === String(tradeTimeInput));
            return foundDirect ? foundDirect.time : candleData[0].time;
        }

        // จัดการกรณีเวลาของแท่งเทียนกับประวัติเทรดคนละ Timezone (ทดสอบ: ตรงๆ, ลบ offset, บวก offset)
        const tzOffsetSec = new Date().getTimezoneOffset() * -60;
        const candidates = [bestTradeEpoch];
        if (tzOffsetSec !== 0) {
            candidates.push(bestTradeEpoch - tzOffsetSec);
            candidates.push(bestTradeEpoch + tzOffsetSec);
        }

        let bestCandleIndex = -1;
        let minDiffSeconds = Infinity;

        for (const testEpoch of candidates) {
            for (let i = 0; i < candleEpochs.length; i++) {
                const cEpoch = candleEpochs[i];
                if (!cEpoch) continue;
                const diff = Math.abs(testEpoch - cEpoch);
                if (diff < minDiffSeconds) {
                    minDiffSeconds = diff;
                    bestCandleIndex = i;
                }
            }
        }

        // ยอมรับถ้าระยะห่างไม่เกิน 24 ชั่วโมง (86400 วินาที)
        if (minDiffSeconds <= 86400 && bestCandleIndex >= 0) {
            return candleData[bestCandleIndex].time;
        }

        // Fallback: จับคู่ตามช่วงเวลา (HH:MM:SS) ในแต่ละวัน
        const tradeDateObj = new Date(bestTradeEpoch * 1000);
        const tradeSecondsInDay = (tradeDateObj.getUTCHours() * 3600) + (tradeDateObj.getUTCMinutes() * 60) + tradeDateObj.getUTCSeconds();

        let bestTodIndex = -1;
        let minTodDiff = Infinity;

        for (let i = 0; i < candleEpochs.length; i++) {
            const cEpoch = candleEpochs[i];
            if (!cEpoch) continue;
            const cDateObj = new Date(cEpoch * 1000);
            const cSecondsInDay = (cDateObj.getUTCHours() * 3600) + (cDateObj.getUTCMinutes() * 60) + cDateObj.getUTCSeconds();
            const todDiff = Math.abs(tradeSecondsInDay - cSecondsInDay);
            if (todDiff < minTodDiff) {
                minTodDiff = todDiff;
                bestTodIndex = i;
            }
        }

        return bestTodIndex >= 0 ? candleData[bestTodIndex].time : candleData[0].time;
    }

    /**
     * แปลงรูปแบบวันเวลาเป็น Epoch Seconds
     */
    _parseToEpochSeconds(timeInput, rootDateStr) {
        if (timeInput === null || timeInput === undefined) return 0;
        if (typeof timeInput === 'number') {
            return timeInput > 1e11 ? Math.floor(timeInput / 1000) : Math.floor(timeInput);
        }

        let str = String(timeInput).trim();
        if (!str) return 0;

        if (/^\d+$/.test(str)) {
            const num = parseInt(str, 10);
            return num > 1e11 ? Math.floor(num / 1000) : num;
        }

        if (/^\d+\.\d+$/.test(str)) {
            const num = parseFloat(str);
            return num > 1e11 ? Math.floor(num / 1000) : Math.floor(num);
        }

        if (/^\d{1,2}:\d{2}(:\d{2})?$/.test(str) && rootDateStr) {
            str = `${rootDateStr} ${str}`;
        }

        const isoLike = str.replace(/^(\d{4}[-/]\d{1,2}[-/]\d{1,2})\s+/, '$1T');
        let d = new Date(isoLike);
        if (!isNaN(d.getTime())) return Math.floor(d.getTime() / 1000);

        d = new Date(str);
        if (!isNaN(d.getTime())) return Math.floor(d.getTime() / 1000);

        const dmyMatch = str.match(/^(\d{1,2})[/\-.](\d{1,2})[/\-.](\d{4})\s*(.*)$/);
        if (dmyMatch) {
            const [, dd, mm, yyyy, rest] = dmyMatch;
            const isoStr = `${yyyy}-${mm.padStart(2,'0')}-${dd.padStart(2,'0')}T${rest || '00:00:00'}`;
            d = new Date(isoStr);
            if (!isNaN(d.getTime())) return Math.floor(d.getTime() / 1000);
        }

        return 0;
    }

    /**
     * ดึงค่า Epoch ของแท่งเทียน
     */
    _getCandleEpoch(cTime) {
        if (typeof cTime === 'number') return cTime > 1e11 ? Math.floor(cTime / 1000) : cTime;
        if (typeof cTime === 'string') {
            if (/^\d+$/.test(cTime)) {
                const num = parseInt(cTime, 10);
                return num > 1e11 ? Math.floor(num / 1000) : num;
            }
            const d = new Date(cTime.replace(' ', 'T'));
            if (!isNaN(d.getTime())) return Math.floor(d.getTime() / 1000);
            const d2 = new Date(cTime);
            if (!isNaN(d2.getTime())) return Math.floor(d2.getTime() / 1000);
        }
        return 0;
    }
}

// Export ตัวแปรไปยัง window สำหรับ browser
if (typeof window !== 'undefined') {
    window.clsDrawMarker = clsDrawMarker;
}

// Export สำหรับ Node.js / module
if (typeof module !== 'undefined' && module.exports) {
    module.exports = clsDrawMarker;
}
