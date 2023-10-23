"use strict";

// Class definition
var KTChartsWidget27 = function () {
    var chart = {
        self: null,
        rendered: false
    };
    // Private methods
    var initChart = function(chart) {
        var element = document.getElementById("kt_charts_widget_27"); 

        if (!element) {
            return;
        }
        
        var labelColor = KTUtil.getCssVariableValue('--bs-gray-800');    
        var borderColor = KTUtil.getCssVariableValue('--bs-border-dashed-color');
        var maxValue = 18;
        
        var options = {
            series: [{
                name: 'Sessions',
                data: [12.478, 7.546, 6.083, 5.041, 4.420]                                                                                                             
            }],           
            chart: {
                fontFamily: 'inherit',
                type: 'bar',
                height: 350,
                toolbar: {
                    show: false
                }                             
            },                    
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    horizontal: true,
                    distributed: true,
                    barHeight: 50,
                    dataLabels: {
				        position: 'bottom' // use 'bottom' for left and 'top' for right align(textAnchor)
			        }                                                       
                }
            },
            dataLabels: {  // Docs: https://apexcharts.com/docs/options/datalabels/
                enabled: true,              
                textAnchor: 'start',  
                offsetX: 0,                 
                formatter: function (val, opts) {
                    var val = val * 1000;
                    var Format = wNumb({
                        //prefix: '$',
                        //suffix: ',-',
                        thousand: ','
                    });

                    return Format.to(val);
                },
                style: {
                    fontSize: '14px',
                    fontWeight: '600',
                    align: 'left',                                                            
                }                                       
            },             
            legend: {
                show: false
            },                               
            colors: ['#3E97FF', '#F1416C', '#50CD89', '#FFC700', '#7239EA'],                                                                      
            xaxis: {
                categories: ["USA", "India", 'Canada', 'Brasil', 'France'],
                labels: {
                    formatter: function (val) {
                        return val + "K"
                    },
                    style: {
                        colors: labelColor,
                        fontSize: '14px',
                        fontWeight: '600',
                        align: 'left'                                              
                    }                  
                },
                axisBorder: {
					show: false
				}                         
            },
            yaxis: {
                labels: {       
                    formatter: function (val, opt) {
                        if (Number.isInteger(val)) {
                            var percentage = parseInt(val * 100 / maxValue) . toString(); 
                            return val + ' - ' + percentage + '%';
                        } else {
                            return val;
                        }
                    },            
                    style: {
                        colors: labelColor,
                        fontSize: '14px',
                        fontWeight: '600'                                                                 
                    },
                    offsetY: 2,
                    align: 'left' 
                }           
            },
            grid: {                
                borderColor: borderColor,                
                xaxis: {
                    lines: {
                        show: true
                    }
                },   
                yaxis: {
                    lines: {
                        show: false  
                    }
                },
                strokeDashArray: 4              
            },
            tooltip: {
                style: {
                    fontSize: '12px'
                },
                y: {
                    formatter: function (val) {
                        return val;
                    }
                }
            }                                 
        };  
          
        chart.self = new ApexCharts(element, options);

        // Set timeout to properly get the parent elements width
        setTimeout(function() {
            chart.self.render();
            chart.rendered = true;
        }, 200);        
    }

    // Public methods
    return {
        init: function () {
            initChart(chart);

            // Update chart on theme mode change
            KTThemeMode.on("kt.thememode.change", function() {                
                if (chart.rendered) {
                    chart.self.destroy();
                }

                initChart(chart);
            });
        }   
    }
}();

// Webpack support
if (typeof module !== 'undefined') {
    module.exports = KTChartsWidget27;
}

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTChartsWidget27.init();
});


 