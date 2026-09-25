#[allow(dead_code)]
#[derive(Debug)]
struct Candle {
    time: u64,
    open: f64,
    high: f64,
    low: f64,
    close: f64,
    volume: u32,
}

impl Candle {
    fn new(time: u64, open: f64, high: f64, low: f64, close: f64, volume: u32) -> Self {
        Self {
            time,
            open,
            high,
            low,
            close,
            volume,
        }
    }

    fn is_bullish(&self) -> bool {
        self.close > self.open
    }

    fn change_percent(&self) -> f64 {
        ((self.close - self.open) / self.open) * 100.0
    }
}

fn calculate_sma(prices: &[f64], period: usize) -> Vec<f64> {
    let mut sma_values = Vec::new();
    if prices.len() < period {
        return sma_values;
    }

    for i in 0..=prices.len() - period {
        let window = &prices[i..i + period];
        let sum: f64 = window.iter().sum();
        let avg = sum / (period as f64);
        sma_values.push(avg); // <<-- ลองวาง Breakpoint ที่นี่
    }

    sma_values
}

fn main() {
    println!("=== เริ่มต้นการทดสอบ Rust Debugger ===");

    // 1. สร้างชุดข้อมูลจำลอง
    let candles = vec![
        Candle::new(1700000000, 100.0, 105.0, 99.0, 104.5, 1200),
        Candle::new(1700000060, 104.5, 108.0, 103.0, 107.2, 1850),
        Candle::new(1700000120, 107.2, 107.5, 102.0, 103.0, 950),
        Candle::new(1700000180, 103.0, 106.0, 101.5, 105.8, 1400),
        Candle::new(1700000240, 105.8, 110.0, 105.0, 109.5, 2100),
    ];

    let mut close_prices = Vec::new();

    // 2. Loop วิเคราะห์แท่งเทียน
    for (index, candle) in candles.iter().enumerate() {
        let is_bull = candle.is_bullish();
        let change = candle.change_percent();
        close_prices.push(candle.close);

        // <<-- ลองวาง Breakpoint ที่บรรทัดนี้เพื่อดูค่า candle, is_bull, change ในแต่ละรอบ
        println!(
            "[{}] Close: {:.2}, Change: {:.2}%, Bullish: {}",
            index + 1,
            candle.close,
            change,
            is_bull
        );
    }

    // 3. คำนวณ Simple Moving Average (SMA Period = 3)
    let period = 3;
    let sma = calculate_sma(&close_prices, period);

    println!("\nSMA (Period = {}) ผลลัพธ์: {:?}", period, sma);
    println!("=== จบการทดสอบ Debugger ===");
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_candle_bullish() {
        let c = Candle::new(1, 100.0, 110.0, 95.0, 105.0, 500);
        // <<-- ลองวาง Breakpoint ที่นี่แล้วกด 'Debug' เหนือฟังก์ชัน test_candle_bullish
        assert!(c.is_bullish());
        assert_eq!(c.change_percent(), 5.0);
    }

    #[test]
    fn test_sma() {
        let prices = vec![10.0, 20.0, 30.0, 40.0];
        let sma = calculate_sma(&prices, 2);
        assert_eq!(sma, vec![15.0, 25.0, 35.0]);
    }
}
