"use strict";

// Class definition
var KTChartsWidget7 = function () {
    // Private methods
    var initChart = function(chartSelector) {
        var element = document.querySelector(chartSelector);

        if (!element) {
            return;
        }

        var height = parseInt(KTUtil.css(element, 'height'));
        var borderColor = KTUtil.getCssVariableValue('--bs-border-dashed-color');

        var options = {
            series: [{
                name: 'Net Profit',
                data: data1
            }, {
                name: 'Revenue',
                data: data2
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
                    columnWidth: ['40%'],
                    borderRadius: [6]
                },
            },
            legend: {
                show: false
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: KTUtil.getCssVariableValue('--bs-gray-700'),
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: KTUtil.getCssVariableValue('--bs-gray-700'),
                        fontSize: '12px'
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
                        return "$" + val + " thousands"
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

        var chart = new ApexCharts(element, options);

        var init = false;
        var tab = document.querySelector(tabSelector);
        
        if (initByDefault === true) {
            chart.render();
            init = true;
        }        

        tab.addEventListener('shown.bs.tab', function (event) {
            if (init == false) {
                chart.render();
                init = true;
            }
        })
          
        var chart = new ApexCharts(element, options);
        chart.render();   
    }

    // Public methods
    return {
        init: function () {          
            initChart('#kt_chart_widget_7_tab_1', '#kt_chart_widget_7_chart_1', [44, 55, 57, 56, 61, 58], [76, 85, 101, 98, 87, 105], true);
            initChart('#kt_chart_widget_7_tab_2', '#kt_chart_widget_7_chart_2', [35, 60, 35, 50, 45, 30], [65, 80, 50, 80, 75, 105], false);
            initChart('#kt_chart_widget_7_tab_3', '#kt_chart_widget_7_chart_3', [25, 40, 45, 50, 40, 60], [76, 85, 101, 98, 87, 105], false);
            initChart('#kt_chart_widget_7_tab_4', '#kt_chart_widget_7_chart_4', [50, 35, 45, 55, 30, 40], [76, 85, 101, 98, 87, 105], false);             
        }   
    }
}();

// Webpack support
if (typeof module !== 'undefined') {
    module.exports = KTChartsWidget7;
}

// On document ready
KTUtil.onDOMContentLoaded(function() {
    //KTChartsWidget7.init();
});


 