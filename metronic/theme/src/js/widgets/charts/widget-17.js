"use strict";

// Class definition
var KTChartsWidget17 = (function () {
    // Private methods
    var initChart = function () {
        // Check if amchart library is included
        if (typeof am5 === "undefined") {
            return;
        }

        var element = document.getElementById("kt_charts_widget_17_chart");

        if (!element) {
            return;
        }

        var root;

        var init = function() {
            // Create root element
            // https://www.amcharts.com/docs/v5/getting-started/#Root_element
            root = am5.Root.new(element);

            // Set themes
            // https://www.amcharts.com/docs/v5/concepts/themes/
            root.setThemes([am5themes_Animated.new(root)]);

            // Create chart
            // https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/
            // start and end angle must be set both for chart and series
            var chart = root.container.children.push(
                am5percent.PieChart.new(root, {
                    startAngle: 180,
                    endAngle: 360,
                    layout: root.verticalLayout,
                    innerRadius: am5.percent(50),
                })
            );

            // Create series
            // https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/#Series
            // start and end angle must be set both for chart and series
            var series = chart.series.push(
                am5percent.PieSeries.new(root, {
                    startAngle: 180,
                    endAngle: 360,
                    valueField: "value",
                    categoryField: "category",
                    alignLabels: false,
                })
            );

            series.labels.template.setAll({
                fontWeight: "400",
                fontSize: 13,
                fill: am5.color(KTUtil.getCssVariableValue('--bs-gray-500'))
            });

            series.states.create("hidden", {
                startAngle: 180,
                endAngle: 180,
            });

            series.slices.template.setAll({
                cornerRadius: 5,
            });

            series.ticks.template.setAll({
                forceHidden: true,
            });

            // Set data
            // https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/#Setting_data
            series.data.setAll([
                { value: 10, category: "One", fill: am5.color(KTUtil.getCssVariableValue('--bs-primary')) },
                { value: 9, category: "Two", fill: am5.color(KTUtil.getCssVariableValue('--bs-success')) },
                { value: 6, category: "Three", fill: am5.color(KTUtil.getCssVariableValue('--bs-danger')) },
                { value: 5, category: "Four", fill: am5.color(KTUtil.getCssVariableValue('--bs-warning')) },
                { value: 4, category: "Five", fill: am5.color(KTUtil.getCssVariableValue('--bs-info')) },
                { value: 3, category: "Six", fill: am5.color(KTUtil.getCssVariableValue('--bs-secondary')) }
            ]);

            series.appear(1000, 100);
        }

        am5.ready(function () {
            init();
        });

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
    module.exports = KTChartsWidget17;
}

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTChartsWidget17.init();
});
