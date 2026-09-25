/**
 * SMC Chart Renderer for LightweightCharts 4.2+
 * Renders SMC indicators (structures, order blocks, FVGs, etc.) on a LightweightCharts chart
 */

/**
 * @typedef {Object} SMCRendererConfig
 * @property {Object} colors
 * @property {string} [colors.bullishStructure='#089981']
 * @property {string} [colors.bearishStructure='#F23645']
 * @property {string} [colors.bullishOB='rgba(49, 121, 245, 0.2)']
 * @property {string} [colors.bearishOB='rgba(247, 124, 128, 0.2)']
 * @property {string} [colors.bullishFVG='rgba(0, 255, 104, 0.3)']
 * @property {string} [colors.bearishFVG='rgba(255, 0, 8, 0.3)']
 * @property {string} [colors.premiumZone='rgba(242, 54, 69, 0.2)']
 * @property {string} [colors.discountZone='rgba(8, 153, 129, 0.2)']
 * @property {string} [colors.equilibrium='rgba(135, 139, 148, 0.2)']
 * @property {string} [colors.strongLevel='#2962FF']
 * @property {string} [colors.weakLevel='#787B86']
 */

class SMCChartRenderer {
    /**
     * @param {Object} chart - LightweightCharts chart instance
     * @param {Object} candlestickSeries - Main candlestick series
     * @param {SMCRendererConfig} config
     */
    constructor(chart, candlestickSeries, config = {}) {
        this.chart = chart;
        this.series = candlestickSeries;

        // Default colors
        this.colors = {
            bullishStructure: config.colors?.bullishStructure || '#089981',
            bearishStructure: config.colors?.bearishStructure || '#F23645',
            bullishOB: config.colors?.bullishOB || 'rgba(49, 121, 245, 0.25)',
            bearishOB: config.colors?.bearishOB || 'rgba(247, 124, 128, 0.25)',
            bullishFVG: config.colors?.bullishFVG || 'rgba(0, 255, 104, 0.22)',
            bearishFVG: config.colors?.bearishFVG || 'rgba(255, 0, 8, 0.22)',
            premiumZone: config.colors?.premiumZone || 'rgba(242, 54, 69, 0.2)',
            discountZone: config.colors?.discountZone || 'rgba(8, 153, 129, 0.2)',
            equilibrium: config.colors?.equilibrium || 'rgba(135, 139, 148, 0.2)',
            strongLevel: config.colors?.strongLevel || '#2962FF',
            weakLevel: config.colors?.weakLevel || '#787B86'
        };

        // Store primitives for cleanup
        this.primitives = [];
        this.markers = [];
        this.markersPrimitive = null;
        this.lineSeries = [];
        this.priceLines = [];
        this.bgZonesPlugin = null;
    }

    /**
     * Safely update markers for Lightweight Charts v4 & v5+
     */
    _updateMarkers() {
        const sorted = this.markers.slice().sort((a, b) => a.time - b.time);
        if (typeof this.series.setMarkers === 'function') {
            this.series.setMarkers(sorted);
        } else if (typeof LightweightCharts !== 'undefined' && typeof LightweightCharts.createSeriesMarkers === 'function') {
            if (this.markersPrimitive && typeof this.markersPrimitive.setMarkers === 'function') {
                this.markersPrimitive.setMarkers(sorted);
            } else {
                this.markersPrimitive = LightweightCharts.createSeriesMarkers(this.series, sorted);
            }
        }
    }

    /**
     * Clear all rendered elements
     */
    clear() {
        // Remove markers
        this.markers = [];
        this._updateMarkers();

        // Remove primitives
        for (const primitive of this.primitives) {
            try {
                this.series.detachPrimitive(primitive);
            } catch (e) {
                // Primitive may already be detached
            }
        }
        this.primitives = [];

        // Remove additional line series
        for (const lineSeries of this.lineSeries) {
            try {
                this.chart.removeSeries(lineSeries);
            } catch (e) {
                // Series may already be removed
            }
        }
        this.lineSeries = [];
        // Remove price lines fallback
        for (const pl of this.priceLines) {
            try { this.series.removePriceLine(pl); } catch (e) { }
        }
        this.priceLines = [];
        // Detach background zones plugin if attached
        if (this.bgZonesPlugin) {
            try { this.series.detachPrimitive(this.bgZonesPlugin); } catch (e) { }
            this.bgZonesPlugin = null;
        }
    }

    /**
     * Render swing point markers (HH, HL, LH, LL)
     * @param {SwingPoint[]} swingPoints
     */
    renderSwingPoints(swingPoints) {
        const markers = swingPoints.map(sp => ({
            time: sp.time,
            position: sp.swing === 'high' ? 'aboveBar' : 'belowBar',
            color: sp.swing === 'high' ? this.colors.bearishStructure : this.colors.bullishStructure,
            shape: sp.swing === 'high' ? 'arrowDown' : 'arrowUp',
            text: sp.type,
            size: 1
        }));

        this.markers = [...this.markers, ...markers];
        this._updateMarkers();
    }

    /**
     * Render structure labels (CHoCH, BOS)
     * @param {StructurePoint[]} structures
     */
    renderStructures(structures) {
        const markers = structures.map(s => ({
            time: s.time,
            position: s.direction === 'bullish' ? 'aboveBar' : 'belowBar',
            color: s.direction === 'bullish' ? this.colors.bullishStructure : this.colors.bearishStructure,
            shape: 'circle',
            text: s.type,
            size: 0.5
        }));

        this.markers = [...this.markers, ...markers];
        this._updateMarkers();
    }

    /**
     * Create a box primitive for order blocks and FVGs
     * Uses LightweightCharts v4.2 Primitives API (attached/detached lifecycle + useBitmapCoordinateSpace)
     * @param {number} time1 - Start time
     * @param {number} time2 - End time (or null for extending)
     * @param {number} price1 - Top price
     * @param {number} price2 - Bottom price
     * @param {string} fillColor - Fill color
     * @param {string} [borderColor] - Border color (optional)
     * @returns {Object} Box primitive
     */
    createBoxPrimitive(time1, time2, price1, price2, fillColor, borderColor = null) {
        const boxData = { time1, time2, price1, price2, fillColor, borderColor };

        const boxPrimitive = {
            _chart: null,
            _series: null,
            _requestUpdate: null,

            // LC v4.2 lifecycle
            attached(param) {
                this._chart = param.chart;
                this._series = param.series;
                this._requestUpdate = param.requestUpdate;
            },

            detached() {
                this._chart = null;
                this._series = null;
                this._requestUpdate = null;
            },

            updateAllViews() { },

            paneViews() {
                return [this];
            },

            // PaneView interface
            zOrder() {
                return 'bottom';
            },

            renderer() {
                const chart = this._chart;
                const series = this._series;
                if (!chart || !series) return null;

                return {
                    draw(target) {
                        target.useBitmapCoordinateSpace(scope => {
                            const ctx = scope.context;
                            const timeScale = chart.timeScale();
                            const hRatio = scope.horizontalPixelRatio;
                            const vRatio = scope.verticalPixelRatio;

                            // Get media coordinates first, then convert to bitmap
                            const x1Media = timeScale.timeToCoordinate(boxData.time1);
                            if (x1Media === null) return;

                            let x2Media;
                            if (boxData.time2) {
                                x2Media = timeScale.timeToCoordinate(boxData.time2);
                                if (x2Media === null) {
                                    // time2 is off-screen right → extend to chart edge
                                    x2Media = scope.mediaSize.width;
                                }
                            } else {
                                x2Media = scope.mediaSize.width;
                            }

                            const y1Media = series.priceToCoordinate(boxData.price1);
                            const y2Media = series.priceToCoordinate(boxData.price2);
                            if (y1Media === null || y2Media === null) return;

                            // Convert media coords to bitmap coords
                            const bx1 = Math.round(x1Media * hRatio);
                            const bx2 = Math.round(x2Media * hRatio);
                            const by1 = Math.round(Math.min(y1Media, y2Media) * vRatio);
                            const by2 = Math.round(Math.max(y1Media, y2Media) * vRatio);
                            const bw = bx2 - bx1;
                            const bh = by2 - by1;

                            if (bw <= 0 || bh <= 0) return;

                            // Draw filled rectangle
                            ctx.fillStyle = boxData.fillColor;
                            ctx.fillRect(bx1, by1, bw, bh);

                            // Draw border if specified
                            if (boxData.borderColor) {
                                ctx.strokeStyle = boxData.borderColor;
                                ctx.lineWidth = Math.max(1, Math.round(1 * hRatio));
                                ctx.strokeRect(bx1, by1, bw, bh);
                            }
                        });
                    }
                };
            }
        };

        return boxPrimitive;
    }

    /**
     * Render order blocks
     * @param {OrderBlock[]} orderBlocks
     * @param {number} [currentTime] - Current time to extend non-mitigated blocks
     */
    renderOrderBlocks(orderBlocks, currentTime = null) {
        for (const ob of orderBlocks) {
            const endTime = ob.mitigated ? ob.mitigatedTime : currentTime;
            const color = ob.bias === 'bullish' ? this.colors.bullishOB : this.colors.bearishOB;

            const primitive = this.createBoxPrimitive(
                ob.time,
                endTime,
                ob.high,
                ob.low,
                color
            );

            try {
                this.series.attachPrimitive(primitive);
                this.primitives.push(primitive);
            } catch (e) {
                console.warn('Could not attach order block primitive:', e);
                // Fallback: draw horizontal top/bottom lines for visibility
                try {
                    const fallbackEnd = endTime || (ob.time + 3600);
                    const color = ob.bias === 'bullish' ? this.colors.bullishOB : this.colors.bearishOB;

                    const topSeries = this.chart.addLineSeries({ color: color, lineWidth: 6, priceLineVisible: false, lastValueVisible: false });
                    topSeries.setData([
                        { time: ob.time, value: ob.high },
                        { time: fallbackEnd, value: ob.high }
                    ]);

                    const bottomSeries = this.chart.addLineSeries({ color: color, lineWidth: 6, priceLineVisible: false, lastValueVisible: false });
                    bottomSeries.setData([
                        { time: ob.time, value: ob.low },
                        { time: fallbackEnd, value: ob.low }
                    ]);

                    this.lineSeries.push(topSeries, bottomSeries);
                    // Also create priceLine fallbacks for guaranteed visibility
                    try {
                        const topPL = this.series.createPriceLine({ price: ob.high, color: color, lineWidth: 2, axisLabelVisible: false, title: 'OB Top' });
                        const bottomPL = this.series.createPriceLine({ price: ob.low, color: color, lineWidth: 2, axisLabelVisible: false, title: 'OB Bottom' });
                        this.priceLines.push(topPL, bottomPL);
                    } catch (e3) { /* ignore */ }
                } catch (e2) {
                    console.warn('Fallback draw for order block failed:', e2);
                }
            }
        }
    }

    /**
     * Render Fair Value Gaps
     * @param {FairValueGap[]} fvgs
     * @param {number} [currentTime] - Current time for extending unfilled gaps
     */
    renderFairValueGaps(fvgs, currentTime = null) {
        for (const fvg of fvgs) {
            const endTime = fvg.filled ? fvg.filledTime : currentTime;
            const color = fvg.bias === 'bullish' ? this.colors.bullishFVG : this.colors.bearishFVG;

            const primitive = this.createBoxPrimitive(
                fvg.time,
                endTime,
                fvg.top,
                fvg.bottom,
                color
            );

            try {
                this.series.attachPrimitive(primitive);
                this.primitives.push(primitive);
            } catch (e) {
                console.warn('Could not attach FVG primitive:', e);
                // Fallback: draw top/bottom lines to indicate gap
                try {
                    const fallbackEnd = endTime || (fvg.time + 3600);
                    const color = fvg.bias === 'bullish' ? this.colors.bullishFVG : this.colors.bearishFVG;

                    const topSeries = this.chart.addLineSeries({ color: color, lineWidth: 4, priceLineVisible: false, lastValueVisible: false });
                    topSeries.setData([
                        { time: fvg.time, value: fvg.top },
                        { time: fallbackEnd, value: fvg.top }
                    ]);

                    const bottomSeries = this.chart.addLineSeries({ color: color, lineWidth: 4, priceLineVisible: false, lastValueVisible: false });
                    bottomSeries.setData([
                        { time: fvg.time, value: fvg.bottom },
                        { time: fallbackEnd, value: fvg.bottom }
                    ]);

                    this.lineSeries.push(topSeries, bottomSeries);
                    // Also create priceLine fallbacks for guaranteed visibility
                    try {
                        const topPL = this.series.createPriceLine({ price: fvg.top, color: color, lineWidth: 1, axisLabelVisible: false, title: 'FVG Top' });
                        const bottomPL = this.series.createPriceLine({ price: fvg.bottom, color: color, lineWidth: 1, axisLabelVisible: false, title: 'FVG Bottom' });
                        this.priceLines.push(topPL, bottomPL);
                    } catch (e3) { /* ignore */ }
                } catch (e2) {
                    console.warn('Fallback draw for FVG failed:', e2);
                }
            }
        }
    }

    /**
     * Render Equal Highs/Lows
     * @param {EqualHighLow[]} equalHLs
     */
    renderEqualHighsLows(equalHLs) {
        const markers = equalHLs.map(eq => ({
            time: eq.time2,
            position: eq.type === 'EQH' ? 'aboveBar' : 'belowBar',
            color: eq.type === 'EQH' ? this.colors.bearishStructure : this.colors.bullishStructure,
            shape: 'circle',
            text: eq.type,
            size: 0.5
        }));

        this.markers = [...this.markers, ...markers];
        this._updateMarkers();
    }

    /**
     * Render Premium/Discount Zone
     * @param {PremiumDiscountZone} zone
     */
    renderPremiumDiscountZone(zone) {
        if (!zone) return;

        // Premium Zone (top)
        const premiumPrimitive = this.createBoxPrimitive(
            zone.startTime,
            zone.endTime,
            zone.premiumTop,
            zone.premiumBottom,
            this.colors.premiumZone
        );

        // Discount Zone (bottom)
        const discountPrimitive = this.createBoxPrimitive(
            zone.startTime,
            zone.endTime,
            zone.discountTop,
            zone.discountBottom,
            this.colors.discountZone
        );

        try {
            this.series.attachPrimitive(premiumPrimitive);
            this.series.attachPrimitive(discountPrimitive);
            this.primitives.push(premiumPrimitive, discountPrimitive);
        } catch (e) {
            console.warn('Could not attach zone primitives:', e);
        }
    }

    /**
     * Render Strong/Weak Levels as horizontal lines or zones
     * @param {StrongWeakLevel[]} levels
     * @param {number} endTime - End time for the lines
     * @param {boolean} [isAlertMode=false] - If true, render as a zone
     */
    renderStrongWeakLevels(levels, endTime, isAlertMode = false) {
        for (const level of levels) {
            const baseColor = level.strength === 'strong' ? this.colors.strongLevel : this.colors.weakLevel;
            const text = level.strength === 'strong'
                ? (level.type === 'high' ? 'Strong High' : 'Strong Low')
                : (level.type === 'high' ? 'Weak High' : 'Weak Low');

            if (isAlertMode) {
                // ZONE MODE: Use EXACT same rendering as Order Blocks
                const price = level.price;
                // Zone half-height: 0.5% of price — very visible band
                const expand = price * 0.005;
                const zoneHigh = price + expand;
                const zoneLow = price - expand;

                // Colors: Purple for High, Orange for Low (distinct from OB Red/Blue)
                let fillColor;
                if (level.strength === 'strong') {
                    fillColor = level.type === 'high'
                        ? 'rgba(168, 85, 247, 0.25)'   // Purple
                        : 'rgba(251, 146, 60, 0.25)';  // Orange
                } else {
                    fillColor = level.type === 'high'
                        ? 'rgba(168, 85, 247, 0.15)'   // Light Purple
                        : 'rgba(251, 146, 60, 0.15)';  // Light Orange
                }

                const primitive = this.createBoxPrimitive(
                    level.time,
                    endTime,
                    zoneHigh,
                    zoneLow,
                    fillColor
                );

                try {
                    this.series.attachPrimitive(primitive);
                    this.primitives.push(primitive);
                } catch (e) {
                    console.warn('S/W Zone primitive failed, using fallback:', e);
                    // Fallback: draw thick horizontal lines at top & bottom — same as OB fallback
                    try {
                        const fallbackEnd = endTime || (level.time + 3600);

                        const topSeries = this.chart.addLineSeries({ color: fillColor, lineWidth: 6, priceLineVisible: false, lastValueVisible: false });
                        topSeries.setData([
                            { time: level.time, value: zoneHigh },
                            { time: fallbackEnd, value: zoneHigh }
                        ]);

                        const bottomSeries = this.chart.addLineSeries({ color: fillColor, lineWidth: 6, priceLineVisible: false, lastValueVisible: false });
                        bottomSeries.setData([
                            { time: level.time, value: zoneLow },
                            { time: fallbackEnd, value: zoneLow }
                        ]);

                        this.lineSeries.push(topSeries, bottomSeries);

                        // PriceLine fallback for guaranteed visibility
                        try {
                            const topPL = this.series.createPriceLine({ price: zoneHigh, color: fillColor, lineWidth: 2, axisLabelVisible: false, title: text + ' Top' });
                            const bottomPL = this.series.createPriceLine({ price: zoneLow, color: fillColor, lineWidth: 2, axisLabelVisible: false, title: text + ' Bottom' });
                            this.priceLines.push(topPL, bottomPL);
                        } catch (e3) { /* ignore */ }
                    } catch (e2) {
                        console.warn('Fallback draw for S/W zone failed:', e2);
                    }
                }

                // Center line with label (Dashed)
                const lineSeries = this.chart.addLineSeries({
                    color: baseColor,
                    lineWidth: 2,
                    lineStyle: 2, // Dashed
                    priceLineVisible: false,
                    lastValueVisible: true,
                    title: text
                });
                lineSeries.setData([
                    { time: level.time, value: price },
                    { time: endTime, value: price }
                ]);
                this.lineSeries.push(lineSeries);

            } else {
                // NORMAL MODE: Single Line
                const lineSeries = this.chart.addLineSeries({
                    color: baseColor,
                    lineWidth: 1,
                    lineStyle: 0, // Solid
                    priceLineVisible: false,
                    lastValueVisible: true,
                    title: text
                });

                lineSeries.setData([
                    { time: level.time, value: level.price },
                    { time: endTime, value: level.price }
                ]);

                this.lineSeries.push(lineSeries);
            }
        }
    }

    /**
     * Render all SMC results
     * @param {Object} smcResults - Results from SMCIndicator.getAllResults()
     * @param {Object} [options]
     * @param {boolean} [options.showSwingPoints=true]
     * @param {boolean} [options.showStructures=true]
     * @param {boolean} [options.showOrderBlocks=true]
     * @param {boolean} [options.showFVG=true]
     * @param {boolean} [options.showEqualHL=true]
     * @param {boolean} [options.showPremiumDiscount=true]
     * @param {boolean} [options.showStrongWeak=true]
     * @param {boolean} [options.showOrderBlocksBull=true] - show bullish OB
     * @param {boolean} [options.showOrderBlocksBear=true] - show bearish OB
     * @param {boolean} [options.showFvGBull=true] - show bullish FVG
     * @param {boolean} [options.showFvGBear=true] - show bearish FVG
     */
    renderAll(smcResults, options = {}) {
        const opts = {
            showSwingPoints: options.showSwingPoints !== false,
            showStructures: options.showStructures !== false,
            showOrderBlocks: options.showOrderBlocks !== false,
            showFVG: options.showFVG !== false,
            showEqualHL: options.showEqualHL !== false,
            showPremiumDiscount: options.showPremiumDiscount !== false,
            showStrongWeak: options.showStrongWeak !== false,
            showStrongWeakAlert: options.showStrongWeakAlert === true
        };

        // Extended filters for bull/bear visibility
        opts.showOrderBlocksBull = options.showOrderBlocksBull !== false;
        opts.showOrderBlocksBear = options.showOrderBlocksBear !== false;
        opts.showFvGBull = options.showFvGBull !== false;
        opts.showFvGBear = options.showFvGBear !== false;

        // Clear previous renders
        this.clear();

        // Get current time for extending elements
        const currentTime = smcResults.premiumDiscountZone?.endTime || Date.now() / 1000;

        // Render each component based on options
        if (opts.showSwingPoints && smcResults.swingPoints) {
            this.renderSwingPoints(smcResults.swingPoints);
        }

        if (opts.showStructures && smcResults.structures) {
            this.renderStructures(smcResults.structures);
        }

        if (opts.showOrderBlocks && smcResults.orderBlocks) {
            // Filter by bull/bear options
            const filteredObs = smcResults.orderBlocks.filter(ob => {
                if (ob.bias === 'bullish' && !opts.showOrderBlocksBull) return false;
                if (ob.bias === 'bearish' && !opts.showOrderBlocksBear) return false;
                return true;
            });
            console.log('Rendering Order Blocks:', filteredObs.length);
            this.renderOrderBlocks(filteredObs, currentTime);
        }

        if (opts.showFVG && smcResults.fairValueGaps) {
            // Filter FVGs by bull/bear options
            const filteredFvgs = smcResults.fairValueGaps.filter(fvg => {
                if (fvg.bias === 'bullish' && !opts.showFvGBull) return false;
                if (fvg.bias === 'bearish' && !opts.showFvGBear) return false;
                return true;
            });
            console.log('Rendering FVGs:', filteredFvgs.length);
            this.renderFairValueGaps(filteredFvgs, currentTime);
        }
        // NOTE: BackgroundColorZonesPlugin removed here because it draws full-height
        // vertical bands. OB/FVG are already rendered correctly as price-bounded
        // horizontal zones by createBoxPrimitive (renderOrderBlocks/renderFairValueGaps).

        if (opts.showEqualHL && smcResults.equalHighsLows) {
            this.renderEqualHighsLows(smcResults.equalHighsLows);
        }

        if (opts.showPremiumDiscount && smcResults.premiumDiscountZone) {
            this.renderPremiumDiscountZone(smcResults.premiumDiscountZone);
        }

        if (opts.showStrongWeak && smcResults.strongWeakLevels) {
            this.renderStrongWeakLevels(smcResults.strongWeakLevels, currentTime, opts.showStrongWeakAlert);
        }
    }
}

// Export for different module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { SMCChartRenderer };
}

if (typeof window !== 'undefined') {
    window.SMCChartRenderer = SMCChartRenderer;
}

// export { SMCChartRenderer };
