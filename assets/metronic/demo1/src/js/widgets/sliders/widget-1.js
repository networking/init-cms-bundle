"use strict";

// Class definition
var KTSlidersWidget1 = function() {
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
    var initChart = function(chart, query, data) {
        var element = document.querySelector(query);

        if ( !element) {
            return;
        }              
        
        if ( chart.rendered === true && element.classList.contains("initialized") ) {
            return;
        }

        var height = parseInt(KTUtil.css(element, 'height'));
        var baseColor = KTUtil.getCssVariableValue('--bs-' + 'primary');
        var lightColor = KTUtil.getCssVariableValue('--bs-' + 'primary-light' );         

        var options = {
            series: [data],
            chart: {
                fontFamily: 'inherit',
                height: height,
                type: 'radialBar',
                sparkline: {
                    enabled: true,
                }
            },
            plotOptions: {
                radialBar: {
                    hollow: {
                        margin: 0,
                        size: "45%"
                    },
                    dataLabels: {
                        showOn: "always",
                        name: {
                            show: false                                 
                        },
                        value: {                                 
                            show: false                              
                        }
                    },
                    track: {
                        background: lightColor,
                        strokeWidth: '100%'
                    }
                }
            },
            colors: [baseColor],
            stroke: {
                lineCap: "round",
            },
            labels: ["Progress"]
        };

        chart.self = new ApexCharts(element, options);
        chart.self.render();
        chart.rendered = true;

        element.classList.add('initialized');
    }

    // Public methods
    return {
        init: function () {
            // Init default chart
            initChart(chart1, '#kt_slider_widget_1_chart_1', 76);

            var carousel = document.querySelector('#kt_sliders_widget_1_slider');
            
            if ( !carousel ) {
                return;
            }

            // Init slide charts
            carousel.addEventListener('slid.bs.carousel', function (e) {
                if (e.to === 1) {
                    // Init second chart
                    initChart(chart2, '#kt_slider_widget_1_chart_2', 55);
                }

                if (e.to === 2) {
                    // Init third chart
                    initChart(chart3, '#kt_slider_widget_1_chart_3', 25);
                }
            });

            // Update chart on theme mode change
            KTThemeMode.on("kt.thememode.change", function() {                
                if (chart1.rendered) {
                    chart1.self.destroy();
                    chart1.rendered = false;
                }

                if (chart2.rendered) {
                    chart2.self.destroy();
                    chart2.rendered = false;
                }

                if (chart3.rendered) {
                    chart3.self.destroy();
                    chart3.rendered = false;
                }

                initChart(chart1, '#kt_slider_widget_1_chart_1', 76);
                initChart(chart2, '#kt_slider_widget_1_chart_2', 55);
                initChart(chart3, '#kt_slider_widget_1_chart_3', 25);
            });
        }   
    }        
}();


// Webpack support
if (typeof module !== 'undefined') {
    module.exports = KTSlidersWidget1;
}

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTSlidersWidget1.init();
});
   
        
        
        
           