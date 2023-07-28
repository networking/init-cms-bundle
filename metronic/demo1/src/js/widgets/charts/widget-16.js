"use strict";

// Class definition
var KTChartsWidget16 = (function () {
    // Private methods
    var initChart = function () {
        // Check if amchart library is included
        if (typeof am5 === "undefined") {
            return;
        }

        var element = document.getElementById("kt_charts_widget_16_chart");

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
            // https://www.amcharts.com/docs/v5/charts/xy-chart/
            var chart = root.container.children.push(
                am5xy.XYChart.new(root, {
                    panX: false,
                    panY: false,
                    wheelX: "panX",
                    wheelY: "zoomX",
                    layout: root.verticalLayout,
                })
            );

            var colors = chart.get("colors");

            var data = [
                {
                    country: "US",
                    visits: 725,
                },
                {
                    country: "UK",
                    visits: 625,
                },
                {
                    country: "China",
                    visits: 602,
                },
                {
                    country: "Japan",
                    visits: 509,
                },
                {
                    country: "Germany",
                    visits: 322,
                },
                {
                    country: "France",
                    visits: 214,
                },
                {
                    country: "India",
                    visits: 204,
                },
                {
                    country: "Spain",
                    visits: 198,
                },
                {
                    country: "Italy",
                    visits: 165,
                },
                {
                    country: "Russia",
                    visits: 130,
                },
                {
                    country: "Norway",
                    visits: 93,
                },
                {
                    country: "Canada",
                    visits: 41,
                },
            ];

            prepareParetoData();

            function prepareParetoData() {
                var total = 0;

                for (var i = 0; i < data.length; i++) {
                    var value = data[i].visits;
                    total += value;
                }

                var sum = 0;
                for (var i = 0; i < data.length; i++) {
                    var value = data[i].visits;
                    sum += value;
                    data[i].pareto = (sum / total) * 100;
                }
            }

            // Create axes
            // https://www.amcharts.com/docs/v5/charts/xy-chart/axes/
            var xAxis = chart.xAxes.push(
                am5xy.CategoryAxis.new(root, {
                    categoryField: "country",
                    renderer: am5xy.AxisRendererX.new(root, {
                        minGridDistance: 30,
                    }),
                })
            );

            xAxis.get("renderer").labels.template.setAll({
                paddingTop: 10,
                fontWeight: "400",
                fontSize: 13,
                fill: am5.color(KTUtil.getCssVariableValue('--bs-gray-500'))
            });

            xAxis.get("renderer").grid.template.setAll({
                disabled: true,
                strokeOpacity: 0
            });

            xAxis.data.setAll(data);

            var yAxis = chart.yAxes.push(
                am5xy.ValueAxis.new(root, {
                    renderer: am5xy.AxisRendererY.new(root, {}),
                })
            );

            yAxis.get("renderer").labels.template.setAll({
                paddingLeft: 10,
                fontWeight: "400",
                fontSize: 13,
                fill: am5.color(KTUtil.getCssVariableValue('--bs-gray-500'))
            });

            yAxis.get("renderer").grid.template.setAll({
                stroke: am5.color(KTUtil.getCssVariableValue('--bs-gray-300')),
                strokeWidth: 1,
                strokeOpacity: 1,
                strokeDasharray: [3]
            });

            var paretoAxisRenderer = am5xy.AxisRendererY.new(root, {
                opposite: true,
            });

            var paretoAxis = chart.yAxes.push(
                am5xy.ValueAxis.new(root, {
                    renderer: paretoAxisRenderer,
                    min: 0,
                    max: 100,
                    strictMinMax: true,
                })
            );
            
            paretoAxis.get("renderer").labels.template.setAll({
                fontWeight: "400",
                fontSize: 13,
                fill: am5.color(KTUtil.getCssVariableValue('--bs-gray-500'))
            });

            paretoAxisRenderer.grid.template.set("forceHidden", true);
            paretoAxis.set("numberFormat", "#'%");

            // Add series
            // https://www.amcharts.com/docs/v5/charts/xy-chart/series/
            var series = chart.series.push(
                am5xy.ColumnSeries.new(root, {
                    xAxis: xAxis,
                    yAxis: yAxis,
                    valueYField: "visits",
                    categoryXField: "country",
                })
            );

            series.columns.template.setAll({
                tooltipText: "{categoryX}: {valueY}",
                tooltipY: 0,
                strokeOpacity: 0,
                cornerRadiusTL: 6,
                cornerRadiusTR: 6,
            });

            series.columns.template.adapters.add(
                "fill",
                function (fill, target) {
                    return chart
                        .get("colors")
                        .getIndex(series.dataItems.indexOf(target.dataItem));
                }
            );

            // pareto series
            var paretoSeries = chart.series.push(
                am5xy.LineSeries.new(root, {
                    xAxis: xAxis,
                    yAxis: paretoAxis,
                    valueYField: "pareto",
                    categoryXField: "country",
                    stroke: am5.color(KTUtil.getCssVariableValue('--bs-dark')),
                    maskBullets: false,
                })
            );

            paretoSeries.bullets.push(function () {
                return am5.Bullet.new(root, {
                    locationY: 1,
                    sprite: am5.Circle.new(root, {
                        radius: 5,
                        fill: am5.color(KTUtil.getCssVariableValue('--bs-primary')),
                        stroke: am5.color(KTUtil.getCssVariableValue('--bs-dark'))
                    }),
                });
            });

            series.data.setAll(data);
            paretoSeries.data.setAll(data);

            // Make stuff animate on load
            // https://www.amcharts.com/docs/v5/concepts/animations/
            series.appear();
            chart.appear(1000, 100);
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
    module.exports = KTChartsWidget16;
}

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTChartsWidget16.init();
});
