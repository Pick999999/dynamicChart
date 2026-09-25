<?php
/**
 * save_full_analysis.php
 * PHP API สำหรับบันทึกข้อมูล candle analysis จาก /getFullAnalysisData
 * ลงใน MySQL ตาราง: full_analysis_data, analysis_pk_trend, analysis_smc
 */

ini_set('memory_limit', '1024M');
set_time_limit(300);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

require_once __DIR__ . '/db.php';

try {
    $inputJSON = file_get_contents('php://input');
    $payload = json_decode($inputJSON, true);

    if (!$payload) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
        exit;
    }

    $serverCode = isset($payload['serverCode']) ? trim((string)$payload['serverCode']) : '2';
    $candles = [];

    // รองรับรูปแบบ payload หลากหลาย:
    // 1. { "serverCode": "...", "candles": [ ... ] }
    // 2. { "serverCode": "...", "data": [ ... ] }
    // 3. { "serverCode": "...", "data": { "1HZ75V": [ ... ], "1HZ10V": [ ... ] } }
    // 4. Array [ ... ]
    if (isset($payload['candles']) && is_array($payload['candles'])) {
        $candles = $payload['candles'];
    } elseif (isset($payload['data'])) {
        if (is_array($payload['data'])) {
            if (isset($payload['data'][0])) {
                $candles = $payload['data'];
            } else {
                foreach ($payload['data'] as $k => $v) {
                    if (is_array($v)) {
                        if (isset($v[0])) {
                            $candles = array_merge($candles, $v);
                        } elseif (isset($v['data']) && is_array($v['data'])) {
                            $candles = array_merge($candles, $v['data']);
                        }
                    }
                }
            }
        }
    } elseif (is_array($payload) && isset($payload[0])) {
        $candles = $payload;
    }

    if (empty($candles)) {
        echo json_encode(['success' => true, 'count' => 0, 'message' => 'No candles provided to save']);
        exit;
    }

    $db = getDbConnection();

    // Prepared statements
    $sqlMain = "INSERT INTO `full_analysis_data` (
        `serverCode`, `index_no`, `assetCode`, `candletime`, `candletime_display`,
        `open`, `high`, `low`, `close`, `color`, `next_color`, `pip_size`,
        `ema_short_value`, `ema_short_direction`, `ema_short_turn_type`, `ema_short_slope_value`, `emaslopeThereshold`, `diff`, `ema_short_flat`, `ema_short_pos`,
        `ema_medium_value`, `ema_medium_direction`, `ema_medium_turn_type`, `ema_medium_slope_value`, `ema_medium_flat`, `ema_medium_pos`,
        `ema_long_value`, `ema_long_direction`, `ema_long_turn_type`, `ema_long_slope_value`, `ema_long_flat`, `ema_long_pos`,
        `short_medium_gap_value`, `is_short_medium_gap_occur`, `medium_long_gap_value`, `is_medium_long_gap_occur`, `ema_above`, `ema_long_above`, `ema_convergence_type`, `ema_long_convergence_type`,
        `previous_ema_short_value`, `previous_ema_medium_value`, `previous_ema_long_value`, `macd_12`, `macd_23`, `previous_macd_12`, `previous_macd_23`,
        `choppy_indicator`, `adx_value`, `rsi_value`, `atrValue`, `is_abnormal_candle`, `is_abnormal_atr`, `is_atr`,
        `bb_upper`, `bb_middle`, `bb_lower`, `bb_position`, `bb_bandwidth`, `is_bb_squeeze`,
        `u_wick`, `u_wick_percent`, `body`, `body_percent`, `l_wick`, `l_wick_percent`,
        `ema_cut_position`, `ema_cut_long_type`, `ema_cut_short_long_type`, `ema_cut_all_type`, `candles_since_ema_cut`, `ageCutCandleCode12`, `ageCutCandleCode123`,
        `up_con_medium_ema`, `down_con_medium_ema`, `up_con_long_ema`, `down_con_long_ema`,
        `is_mark`, `status_code`, `status_desc`, `status_desc_0`, `hint_status`, `suggest_color`, `win_status`, `win_con`, `loss_con`,
        `tick_count`, `buy_tick_count`, `sell_tick_count`, `buy_sell_ratio`, `avg_tick_move`, `max_tick_move`, `sum_tick_move`, `volatility_clustering`, `volatility_level`,
        `range_in_range`, `range_top`, `range_bottom`, `range_avg`, `range_state`,
        `is_alternating_pattern`, `alternating_sequence_length`, `is_alternating_trigger`, `is_alternating_spike`
    ) VALUES (
        :serverCode, :index_no, :assetCode, :candletime, :candletime_display,
        :open, :high, :low, :close, :color, :next_color, :pip_size,
        :ema_short_value, :ema_short_direction, :ema_short_turn_type, :ema_short_slope_value, :emaslopeThereshold, :diff, :ema_short_flat, :ema_short_pos,
        :ema_medium_value, :ema_medium_direction, :ema_medium_turn_type, :ema_medium_slope_value, :ema_medium_flat, :ema_medium_pos,
        :ema_long_value, :ema_long_direction, :ema_long_turn_type, :ema_long_slope_value, :ema_long_flat, :ema_long_pos,
        :short_medium_gap_value, :is_short_medium_gap_occur, :medium_long_gap_value, :is_medium_long_gap_occur, :ema_above, :ema_long_above, :ema_convergence_type, :ema_long_convergence_type,
        :previous_ema_short_value, :previous_ema_medium_value, :previous_ema_long_value, :macd_12, :macd_23, :previous_macd_12, :previous_macd_23,
        :choppy_indicator, :adx_value, :rsi_value, :atrValue, :is_abnormal_candle, :is_abnormal_atr, :is_atr,
        :bb_upper, :bb_middle, :bb_lower, :bb_position, :bb_bandwidth, :is_bb_squeeze,
        :u_wick, :u_wick_percent, :body, :body_percent, :l_wick, :l_wick_percent,
        :ema_cut_position, :ema_cut_long_type, :ema_cut_short_long_type, :ema_cut_all_type, :candles_since_ema_cut, :ageCutCandleCode12, :ageCutCandleCode123,
        :up_con_medium_ema, :down_con_medium_ema, :up_con_long_ema, :down_con_long_ema,
        :is_mark, :status_code, :status_desc, :status_desc_0, :hint_status, :suggest_color, :win_status, :win_con, :loss_con,
        :tick_count, :buy_tick_count, :sell_tick_count, :buy_sell_ratio, :avg_tick_move, :max_tick_move, :sum_tick_move, :volatility_clustering, :volatility_level,
        :range_in_range, :range_top, :range_bottom, :range_avg, :range_state,
        :is_alternating_pattern, :alternating_sequence_length, :is_alternating_trigger, :is_alternating_spike
    ) ON DUPLICATE KEY UPDATE
        `id` = LAST_INSERT_ID(`id`),
        `open` = VALUES(`open`),
        `high` = VALUES(`high`),
        `low` = VALUES(`low`),
        `close` = VALUES(`close`),
        `color` = VALUES(`color`),
        `pip_size` = VALUES(`pip_size`),
        `ema_short_value` = VALUES(`ema_short_value`),
        `ema_medium_value` = VALUES(`ema_medium_value`),
        `ema_long_value` = VALUES(`ema_long_value`),
        `macd_12` = VALUES(`macd_12`),
        `macd_23` = VALUES(`macd_23`),
        `rsi_value` = VALUES(`rsi_value`),
        `atrValue` = VALUES(`atrValue`),
        `bb_upper` = VALUES(`bb_upper`),
        `bb_middle` = VALUES(`bb_middle`),
        `bb_lower` = VALUES(`bb_lower`),
        `bb_position` = VALUES(`bb_position`),
        `status_code` = VALUES(`status_code`),
        `win_status` = VALUES(`win_status`)";

    $stmtMain = $db->prepare($sqlMain);

    $sqlPk = "INSERT INTO `analysis_pk_trend` (
        `analysis_id`, `codeNo`, `trend`, `caseCode`, `caseDesc`, `group_name`,
        `description`, `isSpike`, `extremeTrend`, `closePosition`, `bodyRatio`,
        `structure_higher_high`, `structure_lower_low`, `structure_higher_low`, `structure_lower_high`,
        `rangeRatio`, `colorSequence`, `colorSwitches`, `isWhipsaw`, `whipsawStatus`, `whipsawWarning`,
        `trendScore`, `trendStrength`, `scoreBreakdown`
    ) VALUES (
        :analysis_id, :codeNo, :trend, :caseCode, :caseDesc, :group_name,
        :description, :isSpike, :extremeTrend, :closePosition, :bodyRatio,
        :structure_higher_high, :structure_lower_low, :structure_higher_low, :structure_lower_high,
        :rangeRatio, :colorSequence, :colorSwitches, :isWhipsaw, :whipsawStatus, :whipsawWarning,
        :trendScore, :trendStrength, :scoreBreakdown
    ) ON DUPLICATE KEY UPDATE
        `codeNo` = VALUES(`codeNo`),
        `trend` = VALUES(`trend`),
        `caseCode` = VALUES(`caseCode`),
        `caseDesc` = VALUES(`caseDesc`),
        `group_name` = VALUES(`group_name`),
        `description` = VALUES(`description`),
        `extremeTrend` = VALUES(`extremeTrend`),
        `trendScore` = VALUES(`trendScore`),
        `trendStrength` = VALUES(`trendStrength`),
        `scoreBreakdown` = VALUES(`scoreBreakdown`)";

    $stmtPk = $db->prepare($sqlPk);

    $sqlSmc = "INSERT INTO `analysis_smc` (
        `analysis_id`, `swing_trend`, `internal_trend`,
        `pd_start_time`, `pd_end_time`, `pd_premium_top`, `pd_premium_bottom`,
        `pd_equilibrium`, `pd_discount_top`, `pd_discount_bottom`,
        `structures`, `swing_points`, `order_blocks`, `fair_value_gaps`, `equal_highs_lows`, `strong_weak_levels`
    ) VALUES (
        :analysis_id, :swing_trend, :internal_trend,
        :pd_start_time, :pd_end_time, :pd_premium_top, :pd_premium_bottom,
        :pd_equilibrium, :pd_discount_top, :pd_discount_bottom,
        :structures, :swing_points, :order_blocks, :fair_value_gaps, :equal_highs_lows, :strong_weak_levels
    ) ON DUPLICATE KEY UPDATE
        `swing_trend` = VALUES(`swing_trend`),
        `internal_trend` = VALUES(`internal_trend`),
        `pd_equilibrium` = VALUES(`pd_equilibrium`),
        `structures` = VALUES(`structures`),
        `order_blocks` = VALUES(`order_blocks`),
        `fair_value_gaps` = VALUES(`fair_value_gaps`)";

    $stmtSmc = $db->prepare($sqlSmc);

    // Run within batches/transactions
    $batchSize = 250;
    $totalSaved = 0;
    $count = count($candles);

    for ($i = 0; $i < $count; $i += $batchSize) {
        $slice = array_slice($candles, $i, $batchSize);
        $db->beginTransaction();

        try {
            foreach ($slice as $c) {
                if (!isset($c['assetCode']) || !isset($c['candletime'])) {
                    continue;
                }

                $bb = $c['bb_values'] ?? [];
                $tick = $c['tick_volatility'] ?? [];
                $range = $c['range_detector'] ?? [];

                $candletimeDisplay = $c['candletime_display'] ?? null;
                if (!$candletimeDisplay && !empty($c['candletime'])) {
                    $candletimeDisplay = date('Y-m-d H:i:s', intval($c['candletime']));
                }

                $stmtMain->execute([
                    ':serverCode'                 => $serverCode,
                    ':index_no'                   => intval($c['index'] ?? 0),
                    ':assetCode'                  => (string)$c['assetCode'],
                    ':candletime'                 => intval($c['candletime']),
                    ':candletime_display'         => $candletimeDisplay,
                    ':open'                       => $c['open'] ?? 0,
                    ':high'                       => $c['high'] ?? 0,
                    ':low'                        => $c['low'] ?? 0,
                    ':close'                      => $c['close'] ?? 0,
                    ':color'                      => (string)($c['color'] ?? ''),
                    ':next_color'                 => $c['next_color'] ?? null,
                    ':pip_size'                   => $c['pip_size'] ?? null,
                    ':ema_short_value'            => $c['ema_short_value'] ?? null,
                    ':ema_short_direction'        => $c['ema_short_direction'] ?? null,
                    ':ema_short_turn_type'        => $c['ema_short_turn_type'] ?? null,
                    ':ema_short_slope_value'      => $c['ema_short_slope_value'] ?? null,
                    ':emaslopeThereshold'         => $c['emaslopeThereshold'] ?? 0,
                    ':diff'                       => $c['diff'] ?? null,
                    ':ema_short_flat'             => (string)($c['ema_short_flat'] ?? 'n'),
                    ':ema_short_pos'              => $c['ema_short_pos'] ?? null,
                    ':ema_medium_value'           => $c['ema_medium_value'] ?? null,
                    ':ema_medium_direction'       => $c['ema_medium_direction'] ?? null,
                    ':ema_medium_turn_type'       => $c['ema_medium_turn_type'] ?? null,
                    ':ema_medium_slope_value'     => $c['ema_medium_slope_value'] ?? null,
                    ':ema_medium_flat'            => (string)($c['ema_medium_flat'] ?? 'n'),
                    ':ema_medium_pos'             => $c['ema_medium_pos'] ?? null,
                    ':ema_long_value'             => $c['ema_long_value'] ?? null,
                    ':ema_long_direction'         => $c['ema_long_direction'] ?? null,
                    ':ema_long_turn_type'         => $c['ema_long_turn_type'] ?? null,
                    ':ema_long_slope_value'       => $c['ema_long_slope_value'] ?? null,
                    ':ema_long_flat'              => (string)($c['ema_long_flat'] ?? 'n'),
                    ':ema_long_pos'               => $c['ema_long_pos'] ?? null,
                    ':short_medium_gap_value'     => $c['short_medium_gap_value'] ?? null,
                    ':is_short_medium_gap_occur'  => (string)($c['is_short_medium_gap_occur'] ?? 'n'),
                    ':medium_long_gap_value'      => $c['medium_long_gap_value'] ?? null,
                    ':is_medium_long_gap_occur'   => (string)($c['is_medium_long_gap_occur'] ?? 'n'),
                    ':ema_above'                  => $c['ema_above'] ?? null,
                    ':ema_long_above'             => $c['ema_long_above'] ?? null,
                    ':ema_convergence_type'       => $c['ema_convergence_type'] ?? null,
                    ':ema_long_convergence_type'  => $c['ema_long_convergence_type'] ?? null,
                    ':previous_ema_short_value'   => $c['previous_ema_short_value'] ?? null,
                    ':previous_ema_medium_value'  => $c['previous_ema_medium_value'] ?? null,
                    ':previous_ema_long_value'    => $c['previous_ema_long_value'] ?? null,
                    ':macd_12'                    => $c['macd_12'] ?? null,
                    ':macd_23'                    => $c['macd_23'] ?? null,
                    ':previous_macd_12'           => $c['previous_macd_12'] ?? null,
                    ':previous_macd_23'           => $c['previous_macd_23'] ?? null,
                    ':choppy_indicator'           => $c['choppy_indicator'] ?? null,
                    ':adx_value'                  => $c['adx_value'] ?? null,
                    ':rsi_value'                  => $c['rsi_value'] ?? null,
                    ':atrValue'                   => $c['atrValue'] ?? null,
                    ':is_abnormal_candle'         => !empty($c['is_abnormal_candle']) ? 1 : 0,
                    ':is_abnormal_atr'            => !empty($c['is_abnormal_atr']) ? 1 : 0,
                    ':is_atr'                     => !empty($c['is_atr']) ? 1 : 0,
                    ':bb_upper'                   => $bb['upper'] ?? null,
                    ':bb_middle'                  => $bb['middle'] ?? null,
                    ':bb_lower'                   => $bb['lower'] ?? null,
                    ':bb_position'                => $c['bb_position'] ?? null,
                    ':bb_bandwidth'               => $c['bb_bandwidth'] ?? null,
                    ':is_bb_squeeze'              => !empty($c['is_bb_squeeze']) ? 1 : 0,
                    ':u_wick'                     => $c['u_wick'] ?? null,
                    ':u_wick_percent'             => $c['u_wick_percent'] ?? null,
                    ':body'                       => $c['body'] ?? null,
                    ':body_percent'               => $c['body_percent'] ?? null,
                    ':l_wick'                     => $c['l_wick'] ?? null,
                    ':l_wick_percent'             => $c['l_wick_percent'] ?? null,
                    ':ema_cut_position'           => $c['ema_cut_position'] ?? '-',
                    ':ema_cut_long_type'          => $c['ema_cut_long_type'] ?? '-',
                    ':ema_cut_short_long_type'    => $c['ema_cut_short_long_type'] ?? '-',
                    ':ema_cut_all_type'           => $c['ema_cut_all_type'] ?? '-',
                    ':candles_since_ema_cut'      => intval($c['candles_since_ema_cut'] ?? 0),
                    ':ageCutCandleCode12'         => $c['ageCutCandleCode12'] ?? null,
                    ':ageCutCandleCode123'        => $c['ageCutCandleCode123'] ?? null,
                    ':up_con_medium_ema'          => intval($c['up_con_medium_ema'] ?? 0),
                    ':down_con_medium_ema'        => intval($c['down_con_medium_ema'] ?? 0),
                    ':up_con_long_ema'            => intval($c['up_con_long_ema'] ?? 0),
                    ':down_con_long_ema'          => intval($c['down_con_long_ema'] ?? 0),
                    ':is_mark'                    => (string)($c['is_mark'] ?? 'n'),
                    ':status_code'                => (string)($c['status_code'] ?? '0'),
                    ':status_desc'                => $c['status_desc'] ?? '-',
                    ':status_desc_0'              => $c['status_desc_0'] ?? '-',
                    ':hint_status'                => $c['hint_status'] ?? '',
                    ':suggest_color'              => $c['suggest_color'] ?? '',
                    ':win_status'                 => $c['win_status'] ?? '',
                    ':win_con'                    => intval($c['win_con'] ?? 0),
                    ':loss_con'                   => intval($c['loss_con'] ?? 0),
                    ':tick_count'                 => intval($tick['tick_count'] ?? 0),
                    ':buy_tick_count'             => intval($tick['buy_tick_count'] ?? 0),
                    ':sell_tick_count'            => intval($tick['sell_tick_count'] ?? 0),
                    ':buy_sell_ratio'             => $tick['buy_sell_ratio'] ?? 0.5,
                    ':avg_tick_move'              => $tick['avg_tick_move'] ?? 0,
                    ':max_tick_move'              => $tick['max_tick_move'] ?? 0,
                    ':sum_tick_move'              => $tick['sum_tick_move'] ?? 0,
                    ':volatility_clustering'      => $tick['volatility_clustering'] ?? 0,
                    ':volatility_level'           => $tick['volatility_level'] ?? 'Low',
                    ':range_in_range'             => !empty($range['in_range']) ? 1 : 0,
                    ':range_top'                  => $range['range_top'] ?? null,
                    ':range_bottom'               => $range['range_bottom'] ?? null,
                    ':range_avg'                  => $range['range_avg'] ?? null,
                    ':range_state'                => $range['range_state'] ?? null,
                    ':is_alternating_pattern'     => !empty($c['is_alternating_pattern']) ? 1 : 0,
                    ':alternating_sequence_length'=> intval($c['alternating_sequence_length'] ?? 0),
                    ':is_alternating_trigger'     => !empty($c['is_alternating_trigger']) ? 1 : 0,
                    ':is_alternating_spike'       => !empty($c['is_alternating_spike']) ? 1 : 0
                ]);

                $analysisId = $db->lastInsertId();
                if (!$analysisId) {
                    // Fetch existing id if not returned by lastInsertId
                    $fetchStmt = $db->prepare("SELECT id FROM `full_analysis_data` WHERE `serverCode` = ? AND `assetCode` = ? AND `candletime` = ?");
                    $fetchStmt->execute([$serverCode, (string)$c['assetCode'], intval($c['candletime'])]);
                    $analysisId = $fetchStmt->fetchColumn();
                }

                if ($analysisId) {
                    // Save pkTrend
                    if (!empty($c['pkTrend']) && is_array($c['pkTrend'])) {
                        $pk = $c['pkTrend'];
                        $struct = $pk['structure'] ?? [];
                        $stmtPk->execute([
                            ':analysis_id'            => $analysisId,
                            ':codeNo'                 => $pk['codeNo'] ?? null,
                            ':trend'                  => $pk['trend'] ?? null,
                            ':caseCode'               => $pk['caseCode'] ?? null,
                            ':caseDesc'               => $pk['caseDesc'] ?? null,
                            ':group_name'             => $pk['group'] ?? null,
                            ':description'            => $pk['description'] ?? null,
                            ':isSpike'                => !empty($pk['isSpike']) ? 1 : 0,
                            ':extremeTrend'           => $pk['extremeTrend'] ?? null,
                            ':closePosition'          => $pk['closePosition'] ?? null,
                            ':bodyRatio'              => $pk['bodyRatio'] ?? null,
                            ':structure_higher_high'  => !empty($struct['higherHigh']) ? 1 : 0,
                            ':structure_lower_low'    => !empty($struct['lowerLow']) ? 1 : 0,
                            ':structure_higher_low'   => !empty($struct['higherLow']) ? 1 : 0,
                            ':structure_lower_high'   => !empty($struct['lowerHigh']) ? 1 : 0,
                            ':rangeRatio'             => $pk['rangeRatio'] ?? null,
                            ':colorSequence'          => $pk['colorSequence'] ?? null,
                            ':colorSwitches'          => intval($pk['colorSwitches'] ?? 0),
                            ':isWhipsaw'              => !empty($pk['isWhipsaw']) ? 1 : 0,
                            ':whipsawStatus'          => $pk['whipsawStatus'] ?? null,
                            ':whipsawWarning'         => $pk['whipsawWarning'] ?? null,
                            ':trendScore'             => $pk['trendScore'] ?? 0,
                            ':trendStrength'          => $pk['trendStrength'] ?? null,
                            ':scoreBreakdown'         => isset($pk['scoreBreakdown']) ? json_encode($pk['scoreBreakdown'], JSON_UNESCAPED_UNICODE) : null
                        ]);
                    }

                    // Save smc
                    if (!empty($c['smc']) && is_array($c['smc'])) {
                        $smc = $c['smc'];
                        $pd = $smc['premium_discount_zone'] ?? [];
                        $stmtSmc->execute([
                            ':analysis_id'        => $analysisId,
                            ':swing_trend'        => $smc['swing_trend'] ?? null,
                            ':internal_trend'     => $smc['internal_trend'] ?? null,
                            ':pd_start_time'      => $pd['start_time'] ?? null,
                            ':pd_end_time'        => $pd['end_time'] ?? null,
                            ':pd_premium_top'     => $pd['premium_top'] ?? null,
                            ':pd_premium_bottom'  => $pd['premium_bottom'] ?? null,
                            ':pd_equilibrium'     => $pd['equilibrium'] ?? null,
                            ':pd_discount_top'    => $pd['discount_top'] ?? null,
                            ':pd_discount_bottom' => $pd['discount_bottom'] ?? null,
                            ':structures'         => isset($smc['structures']) ? json_encode($smc['structures'], JSON_UNESCAPED_UNICODE) : null,
                            ':swing_points'       => isset($smc['swing_points']) ? json_encode($smc['swing_points'], JSON_UNESCAPED_UNICODE) : null,
                            ':order_blocks'       => isset($smc['order_blocks']) ? json_encode($smc['order_blocks'], JSON_UNESCAPED_UNICODE) : null,
                            ':fair_value_gaps'    => isset($smc['fair_value_gaps']) ? json_encode($smc['fair_value_gaps'], JSON_UNESCAPED_UNICODE) : null,
                            ':equal_highs_lows'   => isset($smc['equal_highs_lows']) ? json_encode($smc['equal_highs_lows'], JSON_UNESCAPED_UNICODE) : null,
                            ':strong_weak_levels' => isset($smc['strong_weak_levels']) ? json_encode($smc['strong_weak_levels'], JSON_UNESCAPED_UNICODE) : null
                        ]);
                    }
                }

                $totalSaved++;
            }

            $db->commit();
        } catch (Exception $sliceEx) {
            $db->rollBack();
            throw $sliceEx;
        }
    }

    echo json_encode([
        'success'    => true,
        'serverCode' => $serverCode,
        'count'      => $totalSaved,
        'message'    => "Successfully saved {$totalSaved} candles into full_analysis_data, analysis_pk_trend, analysis_smc"
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
