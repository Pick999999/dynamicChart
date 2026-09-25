/**
 * ========================================
 * COMPLETE CHOPPY MARKET DETECTION SYSTEM
 * WITH TRADING ACTION & WIN/LOSS TRACKING
 * ========================================
 * ไฟล์เดียวครบ ไม่ต้องกด Continue
 */

// ==================== UTILITY FUNCTIONS ====================
let theresHold = null ;

function toThaiTime(timestamp) {
  const date = new Date(timestamp * 1000);
  const thaiDate = new Date(date.getTime() + (7 * 60 * 60 * 1000));
  const hours = String(thaiDate.getUTCHours()).padStart(2, '0');
  const minutes = String(thaiDate.getUTCMinutes()).padStart(2, '0');
  const seconds = String(thaiDate.getUTCSeconds()).padStart(2, '0');
  return `${hours}:${minutes}:${seconds}`;
}

function calculatePip(value, decimals) {
  return Math.round(value * Math.pow(10, decimals));
}

function getColor(candle) {
  const open = parseFloat(candle.open);
  const close = parseFloat(candle.close);
  return close > open ? 'Green' : close < open ? 'Red' : 'Doji';
}

function analyzeMomentum(candle) {
  const open = parseFloat(candle.open);
  const close = parseFloat(candle.close);
  const high = parseFloat(candle.high);
  const low = parseFloat(candle.low);

  const bodySize = Math.abs(close - open);
  const candleSize = high - low;
  const bodyPercent = candleSize > 0 ? (bodySize / candleSize) * 100 : 0;

  const isStrong = bodyPercent > 70;
  const direction = close > open ? 'Bullish' : close < open ? 'Bearish' : 'Neutral';

  return {
    direction: direction,
    bodyPercent: bodyPercent,
    isStrong: isStrong,
    bodySize: bodySize
  };
}

// ==================== CHOPPY DETECTION ====================

function analyzeWindow(candles, pipDecimal) {
  let greenCount = 0;
  let redCount = 0;
  let dojiCount = 0;
  let colorChanges = 0;
  let previousColor = null;
  const colors = [];
  let totalBodyPercent = 0;
  const highs = [];
  const lows = [];

  for (let c of candles) {
    const open = parseFloat(c.open);
    const close = parseFloat(c.close);
    const high = parseFloat(c.high);
    const low = parseFloat(c.low);

    highs.push(high);
    lows.push(low);

    const color = close > open ? 'Green' : close < open ? 'Red' : 'Doji';
    colors.push(color[0]);

    if (color === 'Green') greenCount++;
    else if (color === 'Red') redCount++;
    else dojiCount++;

    if (previousColor && color !== previousColor && color !== 'Doji') {
      colorChanges++;
    }

    const candleSize = high - low;
    const bodySize = Math.abs(close - open);
    const bodyPercent = candleSize > 0 ? (bodySize / candleSize) * 100 : 0;
    totalBodyPercent += bodyPercent;

    previousColor = color;
  }

  const rangeHigh = Math.max(...highs);
  const rangeLow = Math.min(...lows);
  const range = rangeHigh - rangeLow;
  const rangePip = calculatePip(range, pipDecimal);
  const avgBodyPercent = totalBodyPercent / candles.length;
  const colorBalance = Math.abs(greenCount - redCount);

  return {
    totalCandles: candles.length,
    greenCount: greenCount,
    redCount: redCount,
    dojiCount: dojiCount,
    colorChanges: colorChanges,
    colorSequence: colors.join('-'),
    colorBalance: colorBalance,
    rangeHigh: rangeHigh,
    rangeLow: rangeLow,
    range: range,
    rangePip: rangePip,
    avgBodyPercent: avgBodyPercent
  };
}

function detectEarlyChoppy(candleData, lookbackPeriod = 10, pipDecimal = 5) {
  if (!candleData || candleData.length < 3) {
    return null;
  }

  const recentCandles = candleData.slice(-lookbackPeriod);
  const last3Candles = candleData.slice(-3);
  const last5Candles = candleData.slice(-5);

  const analysis3 = analyzeWindow(last3Candles, pipDecimal);
  const analysis5 = analyzeWindow(last5Candles, pipDecimal);
  const analysisAll = analyzeWindow(recentCandles, pipDecimal);

  let earlyWarningScore = 0;
  const warnings = [];

  if (analysis3.colorChanges === 2) {
    earlyWarningScore += 35;
    warnings.push(`🔄 สลับสีทั้ง 3 แท่ง (${analysis3.colorSequence})`);
  } else if (analysis3.colorChanges === 1) {
    earlyWarningScore += 15;
    warnings.push(`🔄 สลับสี 1 ครั้งใน 3 แท่ง`);
  }

  if (analysis3.rangePip < 20) {
    earlyWarningScore += 30;
    warnings.push(`📏 Range 3 แท่งแคบมาก (${analysis3.rangePip} pips)`);
  } else if (analysis3.rangePip < 35) {
    earlyWarningScore += 20;
    warnings.push(`📏 Range 3 แท่งแคบ (${analysis3.rangePip} pips)`);
  }

  if (analysis3.avgBodyPercent < 40) {
    earlyWarningScore += 25;
    warnings.push(`📊 Body เล็กมาก ${analysis3.avgBodyPercent.toFixed(1)}% (Wick ยาว)`);
  } else if (analysis3.avgBodyPercent < 50) {
    earlyWarningScore += 15;
    warnings.push(`📊 Body เล็ก ${analysis3.avgBodyPercent.toFixed(1)}%`);
  }

  const lastCandle = last3Candles[last3Candles.length - 1];
  const lastOpen = parseFloat(lastCandle.open);
  const lastClose = parseFloat(lastCandle.close);
  const lastHigh = parseFloat(lastCandle.high);
  const lastLow = parseFloat(lastCandle.low);

  let upperWick, lowerWick;
  if (lastClose >= lastOpen) {
    upperWick = lastHigh - lastClose;
    lowerWick = lastOpen - lastLow;
  } else {
    upperWick = lastHigh - lastOpen;
    lowerWick = lastClose - lastLow;
  }

  const lastCandleSize = lastHigh - lastLow;
  const upperWickPercent = lastCandleSize > 0 ? (upperWick / lastCandleSize) * 100 : 0;
  const lowerWickPercent = lastCandleSize > 0 ? (lowerWick / lastCandleSize) * 100 : 0;

  if (upperWickPercent > 35 && lowerWickPercent > 35) {
    earlyWarningScore += 20;
    warnings.push(`⚠️ แท่งล่าสุดเป็น Doji-like (Wick 2 ฝั่งยาว)`);
  }

  if (analysis5.colorBalance <= 1) {
    earlyWarningScore += 15;
    warnings.push(`⚖️ ไม่มีทิศทางชัด 5 แท่ง (G:${analysis5.greenCount} R:${analysis5.redCount})`);
  }

  if (analysis5.rangePip < 40) {
    earlyWarningScore += 15;
    warnings.push(`🔁 ราคาวนเวียนใน 5 แท่ง (${analysis5.rangePip} pips)`);
  }

  const colorChangeRate10 = (analysisAll.colorChanges / (analysisAll.totalCandles - 1)) * 100;
  if (colorChangeRate10 > 60) {
    earlyWarningScore += 20;
    warnings.push(`🌊 ประวัติสลับสีบ่อย 10 แท่ง (${colorChangeRate10.toFixed(1)}%)`);
  }

  let alertLevel = 'Safe';
  let alertColor = '🟢';
  let recommendation = 'ปกติ - สามารถเทรดได้';

  if (earlyWarningScore >= 80) {
    alertLevel = 'Critical - กำลังเข้า Choppy!';
    alertColor = '🔴';
    recommendation = '🛑 หยุดเทรดทันที! กำลังเข้าสู่ Choppy Market';
  } else if (earlyWarningScore >= 60) {
    alertLevel = 'High Risk - มีสัญญาณ Choppy';
    alertColor = '🟠';
    recommendation = '⚠️ ระวัง! มีสัญญาณเข้า Choppy - ลด Position';
  } else if (earlyWarningScore >= 40) {
    alertLevel = 'Medium Risk - เริ่มมีสัญญาณ';
    alertColor = '🟡';
    recommendation = '⚡ ระมัดระวัง - ใช้ SL แน่น';
  } else if (earlyWarningScore >= 20) {
    alertLevel = 'Low Risk';
    alertColor = '🟢';
    recommendation = '✓ ยังเทรดได้ แต่สังเกต';
  }

  return {
    alertLevel: alertLevel,
    alertColor: alertColor,
    earlyWarningScore: earlyWarningScore,
    warnings: warnings,
    recommendation: recommendation,
    analysis: {
      last3Candles: analysis3,
      last5Candles: analysis5,
      last10Candles: analysisAll,
      lastCandleWicks: {
        upperWickPercent: parseFloat(upperWickPercent.toFixed(2)),
        lowerWickPercent: parseFloat(lowerWickPercent.toFixed(2)),
        isDoji: upperWickPercent > 35 && lowerWickPercent > 35
      }
    }
  };
}

function analyzeChoppyMarket(candleData,theresHoldA, pipDecimal = 5) {

  theresHold = theresHoldA ;
  if (!candleData || candleData.length < 3) {
    return {
      analysis: {
        totalCandles: 0,
        greenCount: 0,
        redCount: 0,
        dojiCount: 0,
        colorChanges: 0,
        colorChangeRate: 0,
        rangeHigh: 0,
        rangeLow: 0,
        totalRange: 0,
        totalRangePip: 0,
        avgCandleSize: 0,
        avgCandleSizePip: 0,
        avgBodyPercent: 0,
        choppyScore: 0,
        marketCondition: 'Unknown',
        reasons: [],
        recommendation: 'ไม่มีข้อมูล'
      }
    };
  }

  let redCount = 0;
  let greenCount = 0;
  let dojiCount = 0;
  let colorChanges = 0;
  let previousColor = null;
  const candles = [];

  for (let i = 0; i < candleData.length; i++) {
    const c = candleData[i];
    const open = parseFloat(c.open);
    const close = parseFloat(c.close);
    const high = parseFloat(c.high);
    const low = parseFloat(c.low);

    const color = close > open ? 'Green' : close < open ? 'Red' : 'Doji';
    const bodySize = Math.abs(close - open);
    const candleSize = high - low;
    const bodyPercent = candleSize > 0 ? (bodySize / candleSize) * 100 : 0;

    if (color === 'Green') greenCount++;
    else if (color === 'Red') redCount++;
    else dojiCount++;

    if (previousColor && color !== previousColor && color !== 'Doji') {
      colorChanges++;
    }

    candles.push({
      index: i,
      color: color,
      bodySize: bodySize,
      bodyPercent: bodyPercent.toFixed(2),
      candleSize: candleSize
    });

    previousColor = color;
  }

  const allHighs = candleData.map(c => parseFloat(c.high));
  const allLows = candleData.map(c => parseFloat(c.low));
  const rangeHigh = Math.max(...allHighs);
  const rangeLow = Math.min(...allLows);
  const totalRange = rangeHigh - rangeLow;
  const totalRangePip = calculatePip(totalRange, pipDecimal);

  let totalCandleSize = 0;
  for (let c of candles) {
    totalCandleSize += c.candleSize;
  }
  const avgCandleSize = totalCandleSize / candles.length;
  const avgCandleSizePip = calculatePip(avgCandleSize, pipDecimal);

  const colorChangeRate = (colorChanges / (candleData.length - 1)) * 100;

  let totalBodyPercent = 0;
  for (let c of candles) {
    totalBodyPercent += parseFloat(c.bodyPercent);
  }
  const avgBodyPercent = totalBodyPercent / candles.length;

  let marketCondition = 'Normal';
  let choppyScore = 0;
  const reasons = [];

  if (colorChangeRate > 60) {
    choppyScore += 30;
    reasons.push(`สลับสีบ่อยมาก ${colorChanges} ครั้ง (${colorChangeRate.toFixed(1)}%)`);
  } else if (colorChangeRate > 40) {
    choppyScore += 20;
    reasons.push(`สลับสีปานกลาง ${colorChanges} ครั้ง`);
  }

  if (totalRangePip < 30) {
    choppyScore += 25;
    reasons.push(`Range แคบมาก ${totalRangePip} pips`);
  } else if (totalRangePip < 50) {
    choppyScore += 15;
    reasons.push(`Range แคบ ${totalRangePip} pips`);
  }

  if (avgBodyPercent < 40) {
    choppyScore += 25;
    reasons.push(`Body เฉลี่ยเล็กมาก ${avgBodyPercent.toFixed(1)}%`);
  } else if (avgBodyPercent < 50) {
    choppyScore += 15;
    reasons.push(`Body เฉลี่ยเล็ก ${avgBodyPercent.toFixed(1)}%`);
  }

  const colorBalance = Math.abs(greenCount - redCount);
  if (colorBalance <= 1) {
    choppyScore += 20;
    reasons.push(`จำนวนสีเท่ากัน (G:${greenCount}, R:${redCount})`);
  } else if (colorBalance <= 2) {
    choppyScore += 10;
    reasons.push(`จำนวนสีใกล้เคียง`);
  }

  if (choppyScore >= 70) {
    marketCondition = 'Very Choppy';
  } else if (choppyScore >= 50) {
    marketCondition = 'Choppy';
  } else if (choppyScore >= 30) {
    marketCondition = 'Slightly Choppy';
  } else {
    marketCondition = 'Trending';
  }

  let recommendation = '';
  if (choppyScore >= 50) {
    recommendation = '⚠️ ไม่แนะนำให้เทรด';
  } else if (choppyScore >= 30) {
    recommendation = '⚡ ระมัดระวัง';
  } else {
    recommendation = '✅ สามารถเทรดได้';
  }

  return {
    analysis: {
      totalCandles: candleData.length,
      greenCount: greenCount,
      redCount: redCount,
      dojiCount: dojiCount,
      colorChanges: colorChanges,
      colorChangeRate: parseFloat(colorChangeRate.toFixed(2)),
      rangeHigh: rangeHigh,
      rangeLow: rangeLow,
      totalRange: parseFloat(totalRange.toFixed(pipDecimal + 2)),
      totalRangePip: totalRangePip,
      avgCandleSize: parseFloat(avgCandleSize.toFixed(pipDecimal + 2)),
      avgCandleSizePip: avgCandleSizePip,
      avgBodyPercent: parseFloat(avgBodyPercent.toFixed(2)),
      choppyScore: choppyScore,
      marketCondition: marketCondition,
      reasons: reasons,
      recommendation: recommendation
    }
  };
}

// ==================== HTML REPORT GENERATOR ====================

function generateChoppyHTMLReportWithAction(candleData, pipDecimal = 5) {
  if (!candleData || candleData.length < 3) {
    return '<p>ข้อมูลไม่เพียงพอ</p>';
  }

  const rows = [];
  let previousChoppyState = false;
  let consecutiveChoppy = 0;
  let currentWinStreak = 0;
  let currentLossStreak = 0;
  let maxWinStreak = 0;
  let maxLossStreak = 0;
  let totalTrades = 0;
  let totalWins = 0;
  let totalLosses = 0;
  let totalIdle = 0;

  for (let i = 0; i < candleData.length; i++) {
    const subset = candleData.slice(0, i + 1);
    const c = candleData[i];
    const currentOpen = parseFloat(c.open);
    const currentClose = parseFloat(c.close);
    const currentHigh = parseFloat(c.high);
    const currentLow = parseFloat(c.low);
    const currentColor = getColor(c);

    if (subset.length < 3) {
      rows.push({
        index: i + 1,
        time: toThaiTime(c.epoch),
        open: currentOpen,
        high: currentHigh,
        low: currentLow,
        close: currentClose,
        color: currentColor,
        score: 0,
        status: 'N/A',
        statusIcon: '⚪',
        rowClass: 'neutral',
        zoneStatus: '-',
        zoneStatusClass: '',
        action: 'Idle',
        actionColor: 'idle',
        nextColor: i < candleData.length - 1 ? getColor(candleData[i + 1]) : '-',
        winStatus: '-',
        winStatusIcon: '-',
        winContinue: 0,
        lossContinue: 0
      });
      continue;
    }

    const result = detectEarlyChoppy(subset, 10, pipDecimal);
    const isChoppy = result.earlyWarningScore >= 60;

    let zoneStatus = '-';
    let zoneStatusClass = '';

    if (isChoppy && !previousChoppyState) {
      zoneStatus = '🚨 เข้าสู่ Choppy Zone!';
      zoneStatusClass = 'zone-enter';
      consecutiveChoppy = 1;
    } else if (isChoppy && previousChoppyState) {
      consecutiveChoppy++;
      zoneStatus = `⚠️ อยู่ใน Choppy (${consecutiveChoppy} แท่ง)`;
      zoneStatusClass = 'zone-inside';
    } else if (!isChoppy && previousChoppyState) {
      zoneStatus = '✅ ออกจาก Choppy Zone';
      zoneStatusClass = 'zone-exit';
      consecutiveChoppy = 0;
    }

    let statusIcon = '🟢';
    let statusText = 'ปกติ';
    let rowClass = 'safe';

    if (result.earlyWarningScore >= 80) {
      statusIcon = '🔴';
      statusText = 'อันตรายมาก';
      rowClass = 'critical';
    } else if (result.earlyWarningScore >= 60) {
      statusIcon = '🟠';
      statusText = 'อันตราย';
      rowClass = 'danger';
    } else if (result.earlyWarningScore >= 40) {
      statusIcon = '🟡';
      statusText = 'เสี่ยงปานกลาง';
      rowClass = 'warning';
    } else if (result.earlyWarningScore >= 20) {
      statusIcon = '🟢';
      statusText = 'เสี่ยงต่ำ';
      rowClass = 'low-risk';
    }

    let action = 'Idle';
    let actionColor = 'idle';
	//alert(theresHold);

    if (result.earlyWarningScore < theresHold && currentColor !== 'Doji') {
      action = currentColor === 'Green' ? 'Buy' : 'Sell';
      actionColor = currentColor.toLowerCase();
    }

    let nextColor = '-';
    let winStatus = '-';
    let winStatusIcon = '-';

    if (i < candleData.length - 1) {
      nextColor = getColor(candleData[i + 1]);

      if (action === 'Idle') {
        winStatus = 'N/A';
        winStatusIcon = '⚪';
        totalIdle++;
      } else {
        totalTrades++;
        const expectedColor = action === 'Buy' ? 'Green' : 'Red';

        if (nextColor === expectedColor) {
          winStatus = 'Win';
          winStatusIcon = '✅';
          totalWins++;
          currentWinStreak++;
          currentLossStreak = 0;
          maxWinStreak = Math.max(maxWinStreak, currentWinStreak);
        } else if (nextColor === 'Doji') {
          winStatus = 'Draw';
          winStatusIcon = '➖';
          currentWinStreak = 0;
          currentLossStreak = 0;
        } else {
          winStatus = 'Loss';
          winStatusIcon = '❌';
          totalLosses++;
          currentLossStreak++;
          currentWinStreak = 0;
          maxLossStreak = Math.max(maxLossStreak, currentLossStreak);
        }
      }
    } else {
      nextColor = '?';
      winStatus = 'Pending';
      winStatusIcon = '⏳';
    }

    rows.push({
      index: i + 1,
      time: toThaiTime(c.epoch),
      open: currentOpen,
      high: currentHigh,
      low: currentLow,
      close: currentClose,
      color: currentColor,
      score: result.earlyWarningScore,
      status: statusText,
      statusIcon: statusIcon,
      rowClass: rowClass,
      zoneStatus: zoneStatus,
      zoneStatusClass: zoneStatusClass,
      action: action,
      actionColor: actionColor,
      nextColor: nextColor,
      winStatus: winStatus,
      winStatusIcon: winStatusIcon,
      winContinue: currentWinStreak,
      lossContinue: currentLossStreak
    });

    previousChoppyState = isChoppy;
  }

  const winRate = totalTrades > 0 ? ((totalWins / totalTrades) * 100).toFixed(2) : 0;
  const fullAnalysis = analyzeChoppyMarket(candleData, pipDecimal);
  const lastResult = detectEarlyChoppy(candleData, 10, pipDecimal);

  const html = `
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choppy Market Trading Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; min-height: 100vh; }
        .container { max-width: 1600px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; text-shadow: 2px 2px 4px rgba(0,0,0,0.2); }
        .header p { font-size: 1.1em; opacity: 0.9; }
        .trading-stats { padding: 30px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
        .trading-stats h2 { text-align: center; font-size: 2em; margin-bottom: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .stat-box { background: rgba(255,255,255,0.2); padding: 20px; border-radius: 10px; text-align: center; }
        .stat-box .stat-value { font-size: 2.5em; font-weight: bold; margin-bottom: 5px; }
        .stat-box .stat-label { font-size: 1em; opacity: 0.9; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; padding: 30px; background: #f8f9fa; }
        .summary-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s; }
        .summary-card:hover { transform: translateY(-5px); }
        .summary-card h3 { font-size: 0.9em; color: #666; margin-bottom: 10px; text-transform: uppercase; }
        .summary-card .value { font-size: 2em; font-weight: bold; margin-bottom: 5px; }
        .summary-card .label { font-size: 0.85em; color: #999; }
        .critical { color: #dc3545; }
        .danger { color: #fd7e14; }
        .warning { color: #ffc107; }
        .safe { color: #28a745; }
        .current-status { padding: 30px; text-align: center; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
        .current-status h2 { font-size: 2em; margin-bottom: 15px; }
        .current-status .status-badge { display: inline-block; padding: 15px 30px; background: rgba(255,255,255,0.2); border-radius: 50px; font-size: 1.5em; margin: 10px; }
        .table-container { padding: 30px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden; font-size: 0.85em; }
        thead { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        th { padding: 12px 8px; text-align: left; font-weight: 600; font-size: 0.85em; text-transform: uppercase; }
        td { padding: 10px 8px; border-bottom: 1px solid #f0f0f0; }
        tr:hover { background: #f8f9fa; }
        .row-critical { background: rgba(220, 53, 69, 0.1) !important; border-left: 4px solid #dc3545; }
        .row-danger { background: rgba(253, 126, 20, 0.1) !important; border-left: 4px solid #fd7e14; }
        .row-warning { background: rgba(255, 193, 7, 0.1) !important; border-left: 4px solid #ffc107; }
        .row-low-risk { background: rgba(40, 167, 69, 0.05) !important; }
        .row-safe { background: white; }
        .color-green { color: #28a745; font-weight: bold; }
        .color-red { color: #dc3545; font-weight: bold; }
        .color-doji { color: #6c757d; font-weight: bold; }
        .score-badge { display: inline-block; padding: 5px 12px; border-radius: 20px; font-weight: bold; font-size: 0.85em; }
        .score-critical { background: #dc3545; color: white; }
        .score-danger { background: #fd7e14; color: white; }
        .score-warning { background: #ffc107; color: #000; }
        .score-low { background: #28a745; color: white; }
        .score-safe { background:#17a2b8; color: white; }
        .action-badge { display: inline-block; padding: 6px 14px; border-radius: 20px; font-weight: bold; font-size: 0.9em; }
        .action-buy { background: #28a745; color: white; }
        .action-sell { background: #dc3545; color: white; }
        .action-idle { background: #6c757d; color: white; }
        .win-badge { display: inline-block; padding: 4px 10px; border-radius: 15px; font-weight: bold; font-size: 0.85em; }
        .win-win { background: #28a745; color: white; }
        .win-loss { background: #dc3545; color: white; }
        .win-draw { background: #6c757d; color: white; }
        .win-pending { background: #ffc107; color: #000; }
        .win-na { background: #e9ecef; color: #6c757d; }
        .streak-badge { font-weight: bold; padding: 2px 8px; border-radius: 10px; font-size: 0.85em; }
        .streak-win { background: rgba(40, 167, 69, 0.2); color: #28a745; }
        .streak-loss { background: rgba(220, 53, 69, 0.2); color: #dc3545; }
        .zone-enter { background: rgba(220, 53, 69, 0.2); padding: 5px 10px; border-radius: 5px; font-weight: bold; color: #dc3545; font-size: 0.85em; }
        .zone-inside { background: rgba(253, 126, 20, 0.2); padding: 5px 10px; border-radius: 5px; font-weight: bold; color: #fd7e14; font-size: 0.85em; }
        .zone-exit { background: rgba(40, 167, 69, 0.2); padding: 5px 10px; border-radius: 5px; font-weight: bold; color: #28a745; font-size: 0.85em; }
        .legend { padding: 20px 30px; background: #f8f9fa; display: flex; justify-content: center; flex-wrap: wrap; gap: 15px; font-size: 0.9em; }
        .legend-item { display: flex; align-items: center; gap: 8px; }
        .legend-icon { font-size: 1.3em; }
        .footer { padding: 20px; text-align: center; background: #f8f9fa; color: #666; font-size: 0.9em; }
        @media (max-width: 768px) { .summary { grid-template-columns: 1fr; } table { font-size: 0.75em; } th, td { padding: 6px 4px; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Choppy Market Trading Report</h1>
            <p>รายงานการเทรดและการตรวจจับภาวะตลาด Choppy พร้อม Action & Win/Loss Analysis</p>
        </div>

        <div class="trading-stats">
            <h2>📈 สถิติการเทรด</h2>
            <div class="stats-grid">
                <div class="stat-box"><div class="stat-value">${totalTrades}</div><div class="stat-label">จำนวนเทรดทั้งหมด</div></div>
                <div class="stat-box"><div class="stat-value">${totalWins}</div><div class="stat-label">✅ ชนะ</div></div>
                <div class="stat-box"><div class="stat-value">${totalLosses}</div><div class="stat-label">❌ แพ้</div></div>
                <div class="stat-box"><div class="stat-value">${winRate}%</div><div class="stat-label">🎯 Win Rate</div></div>
                <div class="stat-box"><div class="stat-value">${maxWinStreak}</div><div class="stat-label">🔥 Max Win Streak</div></div>
                <div class="stat-box"><div class="stat-value">${maxLossStreak}</div><div class="stat-label">❄️ Max Loss Streak</div></div>
                <div class="stat-box"><div class="stat-value">${totalIdle}</div><div class="stat-label">⏸️ Idle</div></div>
            </div>
        </div>

        <div class="summary">
            <div class="summary-card"><h3>แท่งทั้งหมด</h3><div class="value">${rows.length}</div><div class="label">แท่งเทียน</div></div>
            <div class="summary-card"><h3>สถานะปัจจุบัน</h3><div class="value ${lastResult.earlyWarningScore >= 80 ? 'critical' : lastResult.earlyWarningScore >= 60 ? 'danger' : lastResult.earlyWarningScore >= 40 ? 'warning' : 'safe'}">${lastResult.earlyWarningScore}</div><div class="label">คะแนน / 100</div></div>
            <div class="summary-card"><h3>Range</h3><div class="value">${fullAnalysis.analysis.totalRangePip}</div><div class="label">pips</div></div>
            <div class="summary-card"><h3>การสลับสี</h3><div class="value">${fullAnalysis.analysis.colorChangeRate}%</div><div class="label">${fullAnalysis.analysis.colorChanges} ครั้ง</div></div>
        </div>

        <div class="current-status">
            <h2>${lastResult.alertColor} สถานะปัจจุบัน</h2>
            <div class="status-badge">${lastResult.alertLevel}</div>
            <div class="status-badge">${lastResult.recommendation}</div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>เวลา</th><th>OHLC</th><th>สี</th><th>คะแนน</th><th>สถานะ</th><th>Action</th><th>สีถัดไป</th><th>Win/Loss</th><th>Win Streak</th><th>Loss Streak</th><th>Choppy Zone</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows.map(row => `
                    <tr class="row-${row.rowClass}">
                        <td><strong>${row.index}</strong></td>
                        <td style="font-size: 0.8em;">${row.time}</td>
                        <td style="font-size: 0.8em;">O:${row.open.toFixed(2)}<br>H:${row.high.toFixed(2)}<br>L:${row.low.toFixed(2)}<br>C:${row.close.toFixed(2)}</td>
                        <td class="color-${row.color.toLowerCase()}">${row.color}</td>
                        <td><span class="score-badge score-${row.score >= 80 ? 'critical' : row.score >= 60 ? 'danger' : row.score >= 40 ? 'warning' : row.score >= 20 ? 'low' : 'safe'}">${row.score}</span></td>
                        <td style="font-size: 0.85em;">${row.statusIcon} ${row.status}</td>
                        <td><span class="action-badge action-${row.actionColor}">${row.action}</span></td>
                        <td class="color-${row.nextColor.toLowerCase()}">${row.nextColor}</td>
                        <td><span class="win-badge win-${row.winStatus.toLowerCase()}">${row.winStatusIcon} ${row.winStatus}</span></td>
                        <td>${row.winContinue > 0 ? `<span class="streak-badge streak-win">🔥 ${row.winContinue}</span>` : '-'}</td>
                        <td>${row.lossContinue > 0 ? `<span class="streak-badge streak-loss">❄️ ${row.lossContinue}</span>` : '-'}</td>
                        <td>${row.zoneStatus !== '-' ? `<span class="${row.zoneStatusClass}">${row.zoneStatus}</span>` : '-'}</td>
                    </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>

        <div class="legend">
            <div class="legend-item"><span class="legend-icon">✅</span><span>Win</span></div>
            <div class="legend-item"><span class="legend-icon">❌</span><span>Loss</span></div>
            <div class="legend-item"><span class="legend-icon">🔥</span><span>Win Streak</span></div>
            <div class="legend-item"><span class="legend-icon">❄️</span><span>Loss Streak</span></div>
            <div class="legend-item"><span class="legend-icon">🚨</span><span>เข้า Choppy</span></div>
            <div class="legend-item"><span class="legend-icon">✅</span><span>ออกจาก Choppy</span></div>
            <div class="legend-item"><span class="action-badge action-buy" style="font-size: 0.85em;">Buy</span><span>ซื้อ</span></div>
            <div class="legend-item"><span class="action-badge action-sell" style="font-size: 0.85em;">Sell</span><span>ขาย</span></div>
            <div class="legend-item"><span class="action-badge action-idle" style="font-size: 0.85em;">Idle</span><span>ไม่เทรด</span></div>
        </div>

        <div class="footer">
            <p>📊 รายงานสร้างเมื่อ: ${new Date().toLocaleString('th-TH')}</p>
            <p>⚠️ คำเตือน: ข้อมูลนี้ใช้สำหรับการวิเคราะห์เท่านั้น ไม่ใช่คำแนะนำในการลงทุน</p>
            <p>📈 Max Win Streak: ${maxWinStreak} | Max Loss Streak: ${maxLossStreak} | Win Rate: ${winRate}%</p>
        </div>
    </div>
</body>
</html>
  `;

  return html;
}

// ==================== SAVE & DISPLAY FUNCTIONS ====================

function saveHTMLReportWithAction(candleData, filename = 'choppy-trading-report.html', pipDecimal = 5) {
  const html = generateChoppyHTMLReportWithAction(candleData, pipDecimal);

  if (typeof require !== 'undefined') {
    const fs = require('fs');
    fs.writeFileSync(filename, html, 'utf8');
    console.log(`✅ บันทึกรายงานที่ ${filename}`);
  } else {
    const blob = new Blob([html], { type: 'text/html' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
    console.log(`✅ ดาวน์โหลดรายงาน ${filename}`);
  }
}

function displayHTMLReportWithAction(candleData,theresHold99, pipDecimal = 5) {
  const html = generateChoppyHTMLReportWithAction(candleData, pipDecimal);
  const newWindow = window.open('', '_blank');
  theresHold = theresHold99;
  newWindow.document.write(html);
  newWindow.document.close();
}

function printTradingStats(candleData, pipDecimal = 5) {
  console.log('\n═══════════════════════════════════════════════════');
  console.log('       📊 TRADING STATISTICS SUMMARY');
  console.log('═══════════════════════════════════════════════════\n');

  let currentWinStreak = 0;
  let currentLossStreak = 0;
  let maxWinStreak = 0;
  let maxLossStreak = 0;
  let totalTrades = 0;
  let totalWins = 0;
  let totalLosses = 0;
  let totalIdle = 0;
  alert(theresHold) ;

  for (let i = 0; i < candleData.length; i++) {
    const subset = candleData.slice(0, i + 1);
    const c = candleData[i];
    const currentColor = getColor(c);

    if (subset.length < 3) continue;

    const result = detectEarlyChoppy(subset, 10, pipDecimal);

    let action = 'Idle';

    if (result.earlyWarningScore < theresHold && currentColor !== 'Doji') {
      action = currentColor === 'Green' ? 'Buy' : 'Sell';
    }

    if (i < candleData.length - 1) {
      const nextColor = getColor(candleData[i + 1]);

      if (action === 'Idle') {
        totalIdle++;
      } else {
        totalTrades++;
        const expectedColor = action === 'Buy' ? 'Green' : 'Red';

        if (nextColor === expectedColor) {
          totalWins++;
          currentWinStreak++;
          currentLossStreak = 0;
          maxWinStreak = Math.max(maxWinStreak, currentWinStreak);
        } else if (nextColor !== 'Doji') {
          totalLosses++;
          currentLossStreak++;
          currentWinStreak = 0;
          maxLossStreak = Math.max(maxLossStreak, currentLossStreak);
        }
      }
    }
  }

  const winRate = totalTrades > 0 ? ((totalWins / totalTrades) * 100).toFixed(2) : 0;

  console.log('📈 สถิติการเทรด:');
  console.log(`   จำนวนเทรดทั้งหมด: ${totalTrades}`);
  console.log(`   ✅ ชนะ: ${totalWins}`);
  console.log(`   ❌ แพ้: ${totalLosses}`);
  console.log(`   ⏸️  Idle: ${totalIdle}`);
  console.log(`   🎯 Win Rate: ${winRate}%\n`);

  console.log('🔥 Streak Analysis:');
  console.log(`   Max Win Streak: ${maxWinStreak} แท่งติดกัน`);
  console.log(`   Max Loss Streak: ${maxLossStreak} แท่งติดกัน\n`);

  console.log('💡 สรุป:');
  if (parseFloat(winRate) >= 60) {
    console.log('   ✅ Win Rate ดีมาก! กลยุทธ์หลีกเลี่ยง Choppy ได้ผล');
  } else if (parseFloat(winRate) >= 50) {
    console.log('   ⚡ Win Rate พอใช้ แต่ยังต้องปรับปรุง');
  } else {
    console.log('   ⚠️  Win Rate ต่ำ อาจต้องปรับเกณฑ์การเข้าเทรด');
  }

  if (maxLossStreak >= 5) {
    console.log('   🚨 Loss Streak สูง! ต้องระวังการจัดการความเสี่ยง');
  }

  if (totalIdle > totalTrades) {
    console.log('   ⏸️  หลีกเลี่ยง Choppy ได้ดี - Idle มากกว่าเทรด');
  }

  console.log('\n═══════════════════════════════════════════════════\n');

  return { totalTrades, totalWins, totalLosses, totalIdle, winRate, maxWinStreak, maxLossStreak };
}

// ==================== TEST DATA & EXAMPLE ====================
/*
const testData = [
  { "close": 2866.354, "epoch": 1753270320, "high": 2867.35, "low": 2866.138, "open": 2867.148 },
  { "close": 2866.771, "epoch": 1753270380, "high": 2866.771, "low": 2865.845, "open": 2866.079 },
  { "close": 2865.694, "epoch": 1753270440, "high": 2866.93, "low": 2865.694, "open": 2866.93 },
  { "close": 2866.164, "epoch": 1753270500, "high": 2866.404, "low": 2865.671, "open": 2865.671 },
  { "close": 2865.904, "epoch": 1753270560, "high": 2866.199, "low": 2865.158, "open": 2866.199 },
  { "close": 2866.276, "epoch": 1753270620, "high": 2866.969, "low": 2865.631, "open": 2866.039 },
  { "close": 2866.201, "epoch": 1753270680, "high": 2866.446, "low": 2865.172, "open": 2866.211 },
  { "close": 2866.959, "epoch": 1753270740, "high": 2867.042, "low": 2865.745, "open": 2865.965 },
  { "close": 2864.889, "epoch": 1753270800, "high": 2866.947, "low": 2864.889, "open": 2866.947 }
];

console.log('🧪 กำลังสร้างรายงาน Trading Report...\n');

// พิมพ์สถิติใน Console
const stats = printTradingStats(testData, 3);

// บันทึกเป็นไฟล์ HTML
saveHTMLReportWithAction(testData, 'choppy-trading-report.html', 3);

console.log('✅ สร้าง HTML Trading Report สำเร็จ');
console.log('\n📋 วิธีใช้งาน:');
console.log('─────────────────────────────────────────────────');
console.log('1. saveHTMLReportWithAction(candleData, "report.html", 5)');
console.log('2. displayHTMLReportWithAction(candleData, 5)');
console.log('3. printTradingStats(candleData, 5)');
console.log('─────────────────────────────────────────────────\n');

*/