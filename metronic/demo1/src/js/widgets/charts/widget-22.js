"use strict";

// Class definition
var KTChartsWidget22 = function () {
    // Private methods
    var initChart = function(tabSelector, chartSelector, data, initByDefault) {
        var element = document.querySelector(chartSelector);        

        if (!element) {
            return;
        }  
          
        var height = parseInt(KTUtil.css(element, 'height'));
        
        var options = {
            series: data,                 
            chart: {           
                fontFamily: 'inherit', 
                type: 'donut',
                width: 250,
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '50%',
                        labels: {
                            value: {
                                fontSize: '10px'
                            }
                        }                        
                    }
                }
            },
            colors: [
                KTUtil.getCssVariableValue('--bs-info'), 
                KTUtil.getCssVariableValue('--bs-success'), 
                KTUtil.getCssVariableValue('--bs-primary'), 
                KTUtil.getCssVariableValue('--bs-danger') 
            ],           
            stroke: {
              width: 0
            },
            labels: ['Sales', 'Sales', 'Sales', 'Sales'],
            legend: {
                show: false,
            },
            fill: {
                type: 'false',          
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
    }

    // Public methods
    return {
        init: function () {           
            initChart('#kt_chart_widgets_22_tab_1', '#kt_chart_widgets_22_chart_1', [20, 100, 15, 25], true);
            initChart('#kt_chart_widgets_22_tab_2', '#kt_chart_widgets_22_chart_2', [70, 13, 11, 2], false);              
        }   
    }
}();

// Webpack support
if (typeof module !== 'undefined') {
    module.exports = KTChartsWidget22;
}

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTChartsWidget22.init();
});