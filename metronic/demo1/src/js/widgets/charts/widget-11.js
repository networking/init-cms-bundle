"use strict";

// Class definition
var KTChartsWidget11 = function () {
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

    // Private methods
    var initChart = function(chart, toggle, chartSelector, data, initByDefault) {
        var element = document.querySelector(chartSelector);  
        var height = parseInt(KTUtil.css(element, 'height'));

        if (!element) {
            return;
        }        
        
        var labelColor = KTUtil.getCssVariableValue('--bs-gray-500');
        var borderColor = KTUtil.getCssVariableValue('--bs-border-dashed-color');
        var baseColor = KTUtil.getCssVariableValue('--bs-success');         

        var options = {
            series: [{
                name: 'Deliveries',
                data: data
            }],            
            chart: {
                fontFamily: 'inherit',
                type: 'area',
                height: height,
                toolbar: {
                    show: false
                }
            },             
            legend: {
                show: false
            },
            dataLabels: {
                enabled: false
            },
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0,
                    stops: [0, 80, 100]
                }
            },
            stroke: {
                curve: 'smooth',
                show: true,
                width: 3,
                colors: [baseColor]
            },
            xaxis: {
                categories: ['', 'Apr 02', 'Apr 06', 'Apr 06', 'Apr 05', 'Apr 06', 'Apr 10', 'Apr 08', 'Apr 09', 'Apr 14', 'Apr 10', 'Apr 12', 'Apr 18', 'Apr 14', 
                    'Apr 15', 'Apr 14', 'Apr 17', 'Apr 18', 'Apr 02', 'Apr 06', 'Apr 18', 'Apr 05', 'Apr 06', 'Apr 10', 'Apr 08', 'Apr 22', 'Apr 14', 'Apr 11', 'Apr 12', ''
                ],
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                tickAmount: 5,
                labels: {
                    rotate: 0,
                    rotateAlways: true,
                    style: {
                        colors: labelColor,
                        fontSize: '13px'
                    }
                },
                crosshairs: {
                    position: 'front',
                    stroke: {
                        color: baseColor,
                        width: 1,
                        dashArray: 3
                    }
                },
                tooltip: {
                    enabled: true,
                    formatter: undefined,
                    offsetY: 0,
                    style: {
                        fontSize: '13px'
                    }
                }
            },
            yaxis: {
                tickAmount: 4,
                max: 24,
                min: 10,
                labels: {
                    style: {
                        colors: labelColor,
                        fontSize: '13px'
                    }                     
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
                style: {
                    fontSize: '12px'
                },
                y: {
                    formatter: function (val) {
                        return + val  
                    }
                }
            },
            colors: [baseColor],
            grid: {
                borderColor: borderColor,
                strokeDashArray: 3,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            markers: {
                strokeColor: baseColor,
                strokeWidth: 3
            }
        };

        chart.self = new ApexCharts(element, options);        
        var tab = document.querySelector(toggle);
        
        if (initByDefault === true) {
            // Set timeout to properly get the parent elements width
            setTimeout(function() {
                chart.self.render();  
                chart.rendered = true;
            }, 200);
        }        

        tab.addEventListener('shown.bs.tab', function (event) {
            if (chart.rendered === false) {
                chart.self.render();  
                chart.rendered = true;
            }
        });
    }

    // Public methods
    return {
        init: function () { 
            var chart1Data = [16, 19, 19, 16, 16, 14, 15, 15, 17, 17, 19, 19, 18, 18, 20, 20, 18, 18, 22, 22, 20, 20, 18, 18, 20, 20, 18, 20, 20, 22];
            initChart(chart1, '#kt_charts_widget_11_tab_1', '#kt_charts_widget_11_chart_1', chart1Data, false);

            var chart2Data = [18, 18, 20, 20, 18, 18, 22, 22, 20, 20, 18, 18, 20, 20, 18, 18, 20, 20, 22, 15, 18, 18, 17, 17, 15, 15, 17, 17, 19, 17];
            initChart(chart2, '#kt_charts_widget_11_tab_2', '#kt_charts_widget_11_chart_2', chart2Data, false);

            var chart3Data = [17, 20, 20, 19, 19, 17, 17, 19, 19, 21, 21, 19, 19, 21, 21, 18, 18, 16, 17, 17, 19, 19, 21, 21, 19, 19, 17, 17, 18, 18];
            initChart(chart3, '#kt_charts_widget_11_tab_3', '#kt_charts_widget_11_chart_3', chart3Data, true);
           
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
                
                initChart(chart1, '#kt_charts_widget_11_tab_1', '#kt_charts_widget_11_chart_1', chart1Data, chart1.rendered);
                initChart(chart2, '#kt_charts_widget_11_tab_2', '#kt_charts_widget_11_chart_2', chart2Data, chart2.rendered);  
                initChart(chart3, '#kt_charts_widget_11_tab_3', '#kt_charts_widget_11_chart_3', chart3Data, chart3.rendered);                                           
            });             
        }   
    }
}();

// Webpack support
if (typeof module !== 'undefined') {
    module.exports = KTChartsWidget11;
}

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTChartsWidget11.init();
});
