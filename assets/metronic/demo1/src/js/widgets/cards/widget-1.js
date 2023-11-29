"use strict";

// Class definition
var KTCardsWidget1 = function () {
    // Private methods
    var initChart = function() {
        var element = document.getElementById("kt_card_widget_1_chart");
        
        if (!element) {
            return;
        }

        var color = element.getAttribute('data-kt-chart-color');
        
        var height = parseInt(KTUtil.css(element, 'height'));
        var labelColor = KTUtil.getCssVariableValue('--bs-gray-500');         
        var baseColor = KTUtil.isHexColor(color) ? color : KTUtil.getCssVariableValue('--bs-' + color);
        var secondaryColor = KTUtil.getCssVariableValue('--bs-gray-300');        

        var options = {
            series: [{
                name: 'Sales',
                data: [30, 75, 55, 45, 30, 60, 75, 50],
                margin: {
					left: 5,
					right: 5
				}   
            }],
            chart: {
                fontFamily: 'inherit',
                type: 'bar',
                height: height,
                toolbar: {
                    show: false
                },
                sparkline: {
                    enabled: true
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: ['35%'],
                    borderRadius: 6
                }
            },
            legend: {
                show: false
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 4,
                colors: ['transparent']
            },
            xaxis: {                
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    show: false,
                    style: {
                        colors: labelColor,
                        fontSize: '12px'
                    }
                },               
                crosshairs: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    show: false,
                    style: {
                        colors: labelColor,
                        fontSize: '12px'
                    }
                }
            },
            fill: {
                type: 'solid'
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
                x: {
                    formatter: function (val) {
                        return "Feb: " + val
                    }
                },
                y: {
                    formatter: function (val) {
                        return val + "%"  
                    }
                }
            },
            colors: [baseColor, secondaryColor],
            grid: {
                borderColor: false,
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                },
                padding: {
                    top: 10,
					left: 25,
					right: 25     
				}               
            }
        };

        // Set timeout to properly get the parent elements width
        var chart = new ApexCharts(element, options);
        
        // Set timeout to properly get the parent elements width
        setTimeout(function() {
            chart.render();   
        }, 300);  
    }

    // Public methods
    return {
        init: function () {
            initChart();
        }   
    }
}();

// Webpack support
if (typeof module !== 'undefined') {
    module.exports = KTCardsWidget1;
}

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTCardsWidget1.init();
});
   
        
        
        
           