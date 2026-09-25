// วิเคราะห์ Sideways Pattern จาก Binary Option Data
class BinarySidewaysAnalyzer {
  constructor(options = {}) {
    this.lookbackPeriods = options.lookbackPeriods || 10;
    this.sidewaysThreshold = options.sidewaysThreshold || 0.6; // 60% win rate threshold
    this.minConsecutiveLoss = options.minConsecutiveLoss || 3;
    this.conflictWeight = options.conflictWeight || 1.5; // น้ำหนักของ conflict
  }

  // วิเคราะห์ Sideways Pattern
  analyzeSidewaysTrend(data) {
    if (!data || data.length < this.lookbackPeriods) {
      return {
        isSideways: false,
        trend: 'insufficient_data',
        confidence: 0,
        details: 'ข้อมูลไม่เพียงพอสำหรับการวิเคราะห์',
        analysis: null
      };
    }

    // ใช้ข้อมูลล่าสุด
    const recentData = data.slice(-this.lookbackPeriods);

    // วิเคราะห์หลายมิติ
    const winLossPattern = this.analyzeWinLossPattern(recentData);
    const colorPattern = this.analyzeColorPattern(recentData);
    const conflictPattern = this.analyzeConflictPattern(recentData);
    const streakPattern = this.analyzeStreakPattern(recentData);

    // รวมผลการวิเคราะห์
    const sidewaysAnalysis = this.combinedAnalysis({
      winLossPattern,
      colorPattern,
      conflictPattern,
      streakPattern,
      recentData
    });

    return sidewaysAnalysis;
  }

  // วิเคราะห์ Win/Loss Pattern
  analyzeWinLossPattern(data) {
    const winCount = data.filter(d => d.WinStatus === 'Win').length;
    const lossCount = data.filter(d => d.WinStatus === 'Loss').length;
    const winRate = winCount / data.length;

    // ดู distribution ของ win/loss
    let alternatingCount = 0;
    for (let i = 1; i < data.length; i++) {
      if (data[i].WinStatus !== data[i-1].WinStatus) {
        alternatingCount++;
      }
    }

    const alternatingRate = alternatingCount / (data.length - 1);

    return {
      winRate,
      lossRate: 1 - winRate,
      alternatingRate,
      isBalanced: Math.abs(winRate - 0.5) < 0.2, // win rate ใกล้ 50%
      isAlternating: alternatingRate > 0.6 // มีการสลับ win/loss บ่อย
    };
  }

  // วิเคราะห์ Color Pattern (Green/Red)
  analyzeColorPattern(data) {
    const greenCount = data.filter(d => d.thisColor === 'Green').length;
    const redCount = data.filter(d => d.thisColor === 'Red').length;
    const colorBalance = Math.abs(greenCount - redCount) / data.length;

    // ดูการสลับสี
    let colorAlternating = 0;
    for (let i = 1; i < data.length; i++) {
      if (data[i].thisColor !== data[i-1].thisColor) {
        colorAlternating++;
      }
    }

    return {
      greenRate: greenCount / data.length,
      redRate: redCount / data.length,
      colorBalance: colorBalance < 0.3, // สีสมดุล
      alternatingColors: colorAlternating / (data.length - 1) > 0.5
    };
  }

  // วิเคราะห์ Conflict Pattern
  analyzeConflictPattern(data) {
    const conflictData = data.filter(d => d.ConflictType !== 'n');
    const conflictRate = conflictData.length / data.length;

    // ประเภท conflict
    const bullishConflicts = data.filter(d => d.ConflictType === 'Bullish Conflict').length;
    const bearishConflicts = data.filter(d => d.ConflictType === 'Bearish Conflict').length;

    return {
      conflictRate,
      bullishConflictRate: bullishConflicts / data.length,
      bearishConflictRate: bearishConflicts / data.length,
      hasBalancedConflicts: Math.abs(bullishConflicts - bearishConflicts) <= 2
    };
  }

  // วิเคราะห์ Streak Pattern
  analyzeStreakPattern(data) {
    const winStreaks = [];
    const lossStreaks = [];
    let currentStreak = 1;
    let currentType = data[0].WinStatus;

    for (let i = 1; i < data.length; i++) {
      if (data[i].WinStatus === currentType) {
        currentStreak++;
      } else {
        if (currentType === 'Win') {
          winStreaks.push(currentStreak);
        } else {
          lossStreaks.push(currentStreak);
        }
        currentStreak = 1;
        currentType = data[i].WinStatus;
      }
    }

    // เพิ่ม streak สุดท้าย
    if (currentType === 'Win') {
      winStreaks.push(currentStreak);
    } else {
      lossStreaks.push(currentStreak);
    }

    const avgWinStreak = winStreaks.length > 0 ?
      winStreaks.reduce((a, b) => a + b, 0) / winStreaks.length : 0;
    const avgLossStreak = lossStreaks.length > 0 ?
      lossStreaks.reduce((a, b) => a + b, 0) / lossStreaks.length : 0;

    const maxLossStreak = Math.max(...lossStreaks, 0);

    return {
      avgWinStreak,
      avgLossStreak,
      maxLossStreak,
      hasLongLossStreak: maxLossStreak >= this.minConsecutiveLoss,
      streakBalance: Math.abs(avgWinStreak - avgLossStreak) < 1
    };
  }

  // รวมการวิเคราะห์ทั้งหมด
  combinedAnalysis(patterns) {
    const { winLossPattern, colorPattern, conflictPattern, streakPattern, recentData } = patterns;

    // คำนวณ Sideways Score
    let sidewaysScore = 0;
    let indicators = [];

    // 1. Win/Loss Balance (30 คะแนน)
    if (winLossPattern.isBalanced) {
      sidewaysScore += 30;
      indicators.push(`Win Rate สมดุล (${(winLossPattern.winRate * 100).toFixed(1)}%)`);
    }

    // 2. Alternating Pattern (25 คะแนน)
    if (winLossPattern.isAlternating) {
      sidewaysScore += 25;
      indicators.push(`มีการสลับ Win/Loss บ่อย (${(winLossPattern.alternatingRate * 100).toFixed(1)}%)`);
    }

    // 3. Color Balance (20 คะแนน)
    if (colorPattern.colorBalance) {
      sidewaysScore += 20;
      indicators.push(`สีสมดุล (G:${(colorPattern.greenRate * 100).toFixed(1)}% R:${(colorPattern.redRate * 100).toFixed(1)}%)`);
    }

    // 4. Conflict Balance (15 คะแนน)
    if (conflictPattern.hasBalancedConflicts && conflictPattern.conflictRate > 0.2) {
      sidewaysScore += 15;
      indicators.push(`Conflict สมดุล (${(conflictPattern.conflictRate * 100).toFixed(1)}%)`);
    }

    // 5. Streak Pattern (10 คะแนน)
    if (streakPattern.streakBalance && !streakPattern.hasLongLossStreak) {
      sidewaysScore += 10;
      indicators.push(`Streak สมดุล`);
    }

    // ลบคะแนนถ้ามี Loss Streak ยาว
    if (streakPattern.maxLossStreak >= 5) {
      sidewaysScore -= 30;
      indicators.push(`⚠️ Loss Streak ยาว (${streakPattern.maxLossStreak})`);
    }

    // คำนวณ Confidence
    const confidence = Math.max(0, Math.min(1, sidewaysScore / 100));
    const isSideways = sidewaysScore >= 60; // threshold 60%

    // กำหนด Trend
    let trend = 'undefined';
    if (isSideways) {
      trend = 'sideways';
    } else if (winLossPattern.winRate > 0.6) {
      trend = 'favorable'; // trend ที่เอื้อต่อการเทรด
    } else if (winLossPattern.winRate < 0.4 || streakPattern.maxLossStreak >= 5) {
      trend = 'unfavorable'; // trend ที่ไม่เอื้อ
    } else {
      trend = 'neutral';
    }

    // สร้าง details
    const details = indicators.length > 0 ?
      indicators.join(', ') :
      'ไม่พบ pattern ที่ชัดเจน';

    return {
      isSideways,
      trend,
      confidence,
      details,
      sidewaysScore,
      analysis: {
        winRate: winLossPattern.winRate,
        alternatingRate: winLossPattern.alternatingRate,
        conflictRate: conflictPattern.conflictRate,
        maxLossStreak: streakPattern.maxLossStreak,
        colorBalance: colorPattern.colorBalance
      },
      indicators,
      rawPatterns: patterns
    };
  }
}
/*
// ทดสอบกับข้อมูลจริง
function testWithRealData() {
  const analyzer = new BinarySidewaysAnalyzer({
    lookbackPeriods: 15,
    sidewaysThreshold: 0.6,
    minConsecutiveLoss: 3
  });

  // ข้อมูลจริงจาก document
  const realData = [
    {"time":1755759300,"timeDisplay":"06:55","thisColor":"Green","WinStatus":"Win","LossContinue":0,"ConflictType":"Bullish Conflict"},
    {"time":1755759360,"timeDisplay":"06:56","thisColor":"Green","WinStatus":"Loss","LossContinue":1,"ConflictType":"Bullish Conflict"},
    {"time":1755759420,"timeDisplay":"06:57","thisColor":"Red","WinStatus":"Loss","LossContinue":2,"ConflictType":"n"},
    {"time":1755759480,"timeDisplay":"06:58","thisColor":"Green","WinStatus":"Win","LossContinue":0,"ConflictType":"Bullish Conflict"},
    {"time":1755759540,"timeDisplay":"06:59","thisColor":"Green","WinStatus":"Loss","LossContinue":1,"ConflictType":"Bullish Conflict"},
    {"time":1755759600,"timeDisplay":"07:00","thisColor":"Red","WinStatus":"Loss","LossContinue":2,"ConflictType":"n"},
    {"time":1755759660,"timeDisplay":"07:01","thisColor":"Green","WinStatus":"Win","LossContinue":0,"ConflictType":"n"},
    {"time":1755759720,"timeDisplay":"07:02","thisColor":"Green","WinStatus":"Loss","LossContinue":1,"ConflictType":"n"},
    {"time":1755759780,"timeDisplay":"07:03","thisColor":"Red","WinStatus":"Win","LossContinue":0,"ConflictType":"n"},
    {"time":1755759840,"timeDisplay":"07:04","thisColor":"Red","WinStatus":"Loss","LossContinue":1,"ConflictType":"n"},
    {"time":1755759900,"timeDisplay":"07:05","thisColor":"Green","WinStatus":"Win","LossContinue":0,"ConflictType":"Bullish Conflict"},
    {"time":1755759960,"timeDisplay":"07:06","thisColor":"Green","WinStatus":"Loss","LossContinue":1,"ConflictType":"Bullish Conflict"},
    {"time":1755760020,"timeDisplay":"07:07","thisColor":"Red","WinStatus":"Win","LossContinue":0,"ConflictType":"n"},
    {"time":1755760080,"timeDisplay":"07:08","thisColor":"Red","WinStatus":"Loss","LossContinue":1,"ConflictType":"n"},
    {"time":1755760140,"timeDisplay":"07:09","thisColor":"Green","WinStatus":"Loss","LossContinue":2,"ConflictType":"Bullish Conflict"}
  ];

  const result = analyzer.analyzeSidewaysTrend(realData);

  console.log('=== Binary Option Sideways Analysis ===');
  console.log(`Trend: ${result.trend}`);
  console.log(`Is Sideways: ${result.isSideways}`);
  console.log(`Confidence: ${(result.confidence * 100).toFixed(1)}%`);
  console.log(`Sideways Score: ${result.sidewaysScore}/100`);
  console.log(`Details: ${result.details}`);
  console.log('\nKey Metrics:');
  console.log(`- Win Rate: ${(result.analysis.winRate * 100).toFixed(1)}%`);
  console.log(`- Alternating Rate: ${(result.analysis.alternatingRate * 100).toFixed(1)}%`);
  console.log(`- Conflict Rate: ${(result.analysis.conflictRate * 100).toFixed(1)}%`);
  console.log(`- Max Loss Streak: ${result.analysis.maxLossStreak}`);

  return result;
}

// Export และทดสอบ
testWithRealData();
**/