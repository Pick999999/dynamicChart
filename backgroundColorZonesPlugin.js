/**
 * Background Color Zones Plugin for Lightweight Charts v4.x
 * 
 * This plugin allows you to add custom background color zones to your charts.
 * It uses the Primitives API introduced in v4.1.0
 * 
 * Usage:
 *   const bgZones = new BackgroundColorZonesPlugin([
 *     { startTime: 1706500000, endTime: 1706503600, color: 'rgba(0, 255, 0, 0.2)', label: 'Bullish' },
 *     { startTime: 1706503600, endTime: 1706507200, color: 'rgba(255, 0, 0, 0.2)', label: 'Bearish' }
 *   ]);
 *   series.attachPrimitive(bgZones);
 */

class BackgroundColorZonesPlugin {
    constructor(zones = []) {
        this._zones = zones;
        this._series = null;
        this._chart = null;
        this._requestUpdate = null;
        this._paneViews = [];
    }

    // Required by Series Primitive interface
    attached(param) {
        this._series = param.series;
        this._chart = param.chart;
        this._requestUpdate = param.requestUpdate;
        this._paneViews = [new BackgroundZonesPaneView(this)];
    }

    detached() {
        this._series = null;
        this._chart = null;
        this._requestUpdate = null;
        this._paneViews = [];
    }

    // Required by Series Primitive interface - update when visible time range changes
    updateAllViews() {
        this._paneViews.forEach(pv => pv.update());
    }

    // Series Primitive View - returns pane views
    paneViews() {
        return this._paneViews;
    }

    // Getters for internal use by pane view
    chart() {
        return this._chart;
    }

    series() {
        return this._series;
    }

    zones() {
        return this._zones;
    }

    // Zone management methods
    setZones(zones) {
        this._zones = zones;
        if (this._requestUpdate) {
            this._requestUpdate();
        }
    }

    addZone(zone) {
        this._zones.push(zone);
        if (this._requestUpdate) {
            this._requestUpdate();
        }
    }

    removeZone(index) {
        if (index >= 0 && index < this._zones.length) {
            this._zones.splice(index, 1);
            if (this._requestUpdate) {
                this._requestUpdate();
            }
        }
    }

    clearZones() {
        this._zones = [];
        if (this._requestUpdate) {
            this._requestUpdate();
        }
    }

    getZones() {
        return [...this._zones];
    }
}

/**
 * Pane View for background color zones
 */
class BackgroundZonesPaneView {
    constructor(source) {
        this._source = source;
        this._renderer = new BackgroundZonesRenderer(source);
    }

    update() {
        // Called when chart data changes
    }

    renderer() {
        return this._renderer;
    }

    zOrder() {
        return 'bottom';
    }
}

/**
 * Renderer for background color zones
 */
class BackgroundZonesRenderer {
    constructor(source) {
        this._source = source;
    }

    draw(target, isHovered) {
        const chart = this._source.chart();
        const zones = this._source.zones();

        if (!chart || !zones || zones.length === 0) return;

        target.useBitmapCoordinateSpace(scope => {
            const ctx = scope.context;
            const timeScale = chart.timeScale();
            const horizontalPixelRatio = scope.horizontalPixelRatio;
            const verticalPixelRatio = scope.verticalPixelRatio;
            const bitmapHeight = scope.bitmapSize.height;

            zones.forEach(zone => {
                const startX = timeScale.timeToCoordinate(zone.startTime);
                const endX = timeScale.timeToCoordinate(zone.endTime);

                // Debug logging for time shading zones
                if (zone.label && zone.label.includes('PUT') || zone.label && zone.label.includes('CALL')) {
                    console.log(`[Renderer] zone label=${zone.label}, startTime=${zone.startTime}, endTime=${zone.endTime}, startX=${startX}, endX=${endX}, bitmapHeight=${bitmapHeight}`);
                }

                // Skip if zone is not visible
                if (startX === null || endX === null) return;

                let x1 = Math.round(Math.min(startX, endX) * horizontalPixelRatio);
                let x2 = Math.round(Math.max(startX, endX) * horizontalPixelRatio);
                let width = x2 - x1;

                // Ensure minimum visible width of 3 pixels
                if (width < 3) {
                    const midX = (x1 + x2) / 2;
                    x1 = Math.round(midX - 1.5);
                    x2 = Math.round(midX + 1.5);
                    width = x2 - x1;
                }

                let y1 = 0;
                let y2 = bitmapHeight;
                let hasPriceRange = false;

                const series = this._source.series();
                if (zone.startPrice !== undefined && zone.endPrice !== undefined && series) {
                    const priceY1 = series.priceToCoordinate(zone.startPrice);
                    const priceY2 = series.priceToCoordinate(zone.endPrice);
                    if (priceY1 !== null && priceY2 !== null) {
                        y1 = Math.round(Math.min(priceY1, priceY2) * verticalPixelRatio);
                        y2 = Math.round(Math.max(priceY1, priceY2) * verticalPixelRatio);
                        hasPriceRange = true;
                    }
                }

                const height = y2 - y1;

                // Draw background rectangle
                ctx.fillStyle = zone.color || 'rgba(100, 100, 100, 0.2)';
                ctx.fillRect(x1, y1, width, height);

                // Draw borders if bounded by price range
                if (hasPriceRange && zone.borderColor) {
                    ctx.strokeStyle = zone.borderColor;
                    ctx.lineWidth = Math.round(1.5 * verticalPixelRatio);
                    ctx.strokeRect(x1, y1, width, height);
                }

                // Draw label if exists
                if (zone.label) {
                    const fontSize = Math.round(11 * verticalPixelRatio);
                    ctx.fillStyle = zone.labelColor || 'rgba(255, 255, 255, 0.9)';
                    ctx.font = `bold ${fontSize}px Arial, sans-serif`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'top';

                    const labelX = x1 + width / 2;
                    let labelY = Math.round(8 * verticalPixelRatio);
                    if (hasPriceRange) {
                        // Draw label inside the box, near the top edge of the box
                        labelY = y1 + Math.round(4 * verticalPixelRatio);
                    }

                    // Draw label background
                    if (zone.labelBackgroundColor) {
                        const metrics = ctx.measureText(zone.label);
                        const padding = 4 * horizontalPixelRatio;
                        const bgHeight = fontSize + padding * 2;
                        ctx.fillStyle = zone.labelBackgroundColor;
                        ctx.fillRect(
                            labelX - metrics.width / 2 - padding,
                            labelY - padding / 2,
                            metrics.width + padding * 2,
                            bgHeight
                        );
                        ctx.fillStyle = zone.labelColor || 'rgba(255, 255, 255, 0.9)';
                    }

                    ctx.fillText(zone.label, labelX, labelY);
                }
            });
        });
    }
}

/**
 * Helper function to create zones based on analysis data
 * This integrates with your existing analysisArray
 * 
 * @param {Array} analysisData - Your analysisArray from choppiness-indexV3.js
 * @param {Object} options - Configuration options
 * @returns {Array} Array of zone objects
 */
function createZonesFromAnalysis(analysisData, options = {}) {
    const zones = [];
    const {
        trendUpColor = 'rgba(56, 239, 125, 0.15)',      // Green for uptrend
        trendDownColor = 'rgba(244, 92, 67, 0.15)',    // Red for downtrend
        sidewaysColor = 'rgba(102, 126, 234, 0.15)',   // Purple for sideways
        showLabels = true,
        ciThreshold = 61.8,  // CI above this = choppy/sideways
        adxThreshold = 25    // ADX below this = weak trend
    } = options;

    let currentZone = null;

    analysisData.forEach((data, index) => {
        // Determine zone type based on indicators
        let zoneType = 'sideways';
        let zoneColor = sidewaysColor;

        if (data.choppyIndicator !== null && data.adxValue !== null) {
            const ci = data.choppyIndicator;
            const adx = data.adxValue;

            if (ci < 38.2 && adx > adxThreshold) {
                // Strong trend
                if (data.emaMediumDirection === 'Up') {
                    zoneType = 'uptrend';
                    zoneColor = trendUpColor;
                } else if (data.emaMediumDirection === 'Down') {
                    zoneType = 'downtrend';
                    zoneColor = trendDownColor;
                }
            } else if (ci > ciThreshold) {
                zoneType = 'sideways';
                zoneColor = sidewaysColor;
            }
        }

        // Start new zone or extend existing one
        if (!currentZone || currentZone.type !== zoneType) {
            // Save previous zone
            if (currentZone) {
                zones.push(currentZone);
            }

            // Start new zone
            currentZone = {
                startTime: data.candletime,
                endTime: data.candletime,
                color: zoneColor,
                type: zoneType,
                label: showLabels ? zoneType.toUpperCase() : undefined
            };
        } else {
            // Extend current zone
            currentZone.endTime = data.candletime;
        }
    });

    // Add last zone
    if (currentZone) {
        zones.push(currentZone);
    }

    return zones;
}

/**
 * Helper function to create zones for EMA crossover signals
 * 
 * @param {Array} analysisData - Your analysisArray
 * @returns {Array} Array of zone objects marking crossover areas
 */
function createCrossoverZones(analysisData) {
    const zones = [];

    analysisData.forEach((data, index) => {
        if (data.emaCutLongType) {
            // Create a zone around crossover points
            const prevIndex = Math.max(0, index - 5);
            const nextIndex = Math.min(analysisData.length - 1, index + 5);

            zones.push({
                startTime: analysisData[prevIndex].candletime,
                endTime: analysisData[nextIndex].candletime,
                color: data.emaCutLongType === 'UpTrend'
                    ? 'rgba(0, 255, 0, 0.25)'
                    : 'rgba(255, 0, 0, 0.25)',
                label: data.emaCutLongType === 'UpTrend' ? '🔼 Golden' : '🔽 Death',
                labelColor: '#fff',
                labelBackgroundColor: data.emaCutLongType === 'UpTrend'
                    ? 'rgba(17, 153, 142, 0.9)'
                    : 'rgba(235, 51, 73, 0.9)'
            });
        }
    });

    return zones;
}

/**
 * Helper function to create choppy/sideways zones
 * 
 * @param {Array} analysisData - Your analysisArray 
 * @param {number} ciThreshold - CI value above which market is considered choppy
 * @returns {Array} Array of zone objects
 */
function createChoppyZones(analysisData, ciThreshold = 61.8) {
    const zones = [];
    let currentZone = null;

    // Zone color definitions
    const ZONE_COLORS = {
        trending: {
            color: 'rgba(38, 166, 154, 0.18)',        // Green
            label: '🟢 TREND',
            labelColor: '#26a69a',
            labelBackgroundColor: 'rgba(38, 166, 154, 0.85)'
        },
        choppy: {
            color: 'rgba(244, 92, 67, 0.18)',          // Red
            label: '🔴 CHOPPY',
            labelColor: '#fff',
            labelBackgroundColor: 'rgba(244, 92, 67, 0.85)'
        },
        neutral: {
            color: 'rgba(255, 193, 7, 0.15)',           // Yellow
            label: '🟡 NEUTRAL',
            labelColor: '#000',
            labelBackgroundColor: 'rgba(255, 193, 7, 0.85)'
        }
    };

    analysisData.forEach((data) => {
        const ci = data.choppyIndicator;
        const adx = data.adxValue !== null ? parseFloat(data.adxValue) : null;

        // Determine zone type
        let zoneType = 'neutral';
        if (ci !== null && adx !== null) {
            if (ci < 38.2 && adx > 25) {
                zoneType = 'trending';
            } else if (ci > ciThreshold || adx < 20) {
                zoneType = 'choppy';
            } else {
                zoneType = 'neutral';
            }
        }

        if (!currentZone || currentZone.type !== zoneType) {
            // Save previous zone
            if (currentZone) {
                zones.push(currentZone);
            }
            // Start new zone
            const zoneStyle = ZONE_COLORS[zoneType];
            currentZone = {
                startTime: data.candletime,
                endTime: data.candletime,
                color: zoneStyle.color,
                type: zoneType,
                label: zoneStyle.label,
                labelColor: zoneStyle.labelColor,
                labelBackgroundColor: zoneStyle.labelBackgroundColor
            };
        } else {
            // Extend current zone
            currentZone.endTime = data.candletime;
        }
    });

    if (currentZone) {
        zones.push(currentZone);
    }

    return zones;
}

/**
 * Horizontal Price Zone Plugin for Lightweight Charts
 * Allows shading horizontal price bands between entrySpot and exitSpot with green/red theme
 */
class HorizontalPriceZonePlugin {
    constructor(zone = null) {
        this._zone = zone;
        this._series = null;
        this._chart = null;
        this._requestUpdate = null;
        this._paneViews = [new HorizontalPriceZonePaneView(this)];
    }

    attached(param) {
        this._series = param.series;
        this._chart = param.chart;
        this._requestUpdate = param.requestUpdate;
        this._paneViews = [new HorizontalPriceZonePaneView(this)];
    }

    detached() {
        this._series = null;
        this._chart = null;
        this._requestUpdate = null;
        this._paneViews = [];
    }

    updateAllViews() {
        this._paneViews.forEach(pv => pv.update());
    }

    paneViews() {
        return this._paneViews;
    }

    chart() {
        return this._chart;
    }

    series() {
        return this._series;
    }

    getZone() {
        return this._zone;
    }

    setZone(zone) {
        this._zone = zone;
        if (this._requestUpdate) {
            this._requestUpdate();
        }
    }

    clearZone() {
        this._zone = null;
        if (this._requestUpdate) {
            this._requestUpdate();
        }
    }
}

class HorizontalPriceZonePaneView {
    constructor(source) {
        this._source = source;
        this._renderer = new HorizontalPriceZoneRenderer(source);
    }

    update() {}

    renderer() {
        return this._renderer;
    }

    zOrder() {
        return 'bottom';
    }
}

class HorizontalPriceZoneRenderer {
    constructor(source) {
        this._source = source;
    }

    draw(target) {
        const series = this._source.series();
        const chart = this._source.chart();
        const zone = this._source.getZone();

        if (!series || !chart || !zone) return;
        if (zone.entrySpot === null || zone.exitSpot === null || zone.entrySpot === undefined || zone.exitSpot === undefined) return;

        target.useBitmapCoordinateSpace(scope => {
            const ctx = scope.context;
            const hRatio = scope.horizontalPixelRatio;
            const vRatio = scope.verticalPixelRatio;
            const width = scope.bitmapSize.width;
            const height = scope.bitmapSize.height;

            const y1Logical = series.priceToCoordinate(zone.entrySpot);
            const y2Logical = series.priceToCoordinate(zone.exitSpot);

            if (y1Logical === null || y2Logical === null) return;

            const yEntry = y1Logical * vRatio;
            const yExit = y2Logical * vRatio;

            const minY = Math.min(yEntry, yExit);
            const maxY = Math.max(yEntry, yExit);
            const bandHeight = Math.max(maxY - minY, 2 * vRatio);

            const isWin = String(zone.winStatus || '').trim().toLowerCase() === 'win';

            ctx.save();

            // 1. Draw horizontal shaded background
            const fillGrad = ctx.createLinearGradient(0, minY, 0, maxY);
            if (isWin) {
                fillGrad.addColorStop(0, 'rgba(22, 199, 132, 0.25)');
                fillGrad.addColorStop(0.5, 'rgba(22, 199, 132, 0.18)');
                fillGrad.addColorStop(1, 'rgba(22, 199, 132, 0.25)');
            } else {
                fillGrad.addColorStop(0, 'rgba(234, 57, 67, 0.25)');
                fillGrad.addColorStop(0.5, 'rgba(234, 57, 67, 0.18)');
                fillGrad.addColorStop(1, 'rgba(234, 57, 67, 0.25)');
            }

            ctx.fillStyle = fillGrad;
            ctx.fillRect(0, minY, width, bandHeight);

            // 2. Draw Entry Line (Solid/Dashed)
            ctx.strokeStyle = '#38bdf8';
            ctx.lineWidth = 1.5 * vRatio;
            ctx.setLineDash([5 * hRatio, 3 * hRatio]);
            ctx.beginPath();
            ctx.moveTo(0, yEntry);
            ctx.lineTo(width, yEntry);
            ctx.stroke();

            // 3. Draw Exit Line
            ctx.strokeStyle = isWin ? '#16c784' : '#ea3943';
            ctx.lineWidth = 1.5 * vRatio;
            ctx.setLineDash([7 * hRatio, 4 * hRatio]);
            ctx.beginPath();
            ctx.moveTo(0, yExit);
            ctx.lineTo(width, yExit);
            ctx.stroke();

            // 4. Badges and Text Labels
            ctx.setLineDash([]);
            const d = zone.decimals || 2;
            const diff = zone.diffSpot !== undefined ? zone.diffSpot : (zone.exitSpot - zone.entrySpot);
            const diffText = (diff >= 0 ? '+' : '') + diff.toFixed(d);

            const fontSize = Math.max(11 * vRatio, 11);
            ctx.font = `bold ${fontSize}px "JetBrains Mono", monospace`;

            // Extra info: thisAction and codeStrategy
            const actionPart = zone.thisAction || zone.action || '';
            const stratPart = zone.codeStrategy || zone.strategy || '';
            let extraInfo = '';
            if (actionPart && stratPart) {
                extraInfo = ` | ${actionPart} | ${stratPart}`;
            } else if (actionPart) {
                extraInfo = ` | ${actionPart}`;
            } else if (stratPart) {
                extraInfo = ` | ${stratPart}`;
            }

            // Entry Badge (Positioned BELOW the cyan entry line)
            const entryText = ` 🔵 Entry: ${zone.entrySpot.toFixed(d)}${extraInfo} `;
            const entryMetrics = ctx.measureText(entryText);
            const badgeH = 22 * vRatio;
            const badgeW1 = entryMetrics.width + 12 * hRatio;
            const entryBadgeY = yEntry + 5 * vRatio; // อยู่ใต้เส้น entry spot สีฟ้า

            ctx.fillStyle = 'rgba(10, 15, 29, 0.95)';
            ctx.strokeStyle = '#38bdf8';
            ctx.lineWidth = 1.5 * vRatio;
            ctx.beginPath();
            if (typeof ctx.roundRect === 'function') {
                ctx.roundRect(16 * hRatio, entryBadgeY, badgeW1, badgeH, 4 * vRatio);
            } else {
                ctx.rect(16 * hRatio, entryBadgeY, badgeW1, badgeH);
            }
            ctx.fill();
            ctx.stroke();

            ctx.fillStyle = '#38bdf8';
            ctx.textBaseline = 'middle';
            ctx.fillText(entryText, 20 * hRatio, entryBadgeY + badgeH / 2);

            // Exit Badge (Left) - Position above if exit is above entry, else below
            const exitIcon = isWin ? '🟢' : '🔴';
            const exitText = ` ${exitIcon} Exit: ${zone.exitSpot.toFixed(d)} (${diffText}) `;
            const exitMetrics = ctx.measureText(exitText);
            const badgeW2 = exitMetrics.width + 12 * hRatio;
            let exitBadgeY = (yExit < yEntry) ? (yExit - badgeH - 5 * vRatio) : (yExit + 5 * vRatio);
            if (exitBadgeY < 5 * vRatio) exitBadgeY = yExit + 5 * vRatio;

            ctx.fillStyle = 'rgba(10, 15, 29, 0.95)';
            ctx.strokeStyle = isWin ? '#16c784' : '#ea3943';
            ctx.lineWidth = 1.5 * vRatio;
            ctx.beginPath();
            if (typeof ctx.roundRect === 'function') {
                ctx.roundRect(16 * hRatio, exitBadgeY, badgeW2, badgeH, 4 * vRatio);
            } else {
                ctx.rect(16 * hRatio, exitBadgeY, badgeW2, badgeH);
            }
            ctx.fill();
            ctx.stroke();

            ctx.fillStyle = isWin ? '#16c784' : '#ea3943';
            ctx.fillText(exitText, 20 * hRatio, exitBadgeY + badgeH / 2);

            // Summary Badge (Right top of zone)
            const profitStr = (zone.profit !== null && zone.profit !== undefined) ? ((zone.profit >= 0 ? '+$' : '-$') + Math.abs(zone.profit).toFixed(2)) : '';
            const actionStr = actionPart ? ` [${actionPart}]` : '';
            const stratStr = stratPart ? ` [${stratPart}]` : '';
            const summaryText = ` ${isWin ? '✓ WIN' : '✗ LOSS'} ${actionStr}${stratStr} ${profitStr} `;
            
            ctx.font = `bold ${Math.max(10.5 * vRatio, 10)}px "JetBrains Mono", monospace`;
            const sumMetrics = ctx.measureText(summaryText);
            const sumW = sumMetrics.width + 14 * hRatio;
            const sumH = 20 * vRatio;
            const sumX = width - sumW - 75 * hRatio;
            const sumY = Math.min(Math.max(minY + 3 * vRatio, 10 * vRatio), height - sumH - 10 * vRatio);

            ctx.fillStyle = isWin ? 'rgba(22, 199, 132, 0.92)' : 'rgba(234, 57, 67, 0.92)';
            ctx.beginPath();
            if (typeof ctx.roundRect === 'function') {
                ctx.roundRect(sumX, sumY, sumW, sumH, 3 * vRatio);
            } else {
                ctx.rect(sumX, sumY, sumW, sumH);
            }
            ctx.fill();

            ctx.fillStyle = '#ffffff';
            ctx.fillText(summaryText, sumX + 6 * hRatio, sumY + sumH / 2);

            ctx.restore();
        });
    }
}

// Export for use as ES module (if needed)
if (typeof window !== 'undefined') {
    window.BackgroundColorZonesPlugin = BackgroundColorZonesPlugin;
    window.HorizontalPriceZonePlugin = HorizontalPriceZonePlugin;
    window.createZonesFromAnalysis = createZonesFromAnalysis;
    window.createCrossoverZones = createCrossoverZones;
    window.createChoppyZones = createChoppyZones;
}
