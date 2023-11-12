<!--
CanvasJS Vue Chart - https://canvasjs.com/
Copyright 2023 fenopix

--------------------- License Information --------------------
The software in CanvasJS Vue Chart is free and open-source. But, CanvasJS Vue Chart relies on CanvasJS Chart which requires a valid CanvasJS Chart license for commercial use. Please refer to the following link for further details https://canvasjs.com/license/

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
-->
<script>
    import * as CJS from './canvasjs.min';
	var CanvasJS = 'Chart' in CJS ? CJS : window.CanvasJS;

    export default {
        props: {
            'styles': {
                type: Object,
                default: () => ({})
            },
            'options': {
                type: Object,
                default: () => ({})
            }
        },
        emits: ['chart-ref'],
        data() {
            return {
                chart: null,
                updateChart: true,
                containerStyle: {
                    width: this.styles && this.styles.width ? this.styles.width : "100%",
                    height: this.styles && this.styles.height ? this.styles.height : "360px",
                    ...this.styles
                },
            }
        },
        watch: {
            options: {
                handler(options, prevOptions) {
                    this.updateChart = (!(options === prevOptions));
                }
            }
        },
        updated() {
            if (this.chart && this.updateChart) {
                this.chart.options = this.options;
                this.chart.render();
            }
        },
        mounted() {
            this.chart = new CanvasJS.Chart(this.$refs.chartContainer, this.options);
            this.chart.render();
            this.$emit('chart-ref', this.chart);

        },
        unmounted() {
            if (this.chart) {
                this.chart.destroy();
            }
        }
    }
    export { CanvasJS };
</script>
<template>
    <div ref="chartContainer" :style="containerStyle"></div>
</template>