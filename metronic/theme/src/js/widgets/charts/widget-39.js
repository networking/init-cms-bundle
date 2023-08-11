"use strict";

// Class definition
var KTChartsWidget39 = (function () {
	// Private methods
	var initChart = function () {
		// Check if amchart library is included
		if (typeof am5 === "undefined") {
			return;
		}

		var element = document.querySelector('#kt_charts_widget_39_chart');

		if ( !element ) {
			return;
		}

        var root;

		var init = function() {
			// Create root element
			// https://www.amcharts.com/docs/v5/getting-started/#Root_element
			root = am5.Root.new(element);

			if ( !root ) {
				return;
			}

			// Set themes
			// https://www.amcharts.com/docs/v5/concepts/themes/
			root.setThemes([am5themes_Animated.new(root)]);

			// Create chart
			// https://www.amcharts.com/docs/v5/charts/radar-chart/
			var chart = root.container.children.push(
				am5radar.RadarChart.new(root, {
					panX: false,
					panY: false,
					wheelX: "panX",
					wheelY: "zoomX",
				})
			);

			// Create axes and their renderers
			// https://www.amcharts.com/docs/v5/charts/radar-chart/#Adding_axes
			var xRenderer = am5radar.AxisRendererCircular.new(root, {});
			xRenderer.labels.template.setAll({
				radius: 10
			});

			xRenderer.grid.template.setAll({
				stroke: am5.color(KTUtil.getCssVariableValue("--bs-gray-700"))
			});

			var yRenderer = am5radar.AxisRendererRadial.new(root, {
				minGridDistance: 20
			});	
			
			yRenderer.grid.template.setAll({
				stroke: am5.color(KTUtil.getCssVariableValue("--bs-gray-700"))
			});

			var xAxis = chart.xAxes.push(
				am5xy.CategoryAxis.new(root, {
					maxDeviation: 0,
					categoryField: "category",
					renderer: xRenderer,
					tooltip: am5.Tooltip.new(root, {}),
				})
			);			

			var yAxis = chart.yAxes.push(
				am5xy.ValueAxis.new(root, {
					min: 0,
					max: 10,
					renderer: yRenderer
				})
			);

			xRenderer.labels.template.setAll({
				fontSize: 11,
				fill: am5.color(KTUtil.getCssVariableValue("--bs-gray-800")),
			});

			yRenderer.labels.template.setAll({
				fontSize: 11,
				fill: am5.color(KTUtil.getCssVariableValue("--bs-gray-800")),
			});

			//yAxis.get("renderer").labels.template.set("forceHidden", true);

			// Create series
			// https://www.amcharts.com/docs/v5/charts/radar-chart/#Adding_series
			var series = chart.series.push(
				am5radar.RadarColumnSeries.new(root, {
					xAxis: xAxis,
					yAxis: yAxis,
					valueYField: "value",
					categoryXField: "category",
				})
			);

			series.columns.template.setAll({
				tooltipText: "{categoryX}: {valueY}",
				templateField: "columnSettings",
				strokeOpacity: 0,
				width: am5.p100,
			});

			// Set data
			// https://www.amcharts.com/docs/v5/charts/radar-chart/#Setting_data
			var data = [
				{
					category: "Spain",
					value: 5,
					columnSettings: {
						fill: chart.get("colors").next(),
					},
				},
				{
					category: "Spain",
					value: 4,
					columnSettings: {
						fill: chart.get("colors").next(),
					},
				},
				{
					category: "United States",
					value: 9,
					columnSettings: {
						fill: chart.get("colors").next(),
					},
				},
				{
					category: "Italy",
					value: 7,
					columnSettings: {
						fill: chart.get("colors").next(),
					},
				},
				{
					category: "France",
					value: 8,
					columnSettings: {
						fill: chart.get("colors").next(),
					},
				},
				{
					category: "Norway",
					value: 4,
					columnSettings: {
						fill: chart.get("colors").next(),
					},
				},
				{
					category: "Brasil",
					value: 7,
					columnSettings: {
						fill: chart.get("colors").next(),
					},
				},
				{
					category: "Canada",
					value: 5,
					columnSettings: {
						fill: chart.get("colors").next(),
					},
				},
			];

			series.data.setAll(data);
			xAxis.data.setAll(data);

			// Animate chart
			// https://www.amcharts.com/docs/v5/concepts/animations/#Initial_animation
			series.appear(1000);
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
			initChart();
		},
	};
})();

// Webpack support
if (typeof module !== "undefined") {
	module.exports = KTChartsWidget39;
}

// On document ready
KTUtil.onDOMContentLoaded(function () {
	KTChartsWidget39.init();
});
