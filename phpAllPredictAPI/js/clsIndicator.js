class clsIndicator {


   constructor() {
       this.CandleData = null ;


   }

   setCandleData(candleArray) {
     this.CandleData = candleArray ;
	 console.log('Indy Candle Data ',this.CandleData) ;
   }

   AgetCandleColor(candle) {

      if (candle.close > candle.open) return 'Green';
      if (candle.close < candle.open) return 'Red';
      return 'Equal';
   }

   // Determine slope direction
   getSlopeDirection(slopeValue, threshold = 0.0001) {
      if (Math.abs(slopeValue) < threshold) return 'Parallel';
         return slopeValue > 0 ? 'Up' : 'Down';
   }

   getTurnType(currentSlope, previousSlope, threshold = 0.0001) {
	  const currentDir = this.getSlopeDirection(currentSlope, threshold);
	  const previousDir = this.getSlopeDirection(previousSlope, threshold);

	  if (currentDir === previousDir || previousDir === 'Parallel') return 'NoTurn';
	  if (currentDir === 'Up' && previousDir === 'Down') return 'TurnUp';
	  if (currentDir === 'Down' && previousDir === 'Up') return 'TurnDown';
	  return 'NoTurn';
	}

	// Determine EMA position relative to candle
	getEMACutPosition(emaValue, candle) {
	  const { open, high, low, close } = candle;
	  const upperWick = high;
	  const lowerWick = low;
	  const bodyTop = Math.max(open, close);
	  const bodyBottom = Math.min(open, close);

	  if (emaValue > upperWick) return 'Above Upper Wick';
	  if (emaValue >= bodyTop && emaValue <= upperWick) return 'Between Upper Wick and Body';
	  if (emaValue >= bodyBottom && emaValue <= bodyTop) return 'Inside Body';
	  if (emaValue >= lowerWick && emaValue <= bodyBottom) return 'Between Body and Lower Wick';
	  return 'Below Lower Wick';
	}

   // Calculate EMA Old
   calculateEMAIndy(data, period) {
            const ema = [];
            const k = 2 / (period + 1);
            let emaValue = data[0].close;

            for (let i = 0; i < data.length; i++) {
                if (i === 0) {
                    emaValue = data[i].close;
                } else {
                    emaValue = data[i].close * k + emaValue * (1 - k);
                }
                ema.push({ time: data[i].time, value: emaValue });
            }
            //console.log('data Recived',data);
			//console.log('******',ema);


            return ema;
   } // calEMA

   calculateEMA(data, period) {
    const k = 2 / (period + 1);
    const ema = [];

    if (data.length === 0) return ema;
	for (let i = 0; i < data.length; i++) {
      //data[i].time = data[i].time + (7*3600)
	}
	console.log('Cal EMA Data-0 Time',data[0].time);


    // สร้าง SMA สำหรับทุกจุดก่อน period
    for (let i = 0; i < data.length; i++) {
        if (i < period - 1) {
            // คำนวณ SMA จาก 0 ถึง i
            let sum = 0;
            for (let j = 0; j <= i; j++) {
                sum += data[j].close;
            }
            ema.push({ time: data[i].time, value: sum / (i + 1) });
        } else if (i === period - 1) {
            // จุดแรกที่ได้ SMA เต็ม period
            let sum = 0;
            for (let j = 0; j < period; j++) {
                sum += data[j].close;
            }
            const smaValue = sum / period;
            ema.push({ time: data[i].time, value: smaValue });
        } else {
            // EMA ปกติ
            const prevEMA = ema[i - 1].value;
            const emaValue = data[i].close * k + prevEMA * (1 - k);
            ema.push({ time: data[i].time, value: emaValue });
        }
    }

    console.log('ema',period,ema[0]);

    return ema;
   }

   // Calculate Bollinger Bands
   calculateBollingerBands(data, period = 20, stdDev = 2) {
    const sma = [];
    const upper = [];
    const lower = [];

       for (let i = 0; i < data.length; i++) {
                if (i < period - 1) continue;
                const slice = data.slice(i - period + 1, i + 1);
                const mean = slice.reduce((sum, d) => sum + d.close, 0) / period;
                const variance = slice.reduce((sum, d) => sum + Math.pow(d.close - mean, 2), 0) / period;
                const std = Math.sqrt(variance);
                sma.push({ time: data[i].time, value: mean });
                upper.push({ time: data[i].time, value: mean + stdDev * std });
                lower.push({ time: data[i].time, value: mean - stdDev * std });
        }


            return { sma, upper, lower };
     } // end calculateBollingerBands

   // Calculate ATR (Average True Range)
   calculateATR(data, period = 14) {
      const atr = [];
      let atrValue = 0;

            for (let i = 1; i < data.length; i++) {
                const high = data[i].high;
                const low = data[i].low;
                const prevClose = data[i - 1].close;

                const tr = Math.max(
                    high - low,
                    Math.abs(high - prevClose),
                    Math.abs(low - prevClose)
                );

                if (i === 1) {
                    atrValue = tr;
                } else {
                    atrValue = ((atrValue * (period - 1)) + tr) / period;
                }

                if (i >= period) {
                    atr.push({ time: data[i].time, value: atrValue });
                }
            }

            return atr;
   } // cal ATR

   // Calculate VWAP (Volume Weighted Average Price)
   calculateVWAP(data) {
    const vwap = [];
    let cumulativeTPV = 0;
    let cumulativeVolume = 0;

            for (let i = 0; i < data.length; i++) {
                const typical = (data[i].high + data[i].low + data[i].close) / 3;
                const volume = data[i].volume || 1000; // Use dummy volume if not available

                cumulativeTPV += typical * volume;
                cumulativeVolume += volume;

                vwap.push({
                    time: data[i].time,
                    value: cumulativeTPV / cumulativeVolume
                });
            }

            return vwap;
   } // end vwap

   // Find Swing High and Low points
   findSwingPoints(data, lookback = 3) {
            const swingHighs = [];
            const swingLows = [];

            for (let i = lookback; i < data.length - lookback; i++) {
                let isSwingHigh = true;
                let isSwingLow = true;

                for (let j = 1; j <= lookback; j++) {
                    if (data[i].high <= data[i-j].high || data[i].high <= data[i+j].high) {
                        isSwingHigh = false;
                    }
                    if (data[i].low >= data[i-j].low || data[i].low >= data[i+j].low) {
                        isSwingLow = false;
                    }
                }

                if (isSwingHigh) swingHighs.push({time: data[i].time, price: data[i].high, type: 'resistance'});
                if (isSwingLow) swingLows.push({time: data[i].time, price: data[i].low, type: 'support'});
            }

            return [...swingHighs, ...swingLows];
    }

	// Calculate Fibonacci levels
    calculateFibonacci(data) {
            if (data.length < 2) return [];

            const high = Math.max(...data.map(d => d.high));
            const low = Math.min(...data.map(d => d.low));
            const diff = high - low;

            const levels = [
                {price: high, label: '0%', type: 'fib'},
                {price: high - diff * 0.236, label: '23.6%', type: 'fib'},
                {price: high - diff * 0.382, label: '38.2%', type: 'fib'},
                {price: high - diff * 0.5, label: '50%', type: 'fib'},
                {price: high - diff * 0.618, label: '61.8%', type: 'fib'},
                {price: high - diff * 0.786, label: '78.6%', type: 'fib'},
                {price: low, label: '100%', type: 'fib'}
            ];

            return levels;
    } // end Calculate Fibonacci levels

	// Calculate Pivot Points
    calculatePivotPoints(data) {
            if (data.length < 1) return [];

            const lastCandle = data[data.length - 1];
            const high = lastCandle.high;
            const low = lastCandle.low;
            const close = lastCandle.close;

            const pivot = (high + low + close) / 3;
            const r1 = 2 * pivot - low;
            const r2 = pivot + (high - low);
            const r3 = high + 2 * (pivot - low);
            const s1 = 2 * pivot - high;
            const s2 = pivot - (high - low);
            const s3 = low - 2 * (high - pivot);

            return [
                {price: r3, label: 'R3', type: 'resistance'},
                {price: r2, label: 'R2', type: 'resistance'},
                {price: r1, label: 'R1', type: 'resistance'},
                {price: pivot, label: 'PP', type: 'pivot'},
                {price: s1, label: 'S1', type: 'support'},
                {price: s2, label: 'S2', type: 'support'},
                {price: s3, label: 'S3', type: 'support'}
            ];
    } // end

	// Find key levels by clustering
    findKeyLevels(swingPoints, tolerance = 0.001) {
            const levels = [];

            swingPoints.forEach(point => {
                let foundCluster = false;

                for (let level of levels) {
                    if (Math.abs(point.price - level.price) / level.price < tolerance) {
                        level.count++;
                        level.price = (level.price * (level.count - 1) + point.price) / level.count;
                        if (!level.times.includes(point.time)) {
                            level.times.push(point.time);
                        }
                        foundCluster = true;
                        break;
                    }
                }

                if (!foundCluster) {
                    levels.push({
                        price: point.price,
                        count: 1,
                        type: point.type,
                        times: [point.time]
                    });
                }
            });

            return levels.filter(l => l.count >= 2).sort((a, b) => b.count - a.count);
        }

	// Create Support/Resistance Zones
    createSRZones(data, tolerance,swingLookback) {
            const swingPoints = this.findSwingPoints(data, swingLookback);
            const keyLevels = this.findKeyLevels(swingPoints, tolerance);

            return keyLevels.map(level => ({
                price: level.price,
                count: level.count,
                type: level.type,
                zone: {
                    upper: level.price * (1 + tolerance),
                    lower: level.price * (1 - tolerance)
                }
            }));
    } // end SRZones


	analyzeCandleBody_OneCandle(candle) {
		  const {time, open, high, low, close } = candle;

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
            time : time ,
			fullCandleSize: fullCandleSize.toFixed(4),
			upperWickPercent: upperWickPercent.toFixed(2),
			bodyPercent: bodyPercent.toFixed(2),
			lowerWickPercent: lowerWickPercent.toFixed(2),
			codeCandleBody,
			candleDesc: candleDesc.join(', ')
		  };
	} // end analyzeCandleBody_OneCandle

	analyzeCandleBody(AllCandle) {

		let analyList = [] ;
		//console.log('Al Candle',AllCandle);

        for (let i=0;i<=AllCandle.length-1 ;i++ ) {
           let analyOneCandle = this.analyzeCandleBody_OneCandle(AllCandle[i]);
		   analyList.push(analyOneCandle) ;
        }
		 console.log('analyList',analyList) ;


		return analyOneCandle ;

	}








}