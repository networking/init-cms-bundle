"use strict";

// Class definition
var KTChartMap = function () {
    // Charts widgets
    var initChartMap = function() {
        var element = document.getElementById("chartdiv");

        if ( !element ) {
            return;
        } 
        
        // Create root and chart
var root = am5.Root.new("chartdiv"); 

// Set themes
root.setThemes([
  am5themes_Animated.new(root)
]);

var chart = root.container.children.push(
  am5map.MapChart.new(root, {
    panX: "rotateX",
    projection: am5map.geoNaturalEarth1()
  })
);

// Create polygon series
var polygonSeries = chart.series.push(
  am5map.MapPolygonSeries.new(root, {
    geoJSON: am5geodata_continentsLow,
    exclude: ["antarctica"]
  })
);

polygonSeries.mapPolygons.template.setAll({
  tooltipText: "{name}",
  interactive: true,
  templateField: "settings"
});

polygonSeries.mapPolygons.template.states.create("hover", {
  fill: am5.color(0x677935)
});

var colors = am5.ColorSet.new(root, {});

polygonSeries.data.setAll([{
  id: "europe",
  settings: {
    fill: colors.next(),
    fillPattern: am5.LinePattern.new(root, {
      color: am5.color(0xffffff),
      rotation: 45,
      strokeWidth: 1
    })
  }
}, {
  id: "asia",
  settings: {
    fill: colors.next(),
    fillPattern: am5.RectanglePattern.new(root, {
      color: am5.color(0xffffff),
      checkered: true
    })
  }
}, {
  id: "africa",
  settings: {
    fill: colors.next(),
    fillPattern: am5.CirclePattern.new(root, {
      color: am5.color(0xffffff),
      checkered: true
    })
  }
}, {
  id: "northAmerica",
  settings: {
    fill: colors.next(),
    fillPattern: am5.CirclePattern.new(root, {
      color: am5.color(0xffffff)
    })
  }
}, {
  id: "southAmerica",
  settings: {
    fill: colors.next(),
    fillPattern: am5.LinePattern.new(root, {
      color: am5.color(0xffffff),
      rotation: 90,
      strokeWidth: 2
    })
  }
}, {
  id: "oceania",
  settings: {
    fill: colors.next(),
    fillPattern: am5.LinePattern.new(root, {
      color: am5.color(0xffffff),
    })
  }
}])


        // Init chart
        initChart();

        // Update chart on theme mode change
        KTThemeMode.on("kt.thememode.change", function() {                
            if (chart.rendered) {
                chart.self.destroy();
            }

            initChart();
        });              
    }   
     
 

    // Public methods
    return {
        init: function () {            
            // Charts widgets
            initChartMap();              
        }   
    }
}();

// Webpack support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = KTChartMap;
}

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTChartMap.init();
});
