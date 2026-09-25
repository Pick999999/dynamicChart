/**
 * ============================================================================
 * predictNextCandle.js — Next-Candle Predictive Engine
 * ============================================================================
 * ทำนายทิศทางและแนวโน้มของแท่งเทียนถัดไป (Next Candle Prediction)
 * รองรับ 2 โหมด:
 * 1. Pure Candle Mode (ไม่ใช้ Tick Data) — Price Action + V5 Cases + Multi-Bar Context
 * 2. Hybrid Mode (ผสมผสาน Candle + Last 10s Tick Flow) — แม่นยำสูงสุดสำหรับ Realtime
 *
 * @author Antigravity AI
 * @version 1.0.0
 */

(function (root, factory) {
  if (typeof module !== 'undefined' && module.exports) {
    // Node.js / CommonJS
    module.exports = factory(require('./detectTrend_V5.js'));
  } else {
    // Browser Global
    root.NextCandlePredictor = factory(root.TrendDetector);
  }
})(typeof self !== 'undefined' ? self : this, function (trendDetector) {
  'use strict';

  var TREND_CASES = (trendDetector && trendDetector.TREND_CASES) || {};
  var detectTrend = (trendDetector && trendDetector.detectTrend) || function () { return null; };

  /**
   * ทำนายแท่งถัดไปสำหรับแท่งที่ index
   *
   * @param {Array} candles - Array ของแท่งเทียน { open, high, low, close, time, is_atr? }
   * @param {number} index  - ตำแหน่งแท่งปัจจุบันที่ต้องการทำนายแท่งถัดไป
   * @param {Array|null} [candleTicks=null] - Array ของ Ticks เฉพาะในแท่งนี้ [{ candleTimestamp, closePrices }, ...]
   * @param {Object} [options={}]
   * @param {string} [options.mode='auto'] - 'auto' | 'pure_candle' | 'hybrid'
   * @param {number} [options.timeframeSec=60] - ระยะเวลาของแท่ง (วินาที) ปกติคือ 60s (1 นาที)
   * @returns {Object} ผลลัพธ์การทำนาย
   */
  function predictNextCandle(candles, index, candleTicks, options) {
    options = options || {};
    var mode = options.mode || (candleTicks && candleTicks.length >= 5 ? 'hybrid' : 'pure_candle');
    var timeframeSec = options.timeframeSec || 60;

    if (!candles || index < 2 || index >= candles.length) {
      return {
        nextBarBias: 'NEUTRAL',
        expectedAction: 'WAIT',
        confidence: 'LOW',
        score: 50,
        bullScore: 50,
        bearScore: 50,
        mode: mode,
        reasons: ['ข้อมูลแท่งเทียนไม่เพียงพอสำหรับการทำนาย (ต้องการอย่างน้อย 3 แท่ง)'],
        trendResult: null,
        tickAnalysis: null,
      };
    }

    var current = candles[index];
    var prev1 = candles[index - 1];
    var prev2 = candles[index - 2];

    // 1. ดึงผลลัพธ์ detectTrend_V5
    var trendResult = detectTrend(candles, index);
    var caseCode = (trendResult && trendResult.caseCode) || '';
    var caseDesc = (trendResult && trendResult.caseDesc) || '';
    var structure = (trendResult && trendResult.structure) || {};
    var closePos = (trendResult && trendResult.closePosition !== null) ? trendResult.closePosition : 0.5;
    var bodyRatio = (trendResult && trendResult.bodyRatio !== null) ? trendResult.bodyRatio : 0;
    var rangeRatio = (trendResult && trendResult.rangeRatio !== null) ? trendResult.rangeRatio : 1;

    var bullRaw = 0;
    var bearRaw = 0;
    var reasons = [];

    // =========================================================================
    // SECTION 1: Pure Candle Scoring (Max 100 คะแนน)
    // =========================================================================

    // 1.1 V5 Case Signal Scoring (40 คะแนน)
    switch (caseCode) {
      // --- Strong Bullish Patterns ---
      case 'UP-CONFIRM':
        bullRaw += 40;
        reasons.push('🟢 ทำ Higher High และปิดยืนโซนบน (Confirmed Up)');
        break;
      case 'EG-BULLISH':
        bullRaw += 42;
        reasons.push('🟢 Bullish Engulfing กลืนแท่งก่อนหน้าสมบูรณ์');
        break;
      case 'SPK-CONTINUE-UP':
        bullRaw += 40;
        reasons.push('🟢 Spike Breakout พุ่งแรงและปิดยืนโซนบน');
        break;
      case 'RJ-BEARTRAP-STRONG':
      case 'SPK-BEARTRAP':
        bullRaw += 42;
        reasons.push('🟢 Bear Trap! ดักขายแล้วมีแรงซื้อดันกลับขึ้นปิดเต็มแท่ง (Reversal Up)');
        break;

      // --- Strong Bearish Patterns ---
      case 'DN-CONFIRM':
        bearRaw += 40;
        reasons.push('🔴 ทำ Lower Low และปิดกดโซนล่าง (Confirmed Down)');
        break;
      case 'EG-BEARISH':
        bearRaw += 42;
        reasons.push('🔴 Bearish Engulfing กลืนแท่งก่อนหน้าสมบูรณ์');
        break;
      case 'SPK-CONTINUE-DN':
        bearRaw += 40;
        reasons.push('🔴 Spike Breakout ทิ่มลงแรงและปิดกดโซนล่าง');
        break;
      case 'RJ-BULLTRAP-STRONG':
      case 'SPK-BULLTRAP':
        bearRaw += 42;
        reasons.push('🔴 Bull Trap! ดักซื้อแล้วถูกแรงขายทุบกลับลงปิดต่ำ (Reversal Down)');
        break;

      // --- Moderate / Wick Rejections ---
      case 'RJ-UPWICK-WEAK':
      case 'RJ-FOLLOWFAIL-UP':
        bearRaw += 26;
        bullRaw += 6;
        reasons.push('⚠️ ไส้บนยาวหรือโมเมนตัมขาขึ้นเริ่มแผ่ว (มีแรงขายกดปลายแท่ง)');
        break;
      case 'RJ-LOWWICK-WEAK':
      case 'RJ-FOLLOWFAIL-DN':
        bullRaw += 26;
        bearRaw += 6;
        reasons.push('⚠️ ไส้ล่างยาวหรือโมเมนตัมขาลงเริ่มแผ่ว (มีแรงซื้อดันก้นแท่ง)');
        break;

      case 'SPK-HESITANT-UP':
        bullRaw += 20;
        bearRaw += 12;
        reasons.push('⚡ Spike ขึ้นแต่ปิดไม่เด็ดขาด (มีแรงซื้อแต่ยังลังเล)');
        break;
      case 'SPK-HESITANT-DN':
        bearRaw += 20;
        bullRaw += 12;
        reasons.push('⚡ Spike ลงแต่ปิดไม่เด็ดขาด (มีแรงขายแต่ยังลังเล)');
        break;

      case 'SD-FLATRESISTANCE':
        bearRaw += 24;
        reasons.push('🧱 ชนแนวต้านเดิมซ้ำแต่ไม่ผ่าน (มีโอกาสย่อตัว)');
        break;
      case 'SD-FLATSUPPORT':
        bullRaw += 24;
        reasons.push('🧱 ชนแนวรับเดิมซ้ำแล้วเด้งกลับ (มีโอกาสดีดขึ้น)');
        break;

      // --- Indecision / Sideways / Whipsaw ---
      case 'SD-INSIDEBAR':
        bullRaw += 10;
        bearRaw += 10;
        reasons.push('📦 Inside Bar ตลาดบีบตัวรอเลือกทาง');
        break;
      case 'SD-DOJI':
        bullRaw += 10;
        bearRaw += 10;
        reasons.push('⚪ Doji แรงซื้อแรงขายสมดุล');
        break;
      case 'SPK-WHIPSAW':
        bullRaw += 6;
        bearRaw += 6;
        reasons.push('🌪️ Spike Whipsaw สับขาหลอกและผันผวนรุนแรง ไร้ทิศทาง');
        break;
      default:
        bullRaw += 10;
        bearRaw += 10;
        break;
    }

    // 1.2 Structure Scoring (25 คะแนน)
    if (structure.higherHigh && structure.higherLow) {
      bullRaw += 25;
      reasons.push('📈 โครงสร้างขาขึ้นแข็งแกร่ง (HH + HL)');
    } else if (structure.lowerLow && structure.lowerHigh) {
      bearRaw += 25;
      reasons.push('📉 โครงสร้างขาลงแข็งแกร่ง (LL + LH)');
    } else if (structure.higherHigh) {
      bullRaw += 14;
      bearRaw += 4;
    } else if (structure.lowerLow) {
      bearRaw += 14;
      bullRaw += 4;
    }

    // 1.3 Close Position & Body Strength (20 คะแนน)
    if (closePos >= 0.75) {
      bullRaw += 20;
      if (bodyRatio >= 0.5) bullRaw += 5;
    } else if (closePos <= 0.25) {
      bearRaw += 20;
      if (bodyRatio >= 0.5) bearRaw += 5;
    } else if (closePos >= 0.55) {
      bullRaw += 10;
    } else if (closePos <= 0.45) {
      bearRaw += 10;
    }

    // 1.4 Streak / Consecutive Momentum (15 คะแนน)
    var upStreak = 0;
    var downStreak = 0;
    for (var s = index; s >= Math.max(0, index - 4); s--) {
      var cCandle = candles[s];
      if (cCandle.close > cCandle.open) {
        if (downStreak === 0) upStreak++;
        else break;
      } else if (cCandle.close < cCandle.open) {
        if (upStreak === 0) downStreak++;
        else break;
      }
    }

    if (upStreak >= 1 && upStreak <= 3) {
      bullRaw += 15;
    } else if (upStreak >= 5) {
      // Overextended risk
      bearRaw += 10;
      reasons.push('⚠️ แท่งเขียวต่อเนื่อง 5 แท่ง (ระวังพักตัว / Overextended)');
    }

    if (downStreak >= 1 && downStreak <= 3) {
      bearRaw += 15;
    } else if (downStreak >= 5) {
      bullRaw += 10;
      reasons.push('⚠️ แท่งแดงต่อเนื่อง 5 แท่ง (ระวังดีดตัว / Overextended)');
    }

    // =========================================================================
    // 1.5 Whipsaw Zone & Color Alternation Analysis (Whipsaw Guard Filter)
    // =========================================================================
    var whipsawStatus = (trendResult && trendResult.whipsawStatus) || 'TRENDING';
    var isWhipsaw = (trendResult && trendResult.isWhipsaw) || false;
    var colorSequence = (trendResult && trendResult.colorSequence) || '';
    var colorSwitches = (trendResult && typeof trendResult.colorSwitches === 'number') ? trendResult.colorSwitches : 0;
    var trendStrength = (trendResult && trendResult.trendStrength) || 'NEUTRAL';

    var isConfirmedWhipsaw = whipsawStatus === 'CONFIRMED_WHIPSAW' || trendStrength === 'WHIPSAW_ZONE' || colorSwitches >= 3;
    var isEnteringWhipsaw = whipsawStatus === 'ENTERING_WHIPSAW' || (colorSwitches === 2 && isWhipsaw);

    if (isConfirmedWhipsaw) {
      // ดึงคะแนนเข้าสู่โซนสมดุล (Dampen extreme directional bias) เพื่อตัดสัญญาณหลอก
      bullRaw = Math.round(bullRaw * 0.35 + 20);
      bearRaw = Math.round(bearRaw * 0.35 + 20);
      reasons.push('🚫 [Whipsaw Guard] ตรวจพบการสลับสีฟันปลา 4 แท่งติด (' + colorSequence + ') สภาวะ Whipsaw รุนแรง');
    } else if (isEnteringWhipsaw) {
      bullRaw = Math.round(bullRaw * 0.70 + 10);
      bearRaw = Math.round(bearRaw * 0.70 + 10);
      reasons.push('⚠️ [Whipsaw Alert] ตลาดเริ่มสลับสี 3 แท่งติด (' + colorSequence + ') เสี่ยงต่อการกลับตัวหลอก');
    }

    // =========================================================================
    // SECTION 2: Tick Microstructure Analysis (Last 10s Closing Flow)
    // =========================================================================
    var tickAnalysis = null;
    var tickWeight = 0; // 0% in pure candle, 35% in hybrid

    if (mode === 'hybrid' && candleTicks && candleTicks.length >= 5) {
      tickWeight = 35; // 35% of total prediction weight
      var candleStart = Number(current.time);
      var candleEnd = candleStart + timeframeSec;
      var last10sStart = candleEnd - 10;

      // กรอง Ticks ช่วง 10 วินาทีสุดท้าย (Last 10s: 50s - 59s)
      var last10sTicks = candleTicks.filter(function (t) {
        var ts = Number(t.candleTimestamp);
        return ts >= last10sStart && ts <= candleEnd;
      });

      // ถ้าไม่มี ticks ช่วง 10s ให้ใช้ 20% ticks ท้ายสุดแทน
      if (last10sTicks.length < 3) {
        var sliceCount = Math.max(3, Math.floor(candleTicks.length * 0.2));
        last10sTicks = candleTicks.slice(candleTicks.length - sliceCount);
      }

      var tickPrices = last10sTicks.map(function (t) { return Number(t.closePrices); });
      var pStart10s = tickPrices[0];
      var pEnd10s = tickPrices[tickPrices.length - 1];
      var delta10s = pEnd10s - pStart10s;
      var max10s = Math.max.apply(null, tickPrices);
      var min10s = Math.min.apply(null, tickPrices);
      var range10s = max10s - min10s;

      // คำนวณความเร็วและทิศทางของแต่ละ Tick ในช่วง 10s
      var upTicks = 0;
      var downTicks = 0;
      for (var tIdx = 1; tIdx < tickPrices.length; tIdx++) {
        if (tickPrices[tIdx] > tickPrices[tIdx - 1]) upTicks++;
        else if (tickPrices[tIdx] < tickPrices[tIdx - 1]) downTicks++;
      }

      // ตรวจสอบ Last-Second Snapback (3 วินาทีสุดท้าย หักหัวกลับหรือไม่)
      var last3Count = Math.min(4, tickPrices.length);
      var pLast3Start = tickPrices[tickPrices.length - last3Count];
      var deltaLast3 = pEnd10s - pLast3Start;

      var isClosingSurgeUp = delta10s > 0 && pEnd10s >= max10s - (range10s * 0.15) && upTicks > downTicks;
      var isClosingSurgeDown = delta10s < 0 && pEnd10s <= min10s + (range10s * 0.15) && downTicks > upTicks;
      var isBullSnapback = delta10s < 0 && deltaLast3 > 0 && (deltaLast3 / (range10s || 1)) >= 0.4;
      var isBearSnapback = delta10s > 0 && deltaLast3 < 0 && (Math.abs(deltaLast3) / (range10s || 1)) >= 0.4;

      var tickBullScore = 50;
      var tickBearScore = 50;

      if (isClosingSurgeUp) {
        tickBullScore = 90;
        tickBearScore = 10;
        reasons.push('⚡ [Tick 10s] Closing Surge พุ่งขึ้นแรงและปิดยืนจุดสูงสุด');
      } else if (isClosingSurgeDown) {
        tickBearScore = 90;
        tickBullScore = 10;
        reasons.push('⚡ [Tick 10s] Closing Surge ทิ่มลงแรงและปิดกดจุดต่ำสุด');
      } else if (isBearSnapback) {
        tickBearScore = 80;
        tickBullScore = 20;
        reasons.push('⚡ [Tick 10s] เกิด Bearish Snapback โดนตบลงแรงในวินาทีสุดท้าย');
      } else if (isBullSnapback) {
        tickBullScore = 80;
        tickBearScore = 20;
        reasons.push('⚡ [Tick 10s] เกิด Bullish Snapback มีแรงดีดกลับขึ้นในวินาทีสุดท้าย');
      } else if (delta10s > 0) {
        tickBullScore = 65;
        tickBearScore = 35;
        reasons.push('⚡ [Tick 10s] ราคาขยับขึ้นบวกสุทธิในช่วง 10 วิสุดท้าย');
      } else if (delta10s < 0) {
        tickBearScore = 65;
        tickBullScore = 35;
        reasons.push('⚡ [Tick 10s] ราคาขยับลงลบสุทธิในช่วง 10 วิสุดท้าย');
      }

      tickAnalysis = {
        totalTicks: candleTicks.length,
        last10sTickCount: last10sTicks.length,
        delta10s: Number(delta10s.toFixed(2)),
        upTicks: upTicks,
        downTicks: downTicks,
        isClosingSurgeUp: isClosingSurgeUp,
        isClosingSurgeDown: isClosingSurgeDown,
        isBullSnapback: isBullSnapback,
        isBearSnapback: isBearSnapback,
        tickBullScore: tickBullScore,
        tickBearScore: tickBearScore,
      };
    }

    // =========================================================================
    // SECTION 3: Final Combination & Decision Matrix
    // =========================================================================
    var candleTotal = Math.max(1, bullRaw + bearRaw);
    var candleBullPct = (bullRaw / candleTotal) * 100;
    var candleBearPct = (bearRaw / candleTotal) * 100;

    var finalBullScore = 0;
    var finalBearScore = 0;

    if (tickAnalysis && tickWeight > 0) {
      // Hybrid Mode: Candle 65% + Tick 35%
      finalBullScore = (candleBullPct * 0.65) + (tickAnalysis.tickBullScore * 0.35);
      finalBearScore = (candleBearPct * 0.65) + (tickAnalysis.tickBearScore * 0.35);
    } else {
      // Pure Candle Mode: 100% Candle
      finalBullScore = candleBullPct;
      finalBearScore = candleBearPct;
    }

    finalBullScore = Math.min(99, Math.max(1, Math.round(finalBullScore)));
    finalBearScore = Math.min(99, Math.max(1, Math.round(finalBearScore)));

    var nextBarBias = 'NEUTRAL';
    var expectedAction = 'WAIT';
    var confidence = 'LOW';
    var netDiff = Math.abs(finalBullScore - finalBearScore);
    var dominantScore = Math.max(finalBullScore, finalBearScore);

    if (isConfirmedWhipsaw) {
      nextBarBias = 'NEUTRAL';
      expectedAction = 'WAIT';
      confidence = 'LOW';
      reasons.push('🛑 [Action Blocked] บังคับ WAIT เนื่องจากอยู่ใน Whipsaw Zone เพื่อป้องกันการโดนหลอก');
    } else {
      var requiredSpread = isEnteringWhipsaw ? 24 : 14;
      if (finalBullScore > finalBearScore + requiredSpread) {
        nextBarBias = 'BULLISH';
        expectedAction = 'CALL';
      } else if (finalBearScore > finalBullScore + requiredSpread) {
        nextBarBias = 'BEARISH';
        expectedAction = 'PUT';
      } else {
        nextBarBias = 'NEUTRAL';
        expectedAction = 'WAIT';
      }

      // กำหนดระดับความมั่นใจ (Confidence)
      if (dominantScore >= 75 && netDiff >= 30 && !isEnteringWhipsaw) {
        confidence = 'HIGH';
      } else if (dominantScore >= 58 && netDiff >= 15) {
        confidence = isEnteringWhipsaw ? 'LOW' : 'MEDIUM';
      } else {
        confidence = 'LOW';
      }
    }

    var sequenceResult = evaluateSequenceAction(candles, index);

    return {
      nextBarBias: nextBarBias,       // 'BULLISH' | 'BEARISH' | 'NEUTRAL'
      expectedAction: expectedAction, // 'CALL' | 'PUT' | 'WAIT'
      confidence: confidence,         // 'HIGH' | 'MEDIUM' | 'LOW'
      score: dominantScore,           // 0 - 100%
      bullScore: finalBullScore,
      bearScore: finalBearScore,
      mode: mode,                     // 'pure_candle' | 'hybrid'
      reasons: reasons,
      trendResult: trendResult,
      sequenceResult: sequenceResult, // 2-Bar Sequence Matrix Result
      tickAnalysis: tickAnalysis,
    };
  }

  /**
   * ============================================================================
   * 2-Bar Code Sequence Action Engine (วิเคราะห์คู่รหัสแท่งก่อนหน้า -> แท่งปัจจุบัน)
   * ============================================================================
   * @param {Array} candles - Array แท่งเทียน
   * @param {number} index  - Index แท่งปัจจุบัน
   * @returns {Object} ผลลัพธ์การทำนายแบบคู่รหัส (Sequence Action)
   */
  function evaluateSequenceAction(candles, index) {
    if (!candles || index < 1 || index >= candles.length) {
      return {
        prevCodeNo: null,
        currCodeNo: null,
        pairKey: 'N/A',
        pairDisplay: '—',
        patternName: 'ข้อมูลไม่เพียงพอ',
        action: 'WAIT',
        confidence: 'LOW',
        score: 50,
        reason: 'ต้องมีข้อมูลแท่งเทียนอย่างน้อย 2 แท่งในการจับคู่รหัส',
        matchType: 'NONE'
      };
    }

    var currTrend = detectTrend(candles, index);
    var prevTrend = detectTrend(candles, index - 1);

    var codeNoMap = {
      'UP-CONFIRM': 1, 'CONFIRMED_UP': 1,
      'DN-CONFIRM': 2, 'CONFIRMED_DOWN': 2,
      'SPK-CONTINUE-UP': 3, 'SPIKE_CONTINUATION_UP': 3,
      'SPK-CONTINUE-DN': 4, 'SPIKE_CONTINUATION_DOWN': 4,
      'SPK-BULLTRAP': 5, 'SPIKE_BULL_TRAP': 5,
      'SPK-BEARTRAP': 6, 'SPIKE_BEAR_TRAP': 6,
      'SPK-NODIRECTION': 7, 'SPIKE_NO_CLEAR_DIRECTION': 7,
      'SPK-HESITANT-UP': 8, 'SPIKE_HESITANT_UP': 8,
      'EG-BULLISH': 9, 'BULLISH_ENGULFING': 9,
      'EG-BEARISH': 10, 'BEARISH_ENGULFING': 10,
      'RJ-BULLTRAP-STRONG': 11, 'STRONG_BULL_TRAP': 11,
      'RJ-BEARTRAP-STRONG': 12, 'STRONG_BEAR_TRAP': 12,
      'RJ-FOLLOWFAIL-UP': 13, 'FAILED_FOLLOWTHROUGH_UP': 13,
      'RJ-FOLLOWFAIL-DN': 14, 'FAILED_FOLLOWTHROUGH_DOWN': 14,
      'RJ-UPWICK-WEAK': 15, 'WEAK_UPPER_WICK': 15,
      'RJ-LOWWICK-WEAK': 16, 'WEAK_LOWER_WICK': 16,
      'SD-INSIDEBAR': 17, 'INSIDE_BAR': 17,
      'SD-MIXEDSIGNAL': 18, 'MIXED_SIGNAL': 18,
      'SD-MIXEDSIGNAL-DN': 19, 'MIXED_SIGNAL_DOWN': 19,
      'SYS-NODATA': 20, 'INSUFFICIENT_DATA': 20,
      'SD-DOJI': 21, 'DOJI_INDECISION': 21,
      'SD-FLATRESISTANCE': 22, 'FLAT_RESISTANCE_TEST': 22,
      'SD-FLATSUPPORT': 23, 'FLAT_SUPPORT_TEST': 23,
      'SD-NOSTRUCTURE': 24, 'NO_CLEAR_STRUCTURE': 24,
      'EG-INDECISION': 25, 'ENGULFING_INDECISION': 25,
      'SPK-HESITANT-DN': 26, 'SPIKE_HESITANT_DOWN': 26,
      'SPK-WHIPSAW': 27, 'SPIKE_WHIPSAW': 27
    };

    var pCodeNo = (prevTrend && prevTrend.codeNo !== undefined && prevTrend.codeNo !== null) 
      ? prevTrend.codeNo 
      : (prevTrend ? (codeNoMap[prevTrend.caseCode] || codeNoMap[prevTrend.caseDesc] || 0) : 0);

    var cCodeNo = (currTrend && currTrend.codeNo !== undefined && currTrend.codeNo !== null) 
      ? currTrend.codeNo 
      : (currTrend ? (codeNoMap[currTrend.caseCode] || codeNoMap[currTrend.caseDesc] || 0) : 0);

    var pCode = (prevTrend && (prevTrend.caseCode || prevTrend.subType)) || 'SYS-NODATA';
    var cCode = (currTrend && (currTrend.caseCode || currTrend.subType)) || 'SYS-NODATA';

    var pairKey = pCodeNo + '->' + cCodeNo;
    var pairDisplay = '[No. ' + pCodeNo + ' ➔ ' + cCodeNo + '] ' + pCode + ' ➔ ' + cCode;

    var currCandle = candles[index];
    var prevCandle = candles[index - 1];
    var currIsBullish = currCandle.close >= currCandle.open;
    var prevIsBullish = prevCandle.close >= prevCandle.open;

    // ตารางกฎคู่รหัสเฉพาะ (High-Probability 2-Bar Transition Rules)
    var SEQUENCE_RULES = {
      // 🚀 Inside Bar Breakouts (17 -> ...)
      '17->1':  { patternName: 'Inside Bar Breakout UP', action: 'CALL', confidence: 'HIGH', score: 85, reason: 'Inside Bar สะสมพลังแล้วระเบิดทะลุทำ Higher High ขาขึ้นชัดเจน' },
      '17->2':  { patternName: 'Inside Bar Breakout DOWN', action: 'PUT', confidence: 'HIGH', score: 85, reason: 'Inside Bar สะสมพลังแล้วระเบิดทะลุทำ Lower Low ขาลงชัดเจน' },
      '17->9':  { patternName: 'Inside Bar Bullish Engulfing', action: 'CALL', confidence: 'MAX', score: 92, reason: 'สะสมพลังในกรอบแล้วตามด้วยแท่งเขียวกลืนกินเต็มแท่ง (Strong Bull Break)' },
      '17->10': { patternName: 'Inside Bar Bearish Engulfing', action: 'PUT', confidence: 'MAX', score: 92, reason: 'สะสมพลังในกรอบแล้วตามด้วยแท่งแดงกลืนกินเต็มแท่ง (Strong Bear Break)' },
      '17->3':  { patternName: 'Inside Bar Spike Breakout UP', action: 'CALL', confidence: 'HIGH', score: 88, reason: 'Spike พุ่งทะลุกรอบ Inside Bar รุนแรง' },
      '17->4':  { patternName: 'Inside Bar Spike Breakdown DOWN', action: 'PUT', confidence: 'HIGH', score: 88, reason: 'Spike ทิ่มทะลุกรอบ Inside Bar รุนแรง' },
      '17->17': { patternName: 'Double Inside Bar Compression', action: 'WAIT', confidence: 'HIGH', score: 90, reason: 'Inside Bar ซ้อน 2 แท่ง บีบตัวแคบสุดขีด รอเลือกทาง' },

      // ⚠️ Traps & Exhaustion (1 -> ... หรือ 2 -> ...)
      '1->5':   { patternName: 'UpTrend Spike Bull Trap', action: 'PUT', confidence: 'MAX', score: 88, reason: 'ขาขึ้นพุ่งทำ New High แล้วโดนทุบรูดมิดแท่ง (Bull Trap) เสี่ยงกลับตัวลงรุนแรง' },
      '1->11':  { patternName: 'UpTrend Strong Bull Trap', action: 'PUT', confidence: 'MAX', score: 86, reason: 'พยายามทำ High แต่ถูกแรงขายปฏิเสธอย่างรุนแรง (Strong Bull Trap)' },
      '2->6':   { patternName: 'DownTrend Spike Bear Trap', action: 'CALL', confidence: 'MAX', score: 88, reason: 'ขาลงทิ่มทำ New Low แล้วมีแรงซื้อกระชากกลับปิดเต็มแท่ง (Bear Trap) กลับตัวขึ้นแรง' },
      '2->12':  { patternName: 'DownTrend Strong Bear Trap', action: 'CALL', confidence: 'MAX', score: 86, reason: 'พยายามทำ Low แต่ถูกแรงซื้อดันกลับอย่างแข็งแกร่ง (Strong Bear Trap)' },
      '3->5':   { patternName: 'Double Spike Exhaustion Top', action: 'PUT', confidence: 'MAX', score: 90, reason: 'Spike พุ่งสุดตัวแล้วตามด้วย Bull Trap จบคลื่นขาขึ้นทันที' },
      '4->6':   { patternName: 'Double Spike Exhaustion Bottom', action: 'CALL', confidence: 'MAX', score: 90, reason: 'Spike ทิ่มสุดตัวแล้วตามด้วย Bear Trap จบคลื่นขาลงทันที' },

      // 🎯 Trap Confirmation (6 -> 1, 12 -> 1, 5 -> 2, 11 -> 2)
      '6->1':   { patternName: 'Bear Trap Confirmed UP', action: 'CALL', confidence: 'MAX', score: 94, reason: 'Bear Trap ดักขายสำเร็จ และแท่งปัจจุบันดันทำ Higher High ตอกย้ำขาขึ้นชัดเจน' },
      '12->1':  { patternName: 'Strong Bear Trap Confirmed UP', action: 'CALL', confidence: 'MAX', score: 92, reason: 'ยืนยันการกลับตัวขึ้นหลังเกิด Strong Bear Trap' },
      '5->2':   { patternName: 'Bull Trap Confirmed DOWN', action: 'PUT', confidence: 'MAX', score: 94, reason: 'Bull Trap ดักซื้อสำเร็จ และแท่งปัจจุบันทุบทำ Lower Low ตอกย้ำขาลงชัดเจน' },
      '11->2':  { patternName: 'Strong Bull Trap Confirmed DOWN', action: 'PUT', confidence: 'MAX', score: 92, reason: 'ยืนยันการกลับตัวลงหลังเกิด Strong Bull Trap' },

      // 📈 Continuation & Dip Buying (1 -> 1, 2 -> 2, 1 -> 16, 2 -> 15)
      '1->1':   { patternName: 'UpTrend Solid Continuation', action: 'CALL', confidence: 'HIGH', score: 82, reason: 'ทำ Higher High ต่อเนื่อง เทรนด์ขาขึ้นแข็งแกร่ง' },
      '2->2':   { patternName: 'DownTrend Solid Continuation', action: 'PUT', confidence: 'HIGH', score: 82, reason: 'ทำ Lower Low ต่อเนื่อง เทรนด์ขาลงแข็งแกร่ง' },
      '1->16':  { patternName: 'UpTrend Pullback & Dip Buy', action: 'CALL', confidence: 'HIGH', score: 80, reason: 'ขาขึ้นย่อตัวลงมาติดแนวรับเกิดไส้ล่างยาวดันกลับ (Buy on Dip)' },
      '2->15':  { patternName: 'DownTrend Pullback & Sell Rally', action: 'PUT', confidence: 'HIGH', score: 80, reason: 'ขาลงดีดตัวขึ้นไปติดแนวต้านเกิดไส้บนยาวกดลง (Sell on Rally)' },
      '1->23':  { patternName: 'UpTrend Retest Support', action: 'CALL', confidence: 'HIGH', score: 76, reason: 'ขาขึ้นย่อทดสอบแนวรับเดิมแล้วเด้งกลับ' },
      '2->22':  { patternName: 'DownTrend Retest Resistance', action: 'PUT', confidence: 'HIGH', score: 76, reason: 'ขาลงดีดทดสอบแนวต้านเดิมแล้วไม่ผ่าน' },

      // ⚠️ Exhaustion & Indecision (1 -> 13, 2 -> 14, 21 -> ...)
      '1->13':  { patternName: 'UpTrend Momentum Loss', action: 'WAIT', confidence: 'MEDIUM', score: 62, reason: 'ขาขึ้นเริ่มส่งต่อโมเมนตัมไม่ผ่าน (Failed Followthrough)' },
      '2->14':  { patternName: 'DownTrend Momentum Loss', action: 'WAIT', confidence: 'MEDIUM', score: 62, reason: 'ขาลงเริ่มส่งต่อโมเมนตัมไม่ผ่าน (Failed Followthrough)' },
      '1->15':  { patternName: 'UpTrend Upper Wick Rejection', action: 'WAIT', confidence: 'MEDIUM', score: 65, reason: 'ทำ New High แต่มีไส้บนยาว กดดันโมเมนตัมฝั่งซื้อ' },
      '2->16':  { patternName: 'DownTrend Lower Wick Rejection', action: 'WAIT', confidence: 'MEDIUM', score: 65, reason: 'ทำ New Low แต่มีไส้ล่างยาว มีแรงซื้อพยุงก้นแท่ง' },
      '21->17': { patternName: 'Double Squeeze Compression', action: 'WAIT', confidence: 'MAX', score: 95, reason: 'ตลาดบีบตัวแคบต่อเนื่อง 2 แท่ง ปริมาณการเทรดนิ่งสนิท รอระเบิดทิศทาง' },
      '21->21': { patternName: 'Dual Doji Indecision', action: 'WAIT', confidence: 'HIGH', score: 90, reason: 'Doji ต่อเนื่อง 2 แท่ง สองฝั่งสู้กันเสมอกัน ไร้ทิศทาง' },
      '21->9':  { patternName: 'Morning Star Bullish Break', action: 'CALL', confidence: 'HIGH', score: 84, reason: 'จาก Doji ลังเล เปลี่ยนเป็นแท่งเขียวกลืนกินเต็มแท่ง (Morning Reversal)' },
      '21->10': { patternName: 'Evening Star Bearish Break', action: 'PUT', confidence: 'HIGH', score: 84, reason: 'จาก Doji ลังเล เปลี่ยนเป็นแท่งแดงกลืนกินเต็มแท่ง (Evening Reversal)' },
      '21->1':  { patternName: 'Doji Breakout UP', action: 'CALL', confidence: 'HIGH', score: 80, reason: 'หลุดจาก Doji ลังเลด้วยแท่ง Up Confirm' },
      '21->2':  { patternName: 'Doji Breakdown DOWN', action: 'PUT', confidence: 'HIGH', score: 80, reason: 'หลุดจาก Doji ลังเลด้วยแท่ง Down Confirm' },
      '18->19': { patternName: 'Mixed Signal Choppiness', action: 'WAIT', confidence: 'HIGH', score: 85, reason: 'สัญญาณขัดแย้ง High/Low สลับไปมา ติด Whipsaw' },
      '19->18': { patternName: 'Mixed Signal Choppiness', action: 'WAIT', confidence: 'HIGH', score: 85, reason: 'สัญญาณขัดแย้ง High/Low สลับไปมา ติด Whipsaw' },
      '27->1':  { patternName: 'Spike Whipsaw Breakout UP', action: 'CALL', confidence: 'MEDIUM', score: 72, reason: 'หลุดพ้นจาก Whipsaw ด้วยแท่ง Up Confirm' },
      '27->2':  { patternName: 'Spike Whipsaw Breakdown DOWN', action: 'PUT', confidence: 'MEDIUM', score: 72, reason: 'หลุดพ้นจาก Whipsaw ด้วยแท่ง Down Confirm' }
    };

    if (SEQUENCE_RULES[pairKey]) {
      var matched = SEQUENCE_RULES[pairKey];
      return {
        prevCodeNo: pCodeNo,
        currCodeNo: cCodeNo,
        prevCaseCode: pCode,
        currCaseCode: cCode,
        pairKey: pairKey,
        pairDisplay: pairDisplay,
        patternName: matched.patternName,
        action: matched.action,
        confidence: matched.confidence,
        score: matched.score,
        reason: matched.reason,
        matchType: 'EXACT_RULE'
      };
    }

    // กฎ Dynamic Transition สำหรับคู่ทั่วไป
    var dynamicAction = 'WAIT';
    var dynamicConf = 'LOW';
    var dynamicScore = 50;
    var dynamicPattern = 'General 2-Bar Transition';
    var dynamicReason = 'การส่งต่อโมเมนตัมระหว่าง ' + pCode + ' ➔ ' + cCode;

    if (currTrend && currTrend.trend === 'UpTrend') {
      if (currIsBullish) {
        dynamicAction = 'CALL';
        dynamicConf = 'MEDIUM';
        dynamicScore = 68;
        dynamicPattern = 'UpTrend Momentum (' + pCodeNo + '➔' + cCodeNo + ')';
        dynamicReason = 'ส่งต่อโมเมนตัมขาขึ้นด้วยแท่งเขียว';
      } else {
        dynamicAction = 'WAIT';
        dynamicConf = 'LOW';
        dynamicScore = 55;
        dynamicPattern = 'UpTrend Hesitation (' + pCodeNo + '➔' + cCodeNo + ')';
        dynamicReason = 'โครงสร้างขึ้นแต่แท่งปัจจุบันปิดแดง ควรชะลอรอยืนยัน';
      }
    } else if (currTrend && currTrend.trend === 'DownTrend') {
      if (!currIsBullish) {
        dynamicAction = 'PUT';
        dynamicConf = 'MEDIUM';
        dynamicScore = 68;
        dynamicPattern = 'DownTrend Momentum (' + pCodeNo + '➔' + cCodeNo + ')';
        dynamicReason = 'ส่งต่อโมเมนตัมขาลงด้วยแท่งแดง';
      } else {
        dynamicAction = 'WAIT';
        dynamicConf = 'LOW';
        dynamicScore = 55;
        dynamicPattern = 'DownTrend Hesitation (' + pCodeNo + '➔' + cCodeNo + ')';
        dynamicReason = 'โครงสร้างลงแต่แท่งปัจจุบันปิดเขียว ควรชะลอรอยืนยัน';
      }
    } else {
      dynamicAction = 'WAIT';
      dynamicConf = 'MEDIUM';
      dynamicScore = 60;
      dynamicPattern = 'Sideways/Transition (' + pCodeNo + '➔' + cCodeNo + ')';
      dynamicReason = 'สภาวะตลาดไร้ทิศทางชัดเจน รอการเลือกทาง';
    }

    return {
      prevCodeNo: pCodeNo,
      currCodeNo: cCodeNo,
      prevCaseCode: pCode,
      currCaseCode: cCode,
      pairKey: pairKey,
      pairDisplay: pairDisplay,
      patternName: dynamicPattern,
      action: dynamicAction,
      confidence: dynamicConf,
      score: dynamicScore,
      reason: dynamicReason,
      matchType: 'DYNAMIC_TRANSITION'
    };
  }

  /**
   * ทำนายทั้งชุดข้อมูลแท่งเทียน
   * @param {Array} candles
   * @param {Object} [ticksMap=null] - Object แมป { candleTime: [ticks] }
   * @param {Object} [options={}]
   * @returns {Array} Array ของผลการทำนาย
   */
  function predictNextCandleSeries(candles, ticksMap, options) {
    if (!candles || !candles.length) return [];
    return candles.map(function (c, i) {
      var ticks = (ticksMap && ticksMap[c.time]) ? ticksMap[c.time] : null;
      return predictNextCandle(candles, i, ticks, options);
    });
  }

  return {
    predictNextCandle: predictNextCandle,
    predictNextCandleSeries: predictNextCandleSeries,
    evaluateSequenceAction: evaluateSequenceAction,
  };
});
