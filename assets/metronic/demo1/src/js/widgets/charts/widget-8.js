"use strict";

// Class definition
var KTChartsWidget8 = function () {
    var chart1 = {
        self: null,
        rendered: false
    };

    var chart2 = {
        self: null,
        rendered: false
    };

    // Private methods
    var initChart = function(chart, toggle, selector, data, initByDefault) {
        var element = document.querySelector(selector);

        if (!element) {
            return;
        }

        var height = parseInt(KTUtil.css(element, 'height'));    
        var borderColor = KTUtil.getCssVariableValue('--bs-border-dashed-color');    

        var options = {
            series: [
                {
                    name: 'Social Campaigns',
                    data: data[0]  // array value is of the format [x, y, z] where x (timestamp) and y are the two axes coordinates,
                }, {
                    name: 'Email Newsletter',
                    data: data[1]
                }, {
                    name: 'TV Campaign',
                    data: data[2]
                }, {
                    name: 'Google Ads',
                    data: data[3]
                }, {
                    name: 'Courses',
                    data: data[4]
                }, {
                    name: 'Radio',
                    data: data[5]
                }                
            ],
            chart: {
                fontFamily: 'inherit',
                type: 'bubble',    
                height: height,
                toolbar: {
                    show: false
                }                         
            },                                 
            plotOptions: {
                bubble: {
                }
            },
            stroke: {
                show: false,
                width: 0
            },
            legend: {
                show: false
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                type: 'numeric',             
                tickAmount: 7,
                min: 0,
                max: 700,
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: true,
                    height: 0,
                },
                labels: {
                    show: true,
                    trim: true,
                    style: {
                        colors: KTUtil.getCssVariableValue('--bs-gray-500'),
                        fontSize: '13px'
                    }
                }
            },
            yaxis: {
                tickAmount: 7,
                min: 0,
                max: 700,
                labels: {
                    style: {
                        colors: KTUtil.getCssVariableValue('--bs-gray-500'),
                        fontSize: '13px'
                    }
                }               
            },
            tooltip: {
                style: {
                    fontSize: '12px'
                },
                x: {
                    formatter: function (val) {
                        return "Clicks: " + val;
                    }
                },
                y: {
                    formatter: function (val) {
                        return "$" + val + "K"
                    }
                },
                z: {
                    title: 'Impression: '
                }
            },
            crosshairs: {
                show: true,
                position: 'front',
                stroke: {
                    color: KTUtil.getCssVariableValue('--bs-border-dashed-color'),
                    width: 1,
                    dashArray: 0,
                }
            },           
            colors: [
                KTUtil.getCssVariableValue('--bs-primary'),
                KTUtil.getCssVariableValue('--bs-success'),   
                KTUtil.getCssVariableValue('--bs-warning'),
                KTUtil.getCssVariableValue('--bs-danger'),
                KTUtil.getCssVariableValue('--bs-info'),
                '#43CED7'
            ],
            fill: {
                opacity: 1,                
            },
            markers: {
                strokeWidth: 0
            },
            grid: {
                borderColor: borderColor,
                strokeDashArray: 4,
                padding: {
                    right: 20
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                }
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
            var data1 = [
                [[100, 250, 30]], [[225, 300, 35]], [[300, 350, 25]], [[350, 350, 20]], [[450, 400, 25]], [[550, 350, 35]]
            ];

            var data2 = [
                [[125, 300, 40]], [[250, 350, 35]], [[350, 450, 30]], [[450, 250, 25]], [[500, 500, 30]], [[600, 250, 28]]
            ];

            initChart(chart1, '#kt_chart_widget_8_week_toggle', '#kt_chart_widget_8_week_chart', data1, false);
            initChart(chart2, '#kt_chart_widget_8_month_toggle', '#kt_chart_widget_8_month_chart', data2, true);    

            // Update chart on theme mode change
            var handlerId = KTThemeMode.on("kt.thememode.change", function() {
                if (chart1.rendered) {
                    chart1.self.destroy();
                }

                if (chart2.rendered) {
                    chart2.self.destroy();
                }

                initChart(chart1, '#kt_chart_widget_8_week_toggle', '#kt_chart_widget_8_week_chart', data1, chart1.rendered);
                initChart(chart2, '#kt_chart_widget_8_month_toggle', '#kt_chart_widget_8_month_chart', data2, chart2.rendered);  
            });
        }   
    }
}();

// Webpack support
if (typeof module !== 'undefined') {
    module.exports = KTChartsWidget8;
}

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTChartsWidget8.init();
});


 