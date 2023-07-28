"use strict";

// Class definition
var KTChartsWidget10 = function () {
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

    // Private methods
    var initChart = function(chart, toggle, chartSelector, data, initByDefault) {
        var element = document.querySelector(chartSelector);

        if (!element) {
            return;
        }
        
        var height = parseInt(KTUtil.css(element, 'height'));
        var labelColor = KTUtil.getCssVariableValue('--bs-gray-900');
        var borderColor = KTUtil.getCssVariableValue('--bs-border-dashed-color');    

        var options = {
            series: [{
                name: 'Achieved Target',
                data: data
            }],
            chart: {
                fontFamily: 'inherit',
                type: 'bar',
                height: height,
                toolbar: {
                    show: false
                }              
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: ['22%'],
                    borderRadius: 5,                     
                    dataLabels: {
                        position: "top" // top, center, bottom
                    },
                    startingShape: 'flat'
                },
            },
            legend: {
                show: false
            },
            dataLabels: {
                enabled: true, 
                offsetY: -30,                                             
                style: {
                    fontSize: '13px',
                    colors: [labelColor]
                },
                formatter: function(val) {
                    return val + "K";
                }          
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: ['Metals', 'Energy', 'Agro', 'Machines', 'Transport', 'Textile', 'Wood'],
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: KTUtil.getCssVariableValue('--bs-gray-500'),
                        fontSize: '13px'
                    }                    
                },
                crosshairs: {
                    fill: {         
                        gradient: {         
                            opacityFrom: 0,
                            opacityTo: 0
                        }
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: KTUtil.getCssVariableValue('--bs-gray-500'),
                        fontSize: '13px'
                    },
                    formatter: function (val) {
                        return parseInt(val) + "K"
                    }
                }
            },
            fill: {
                opacity: 1
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
                        return + val + "K"
                    }
                }
            },
            colors: [KTUtil.getCssVariableValue('--bs-primary'), KTUtil.getCssVariableValue('--bs-primary-light')],
            grid: {
                borderColor: borderColor,
                strokeDashArray: 4,
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
            var chart1Data = [30, 18, 43, 70, 13, 37, 23];
            initChart(chart1, '#kt_charts_widget_10_tab_1', '#kt_charts_widget_10_chart_1', chart1Data, true);

            var chart2Data = [25, 55, 35, 50, 45, 20, 31];
            initChart(chart2, '#kt_charts_widget_10_tab_2', '#kt_charts_widget_10_chart_2', chart2Data, false);

            var chart3Data = [45, 15, 35, 70, 45, 50, 21];
            initChart(chart3, '#kt_charts_widget_10_tab_3', '#kt_charts_widget_10_chart_3', chart3Data, false);

            var chart4Data = [15, 55, 25, 50, 25, 60, 31];
            initChart(chart4, '#kt_charts_widget_10_tab_4', '#kt_charts_widget_10_chart_4', chart4Data, false);


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
                
                initChart(chart1, '#kt_charts_widget_10_tab_1', '#kt_charts_widget_10_chart_1', chart1Data, chart1.rendered);
                initChart(chart2, '#kt_charts_widget_10_tab_2', '#kt_charts_widget_10_chart_2', chart2Data, chart2.rendered);  
                initChart(chart3, '#kt_charts_widget_10_tab_3', '#kt_charts_widget_10_chart_3', chart3Data, chart3.rendered);
                initChart(chart4, '#kt_charts_widget_10_tab_4', '#kt_charts_widget_10_chart_4', chart4Data, chart4.rendered);                  
            });      
        }   
    }
}();

// Webpack support
if (typeof module !== 'undefined') {
    module.exports = KTChartsWidget10;
}

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTChartsWidget10.init();
});


 