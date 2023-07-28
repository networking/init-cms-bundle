"use strict";

// Class definition
var KTAppSidebar = function () {
	// Private variables
	var toggle;
	var sidebar;
	var headerMenu;
	var menuDashboardsCollapse;
	var menuScroll;
	var toggle;

	// Private functions
	// Handle sidebar minimize mode toggle
	var handleToggle = function () {
	   	var toggleObj = KTToggle.getInstance(toggle);
	   	var headerMenuObj = KTMenu.getInstance(headerMenu);

		if ( toggleObj === null) {
			return;
		}

	   	// Add a class to prevent sidebar hover effect after toggle click
	   	toggleObj.on('kt.toggle.change', function() {
			// Set animation state
			sidebar.classList.add('animating');
			
			// Wait till animation finishes
			setTimeout(function() {
				// Remove animation state
				sidebar.classList.remove('animating');
			}, 300);

			// Prevent header menu dropdown display on hover
			if (headerMenuObj) {
				headerMenuObj.disable();

				// Timeout to enable header menu 
				setTimeout(function() {
					headerMenuObj.enable();
				}, 1000);
			}
	   	});

		// Store sidebar minimize state in cookie
		toggleObj.on('kt.toggle.changed', function() {
			// In server side check sidebar_minimize_state cookie 
			// value and add data-kt-app-sidebar-minimize="on" 
			// attribute to Body tag and "active" class to the toggle button
			var date = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000); // 30 days from now

			KTCookie.set("sidebar_minimize_state", toggleObj.isEnabled() ? "on" : "off", {expires: date}); 
		});
	}

	// Handle dashboards menu items collapse mode
	var handleShowMore = function() {
		menuDashboardsCollapse.addEventListener('hide.bs.collapse', event => {
			menuScroll.scrollTo({
				top: 0,
				behavior: 'instant'
			});
		});        
	}

	var handleMenuScroll = function() {
		var menuActiveItem = menuScroll.querySelector(".menu-link.active");

		if ( !menuActiveItem ) {
			return;
		} 

		if ( KTUtil.isVisibleInContainer(menuActiveItem, menuScroll) === true) {
			return;
		}

		menuScroll.scroll({
			top: KTUtil.getRelativeTopPosition(menuActiveItem, menuScroll),
			behavior: 'smooth'
		});
	}

	// Public methods
	return {
		init: function () {
			// Elements
			sidebar = document.querySelector('#kt_app_sidebar');
			toggle = document.querySelector('#kt_app_sidebar_toggle');
			headerMenu = document.querySelector('#kt_app_header_menu');
			menuDashboardsCollapse = document.querySelector('#kt_app_sidebar_menu_dashboards_collapse');
			menuScroll = document.querySelector('#kt_app_sidebar_menu_scroll');
			
			if ( sidebar === null ) {
				return;
			}

			if ( toggle ) {
				handleToggle();	
			}

			if ( menuScroll ) {
				handleMenuScroll();
			}

			if ( menuDashboardsCollapse ) {
				handleShowMore();
			}
		}
	};
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
	KTAppSidebar.init();
});