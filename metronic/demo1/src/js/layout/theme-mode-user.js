"use strict";

// Class definition
var KTThemeModeUser = function () {
    
    var handleSubmit = function() {
		// Update chart on theme mode change
        KTThemeMode.on("kt.thememode.change", function() {                
            var menuMode = KTThemeMode.getMenuMode();
            var mode = KTThemeMode.getMode();
            console.log("user selected theme mode:" + menuMode);
            console.log("theme mode:" + mode);

            // Submit selected theme mode menu option via ajax and 
            // store it in user profile and set the user opted theme mode via HTML attribute
            // <html data-theme-mode="light"> .... </html>
        });
    }

    return {
        init: function () {
			handleSubmit();
        }
    };
}();

// Initialize app on document ready
KTUtil.onDOMContentLoaded(function () {
    KTThemeModeUser.init();
});

// Declare KTThemeModeUser for Webpack support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = KTThemeModeUser;
}