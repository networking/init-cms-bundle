//
// Global init of core components
//

// Init components
var KTComponents = function () {
    // Public methods
    return {
        init: function () {
            KTApp.init();
			KTDrawer.init();
			KTMenu.init();
			KTScroll.init();
			KTSticky.init();
			KTSwapper.init();
			KTToggle.init();
			KTScrolltop.init();
			KTDialer.init();	
			KTImageInput.init();
			KTPasswordMeter.init();	
        }
    }	
}();

// On document ready
if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", function() {
		KTComponents.init();
	});
 } else {
	KTComponents.init();
 }

 // Init page loader
window.addEventListener("load", function() {
    KTApp.hidePageLoading();
});

// Declare KTApp for Webpack support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
	window.KTComponents = module.exports = KTComponents;
}