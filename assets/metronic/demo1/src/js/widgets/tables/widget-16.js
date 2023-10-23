"use strict";

// Class definition
var KTTablesWidget16 = function () {
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

    var chart6 = {
        self: null,
        rendered: false
    }; 

    var chart7 = {
        self: null,
        rendered: false
    };

    var chart8 = {
        self: null,
        rendered: false
    };

    var chart9 = {
        self: null,
        rendered: false
    };

    var chart10 = {
        self: null,
        rendered: false
    };

    var chart11 = {
        self: null,
        rendered: false
    }; 

    var chart12 = {
        self: null,
        rendered: false
    };

    var chart13 = {
        self: null,
        rendered: false
    };

    var chart14 = {
        self: null,
        rendered: false
    };

    var chart15 = {
        self: null,
        rendered: false
    };

    var chart16 = {
        self: null,
        rendered: false
    }; 

    var chart17 = {
        self: null,
        rendered: false
    };

    var chart18 = {
        self: null,
        rendered: false
    };

    var chart19 = {
        self: null,
        rendered: false
    };

    var chart20 = {
        self: null,
        rendered: false
    };

    // Private methods
    var initChart = function(chart, toggle, selector, data, initByDefault) {
        var element = document.querySelector(selector);

        if ( !element ) {
            return;
        }
        
        var height = parseInt(KTUtil.css(element, 'height'));
        var color = element.getAttribute('data-kt-chart-color');
        var labelColor = KTUtil.getCssVariableValue('--bs-gray-800');
        var strokeColor = KTUtil.getCssVariableValue('--bs-gray-300');
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
            var chart1Data = [16, 10, 15, 21, 6, 11, 5, 23, 5, 11, 18, 7, 21, 13];  
            initChart(chart1, '#kt_stats_widget_16_tab_link_1', '#kt_table_widget_16_chart_1_1', chart1Data, true);        
             
            var chart2Data = [8, 5, 16, 3, 23, 16, 11, 15, 3, 11, 15, 7, 17, 9];  
            initChart(chart2, '#kt_stats_widget_16_tab_link_1', '#kt_table_widget_16_chart_1_2', chart2Data, true);    
            
            var chart3Data = [8, 6, 16, 3, 23, 16, 11, 14, 3, 11, 15, 8, 17, 9];  
            initChart(chart3, '#kt_stats_widget_16_tab_link_1', '#kt_table_widget_16_chart_1_3', chart3Data, true);  
            
            var chart4Data = [12, 5, 23, 12, 21, 9, 17, 20, 4, 24, 9, 13, 18, 9];  
            initChart(chart4, '#kt_stats_widget_16_tab_link_1', '#kt_table_widget_16_chart_1_4', chart4Data, true); 


            var chart5Data = [13, 10, 15, 21, 6, 11, 5, 21, 5, 12, 18, 7, 21, 13];  
            initChart(chart5, '#kt_stats_widget_16_tab_link_2', '#kt_table_widget_16_chart_2_1', chart5Data, false);        
             
            var chart6Data = [13, 5, 21, 12, 21, 9, 17, 20, 4, 23, 9, 17, 21, 7];  
            initChart(chart6, '#kt_stats_widget_16_tab_link_2', '#kt_table_widget_16_chart_2_2', chart6Data, false);    
            
            var chart7Data = [8, 10, 14, 21, 6, 31, 5, 21, 5, 11, 15, 7, 23, 13];  
            initChart(chart7, '#kt_stats_widget_16_tab_link_2', '#kt_table_widget_16_chart_2_3', chart7Data, false);  
            
            var chart8Data = [6, 10, 12, 21, 6, 11, 7, 23, 5, 12, 18, 7, 21, 15];  
            initChart(chart8, '#kt_stats_widget_16_tab_link_2', '#kt_table_widget_16_chart_2_4', chart8Data, false); 


            var chart9Data = [7, 10, 5, 21, 6, 11, 5, 23, 5, 11, 18, 7, 21,13];  
            initChart(chart9, '#kt_stats_widget_16_tab_link_3', '#kt_table_widget_16_chart_3_1', chart9Data, false);        
             
            var chart10Data = [8, 5, 16, 2, 19, 9, 17, 21, 4, 24, 4, 13, 21,5];  
            initChart(chart10, '#kt_stats_widget_16_tab_link_3', '#kt_table_widget_16_chart_3_2', chart10Data, false);    
            
            var chart11Data = [15, 10, 12, 21, 6, 11, 23, 11, 5, 12, 18, 7, 21, 15];  
            initChart(chart11, '#kt_stats_widget_16_tab_link_3', '#kt_table_widget_16_chart_3_3', chart11Data, false);  
            
            var chart12Data = [3, 9, 12, 23, 6, 11, 7, 23, 5, 12, 14, 7, 21, 8];  
            initChart(chart12, '#kt_stats_widget_16_tab_link_3', '#kt_table_widget_16_chart_3_4', chart12Data, false);


            var chart13Data = [9, 14, 15, 21, 8, 11, 5, 23, 5, 11, 18, 5, 23, 8];  
            initChart(chart13, '#kt_stats_widget_16_tab_link_4', '#kt_table_widget_16_chart_4_1', chart13Data, false);        
             
            var chart14Data = [7, 5, 23, 12, 21, 9, 17, 15, 4, 24, 9, 17, 21, 7];  
            initChart(chart14, '#kt_stats_widget_16_tab_link_4', '#kt_table_widget_16_chart_4_2', chart14Data, false);    
            
            var chart15Data = [8, 10, 14, 21, 6, 31, 8, 23, 5, 3, 14, 7, 21, 12];  
            initChart(chart15, '#kt_stats_widget_16_tab_link_4', '#kt_table_widget_16_chart_4_3', chart15Data, false);  
            
            var chart16Data = [6, 12, 12, 19, 6, 11, 7, 23, 5, 12, 18, 7, 21, 15];  
            initChart(chart16, '#kt_stats_widget_16_tab_link_4', '#kt_table_widget_16_chart_4_4', chart16Data, false);


            var chart17Data = [5, 10, 15, 21, 6, 11, 5, 23, 5, 11, 17, 7, 21, 13];  
            initChart(chart17, '#kt_stats_widget_16_tab_link_5', '#kt_table_widget_16_chart_5_1', chart17Data, false);        
             
            var chart18Data = [4, 5, 23, 12, 21, 9, 17, 15, 4, 24, 9, 17, 21, 7];  
            initChart(chart18, '#kt_stats_widget_16_tab_link_5', '#kt_table_widget_16_chart_5_2', chart18Data, false);    
            
            var chart19Data = [7, 10, 14, 21, 6, 31, 5, 23, 5, 11, 15, 7, 21, 17];  
            initChart(chart19, '#kt_stats_widget_16_tab_link_5', '#kt_table_widget_16_chart_5_3', chart19Data, false);  
            
            var chart20Data = [3, 10, 12, 23, 6, 11, 7, 22, 5, 12, 18, 7, 21, 14];  
            initChart(chart20, '#kt_stats_widget_16_tab_link_5', '#kt_table_widget_16_chart_5_4', chart20Data, false);

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

                if (chart6.rendered) {
                    chart6.self.destroy();
                }

                if (chart7.rendered) {
                    chart7.self.destroy();
                }

                if (chart8.rendered) {
                    chart8.self.destroy();
                }

                if (chart9.rendered) {
                    chart9.self.destroy();
                }

                if (chart10.rendered) {
                    chart10.self.destroy();
                }

                if (chart11.rendered) {
                    chart11.self.destroy();
                }

                if (chart12.rendered) {
                    chart12.self.destroy();
                }

                if (chart13.rendered) {
                    chart13.self.destroy();
                }

                if (chart14.rendered) {
                    chart14.self.destroy();
                }

                if (chart15.rendered) {
                    chart15.self.destroy();
                }

                if (chart16.rendered) {
                    chart16.self.destroy();
                }

                if (chart17.rendered) {
                    chart17.self.destroy();
                }

                if (chart18.rendered) {
                    chart18.self.destroy();
                }

                if (chart19.rendered) {
                    chart19.self.destroy();
                }

                if (chart20.rendered) {
                    chart20.self.destroy();
                }                

                initChart(chart1, '#kt_stats_widget_16_tab_link_1', '#kt_table_widget_16_chart_1_1', chart1Data, chart1.rendered);
                initChart(chart2, '#kt_stats_widget_16_tab_link_1', '#kt_table_widget_16_chart_1_2', chart2Data, chart2.rendered);  
                initChart(chart3, '#kt_stats_widget_16_tab_link_1', '#kt_table_widget_16_chart_1_3', chart3Data, chart3.rendered);
                initChart(chart4, '#kt_stats_widget_16_tab_link_1', '#kt_table_widget_16_chart_1_4', chart4Data, chart4.rendered); 

                initChart(chart5, '#kt_stats_widget_16_tab_link_2', '#kt_table_widget_16_chart_2_1', chart5Data, chart5.rendered);
                initChart(chart6, '#kt_stats_widget_16_tab_link_2', '#kt_table_widget_16_chart_2_2', chart6Data, chart6.rendered);  
                initChart(chart7, '#kt_stats_widget_16_tab_link_2', '#kt_table_widget_16_chart_2_3', chart7Data, chart7.rendered);
                initChart(chart8, '#kt_stats_widget_16_tab_link_2', '#kt_table_widget_16_chart_2_4', chart8Data, chart8.rendered); 

                initChart(chart9, '#kt_stats_widget_16_tab_link_3', '#kt_table_widget_16_chart_3_1', chart9Data, chart9.rendered);
                initChart(chart10, '#kt_stats_widget_16_tab_link_3', '#kt_table_widget_16_chart_3_2', chart10Data, chart10.rendered);  
                initChart(chart11, '#kt_stats_widget_16_tab_link_3', '#kt_table_widget_16_chart_3_3', chart11Data, chart11.rendered);
                initChart(chart12, '#kt_stats_widget_16_tab_link_3', '#kt_table_widget_16_chart_3_4', chart12Data, chart12.rendered); 

                initChart(chart13, '#kt_stats_widget_16_tab_link_4', '#kt_table_widget_16_chart_4_1', chart13Data, chart13.rendered);
                initChart(chart14, '#kt_stats_widget_16_tab_link_4', '#kt_table_widget_16_chart_4_2', chart14Data, chart14.rendered);  
                initChart(chart15, '#kt_stats_widget_16_tab_link_4', '#kt_table_widget_16_chart_4_3', chart15Data, chart15.rendered);
                initChart(chart16, '#kt_stats_widget_16_tab_link_4', '#kt_table_widget_16_chart_4_4', chart16Data, chart16.rendered); 

                initChart(chart17, '#kt_stats_widget_16_tab_link_5', '#kt_table_widget_16_chart_5_1', chart17Data, chart17.rendered);
                initChart(chart18, '#kt_stats_widget_16_tab_link_5', '#kt_table_widget_16_chart_5_2', chart18Data, chart18.rendered);  
                initChart(chart19, '#kt_stats_widget_16_tab_link_5', '#kt_table_widget_16_chart_5_3', chart19Data, chart19.rendered);
                initChart(chart20, '#kt_stats_widget_16_tab_link_5', '#kt_table_widget_16_chart_5_4', chart20Data, chart20.rendered);                 
            });            
        }   
    }
}();

// Webpack support
if (typeof module !== 'undefined') {
    module.exports = KTTablesWidget16;
}

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTTablesWidget16.init();
});


 