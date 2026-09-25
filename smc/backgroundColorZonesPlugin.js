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
        const series = this._source.series();
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

                console.log('[BackgroundZonesRenderer] zone:', zone,
                            '| startTime:', zone.startTime, 'type:', typeof zone.startTime,
                            '| endTime:', zone.endTime, 'type:', typeof zone.endTime,
                            '| startX:', startX, '| endX:', endX);

                // Skip if zone is not visible
                if (startX === null || endX === null) return;
                if (Math.abs(startX - endX) < 0.2) return;

                const x1 = Math.round(Math.min(startX, endX) * horizontalPixelRatio);
                const x2 = Math.round(Math.max(startX, endX) * horizontalPixelRatio);
                const width = Math.max(1, x2 - x1);

                let y1 = 0;
                let height = bitmapHeight;

                // Calculate Y coordinates from topPrice & bottomPrice if provided
                if (series && zone.topPrice !== undefined && zone.bottomPrice !== undefined) {
                    const startY = series.priceToCoordinate(zone.topPrice);
                    const endY = series.priceToCoordinate(zone.bottomPrice);

                    if (startY !== null && endY !== null) {
                        const topY = Math.round(Math.min(startY, endY) * verticalPixelRatio);
                        const botY = Math.round(Math.max(startY, endY) * verticalPixelRatio);
                        y1 = topY;
                        height = Math.max(2, botY - topY);
                    }
                }

                // Draw horizontal background rectangle box
                ctx.fillStyle = zone.color || 'rgba(100, 100, 100, 0.2)';
                ctx.fillRect(x1, y1, width, height);

                // Draw border outline if specified
                if (zone.borderColor) {
                    ctx.strokeStyle = zone.borderColor;
                    ctx.lineWidth = Math.max(1, Math.round((zone.borderWidth || 1) * horizontalPixelRatio));
                    ctx.strokeRect(x1, y1, width, height);
                }

                // Draw label if exists
                if (zone.label) {
                    const fontSize = Math.round(10 * verticalPixelRatio);
                    ctx.font = `bold ${fontSize}px Arial, sans-serif`;
                    ctx.textAlign = 'left';
                    ctx.textBaseline = 'top';

                    const labelX = x1 + Math.round(4 * horizontalPixelRatio);
                    const labelY = y1 + Math.round(4 * verticalPixelRatio);

                    // Draw label background badge
                    if (zone.labelBackgroundColor) {
                        const metrics = ctx.measureText(zone.label);
                        const padding = 3 * horizontalPixelRatio;
                        const bgHeight = fontSize + padding * 2;
                        ctx.fillStyle = zone.labelBackgroundColor;
                        ctx.fillRect(
                            labelX - padding,
                            labelY - padding / 2,
                            metrics.width + padding * 2,
                            bgHeight
                        );
                        ctx.fillStyle = zone.labelColor || 'rgba(255, 255, 255, 0.9)';
                    } else {
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

// Export for use as ES module (if needed)
if (typeof window !== 'undefined') {
    window.BackgroundColorZonesPlugin = BackgroundColorZonesPlugin;
    window.createZonesFromAnalysis = createZonesFromAnalysis;
    window.createCrossoverZones = createCrossoverZones;
    window.createChoppyZones = createChoppyZones;
}
