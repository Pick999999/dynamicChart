// ========================================
// Deriv OHLC Data Fetcher for SuperTrend AI
// ========================================

class DerivDataFetcher {
    constructor(appId = 'YOUR_APP_ID') {
        this.appId = appId; // Get free app_id from https://api.deriv.com/app-registration
        this.ws = null;
    }

    // เชื่อมต่อ WebSocket
    connect() {
        return new Promise((resolve, reject) => {
            this.ws = new WebSocket(`wss://ws.derivws.com/websockets/v3?app_id=${this.appId}`);

            this.ws.onopen = () => {
                console.log('✅ Connected to Deriv WebSocket');
                resolve();
            };

            this.ws.onerror = (error) => {
                console.error('❌ WebSocket Error:', error);
                reject(error);
            };
        });
    }

    // ดึงข้อมูล OHLC
    async getOHLC(symbol = 'R_100', granularity = 60, count = 500) {
        /**
         * symbol: 'R_100', 'R_50', 'frxEURUSD', 'BTCUSD', etc.
         * granularity: 60 (1min), 120 (2min), 300 (5min), 900 (15min),
         *              3600 (1hr), 14400 (4hr), 86400 (1day)
         * count: จำนวน candles (max 5000)
         */
        //alert('a');
        return new Promise((resolve, reject) => {

			const requestA = {
						ticks_history: 'R_100',
						count: 300,
                        end: 'latest',
						style: 'candles',
						granularity: 60,
						req_id: 1
					};
            const request = {
                ticks_history: symbol,
                adjust_start_time: 1,
                count: count,
                end: 'latest',
                start: 1,
                style: 'candles',
                granularity: granularity
            };
			console.log('Send Requests ',requestA)


            this.ws.send(JSON.stringify(requestA));

            this.ws.onmessage = (msg) => {
                const data = JSON.parse(msg.data);

                if (data.error) {
                    console.error('❌ Deriv API Error:', data.error.message);
                    reject(data.error);
                    return;
                }

                if (data.candles) {
                    console.log('✅ Received OHLC data:', data.candles.length, 'candles');
                    resolve(data);
                }
            };
        });
    }

    // แปลงข้อมูล Deriv เป็นรูปแบบที่ Indicator ใช้
    convertToIndicatorFormat(derivData) {
        const candles = derivData.candles;

        // แปลงข้อมูล
        const converted = candles.map(candle => ({
            time: candle.epoch,           // Unix timestamp
            open: parseFloat(candle.open),
            high: parseFloat(candle.high),
            low: parseFloat(candle.low),
            close: parseFloat(candle.close),
            volume: 1                      // Deriv ไม่มี volume ใช้ 1 แทน
        }));

        // เรียงข้อมูลตาม time (เก่าไปใหม่)
        converted.sort((a, b) => a.time - b.time);

        // กรอง duplicate และ null values
        const filtered = converted.filter((candle, index, arr) => {
            // ตรวจสอบว่าไม่ใช่ duplicate
            if (index > 0 && candle.time === arr[index - 1].time) {
                return false;
            }
            // ตรวจสอบว่าข้อมูลถูกต้อง
            return candle.open > 0 && candle.high > 0 &&
                   candle.low > 0 && candle.close > 0 &&
                   candle.high >= candle.low;
        });

        console.log('📊 Converted candles:', filtered.length);
        console.log('⏰ Time range:',
            new Date(filtered[0].time * 1000).toLocaleString(),
            'to',
            new Date(filtered[filtered.length - 1].time * 1000).toLocaleString()
        );

        return filtered;
    }

    // ปิดการเชื่อมต่อ
    close() {
        if (this.ws) {
            this.ws.close();
            console.log('🔌 Disconnected from Deriv');
        }
    }
}

// ========================================
// ตัวอย่างการใช้งาน
// ========================================

async function example() {
    try {
        // 1. สร้าง instance (ใส่ app_id ของคุณ)
        const fetcher = new DerivDataFetcher('1089'); // ใช้ app_id ทดสอบ

        // 2. เชื่อมต่อ
        await fetcher.connect();

        // 3. ดึงข้อมูล OHLC
        const derivData = await fetcher.getOHLC(
            'R_100',  // Volatility 100 Index
            60,       // 1 minute candles
            500       // 500 candles
        );

        console.log('📊 Raw Deriv Data:', derivData);

        // 4. แปลงข้อมูลให้ใช้กับ Indicator
        const indicatorData = fetcher.convertToIndicatorFormat(derivData);

        console.log('✅ Converted Data (first 3):', indicatorData.slice(0, 3));
        console.log('Total candles:', indicatorData.length);

        // 5. ใช้กับ SuperTrend AI Indicator
        // const indicator = new VolumeSuperTrendAI({ ... });
        // indicator.calculate(indicatorData);

        // 6. ปิดการเชื่อมต่อ
        fetcher.close();

        return indicatorData;

    } catch (error) {
        console.error('Error:', error);
    }
}

// เรียกใช้งาน
// example();


// ========================================
// ข้อมูลที่ได้กลับมาจาก Deriv (ตัวอย่าง)
// ========================================

/*
RAW DERIV RESPONSE:
{
    "candles": [
        {
            "close": "10382.71",
            "epoch": 1640000000,
            "high": "10383.45",
            "low": "10380.12",
            "open": "10381.50"
        },
        ...
    ],
    "pip_size": 2
}

CONVERTED FOR INDICATOR:
[
    {
        "time": 1640000000,
        "open": 10381.50,
        "high": 10383.45,
        "low": 10380.12,
        "close": 10382.71,
        "volume": 1
    },
    ...
]
*/


// ========================================
// Symbols ที่ใช้ได้กับ Deriv
// ========================================

const DERIV_SYMBOLS = {
    // Volatility Indices
    volatility: {
        'R_10': 'Volatility 10 Index',
        'R_25': 'Volatility 25 Index',
        'R_50': 'Volatility 50 Index',
        'R_75': 'Volatility 75 Index',
        'R_100': 'Volatility 100 Index',
    },

    // Forex
    forex: {
        'frxEURUSD': 'EUR/USD',
        'frxGBPUSD': 'GBP/USD',
        'frxUSDJPY': 'USD/JPY',
        'frxAUDUSD': 'AUD/USD',
    },

    // Crypto
    crypto: {
        'cryBTCUSD': 'BTC/USD',
        'cryETHUSD': 'ETH/USD',
    },

    // Commodities
    commodities: {
        'frxXAUUSD': 'Gold/USD',
        'frxXAGUSD': 'Silver/USD',
    }
};

// ========================================
// Granularity (Timeframes)
// ========================================

const TIMEFRAMES = {
    '1m': 60,
    '2m': 120,
    '3m': 180,
    '5m': 300,
    '10m': 600,
    '15m': 900,
    '30m': 1800,
    '1h': 3600,
    '2h': 7200,
    '4h': 14400,
    '8h': 28800,
    '1d': 86400
};

console.log('📝 Available Symbols:', DERIV_SYMBOLS);
console.log('⏰ Available Timeframes:', TIMEFRAMES);