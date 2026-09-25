-- =============================================================
-- Database: database_chart
-- Tables: full_analysis_data, analysis_pk_trend, analysis_smc
-- =============================================================

CREATE DATABASE IF NOT EXISTS `database_chart` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `database_chart`;

-- -------------------------------------------------------------
-- 1. ตารางหลัก: full_analysis_data
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `full_analysis_data` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `serverCode` VARCHAR(50) NOT NULL COMMENT 'รหัส Server เช่น Server1, VPS-01',
  `index_no` INT NOT NULL COMMENT 'index ของแท่งเทียน',
  `assetCode` VARCHAR(30) NOT NULL COMMENT 'รหัสคู่เหรียญ/สินทรัพย์ เช่น 1HZ75V',
  `candletime` BIGINT UNSIGNED NOT NULL COMMENT 'Unix timestamp (วินาที)',
  `candletime_display` DATETIME NOT NULL COMMENT 'รูปแบบวันที่ เช่น 2026-09-11 06:59:00',
  
  -- ราคา OHLC & สีแท่งเทียน
  `open` DECIMAL(18, 6) NOT NULL,
  `high` DECIMAL(18, 6) NOT NULL,
  `low` DECIMAL(18, 6) NOT NULL,
  `close` DECIMAL(18, 6) NOT NULL,
  `color` VARCHAR(10) NOT NULL,
  `next_color` VARCHAR(10) DEFAULT NULL,
  `pip_size` DECIMAL(18, 6) DEFAULT NULL,

  -- EMA Short
  `ema_short_value` DECIMAL(18, 6) DEFAULT NULL,
  `ema_short_direction` VARCHAR(10) DEFAULT NULL,
  `ema_short_turn_type` VARCHAR(20) DEFAULT NULL,
  `ema_short_slope_value` DECIMAL(18, 6) DEFAULT NULL,
  `emaslopeThereshold` DECIMAL(18, 6) DEFAULT 0,
  `diff` DECIMAL(18, 6) DEFAULT NULL,
  `ema_short_flat` CHAR(1) DEFAULT 'n',
  `ema_short_pos` VARCHAR(20) DEFAULT NULL,

  -- EMA Medium
  `ema_medium_value` DECIMAL(18, 6) DEFAULT NULL,
  `ema_medium_direction` VARCHAR(10) DEFAULT NULL,
  `ema_medium_turn_type` VARCHAR(20) DEFAULT NULL,
  `ema_medium_slope_value` DECIMAL(18, 6) DEFAULT NULL,
  `ema_medium_flat` CHAR(1) DEFAULT 'n',
  `ema_medium_pos` VARCHAR(20) DEFAULT NULL,

  -- EMA Long
  `ema_long_value` DECIMAL(18, 6) DEFAULT NULL,
  `ema_long_direction` VARCHAR(10) DEFAULT NULL,
  `ema_long_turn_type` VARCHAR(20) DEFAULT NULL,
  `ema_long_slope_value` DECIMAL(18, 6) DEFAULT NULL,
  `ema_long_flat` CHAR(1) DEFAULT 'n',
  `ema_long_pos` VARCHAR(20) DEFAULT NULL,

  -- EMA Gaps & Convergence
  `short_medium_gap_value` DECIMAL(18, 6) DEFAULT NULL,
  `is_short_medium_gap_occur` CHAR(1) DEFAULT 'n',
  `medium_long_gap_value` DECIMAL(18, 6) DEFAULT NULL,
  `is_medium_long_gap_occur` CHAR(1) DEFAULT 'n',
  `ema_above` VARCHAR(30) DEFAULT NULL,
  `ema_long_above` VARCHAR(30) DEFAULT NULL,
  `ema_convergence_type` VARCHAR(30) DEFAULT NULL,
  `ema_long_convergence_type` VARCHAR(30) DEFAULT NULL,

  -- Previous EMA & MACD
  `previous_ema_short_value` DECIMAL(18, 6) DEFAULT NULL,
  `previous_ema_medium_value` DECIMAL(18, 6) DEFAULT NULL,
  `previous_ema_long_value` DECIMAL(18, 6) DEFAULT NULL,
  `macd_12` DECIMAL(18, 6) DEFAULT NULL,
  `macd_23` DECIMAL(18, 6) DEFAULT NULL,
  `previous_macd_12` DECIMAL(18, 6) DEFAULT NULL,
  `previous_macd_23` DECIMAL(18, 6) DEFAULT NULL,

  -- Other Technical Indicators
  `choppy_indicator` DECIMAL(18, 6) DEFAULT NULL,
  `adx_value` DECIMAL(18, 6) DEFAULT NULL,
  `rsi_value` DECIMAL(18, 6) DEFAULT NULL,
  `atrValue` DECIMAL(18, 6) DEFAULT NULL,
  `is_abnormal_candle` BOOLEAN DEFAULT FALSE,
  `is_abnormal_atr` BOOLEAN DEFAULT FALSE,
  `is_atr` BOOLEAN DEFAULT FALSE,

  -- Bollinger Bands (bb_values)
  `bb_upper` DECIMAL(18, 6) DEFAULT NULL,
  `bb_middle` DECIMAL(18, 6) DEFAULT NULL,
  `bb_lower` DECIMAL(18, 6) DEFAULT NULL,
  `bb_position` VARCHAR(20) DEFAULT NULL,
  `bb_bandwidth` DECIMAL(18, 6) DEFAULT NULL,
  `is_bb_squeeze` BOOLEAN DEFAULT FALSE,

  -- Candle Wick & Body Analysis
  `u_wick` DECIMAL(18, 6) DEFAULT NULL,
  `u_wick_percent` DECIMAL(8, 4) DEFAULT NULL,
  `body` DECIMAL(18, 6) DEFAULT NULL,
  `body_percent` DECIMAL(8, 4) DEFAULT NULL,
  `l_wick` DECIMAL(18, 6) DEFAULT NULL,
  `l_wick_percent` DECIMAL(8, 4) DEFAULT NULL,

  -- EMA Cuts & Consecutives
  `ema_cut_position` VARCHAR(20) DEFAULT '-',
  `ema_cut_long_type` VARCHAR(20) DEFAULT '-',
  `ema_cut_short_long_type` VARCHAR(20) DEFAULT '-',
  `ema_cut_all_type` VARCHAR(20) DEFAULT '-',
  `candles_since_ema_cut` INT DEFAULT 0,
  `ageCutCandleCode12` VARCHAR(50) DEFAULT NULL,
  `ageCutCandleCode123` VARCHAR(50) DEFAULT NULL,
  `up_con_medium_ema` INT DEFAULT 0,
  `down_con_medium_ema` INT DEFAULT 0,
  `up_con_long_ema` INT DEFAULT 0,
  `down_con_long_ema` INT DEFAULT 0,

  -- Status & Win/Loss Signals
  `is_mark` CHAR(1) DEFAULT 'n',
  `status_code` VARCHAR(20) DEFAULT '0',
  `status_desc` VARCHAR(100) DEFAULT '-',
  `status_desc_0` VARCHAR(100) DEFAULT '-',
  `hint_status` VARCHAR(50) DEFAULT '',
  `suggest_color` VARCHAR(20) DEFAULT '',
  `win_status` VARCHAR(20) DEFAULT '',
  `win_con` INT DEFAULT 0,
  `loss_con` INT DEFAULT 0,

  -- Tick Volatility (tick_volatility)
  `tick_count` INT DEFAULT 0,
  `buy_tick_count` INT DEFAULT 0,
  `sell_tick_count` INT DEFAULT 0,
  `buy_sell_ratio` DECIMAL(8, 4) DEFAULT 0.5,
  `avg_tick_move` DECIMAL(18, 6) DEFAULT 0,
  `max_tick_move` DECIMAL(18, 6) DEFAULT 0,
  `sum_tick_move` DECIMAL(18, 6) DEFAULT 0,
  `volatility_clustering` DECIMAL(18, 6) DEFAULT 0,
  `volatility_level` VARCHAR(20) DEFAULT 'Low',

  -- Range Detector (range_detector)
  `range_in_range` BOOLEAN DEFAULT FALSE,
  `range_top` DECIMAL(18, 6) DEFAULT NULL,
  `range_bottom` DECIMAL(18, 6) DEFAULT NULL,
  `range_avg` DECIMAL(18, 6) DEFAULT NULL,
  `range_state` VARCHAR(20) DEFAULT NULL,

  -- Alternating Patterns
  `is_alternating_pattern` BOOLEAN DEFAULT FALSE,
  `alternating_sequence_length` INT DEFAULT 0,
  `is_alternating_trigger` BOOLEAN DEFAULT FALSE,
  `is_alternating_spike` BOOLEAN DEFAULT FALSE,

  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_server_asset_candle` (`serverCode`, `assetCode`, `candletime`),
  KEY `idx_asset_candletime` (`assetCode`, `candletime_display`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -------------------------------------------------------------
-- 2. ตารางแยก: analysis_pk_trend
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `analysis_pk_trend` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `analysis_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK เชื่อมกับ full_analysis_data.id',
  
  `codeNo` INT DEFAULT NULL,
  `trend` VARCHAR(50) DEFAULT NULL,
  `caseCode` VARCHAR(50) DEFAULT NULL,
  `caseDesc` VARCHAR(50) DEFAULT NULL,
  `group_name` VARCHAR(50) DEFAULT NULL COMMENT 'group เดิมใน JSON (เปลี่ยนชื่อเพื่อไม่ให้ชน keyword)',
  `description` TEXT DEFAULT NULL,
  `isSpike` BOOLEAN DEFAULT FALSE,
  `extremeTrend` VARCHAR(50) DEFAULT NULL,
  `closePosition` DECIMAL(8, 4) DEFAULT NULL,
  `bodyRatio` DECIMAL(8, 4) DEFAULT NULL,
  
  -- structure
  `structure_higher_high` BOOLEAN DEFAULT FALSE,
  `structure_lower_low` BOOLEAN DEFAULT FALSE,
  `structure_higher_low` BOOLEAN DEFAULT FALSE,
  `structure_lower_high` BOOLEAN DEFAULT FALSE,

  `rangeRatio` DECIMAL(8, 4) DEFAULT NULL,
  `colorSequence` VARCHAR(50) DEFAULT NULL,
  `colorSwitches` INT DEFAULT 0,
  `isWhipsaw` BOOLEAN DEFAULT FALSE,
  `whipsawStatus` VARCHAR(50) DEFAULT NULL,
  `whipsawWarning` VARCHAR(255) DEFAULT NULL,
  `trendScore` DECIMAL(8, 2) DEFAULT 0,
  `trendStrength` VARCHAR(30) DEFAULT NULL,

  -- scoreBreakdown (เก็บเป็น JSON)
  `scoreBreakdown` JSON DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pk_trend_analysis_id` (`analysis_id`),
  CONSTRAINT `fk_pk_trend_analysis` 
    FOREIGN KEY (`analysis_id`) REFERENCES `full_analysis_data` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -------------------------------------------------------------
-- 3. ตารางแยก: analysis_smc
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `analysis_smc` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `analysis_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK เชื่อมกับ full_analysis_data.id',

  -- Trends
  `swing_trend` VARCHAR(30) DEFAULT NULL,
  `internal_trend` VARCHAR(30) DEFAULT NULL,

  -- Premium Discount Zone (premium_discount_zone)
  `pd_start_time` BIGINT UNSIGNED DEFAULT NULL,
  `pd_end_time` BIGINT UNSIGNED DEFAULT NULL,
  `pd_premium_top` DECIMAL(18, 6) DEFAULT NULL,
  `pd_premium_bottom` DECIMAL(18, 6) DEFAULT NULL,
  `pd_equilibrium` DECIMAL(18, 6) DEFAULT NULL,
  `pd_discount_top` DECIMAL(18, 6) DEFAULT NULL,
  `pd_discount_bottom` DECIMAL(18, 6) DEFAULT NULL,

  -- ข้อมูล Arrays ใน SMC เก็บเป็น JSON
  `structures` JSON DEFAULT NULL,
  `swing_points` JSON DEFAULT NULL,
  `order_blocks` JSON DEFAULT NULL,
  `fair_value_gaps` JSON DEFAULT NULL,
  `equal_highs_lows` JSON DEFAULT NULL,
  `strong_weak_levels` JSON DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_smc_analysis_id` (`analysis_id`),
  CONSTRAINT `fk_smc_analysis` 
    FOREIGN KEY (`analysis_id`) REFERENCES `full_analysis_data` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
