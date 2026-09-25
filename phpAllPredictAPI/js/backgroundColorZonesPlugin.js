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

                // Skip if zone is not visible
                if (startX === null || endX === null) return;
                if (Math.abs(startX - endX) < 1) return;

                const x1 = Math.round(Math.min(startX, endX) * horizontalPixelRatio);
                const x2 = Math.round(Math.max(startX, endX) * horizontalPixelRatio);
                const width = x2 - x1;

                // Draw background rectangle
                ctx.fillStyle = zone.color || 'rgba(100, 100, 100, 0.2)';
                ctx.fillRect(x1, 0, width, bitmapHeight);

                // Draw label if exists
                if (zone.label) {
                    const fontSize = Math.round(11 * verticalPixelRatio);
                    ctx.fillStyle = zone.labelColor || 'rgba(255, 255, 255, 0.9)';
                    ctx.font = `bold ${fontSize}px Arial, sans-serif`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'top';

                    const labelX = x1 + width / 2;
                    const labelY = Math.round(8 * verticalPixelRatio);

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

    analysisData.forEach((data) => {
        const isChoppy = data.choppyIndicator !== null && data.choppyIndicator >= ciThreshold;

        if (isChoppy) {
            if (!currentZone) {
                currentZone = {
                    startTime: data.candletime,
                    endTime: data.candletime,
                    color: 'rgba(255, 193, 7, 0.2)',
                    label: '⚠️ CHOPPY',
                    labelColor: '#000',
                    labelBackgroundColor: 'rgba(255, 193, 7, 0.9)'
                };
            } else {
                currentZone.endTime = data.candletime;
            }
        } else {
            if (currentZone) {
                zones.push(currentZone);
                currentZone = null;
            }
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
