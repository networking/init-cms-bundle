"use strict";

// Class definition
var KTChartsWidget13 = (function () {
    // Private methods
    var initChart = function () {
        // Check if amchart library is included
        if (typeof am5 === "undefined") {
            return;
        }

        var element = document.getElementById("kt_charts_widget_13_chart");

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
                    panX: true,
                    panY: true,
                    wheelX: "panX",
                    wheelY: "zoomX",
                })
            );

            // Add cursor
            // https://www.amcharts.com/docs/v5/charts/xy-chart/cursor/
            var cursor = chart.set(
                "cursor",
                am5xy.XYCursor.new(root, {
                    behavior: "none"
                })
            );

            cursor.lineY.set("visible", false);

            // The data
            var data = [
                {
                    year: "2003",
                    cars: 1587,
                    motorcycles: 650,
                    bicycles: 121,
                },
                {
                    year: "2004",
                    cars: 1567,
                    motorcycles: 683,
                    bicycles: 146,
                },
                {
                    year: "2005",
                    cars: 1617,
                    motorcycles: 691,
                    bicycles: 138,
                },
                {
                    year: "2006",
                    cars: 1630,
                    motorcycles: 642,
                    bicycles: 127,
                },
                {
                    year: "2007",
                    cars: 1660,
                    motorcycles: 699,
                    bicycles: 105,
                },
                {
                    year: "2008",
                    cars: 1683,
                    motorcycles: 721,
                    bicycles: 109,
                },
                {
                    year: "2009",
                    cars: 1691,
                    motorcycles: 737,
                    bicycles: 112,
                },
                {
                    year: "2010",
                    cars: 1298,
                    motorcycles: 680,
                    bicycles: 101,
                },
                {
                    year: "2011",
                    cars: 1275,
                    motorcycles: 664,
                    bicycles: 97,
                },
                {
                    year: "2012",
                    cars: 1246,
                    motorcycles: 648,
                    bicycles: 93,
                },
                {
                    year: "2013",
                    cars: 1318,
                    motorcycles: 697,
                    bicycles: 111,
                },
                {
                    year: "2014",
                    cars: 1213,
                    motorcycles: 633,
                    bicycles: 87,
                },
                {
                    year: "2015",
                    cars: 1199,
                    motorcycles: 621,
                    bicycles: 79,
                },
                {
                    year: "2016",
                    cars: 1110,
                    motorcycles: 210,
                    bicycles: 81,
                },
                {
                    year: "2017",
                    cars: 1165,
                    motorcycles: 232,
                    bicycles: 75,
                },
                {
                    year: "2018",
                    cars: 1145,
                    motorcycles: 219,
                    bicycles: 88,
                },
                {
                    year: "2019",
                    cars: 1163,
                    motorcycles: 201,
                    bicycles: 82,
                },
                {
                    year: "2020",
                    cars: 1180,
                    motorcycles: 285,
                    bicycles: 87,
                },
                {
                    year: "2021",
                    cars: 1159,
                    motorcycles: 277,
                    bicycles: 71,
                },
            ];

            // Create axes
            // https://www.amcharts.com/docs/v5/charts/xy-chart/axes/
            var xAxis = chart.xAxes.push(
                am5xy.CategoryAxis.new(root, {
                    categoryField: "year",
                    startLocation: 0.5,
                    endLocation: 0.5,
                    renderer: am5xy.AxisRendererX.new(root, {}),
                    tooltip: am5.Tooltip.new(root, {}),
                })
            );

            xAxis.get("renderer").grid.template.setAll({
                disabled: true,
                strokeOpacity: 0
            });

            xAxis.get("renderer").labels.template.setAll({
                fontWeight: "400",
                fontSize: 13,
                fill: am5.color(KTUtil.getCssVariableValue('--bs-gray-500'))
            });

            xAxis.data.setAll(data);

            var yAxis = chart.yAxes.push(
                am5xy.ValueAxis.new(root, {
                    renderer: am5xy.AxisRendererY.new(root, {}),
                })
            );

            yAxis.get("renderer").grid.template.setAll({
                stroke: am5.color(KTUtil.getCssVariableValue('--bs-gray-300')),
                strokeWidth: 1,
                strokeOpacity: 1,
                strokeDasharray: [3]
            });

            yAxis.get("renderer").labels.template.setAll({
                fontWeight: "400",
                fontSize: 13,
                fill: am5.color(KTUtil.getCssVariableValue('--bs-gray-500'))
            });

            // Add series
            // https://www.amcharts.com/docs/v5/charts/xy-chart/series/

            function createSeries(name, field, color) {
                var series = chart.series.push(
                    am5xy.LineSeries.new(root, {
                        name: name,
                        xAxis: xAxis,
                        yAxis: yAxis,
                        stacked: true,
                        valueYField: field,
                        categoryXField: "year",
                        fill: am5.color(color),
                        tooltip: am5.Tooltip.new(root, {
                            pointerOrientation: "horizontal",
                            labelText: "[bold]{name}[/]\n{categoryX}: {valueY}",
                        }),
                    })
                );

                

                series.fills.template.setAll({
                    fillOpacity: 0.5,
                    visible: true,
                });

                series.data.setAll(data);
                series.appear(1000);
            }

            createSeries("Cars", "cars", KTUtil.getCssVariableValue('--bs-primary'));
            createSeries("Motorcycles", "motorcycles", KTUtil.getCssVariableValue('--bs-success'));
            createSeries("Bicycles", "bicycles", KTUtil.getCssVariableValue('--bs-warning'));

            // Add scrollbar
            // https://www.amcharts.com/docs/v5/charts/xy-chart/scrollbars/
            var scrollbarX = chart.set(
                "scrollbarX",
                am5.Scrollbar.new(root, {
                    orientation: "horizontal",
                    marginBottom: 25,
                    height: 8
                })
            );

            // Create axis ranges
            // https://www.amcharts.com/docs/v5/charts/xy-chart/axes/axis-ranges/
            var rangeDataItem = xAxis.makeDataItem({
                category: "2016",
                endCategory: "2021",
            });

            var range = xAxis.createAxisRange(rangeDataItem);

            rangeDataItem.get("grid").setAll({
                stroke: am5.color(KTUtil.getCssVariableValue('--bs-gray-200')),
                strokeOpacity: 0.5,
                strokeDasharray: [3],
            });

            rangeDataItem.get("axisFill").setAll({
                fill: am5.color(KTUtil.getCssVariableValue('--bs-gray-200')),
                fillOpacity: 0.1,
            });

            rangeDataItem.get("label").setAll({
                inside: true,
                text: "Fines increased",
                rotation: 90,
                centerX: am5.p100,
                centerY: am5.p100,
                location: 0,
                paddingBottom: 10,
                paddingRight: 15,
            });

            var rangeDataItem2 = xAxis.makeDataItem({
                category: "2021",
            });

            var range2 = xAxis.createAxisRange(rangeDataItem2);

            rangeDataItem2.get("grid").setAll({
                stroke: am5.color(KTUtil.getCssVariableValue('--bs-danger')),
                strokeOpacity: 1,
                strokeDasharray: [3],
            });

            rangeDataItem2.get("label").setAll({
                inside: true,
                text: "Fee introduced",
                rotation: 90,
                centerX: am5.p100,
                centerY: am5.p100,
                location: 0,
                paddingBottom: 10,
                paddingRight: 15,
            });

            // Make stuff animate on load
            // https://www.amcharts.com/docs/v5/concepts/animations/
            chart.appear(1000, 100);
        }

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
    module.exports = KTChartsWidget13;
}

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTChartsWidget13.init();
});
