"use strict";

// Class definition
var KTChartsWidget33 = function () {
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
    var initChart = function(chart, toggle, chartSelector, data, labels, initByDefault) {
        var element = document.querySelector(chartSelector);

        if (!element) {
            return;
        }
        
        var color = element.getAttribute('data-kt-chart-color');
        var height = parseInt(KTUtil.css(element, 'height'));
        
        var labelColor = KTUtil.getCssVariableValue('--bs-gray-500');
        var borderColor = KTUtil.getCssVariableValue('--bs-border-dashed-color');
        var baseColor = KTUtil.getCssVariableValue('--bs-' + color);    

        var options = {
            series: [{
                name: 'Etherium ',
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
                    opacityTo: 0.2,
                    stops: [15, 120, 100]
                }
            },
            stroke: {
                curve: 'smooth',
                show: true,
                width: 3,
                colors: [baseColor]
            },
            xaxis: {
                categories: labels,
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                offsetX: 20,
                tickAmount: 4,
                labels: {
                    rotate: 0,
                    rotateAlways: false,
                    show: false,
                    style: {
                        colors: labelColor,
                        fontSize: '12px'                       
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
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                tickAmount: 4,
                max: 4000,
                min: 1000,
                labels: {
                    show: false                    
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
                        return val + '$';
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
            var chart1Data = [2100, 3200, 3200, 2400, 2400, 1800, 1800, 2400, 2400, 3200, 3200, 3000, 3000, 3250, 3250];
            var chart1Labels = ['10AM', '10.30AM', '11AM', '11.15AM', '11.30AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM', '8PM', '9PM'];

            initChart(chart1, '#kt_charts_widget_33_tab_1', '#kt_charts_widget_33_chart_1', chart1Data, chart1Labels, true);

            var chart2Data = [2300, 2300, 2000, 3200, 3200, 2800, 2400, 2400, 3100, 2900, 3100, 3100, 2600, 2600, 3200];
            var chart2Labels = ['Apr 01', 'Apr 02', 'Apr 03', 'Apr 04', 'Apr 05', 'Apr 06', 'Apr 07', 'Apr 08', 'Apr 09', 'Apr 10', 'Apr 11', 'Apr 12', 'Apr 13', 'Apr 14', 'Apr 15'];

            initChart(chart2, '#kt_charts_widget_33_tab_2', '#kt_charts_widget_33_chart_2', chart2Data, chart2Labels, false);

            var chart3Data = [2600, 3200, 2300, 2300, 2000, 3200, 3100, 2900, 3200, 3200, 2600, 3100, 2800, 2400, 2400];
            var chart3Labels = ['Apr 02', 'Apr 03', 'Apr 04', 'Apr 05', 'Apr 06', 'Apr 09', 'Apr 10', 'Apr 12', 'Apr 14', 'Apr 17', 'Apr 18', 'Apr 18', 'Apr 20', 'Apr 22', 'Apr 24'];

            initChart(chart3, '#kt_charts_widget_33_tab_3', '#kt_charts_widget_33_chart_3', chart3Data, chart3Labels, false);

            var chart4Data =  [1800, 1800, 2400, 2400, 3200, 3200, 3000, 2100, 3200, 3300, 2400, 2400, 3000, 3200, 3100];
            var chart4Labels = ['Jun 2021', 'Jul 2021', 'Aug 2021', 'Sep 2021', 'Oct 2021', 'Nov 2021', 'Dec 2021', 'Jan 2022', 'Feb 2022', 'Mar 2022', 'Apr 2022', 'May 2022', 'Jun 2022', 'Jul 2022', 'Aug 2022'];

            initChart(chart4, '#kt_charts_widget_33_tab_4', '#kt_charts_widget_33_chart_4', chart4Data, chart4Labels, false);                
            
            var chart5Data = [3000, 2100, 3300, 3100, 1800, 1800, 2400, 2400, 3100, 3100, 2400, 2400, 3000, 2400, 2800];
            var chart5Labels = ['Sep 2021', 'Oct 2021', 'Nov 2021', 'Dec 2021', 'Jan 2022', 'Feb 2022', 'Mar 2022', 'Apr 2022', 'May 2022', 'Jun 2022', 'Jul 2022', 'Aug 2022', 'Sep 2022', 'Oct 2022', 'Nov 2022'];

            initChart(chart5, '#kt_charts_widget_33_tab_5', '#kt_charts_widget_33_chart_5', chart5Data, chart5Labels, false);

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

                initChart(chart1, '#kt_charts_widget_33_tab_1', '#kt_charts_widget_33_chart_1', chart1Data, chart1Labels, chart1.rendered);
                initChart(chart2, '#kt_charts_widget_33_tab_2', '#kt_charts_widget_33_chart_2', chart2Data, chart2Labels, chart2.rendered);  
                initChart(chart3, '#kt_charts_widget_33_tab_3', '#kt_charts_widget_33_chart_3', chart3Data, chart3Labels, chart3.rendered);
                initChart(chart4, '#kt_charts_widget_33_tab_4', '#kt_charts_widget_33_chart_4', chart4Data, chart4Labels, chart4.rendered); 
                initChart(chart5, '#kt_charts_widget_33_tab_5', '#kt_charts_widget_33_chart_5', chart5Data, chart5Labels, chart5.rendered); 
            });
        }  
    }
}();

// Webpack support
if (typeof module !== 'undefined') {
    module.exports = KTChartsWidget33;
}

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTChartsWidget33.init();
});


 