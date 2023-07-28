"use strict";

// Class definition
var KTChartsWidget31 = (function () {
	// Private methods
	var initChart1 = function () {
		// Check if amchart library is included
		if (typeof am5 === "undefined") {
			return;
		}

		var element = document.getElementById("kt_charts_widget_31_chart");

		if (!element) {
			return;
		}

		var chart;
		var root;

		var init = function() {
			// Create root element
			// https://www.amcharts.com/docs/v5/getting-started/#Root_element
			root = am5.Root.new(element);

			// Set themes
			// https://www.amcharts.com/docs/v5/concepts/themes/
			root.setThemes([am5themes_Animated.new(root)]);

			// Create chart
			// https://www.amcharts.com/docs/v5/charts/radar-chart/
			chart = root.container.children.push(
				am5radar.RadarChart.new(root, {
					panX: false,
					panY: false,
					wheelX: "panX",
					wheelY: "zoomX",
					innerRadius: am5.percent(40),
					radius: am5.percent(70),
					arrangeTooltips: false,
				})
			);

			var cursor = chart.set(
				"cursor",
				am5radar.RadarCursor.new(root, {
					behavior: "zoomX",
				})
			);

			cursor.lineY.set("visible", false);

			// Create axes and their renderers
			// https://www.amcharts.com/docs/v5/charts/radar-chart/#Adding_axes
			var xRenderer = am5radar.AxisRendererCircular.new(root, {
				minGridDistance: 30,
			});
			
			xRenderer.labels.template.setAll({
				textType: "radial",
				radius: 10,
				paddingTop: 0,
				paddingBottom: 0,
				centerY: am5.p50,
				fontWeight: "400",
				fontSize: 11,
				fill: am5.color(KTUtil.getCssVariableValue("--bs-gray-800")),
			});

			xRenderer.grid.template.setAll({
				location: 0.5,
				strokeDasharray: [2, 2],
				stroke: KTUtil.getCssVariableValue('--bs-gray-400')
			});

			var xAxis = chart.xAxes.push(
				am5xy.CategoryAxis.new(root, {
					maxDeviation: 0,
					categoryField: "name",
					renderer: xRenderer,
				})
			);

			var yRenderer = am5radar.AxisRendererRadial.new(root, {
				minGridDistance: 30,
			});

			yRenderer.labels.template.setAll({
				fontWeight: "500",
				fontSize: 12,
				fill: am5.color(KTUtil.getCssVariableValue("--bs-gray-700")),
			});

			var yAxis = chart.yAxes.push(
				am5xy.ValueAxis.new(root, {
					renderer: yRenderer,
				})
			);

			yRenderer.grid.template.setAll({
				strokeDasharray: [2, 2],
				stroke: KTUtil.getCssVariableValue('--bs-gray-400')				
			});

			// Create series
			// https://www.amcharts.com/docs/v5/charts/radar-chart/#Adding_series
			var series1 = chart.series.push(
				am5radar.RadarLineSeries.new(root, {
					name: "Revenue",
					xAxis: xAxis,
					yAxis: yAxis,
					valueYField: "value1",
					categoryXField: "name",
					fill: am5.color(KTUtil.getCssVariableValue("--bs-primary")),
				})
			);

			series1.strokes.template.setAll({
				strokeOpacity: 0,
			});

			series1.fills.template.setAll({
				visible: true,
				fill: am5.color(KTUtil.getCssVariableValue("--bs-primary")),
				fillOpacity: 0.5,
			});

			var series2 = chart.series.push(
				am5radar.RadarLineSeries.new(root, {
					name: "Expense",
					xAxis: xAxis,
					yAxis: yAxis,
					valueYField: "value2",
					categoryXField: "name",
					stacked: true,
					tooltip: am5.Tooltip.new(root, {
						labelText: "Revenue: {value1}\nExpense:{value2}",
					}),
					fill: am5.color(KTUtil.getCssVariableValue("--bs-success")),
				})
			);

			series2.strokes.template.setAll({
				strokeOpacity: 0,
			});

			series2.fills.template.setAll({
				visible: true,
				fill: am5.color(KTUtil.getCssVariableValue("--bs-primary")),
				fillOpacity: 0.5,
			});

			var legend = chart.radarContainer.children.push(
				am5.Legend.new(root, {
					width: 150,
					centerX: am5.p50,
					centerY: am5.p50
				})
			);
			legend.data.setAll([series1, series2]);

			legend.labels.template.setAll({
				fontWeight: "600",
				fontSize: 13,
				fill: am5.color(KTUtil.getCssVariableValue("--bs-gray-700")),
			});

			// Set data
			// https://www.amcharts.com/docs/v5/charts/radar-chart/#Setting_data
			var data = [
				{
					name: "Openlane",
					value1: 160.2,
					value2: 26.9,
				},
				{
					name: "Yearin",
					value1: 120.1,
					value2: 50.5,
				},
				{
					name: "Goodsilron",
					value1: 150.7,
					value2: 12.3,
				},
				{
					name: "Condax",
					value1: 69.4,
					value2: 74.5,
				},
				{
					name: "Opentech",
					value1: 78.5,
					value2: 29.7,
				},
				{
					name: "Golddex",
					value1: 77.6,
					value2: 102.2,
				},
				{
					name: "Isdom",
					value1: 69.8,
					value2: 22.6,
				},
				{
					name: "Plusstrip",
					value1: 63.6,
					value2: 45.3,
				},
				{
					name: "Kinnamplus",
					value1: 59.7,
					value2: 12.8,
				},
				{
					name: "Zumgoity",
					value1: 64.3,
					value2: 19.6,
				},
				{
					name: "Stanredtax",
					value1: 52.9,
					value2: 96.3,
				},
				{
					name: "Conecom",
					value1: 42.9,
					value2: 11.9,
				},
				{
					name: "Zencorporation",
					value1: 40.9,
					value2: 16.8,
				},
				{
					name: "Iselectrics",
					value1: 39.2,
					value2: 9.9,
				},
				{
					name: "Treequote",
					value1: 76.6,
					value2: 36.9,
				},
				{
					name: "Sumace",
					value1: 34.8,
					value2: 14.6,
				},
				{
					name: "Lexiqvolax",
					value1: 32.1,
					value2: 35.6,
				},
				{
					name: "Sunnamplex",
					value1: 31.8,
					value2: 5.9,
				},
				{
					name: "Faxquote",
					value1: 29.3,
					value2: 14.7,
				},
				{
					name: "Donware",
					value1: 23.0,
					value2: 2.8,
				},
				{
					name: "Warephase",
					value1: 21.5,
					value2: 12.1,
				},
				{
					name: "Donquadtech",
					value1: 19.7,
					value2: 10.8,
				},
				{
					name: "Nam-zim",
					value1: 15.5,
					value2: 4.1,
				},
				{
					name: "Y-corporation",
					value1: 14.2,
					value2: 11.3,
				},
			];

			series1.data.setAll(data);
			series2.data.setAll(data);
			xAxis.data.setAll(data);

			// Animate chart and series in
			// https://www.amcharts.com/docs/v5/concepts/animations/#Initial_animation
			series1.appear(1000);
			series2.appear(1000);
			chart.appear(1000, 100);
		}

		// On amchart ready
		am5.ready(function () {
			init();
		}); // end am5.ready()

		// Update chart on theme mode change
		KTThemeMode.on("kt.thememode.change", function() {     
			// Destroy chart
			root.dispose();

			// Reinit chart
			init();
		});
	};

	// Public methods
	return {
		init: function () {
			initChart1();
		}
	};
})();

// Webpack support
if (typeof module !== "undefined") {
	module.exports = KTChartsWidget31;
}

// On document ready
KTUtil.onDOMContentLoaded(function () {
	KTChartsWidget31.init();
});
