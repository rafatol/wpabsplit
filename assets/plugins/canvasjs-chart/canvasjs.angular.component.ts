/*
CanvasJS Angular Chart- https://canvasjs.com/
Copyright 2023 fenopix

--------------------- License Information --------------------
The software in CanvasJS Angular Chart is free and open-source. But, CanvasJS Angular Chart relies on CanvasJS Chart which requires a valid CanvasJS Chart license for commercial use. Please refer to the following link for further details https://canvasjs.com/license/

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

/*tslint:disable*/
/*eslint-disable*/
/*jshint ignore:start*/
import { Component, AfterViewInit, OnChanges, OnDestroy, Input, Output, EventEmitter } from '@angular/core';
declare var require: any;
var CanvasJS = require('./canvasjs.min');

@Component({
  selector: 'canvasjs-chart',
  template: '<div id="{{chartContainerId}}" [ngStyle]="styles"></div>'
})

class CanvasJSChart implements AfterViewInit, OnChanges, OnDestroy {
	static _cjsChartContainerId = 0;
	chart: any;
	chartContainerId: any;
	prevChartOptions: any;
	shouldUpdateChart = false;

	@Input()
		options: any;
	@Input()
		styles: any;
		
	@Output()
		chartInstance = new EventEmitter<object>();
		
	constructor() {
		this.options = this.options ? this.options : {};
		this.styles = this.styles ? this.styles : { width: "100%", position: "relative" };
		this.styles.height = this.options.height ? this.options.height + "px" : "400px";
		
		this.chartContainerId = 'canvasjs-angular-chart-container-' + CanvasJSChart._cjsChartContainerId++;
	}

	ngDoCheck() {
		if(this.prevChartOptions != this.options) {
			this.shouldUpdateChart = true;
		}
	}
	
	ngOnChanges() {
		//Update Chart Options & Render
		if(this.shouldUpdateChart && this.chart) {
			this.chart.options = this.options;
			this.chart.render();
			this.shouldUpdateChart = false;
			this.prevChartOptions = this.options;
		}
	}
	
	ngAfterViewInit() {		
	  this.chart = new CanvasJS.Chart(this.chartContainerId, this.options);
	  this.chart.render();
	  this.prevChartOptions = this.options;
	  this.chartInstance.emit(this.chart);
	}

	ngOnDestroy() {
		if(this.chart)
			this.chart.destroy();
	}
}

export {
	CanvasJSChart,
	CanvasJS
};
/*tslint:enable*/
/*eslint-enable*/
/*jshint ignore:end*/