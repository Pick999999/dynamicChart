/**
 * ============================================================================
 * labBarrierClass.js
 * ============================================================================
 * Class สำหรับวิเคราะห์ราคา Close ของแท่งเทียนเทียบกับ High Barrier และ Low Barrier
 * 
 * คุณสมบัติ:
 * 1. กำหนด High Barrier และ Low Barrier จาก High / Low ของแท่งเทียนอ้างอิง (Reference Candle) หรือกำหนดเอง
 * 2. มีฟังก์ชันสำหรับวิเคราะห์เมื่อมีแท่งเทียนใหม่เข้ามา เพื่อดูว่าราคา Close ทะลุ High/Low Barrier หรือไม่
 * 3. ส่งผลลัพธ์การวิเคราะห์กลับพร้อมสถานะ, ระยะห่าง (Diff), เปอร์เซ็นต์ และ Flag ต่างๆ
 * 
 * @version 1.0.0
 * @license MIT
 */

class labBarrierClass {
    /**
     * @param {Object} [config] - การตั้งค่าเริ่มต้น (Optional)
     * @param {Object} [config.referenceCandle] - แท่งเทียนอ้างอิง { high, low, close, open, time }
     * @param {number} [config.highBarrier] - ค่า High Barrier เจาะจง (ถ้าไม่ใช้จาก referenceCandle)
     * @param {number} [config.lowBarrier] - ค่า Low Barrier เจาะจง (ถ้าไม่ใช้จาก referenceCandle)
     * @param {number} [config.buffer] - บัฟเฟอร์ราคาเพิ่มเติม (Default: 0)
     * @param {Function} [config.onBreak] - Callback เมื่อเกิดการทะลุกรอบ (breakout)
     */
    constructor(config = {}) {
        this.highBarrier = null;
        this.lowBarrier = null;
        this.referenceCandle = null;
        this.buffer = typeof config.buffer === 'number' ? config.buffer : 0;
        this.onBreak = typeof config.onBreak === 'function' ? config.onBreak : null;
        this.history = []; // เก็บประวัติการวิเคราะห์

        // กำหนด Barrier ตั้งต้นถ้ามีส่งเข้ามา
        if (config.referenceCandle) {
            this.setReferenceCandle(config.referenceCandle);
        } else {
            if (typeof config.highBarrier === 'number') this.highBarrier = config.highBarrier;
            if (typeof config.lowBarrier === 'number') this.lowBarrier = config.lowBarrier;
            if (typeof config.highbarrier === 'number') this.highBarrier = config.highbarrier;
            if (typeof config.lowbarrier === 'number') this.lowBarrier = config.lowbarrier;
        }
    }

    /**
     * กำหนดแท่งเทียนอ้างอิงเพื่อดึง High และ Low มาเป็น Barrier
     * @param {Object} candle - แท่งเทียนอ้างอิง { high, low, open, close, time }
     * @returns {labBarrierClass} คืนค่า this สำหรับ chaining
     */
    setReferenceCandle(candle) {
        if (!candle || typeof candle !== 'object') {
            console.error('[labBarrierClass] Invalid reference candle object.');
            return this;
        }

        const high = this._extractPrice(candle, 'high');
        const low = this._extractPrice(candle, 'low');

        if (high === null || low === null) {
            console.error('[labBarrierClass] Reference candle must contain high and low properties.', candle);
            return this;
        }

        this.referenceCandle = { ...candle };
        this.highBarrier = high + this.buffer;
        this.lowBarrier = low - this.buffer;

        return this;
    }

    /**
     * กำหนดค่า High Barrier และ Low Barrier โดยตรง
     * @param {number} high - ค่า High Barrier
     * @param {number} low - ค่า Low Barrier
     * @returns {labBarrierClass} คืนค่า this สำหรับ chaining
     */
    setBarriers(high, low) {
        if (typeof high === 'number') this.highBarrier = high;
        if (typeof low === 'number') this.lowBarrier = low;
        return this;
    }

    /**
     * Alias สำหรับ highBarrier (getter/setter)
     */
    get highbarrier() {
        return this.highBarrier;
    }

    set highbarrier(val) {
        this.highBarrier = val;
    }

    /**
     * Alias สำหรับ lowBarrier (getter/setter)
     */
    get lowbarrier() {
        return this.lowBarrier;
    }

    set lowbarrier(val) {
        this.lowBarrier = val;
    }

    /**
     * วิเคราะห์แท่งเทียนใหม่ว่า Close Price ทะลุ High Barrier หรือ Low Barrier หรือไม่
     * 
     * @param {Object|number} candle - แท่งเทียนใหม่ { close, high, low, open, time } หรือตัวเลขราคา Close
     * @returns {Object} ผลการวิเคราะห์
     * @property {string} status - สถานะ: 'BREAK_HIGH', 'BREAK_LOW', 'TOUCH_HIGH', 'TOUCH_LOW', 'INSIDE', 'NO_BARRIER'
     * @property {boolean} isBreakHigh - ราคา Close ทะลุ High Barrier ขึ้นไปหรือไม่
     * @property {boolean} isBreakLow - ราคา Close ทะลุ Low Barrier ลงมาหรือไม่
     * @property {boolean} isBreak - มีการทะลุฝั่งใดฝั่งหนึ่งหรือไม่ (isBreakHigh || isBreakLow)
     * @property {boolean} isInside - ราคา Close อยู่ในกรอบระหว่าง Low และ High หรือไม่
     * @property {number} closePrice - ราคา Close ของแท่งเทียน
     * @property {number|null} highBarrier - ค่า High Barrier ที่ใช้เปรียบเทียบ
     * @property {number|null} lowBarrier - ค่า Low Barrier ที่ใช้เปรียบเทียบ
     * @property {number|null} diffHigh - ผลต่าง (close - highBarrier)
     * @property {number|null} diffLow - ผลต่าง (close - lowBarrier)
     * @property {number|null} percentDiffHigh - เปอร์เซ็นต์ผลต่างเทียบกับ High Barrier
     * @property {number|null} percentDiffLow - เปอร์เซ็นต์ผลต่างเทียบกับ Low Barrier
     * @property {boolean} wickBreakHigh - ไส้เทียน (High) ทะลุ High Barrier หรือไม่ (ถ้าส่ง candle object)
     * @property {boolean} wickBreakLow - ไส้เทียน (Low) ทะลุ Low Barrier หรือไม่ (ถ้าส่ง candle object)
     * @property {Object|number} rawInput - ข้อมูลดิบที่ส่งเข้ามา
     */
    analyze(candle) {
        const closePrice = this._extractPrice(candle, 'close');
        const candleHigh = this._extractPrice(candle, 'high');
        const candleLow = this._extractPrice(candle, 'low');
        const candleTime = (candle && typeof candle === 'object' && candle.time) ? candle.time : null;

        if (closePrice === null) {
            return {
                status: 'INVALID_INPUT',
                isBreakHigh: false,
                isBreakLow: false,
                isBreak: false,
                isInside: false,
                closePrice: null,
                highBarrier: this.highBarrier,
                lowBarrier: this.lowBarrier,
                diffHigh: null,
                diffLow: null,
                percentDiffHigh: null,
                percentDiffLow: null,
                wickBreakHigh: false,
                wickBreakLow: false,
                time: candleTime,
                rawInput: candle,
                message: 'Close price not found in candle data.'
            };
        }

        if (this.highBarrier === null && this.lowBarrier === null) {
            return {
                status: 'NO_BARRIER',
                isBreakHigh: false,
                isBreakLow: false,
                isBreak: false,
                isInside: false,
                closePrice: closePrice,
                highBarrier: null,
                lowBarrier: null,
                diffHigh: null,
                diffLow: null,
                percentDiffHigh: null,
                percentDiffLow: null,
                wickBreakHigh: false,
                wickBreakLow: false,
                time: candleTime,
                rawInput: candle,
                message: 'No barriers set. Please set reference candle or barriers first.'
            };
        }

        const hasHigh = typeof this.highBarrier === 'number';
        const hasLow = typeof this.lowBarrier === 'number';

        let isBreakHigh = false;
        let isBreakLow = false;
        let isTouchHigh = false;
        let isTouchLow = false;
        let isInside = false;
        let status = 'INSIDE';

        const diffHigh = hasHigh ? Number((closePrice - this.highBarrier).toFixed(8)) : null;
        const diffLow = hasLow ? Number((closePrice - this.lowBarrier).toFixed(8)) : null;
        const percentDiffHigh = (hasHigh && this.highBarrier !== 0) ? Number(((diffHigh / this.highBarrier) * 100).toFixed(4)) : null;
        const percentDiffLow = (hasLow && this.lowBarrier !== 0) ? Number(((diffLow / this.lowBarrier) * 100).toFixed(4)) : null;

        // เช็คการทะลุ High Barrier
        if (hasHigh && closePrice > this.highBarrier) {
            isBreakHigh = true;
            status = 'BREAK_HIGH';
        } else if (hasHigh && closePrice === this.highBarrier) {
            isTouchHigh = true;
            status = 'TOUCH_HIGH';
        }

        // เช็คการทะลุ Low Barrier
        if (hasLow && closePrice < this.lowBarrier) {
            isBreakLow = true;
            status = 'BREAK_LOW';
        } else if (hasLow && closePrice === this.lowBarrier) {
            isTouchLow = true;
            status = 'TOUCH_LOW';
        }

        // กรณีอยู่ในกรอบ
        if (!isBreakHigh && !isBreakLow && !isTouchHigh && !isTouchLow) {
            isInside = true;
            status = 'INSIDE';
        }

        // ไส้เทียนทะลุกรอบ (Wick breakouts)
        const wickBreakHigh = hasHigh && candleHigh !== null && candleHigh > this.highBarrier;
        const wickBreakLow = hasLow && candleLow !== null && candleLow < this.lowBarrier;

        const result = {
            status: status,
            isBreakHigh: isBreakHigh,
            isBreakLow: isBreakLow,
            isBreak: isBreakHigh || isBreakLow,
            isTouchHigh: isTouchHigh,
            isTouchLow: isTouchLow,
            isInside: isInside,
            closePrice: closePrice,
            highBarrier: this.highBarrier,
            lowBarrier: this.lowBarrier,
            diffHigh: diffHigh,
            diffLow: diffLow,
            percentDiffHigh: percentDiffHigh,
            percentDiffLow: percentDiffLow,
            wickBreakHigh: wickBreakHigh,
            wickBreakLow: wickBreakLow,
            time: candleTime,
            rawInput: candle
        };

        // บันทึกประวัติ
        this.history.push(result);

        // ยิง Callback ถ้ามีการตั้งค่าไว้และมีการทะลุ
        if (result.isBreak && this.onBreak) {
            this.onBreak(result);
        }

        return result;
    }

    /**
     * วิเคราะห์แท่งเทียนเป็นชุด (Array of Candles)
     * @param {Array<Object|number>} candles - รายการแท่งเทียน
     * @returns {Array<Object>} รายการผลลัพธ์การวิเคราะห์
     */
    analyzeBatch(candles) {
        if (!Array.isArray(candles)) {
            console.error('[labBarrierClass] Input must be an array of candles.');
            return [];
        }
        return candles.map(candle => this.analyze(candle));
    }

    /**
     * ดึงค่า Barrier ปัจจุบัน
     * @returns {{ highBarrier: number|null, lowBarrier: number|null, range: number|null }}
     */
    getBarriers() {
        const range = (typeof this.highBarrier === 'number' && typeof this.lowBarrier === 'number')
            ? Number((this.highBarrier - this.lowBarrier).toFixed(8))
            : null;

        return {
            highBarrier: this.highBarrier,
            lowBarrier: this.lowBarrier,
            range: range
        };
    }

    /**
     * ดึงผลลัพธ์การวิเคราะห์ล่าสุด
     * @returns {Object|null}
     */
    getLastResult() {
        return this.history.length > 0 ? this.history[this.history.length - 1] : null;
    }

    /**
     * ล้างประวัติการวิเคราะห์
     */
    clearHistory() {
        this.history = [];
    }

    /**
     * Helper สำหรับดึงค่าราคาจาก candle object หรือ number
     * @private
     */
    _extractPrice(candle, field) {
        if (candle === null || candle === undefined) return null;
        if (typeof candle === 'number') {
            return field === 'close' ? candle : null;
        }
        if (typeof candle === 'object') {
            const key = Object.keys(candle).find(k => k.toLowerCase() === field.toLowerCase());
            if (key !== undefined && typeof candle[key] === 'number') {
                return candle[key];
            }
            if (key !== undefined && !isNaN(Number(candle[key]))) {
                return Number(candle[key]);
            }
        }
        return null;
    }
}

// Export สำหรับ Browser Environment
if (typeof window !== 'undefined') {
    window.labBarrierClass = labBarrierClass;
}

// Export สำหรับ Node.js Environment
if (typeof module !== 'undefined' && module.exports) {
    module.exports = labBarrierClass;
}
