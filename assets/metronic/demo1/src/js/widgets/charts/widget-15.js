"use strict";

// Class definition
var KTChartsWidget15 = (function () {
    // Private methods
    var initChart = function () {
        // Check if amchart library is included
        if (typeof am5 === "undefined") {
            return;
        }

        var element = document.getElementById("kt_charts_widget_15_chart");

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
                    //wheelX: "panX",
                    //wheelY: "zoomX",
                    layout: root.verticalLayout,
                })
            );

            // Data
            var colors = chart.get("colors");

            var data = [
                {
                    country: "US",
                    visits: 725,
                    icon: "https://www.amcharts.com/wp-content/uploads/flags/united-states.svg",
                    columnSettings: { 
                        fill: am5.color(KTUtil.getCssVariableValue('--bs-primary'))        
                    }
                },
                {
                    country: "UK",
                    visits: 625,
                    icon: "https://www.amcharts.com/wp-content/uploads/flags/united-kingdom.svg",
                    columnSettings: { 
                        fill: am5.color(KTUtil.getCssVariableValue('--bs-primary'))        
                    }
                },
                {
                    country: "China",
                    visits: 602,
                    icon: "https://www.amcharts.com/wp-content/uploads/flags/china.svg",
                    columnSettings: { 
                        fill: am5.color(KTUtil.getCssVariableValue('--bs-primary'))        
                    }
                },
                {
                    country: "Japan",
                    visits: 509,
                    icon: "https://www.amcharts.com/wp-content/uploads/flags/japan.svg",
                    columnSettings: { 
                        fill: am5.color(KTUtil.getCssVariableValue('--bs-primary'))        
                    }
                },
                {
                    country: "Germany",
                    visits: 322,
                    icon: "https://www.amcharts.com/wp-content/uploads/flags/germany.svg",
                    columnSettings: { 
                        fill: am5.color(KTUtil.getCssVariableValue('--bs-primary'))        
                    }
                },
                {
                    country: "France",
                    visits: 214,
                    icon: "https://www.amcharts.com/wp-content/uploads/flags/france.svg",
                    columnSettings: { 
                        fill: am5.color(KTUtil.getCssVariableValue('--bs-primary'))        
                    }
                },
                {
                    country: "India",
                    visits: 204,
                    icon: "https://www.amcharts.com/wp-content/uploads/flags/india.svg",
                    columnSettings: { 
                        fill: am5.color(KTUtil.getCssVariableValue('--bs-primary')),        
                    }
                },
                {
                    country: "Spain",
                    visits: 200,
                    icon: "https://www.amcharts.com/wp-content/uploads/flags/spain.svg",
                    columnSettings: { 
                        fill: am5.color(KTUtil.getCssVariableValue('--bs-primary'))        
                    }
                },
                {
                    country: "Italy",
                    visits: 165,
                    icon: "https://www.amcharts.com/wp-content/uploads/flags/italy.svg",
                    columnSettings: { 
                        fill: am5.color(KTUtil.getCssVariableValue('--bs-primary'))        
                    }
                },
                {
                    country: "Russia",
                    visits: 152,
                    icon: "https://www.amcharts.com/wp-content/uploads/flags/russia.svg",
                    columnSettings: { 
                        fill: am5.color(KTUtil.getCssVariableValue('--bs-primary'))        
                    }
                },
                {
                    country: "Norway",
                    visits: 125,
                    icon: "https://www.amcharts.com/wp-content/uploads/flags/norway.svg",
                    columnSettings: { 
                        fill: am5.color(KTUtil.getCssVariableValue('--bs-primary'))        
                    }
                },
                {
                    country: "Canada",
                    visits: 99,
                    icon: "https://www.amcharts.com/wp-content/uploads/flags/canada.svg",
                   columnSettings: { 
                        fill: am5.color(KTUtil.getCssVariableValue('--bs-primary'))        
                    }
                },
            ];

            // Create axes
            // https://www.amcharts.com/docs/v5/charts/xy-chart/axes/
            var xAxis = chart.xAxes.push(
                am5xy.CategoryAxis.new(root, {
                    categoryField: "country",
                    renderer: am5xy.AxisRendererX.new(root, {
                        minGridDistance: 30,
                    }),
                    bullet: function (root, axis, dataItem) {
                        return am5xy.AxisBullet.new(root, {
                            location: 0.5,
                            sprite: am5.Picture.new(root, {
                                width: 24,
                                height: 24,
                                centerY: am5.p50,
                                centerX: am5.p50,
                                src: dataItem.dataContext.icon,
                            }),
                        });
                    },
                })
            );

            xAxis.get("renderer").labels.template.setAll({
                paddingTop: 20,                
                fontWeight: "400",
                fontSize: 10,
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

            yAxis.get("renderer").grid.template.setAll({
                stroke: am5.color(KTUtil.getCssVariableValue('--bs-gray-300')),
                strokeWidth: 1,
                strokeOpacity: 1,
                strokeDasharray: [3]
            });

            yAxis.get("renderer").labels.template.setAll({
                fontWeight: "400",
                fontSize: 10,
                fill: am5.color(KTUtil.getCssVariableValue('--bs-gray-500'))
            });

            // Add series
            // https://www.amcharts.com/docs/v5/charts/xy-chart/series/
            var series = chart.series.push(
                am5xy.ColumnSeries.new(root, {
                    xAxis: xAxis,
                    yAxis: yAxis,
                    valueYField: "visits",
                    categoryXField: "country"
                })
            );

            series.columns.template.setAll({
                tooltipText: "{categoryX}: {valueY}",
                tooltipY: 0,
                strokeOpacity: 0,
                templateField: "columnSettings",
            });

            series.columns.template.setAll({
                strokeOpacity: 0,
                cornerRadiusBR: 0,
                cornerRadiusTR: 6,
                cornerRadiusBL: 0,
                cornerRadiusTL: 6,
            });

            series.data.setAll(data);

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
    module.exports = KTChartsWidget15;
}

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTChartsWidget15.init();
});
