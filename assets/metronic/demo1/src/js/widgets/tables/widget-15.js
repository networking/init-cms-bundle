"use strict";

// Class definition
var KTTablesWidget15 = function () {
    var chart1 = {
        self: null,
        rendered: false
    };

    var chart2 = {
        self: null,
        rendered: false
    };

    var chart3 = {
        self: null,
        rendered: false
    };

    var chart4 = {
        self: null,
        rendered: false
    };

    var chart5 = {
        self: null,
        rendered: false
    };

    // Private methods
    var initChart = function(chart, chartSelector, data, initByDefault) {
        var element = document.querySelector(chartSelector);

        if (!element) {
            return;
        }
        
        var height = parseInt(KTUtil.css(element, 'height'));
        var color = element.getAttribute('data-kt-chart-color');
         
        var strokeColor = KTUtil.getCssVariableValue('--bs-' + 'gray-300');
        var baseColor = KTUtil.getCssVariableValue('--bs-' + color);
        var lightColor = KTUtil.getCssVariableValue('--bs-body-bg');

        var options = {
            series: [{
                name: 'Net Profit',
                data: data
            }],
            chart: {
                fontFamily: 'inherit',
                type: 'area',
                height: height,
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: false
                },
                sparkline: {
                    enabled: true
                }
            },
            plotOptions: {},
            legend: {
                show: false
            },
            dataLabels: {
                enabled: false
            },
            fill: {
                type: 'solid',
                opacity: 1
            },
            stroke: {
                curve: 'smooth',
                show: true,
                width: 2,
                colors: [baseColor]
            },
            xaxis: {                
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    show: false                    
                },
                crosshairs: {
                    show: false,
                    position: 'front',
                    stroke: {
                        color: strokeColor,
                        width: 1,
                        dashArray: 3
                    }
                },
                tooltip: {
                    enabled: false
                }
            },
            yaxis: {
                min: 0,
                max: 60,
                labels: {
                    show: false,
                    ontSize: '12px'                   
                }
            },
            states: {
                normal: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                hover: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'none',
                        value: 0
                    }
                }
            },
            tooltip: {
                enabled: false
            },
            colors: [lightColor],
            markers: {
                colors: [lightColor],
                strokeColor: [baseColor],
                strokeWidth: 3
            }
        };

        chart.self = new ApexCharts(element, options);          
        
        if (initByDefault === true) {
            // Set timeout to properly get the parent elements width
            setTimeout(function() {
                chart.self.render();  
                chart.rendered = true;
            }, 200);
        }              
    }

    // Public methods
    return {
        init: function () { 
            var chart1Data = [7, 10, 5, 21, 6, 11, 5, 23, 5, 11, 18, 7, 21,13];            
            initChart(chart1, '#kt_table_widget_15_chart_1', chart1Data, true);

            var chart2Data = [17, 5, 23, 2, 21, 9, 17, 23, 4, 24, 9, 17, 21,7];            
            initChart(chart2, '#kt_table_widget_15_chart_2', chart2Data, true);

            var chart3Data = [2, 24, 5, 17, 7, 2, 12, 24, 5, 24, 2, 8, 12,7];            
            initChart(chart3, '#kt_table_widget_15_chart_3', chart3Data, true);

            var chart4Data = [24, 3, 5, 19, 3, 7, 25, 14, 5, 14, 2, 8, 5,17];            
            initChart(chart4, '#kt_table_widget_15_chart_4', chart4Data, true);

            var chart5Data = [3, 23, 1, 19, 3, 17, 3, 9, 25, 4, 2, 18, 25,3];            
            initChart(chart5, '#kt_table_widget_15_chart_5', chart5Data, true);
            
            // Update chart on theme mode change
            KTThemeMode.on("kt.thememode.change", function() {
                if (chart1.rendered) {
                    chart1.self.destroy();
                }

                if (chart2.rendered) {
                    chart2.self.destroy();
                }

                if (chart3.rendered) {
                    chart3.self.destroy();
                }

                if (chart4.rendered) {
                    chart4.self.destroy();
                }

                if (chart5.rendered) {
                    chart5.self.destroy();
                }

                initChart(chart1, '#kt_table_widget_15_chart_1', chart1Data, chart1.rendered);
                initChart(chart2, '#kt_table_widget_15_chart_2', chart2Data, chart2.rendered);  
                initChart(chart3, '#kt_table_widget_15_chart_3', chart3Data, chart3.rendered);
                initChart(chart4, '#kt_table_widget_15_chart_4', chart4Data, chart4.rendered); 
                initChart(chart5, '#kt_table_widget_15_chart_5', chart5Data, chart5.rendered); 
            });
        }   
    }
}();

// Webpack support
if (typeof module !== 'undefined') {
    module.exports = KTTablesWidget15;
}

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTTablesWidget15.init();
});


 