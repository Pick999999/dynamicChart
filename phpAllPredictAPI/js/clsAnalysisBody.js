class AnalysisBodyAndTrend {
   //const clsAnalyBody = new AnalysisBodyAndTrend();
   constructor() {


   }

	// Analyze candle body
	// รับ RawData
	 analyzeCandleBody(candle) {

	  console.log('Candle',candle)

	  const { open, high, low, close } = candle;

	  const fullCandleSize = high - low;
	  const bodySize = Math.abs(close - open);
	  const upperWickSize = high - Math.max(open, close);
	  const lowerWickSize = Math.min(open, close) - low;

	  const upperWickPercent = fullCandleSize > 0 ? (upperWickSize / fullCandleSize) * 100 : 0;
	  const bodyPercent = fullCandleSize > 0 ? (bodySize / fullCandleSize) * 100 : 0;
	  const lowerWickPercent = fullCandleSize > 0 ? (lowerWickSize / fullCandleSize) * 100 : 0;

	  // Determine candle type
	  let candleDesc = [];
	  let codeCandleBody = '';

	  // Doji detection (body < 10% of full candle)
	  if (bodyPercent < 10) {
		candleDesc.push('Doji');
		codeCandleBody = 'DOJI';
	  }

	  // Hammer detection (small body at top, long lower wick)
	  if (bodyPercent < 30 && lowerWickPercent > 60 && upperWickPercent < 10) {
		candleDesc.push('Hammer');
		codeCandleBody = 'HAMMER';
	  }

	  // Shooting Star (small body at bottom, long upper wick)
	  if (bodyPercent < 30 && upperWickPercent > 60 && lowerWickPercent < 10) {
		candleDesc.push('ShootingStar');
		codeCandleBody = 'SHOOTING_STAR';
	  }

	  if (candleDesc.length === 0) {
		candleDesc.push('Normal');
		codeCandleBody = 'NORMAL';
	  }

	  return {
		fullCandleSize: fullCandleSize.toFixed(4),
		upperWickPercent: upperWickPercent.toFixed(2),
		bodyPercent: bodyPercent.toFixed(2),
		lowerWickPercent: lowerWickPercent.toFixed(2),
		codeCandleBody,
		candleDesc: candleDesc.join(', ')
	  };
	} // end Func


}