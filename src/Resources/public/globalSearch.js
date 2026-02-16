/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/cms/search.js"
/*!******************************!*\
  !*** ./assets/cms/search.js ***!
  \******************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });


class SearchModule {

    constructor(element, options) {

        if(!element) {
            return;
        }
        this.options = {};
        this.element = element;
        if(options !== undefined) {
            this.options = options
        }

        this.processing = false;
        this.init();


    }

    init() {
        const defaultOptions = {
            minLength: 2,  // Miniam text lenght to query search
            keypress: true,  // Enable search on keypress
            enter: true,  // Enable search on enter key press
            layout: 'menu',  // Use 'menu' or 'inline' layout options to display search results
            responsive: null, // Pass integer value or bootstrap compatible breakpoint key(sm,md,lg,xl,xxl) to enable reponsive form mode for device width below the breakpoint value
            showOnFocus: true // Always show menu on input focus
        };
        // Variables
        this.options = KTUtil.deepExtend({}, defaultOptions, this.options);
        this.processing = false;

        // Elements
        this.contentElement = this._getElement('content');
        this.formElement = this._getElement('form');
        this.inputElement = this._getElement('input');
        this.spinnerElement = this._getElement('spinner');
        this.clearElement = this._getElement('clear');
        this.toggleElement = this._getElement('toggle');
        this.submitElement = this._getElement('submit');
        this.toolbarElement = this._getElement('toolbar');

        this.resultsElement = this._getElement('results');
        this.suggestionElement = this._getElement('suggestion');
        this.emptyElement = this._getElement('empty');

        // Set initialized
        this.element.setAttribute('data-kt-search', 'true');

        // Layout
        this.layout = this._getOption('layout');

        // // Menu
        if ( this.layout === 'menu' ) {
            this.menuObject = new KTMenu(this.contentElement);
        } else {
            this.menuObject = null;
        }

        // Update
       this._update();

        // Event Handlers
       this._handlers();

        // Bind Instance
        KTUtil.data(this.element).set('search', this);
    }

    _handlers = function() {
        // Focus
        this.inputElement.addEventListener('focus',this._focus.bind(this));

        // Blur
        this.inputElement.addEventListener('blur',this._blur.bind(this));

        // Keypress
        if (this._getOption('keypress') === true ) {
            this.inputElement.addEventListener('input',this._input.bind(this));
        }

        // Submit
        if ( this.submitElement ) {
            this.submitElement.addEventListener('click',this._search.bind(this));
        }

        // Enter
        if (this._getOption('enter') === true ) {
            this.inputElement.addEventListener('keypress',this._enter.bind(this));
        }

        // Clear
        if ( this.clearElement ) {
            this.clearElement.addEventListener('click',this._clear.bind(this));
        }

        // Menu
        if ( this.menuObject ) {
            // Toggle menu
            if ( this.toggleElement ) {
                this.toggleElement.addEventListener('click',this._show.bind(this));

                this.menuObject.on('kt.menu.dropdown.show', (item) =>{
                    if (KTUtil.visible(this.toggleElement)) {
                        this.toggleElement.classList.add('active');
                        this.toggleElement.classList.add('show');
                    }
                });

                this.menuObject.on('kt.menu.dropdown.hide', (item) => {
                    if (KTUtil.visible(this.toggleElement)) {
                        this.toggleElement.classList.remove('active');
                        this.toggleElement.classList.remove('show');
                    }
                });
            }

            this.menuObject.on('kt.menu.dropdown.shown', () => {
                this.inputElement.focus();
            });
        }

        // Window resize handling
        window.addEventListener('resize', () => {
            let timer;

            KTUtil.throttle(timer, () => {
               this._update();
            }, 200);
        });
    }

    _focus() {

        this.element.classList.add('focus');

        if (this._getOption('show-on-focus') === true || this.inputElement.value.length >= minLength ) {
           this._show();
        }
    }

    // Blur
    _blur() {
        this.element.classList.remove('focus');
    }

    // Enter
    _enter(e) {
        var key = e.charCode || e.keyCode || 0;

        if (key == 13) {
            e.preventDefault();

           this._search();
        }
    }

    _input() {
        if ( this._getOption('min-length') )  {
            var minLength = parseInt(this._getOption('min-length'));



            if ( this.inputElement.value.length >= minLength ) {

                clearTimeout(this.timer);

                this.timer = setTimeout(() => {
                    this._search();
                }, 1000);

            } else if ( this.inputElement.value.length === 0 ) {
                this._clear();
            }
        }
    }

    _search() {
        if (this.processing === false) {
            // Show search spinner
            if (this.spinnerElement) {
                this.spinnerElement.classList.remove("d-none");
            }

            // Hide search clear button
            if (this.clearElement) {
                this.clearElement.classList.add("d-none");
            }

            // Hide search toolbar
            if (this.toolbarElement && this.formElement.contains(this.toolbarElement)) {
                this.toolbarElement.classList.add("d-none");
            }

            // Focus input
            this.inputElement.focus();

            this.processing = true;
            KTEventHandler.trigger(this.element, 'kt.search.process', this);
        }
    }


    // Search

    // Complete
     _complete() {
        if (this.spinnerElement) {
            this.spinnerElement.classList.add("d-none");
        }

        // Show search toolbar
        if (this.clearElement) {
            this.clearElement.classList.remove("d-none");
        }

        if ( this.inputElement.value.length === 0 ) {
           this._clear();
        }

        // Focus input
        this.inputElement.focus();

       this._show();

        this.processing = false;
    }

    // Clear
     _clear() {
        if ( KTEventHandler.trigger(this.element, 'kt.search.clear', this) === false )  {
            return;
        }

        // Clear and focus input
        this.inputElement.value = "";
        this.inputElement.focus();

        // Hide clear icon
        if (this.clearElement) {
            this.clearElement.classList.add("d-none");
        }

        // Show search toolbar
        if (this.toolbarElement && this.formElement.contains(this.toolbarElement)) {
            this.toolbarElement.classList.remove("d-none");
        }

        // Hide menu
        if (this._getOption('show-on-focus') === false ) {
           this._hide();
        }

        KTEventHandler.trigger(this.element, 'kt.search.cleared', this);
    }

    // Update
     _update() {
        // Handle responsive form
        if (this.layout === 'menu') {
            var responsiveFormMode =this._getResponsiveFormMode();

            if ( responsiveFormMode === 'on' && this.contentElement.contains(this.formElement) === false ) {
                this.contentElement.prepend(this.formElement);
                this.formElement.classList.remove('d-none');
            } else if ( responsiveFormMode === 'off' && this.contentElement.contains(this.formElement) === true ) {
                this.element.prepend(this.formElement);
                this.formElement.classList.add('d-none');
            }
        }
    }

    // Show menu
     _show() {
        if ( this.menuObject ) {
           this._update();

            this.menuObject.show(this.element);
        }
    }

    // Hide menu
     _hide() {
        if ( this.menuObject ) {
           this._update();

            this.menuObject.hide(this.element);
        }
    }

    // Get option
     _getOption(name) {
        if ( this.element.hasAttribute('data-kt-search-' + name) === true ) {
            var attr = this.element.getAttribute('data-kt-search-' + name);
            var value = KTUtil.getResponsiveValue(attr);

            if ( value !== null && String(value) === 'true' ) {
                value = true;
            } else if ( value !== null && String(value) === 'false' ) {
                value = false;
            }

            return value;
        } else {
            var optionName = KTUtil.snakeToCamel(name);

            if ( this.options[optionName] ) {
                return KTUtil.getResponsiveValue(this.options[optionName]);
            } else {
                return null;
            }
        }
    }

    // Get element
     _getElement(name) {
        return this.element.querySelector('[data-kt-search-element="' + name + '"]');
    }

    // Check if responsive form mode is enabled
     _getResponsiveFormMode() {
        var responsive =this._getOption('responsive');
        var width = KTUtil.getViewPort().width;

        if (!responsive) {
            return null;
        }

        var breakpoint = KTUtil.getBreakpoint(responsive);

        if (!breakpoint ) {
            breakpoint = parseInt(responsive);
        }

        if (width < breakpoint) {
            return "on";
        } else {
            return "off";
        }
    }

     _destroy() {
        KTUtil.data(this.element).remove('search');
    }

    // Plugin API
    show() {
        return this._show();
    }

    focus() {
        return this.inputElement.focus();
    }

    hide() {
        return this._hide();
    }

    update() {
        return this._update();
    }

    search() {
        return this._search();
    }

    complete() {
        return this._complete();
    }

    clear() {
        return this._clear();
    }

    isProcessing() {
        return this.processing;
    }

    getQuery() {
        return this.inputElement.value;
    }

    getMenu() {
        return this.menuObject;
    }

    getFormElement() {
        return this.formElement;
    }

    getInputElement() {
        return this.inputElement;
    }

    getContentElement() {
        return this.contentElement;
    }

    getElement() {
        return this.element;
    }

    destroy() {
        return this._destroy();
    }

    // Event API
    on(name, handler) {
        return KTEventHandler.on(this.element, name, handler);
    }

    one(name, handler) {
        return KTEventHandler.one(this.element, name, handler);
    }

    off(name, handlerId) {
        return KTEventHandler.off(this.element, name, handlerId);
    }
}

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (SearchModule);

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Check if module exists (development only)
/******/ 		if (__webpack_modules__[moduleId] === undefined) {
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!*************************************!*\
  !*** ./assets/cms/global-search.js ***!
  \*************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _search__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./search */ "./assets/cms/search.js");



// Class definition
var KTLayoutSearch = function() {
    // Private variables
    let element;
    let formElement;
    let mainElement;
    let resultsElement;
    let wrapperElement;
    let emptyElement;

    let preferencesElement;
    let preferencesShowElement;
    let preferencesDismissElement;
    let preferencesSaveElement;

    let advancedOptionsFormElement;
    let advancedOptionsFormShowElement;
    let advancedOptionsFormCancelElement;
    let advancedOptionsFormSearchElement;

    let adminGroups = [];
    let admins = new Map();

    let tryAdmins = [];

    let searchObject;

    let processsAjax = (search) => {

        setTryAdmins();

        // Learn more: https://axios-http.com/docs/intro
        axios.get('/admin/search',{
                ...axiosConfig,
                params: {
                    q: search.getQuery(),
                    admins: tryAdmins
                }
            })
            .then(function (response) {

                // Populate results
                resultsElement.innerHTML = response.data;
                // Show results
                resultsElement.classList.remove('d-none');
                // Hide empty message
                emptyElement.classList.add('d-none');

                // Complete search
                search.complete();
            })
            .catch(function (error) {
                // Hide results
                resultsElement.classList.add('d-none');
                // Show empty message
                emptyElement.classList.remove('d-none');

                // Complete search
                search.complete();
            });
    }

    let clear = (search) => {
        // Show recently viewed
        // mainElement.classList.remove('d-none');
        // Hide results
        resultsElement.classList.add('d-none');
        // // Hide empty message
        emptyElement.classList.add('d-none');
    }

    let handlePreferences = () => {

        adminGroups = JSON.parse(preferencesElement.dataset.cmsAdminGroups);
        adminGroups.forEach((admin) => {
            if(localStorage.getItem(admin.code) === null){
                localStorage.setItem(admin.code, 'true')
            }
            admins.set(admin.code, localStorage.getItem(admin.code) === 'true')
        });

        let checkboxes = preferencesElement.querySelectorAll('input[type="checkbox"]');

        checkboxes.forEach((checkbox) => {
            let isChecked = admins.get(checkbox.dataset.adminCode);
            checkbox.checked = isChecked;

            checkbox.addEventListener('change', () => {
                admins.set(checkbox.dataset.adminCode, checkbox.checked);
                localStorage.setItem(checkbox.dataset.adminCode, checkbox.checked)
            })
        })


        setTryAdmins();

        // Preference show handler
        if (preferencesShowElement) {
            preferencesShowElement.addEventListener('click', function() {
                wrapperElement.classList.add('d-none');
                preferencesElement.classList.remove('d-none');
            });
        }

        // Preference dismiss handler
        if (preferencesDismissElement) {
            preferencesDismissElement.addEventListener('click', function() {
                wrapperElement.classList.remove('d-none');
                preferencesElement.classList.add('d-none');
                searchObject.focus()
            });
        }

        // Preference dismiss handler
        if (preferencesSaveElement) {
            preferencesSaveElement.addEventListener('click', function(e) {
                e.preventDefault();
                setTryAdmins();
                searchObject.search();
                wrapperElement.classList.remove('d-none');
                preferencesElement.classList.add('d-none');
            });
        }

    }

    let setTryAdmins = () => {
        tryAdmins = Array.from(admins.entries()).filter((v) => {
            return v[1];
        }).map((v) => {
            return v[0];
        })
        document.querySelector('input[name="admins"]').value = JSON.stringify(tryAdmins);
    }

    // Public methods
    return {
        init: function() {
            // Elements
            element = document.querySelector('#kt_cms_search');

            if (!element) {
                return;
            }

            wrapperElement = element.querySelector('[data-kt-search-element="wrapper"]');
            formElement = element.querySelector('[data-kt-search-element="form"]');
            resultsElement = element.querySelector('[data-kt-search-element="results"]');
            emptyElement = element.querySelector('[data-kt-search-element="empty"]');

            preferencesElement = element.querySelector('[data-kt-search-element="preferences"]');
            preferencesShowElement = element.querySelector('[data-kt-search-element="preferences-show"]');
            preferencesDismissElement = element.querySelector('[data-kt-search-element="preferences-dismiss"]');
            preferencesSaveElement = element.querySelector('[data-kt-search-element="preferences-save"]');

            // Initialize search handler
            searchObject = new _search__WEBPACK_IMPORTED_MODULE_0__["default"](element)

            // Demo search handler
            searchObject.on('kt.search.process', processsAjax);

            // Clear handler
            searchObject.on('kt.search.clear', clear);

            // Custom handlers
            if (preferencesElement) {
                handlePreferences();

            }
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTLayoutSearch.init();
});
})();

/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZ2xvYmFsU2VhcmNoLmpzIiwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7O0FBQWE7O0FBRWI7O0FBRUE7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOzs7QUFHQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLDJDQUEyQztBQUMzQzs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQSxVQUFVO0FBQ1Y7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGlCQUFpQjs7QUFFakI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGlCQUFpQjtBQUNqQjs7QUFFQTtBQUNBO0FBQ0EsYUFBYTtBQUNiOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0EsYUFBYTtBQUNiLFNBQVM7QUFDVDs7QUFFQTs7QUFFQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOzs7O0FBSUE7O0FBRUE7O0FBRUE7QUFDQTtBQUNBLGlCQUFpQjs7QUFFakIsY0FBYztBQUNkO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBOzs7QUFHQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBLGNBQWM7QUFDZDtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0EsY0FBYztBQUNkO0FBQ0E7O0FBRUE7QUFDQSxVQUFVO0FBQ1Y7O0FBRUE7QUFDQTtBQUNBLGNBQWM7QUFDZDtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7O0FBRUE7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQSxVQUFVO0FBQ1Y7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQSxpRUFBZSxZQUFZLEU7Ozs7OztVQ3RhM0I7VUFDQTs7VUFFQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTs7VUFFQTtVQUNBOztVQUVBO1VBQ0E7VUFDQTs7Ozs7V0M1QkE7V0FDQTtXQUNBO1dBQ0E7V0FDQSx5Q0FBeUMsd0NBQXdDO1dBQ2pGO1dBQ0E7V0FDQSxFOzs7OztXQ1BBLHdGOzs7OztXQ0FBO1dBQ0E7V0FDQTtXQUNBLHVEQUF1RCxpQkFBaUI7V0FDeEU7V0FDQSxnREFBZ0QsYUFBYTtXQUM3RCxFOzs7Ozs7Ozs7Ozs7QUNOYTtBQUN1Qjs7QUFFcEM7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7O0FBRUE7O0FBRUE7O0FBRUE7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFhO0FBQ2I7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQSxhQUFhO0FBQ2I7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0EsYUFBYTtBQUNiOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUzs7QUFFVDs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0EsYUFBYTtBQUNiLFNBQVM7OztBQUdUOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFhO0FBQ2I7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsYUFBYTtBQUNiOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFhO0FBQ2I7O0FBRUE7O0FBRUE7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNUO0FBQ0EsU0FBUztBQUNUO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQSwrQkFBK0IsK0NBQVk7O0FBRTNDO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0EsQ0FBQzs7QUFFRDtBQUNBO0FBQ0E7QUFDQSxDQUFDLEUiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9hc3NldHMvY21zL3NlYXJjaC5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlL3dlYnBhY2svcnVudGltZS9kZWZpbmUgcHJvcGVydHkgZ2V0dGVycyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvd2VicGFjay9ydW50aW1lL2hhc093blByb3BlcnR5IHNob3J0aGFuZCIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvd2VicGFjay9ydW50aW1lL21ha2UgbmFtZXNwYWNlIG9iamVjdCIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9hc3NldHMvY21zL2dsb2JhbC1zZWFyY2guanMiXSwic291cmNlc0NvbnRlbnQiOlsiXCJ1c2Ugc3RyaWN0XCI7XG5cbmNsYXNzIFNlYXJjaE1vZHVsZSB7XG5cbiAgICBjb25zdHJ1Y3RvcihlbGVtZW50LCBvcHRpb25zKSB7XG5cbiAgICAgICAgaWYoIWVsZW1lbnQpIHtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuICAgICAgICB0aGlzLm9wdGlvbnMgPSB7fTtcbiAgICAgICAgdGhpcy5lbGVtZW50ID0gZWxlbWVudDtcbiAgICAgICAgaWYob3B0aW9ucyAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICAgICAgICB0aGlzLm9wdGlvbnMgPSBvcHRpb25zXG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLnByb2Nlc3NpbmcgPSBmYWxzZTtcbiAgICAgICAgdGhpcy5pbml0KCk7XG5cblxuICAgIH1cblxuICAgIGluaXQoKSB7XG4gICAgICAgIGNvbnN0IGRlZmF1bHRPcHRpb25zID0ge1xuICAgICAgICAgICAgbWluTGVuZ3RoOiAyLCAgLy8gTWluaWFtIHRleHQgbGVuZ2h0IHRvIHF1ZXJ5IHNlYXJjaFxuICAgICAgICAgICAga2V5cHJlc3M6IHRydWUsICAvLyBFbmFibGUgc2VhcmNoIG9uIGtleXByZXNzXG4gICAgICAgICAgICBlbnRlcjogdHJ1ZSwgIC8vIEVuYWJsZSBzZWFyY2ggb24gZW50ZXIga2V5IHByZXNzXG4gICAgICAgICAgICBsYXlvdXQ6ICdtZW51JywgIC8vIFVzZSAnbWVudScgb3IgJ2lubGluZScgbGF5b3V0IG9wdGlvbnMgdG8gZGlzcGxheSBzZWFyY2ggcmVzdWx0c1xuICAgICAgICAgICAgcmVzcG9uc2l2ZTogbnVsbCwgLy8gUGFzcyBpbnRlZ2VyIHZhbHVlIG9yIGJvb3RzdHJhcCBjb21wYXRpYmxlIGJyZWFrcG9pbnQga2V5KHNtLG1kLGxnLHhsLHh4bCkgdG8gZW5hYmxlIHJlcG9uc2l2ZSBmb3JtIG1vZGUgZm9yIGRldmljZSB3aWR0aCBiZWxvdyB0aGUgYnJlYWtwb2ludCB2YWx1ZVxuICAgICAgICAgICAgc2hvd09uRm9jdXM6IHRydWUgLy8gQWx3YXlzIHNob3cgbWVudSBvbiBpbnB1dCBmb2N1c1xuICAgICAgICB9O1xuICAgICAgICAvLyBWYXJpYWJsZXNcbiAgICAgICAgdGhpcy5vcHRpb25zID0gS1RVdGlsLmRlZXBFeHRlbmQoe30sIGRlZmF1bHRPcHRpb25zLCB0aGlzLm9wdGlvbnMpO1xuICAgICAgICB0aGlzLnByb2Nlc3NpbmcgPSBmYWxzZTtcblxuICAgICAgICAvLyBFbGVtZW50c1xuICAgICAgICB0aGlzLmNvbnRlbnRFbGVtZW50ID0gdGhpcy5fZ2V0RWxlbWVudCgnY29udGVudCcpO1xuICAgICAgICB0aGlzLmZvcm1FbGVtZW50ID0gdGhpcy5fZ2V0RWxlbWVudCgnZm9ybScpO1xuICAgICAgICB0aGlzLmlucHV0RWxlbWVudCA9IHRoaXMuX2dldEVsZW1lbnQoJ2lucHV0Jyk7XG4gICAgICAgIHRoaXMuc3Bpbm5lckVsZW1lbnQgPSB0aGlzLl9nZXRFbGVtZW50KCdzcGlubmVyJyk7XG4gICAgICAgIHRoaXMuY2xlYXJFbGVtZW50ID0gdGhpcy5fZ2V0RWxlbWVudCgnY2xlYXInKTtcbiAgICAgICAgdGhpcy50b2dnbGVFbGVtZW50ID0gdGhpcy5fZ2V0RWxlbWVudCgndG9nZ2xlJyk7XG4gICAgICAgIHRoaXMuc3VibWl0RWxlbWVudCA9IHRoaXMuX2dldEVsZW1lbnQoJ3N1Ym1pdCcpO1xuICAgICAgICB0aGlzLnRvb2xiYXJFbGVtZW50ID0gdGhpcy5fZ2V0RWxlbWVudCgndG9vbGJhcicpO1xuXG4gICAgICAgIHRoaXMucmVzdWx0c0VsZW1lbnQgPSB0aGlzLl9nZXRFbGVtZW50KCdyZXN1bHRzJyk7XG4gICAgICAgIHRoaXMuc3VnZ2VzdGlvbkVsZW1lbnQgPSB0aGlzLl9nZXRFbGVtZW50KCdzdWdnZXN0aW9uJyk7XG4gICAgICAgIHRoaXMuZW1wdHlFbGVtZW50ID0gdGhpcy5fZ2V0RWxlbWVudCgnZW1wdHknKTtcblxuICAgICAgICAvLyBTZXQgaW5pdGlhbGl6ZWRcbiAgICAgICAgdGhpcy5lbGVtZW50LnNldEF0dHJpYnV0ZSgnZGF0YS1rdC1zZWFyY2gnLCAndHJ1ZScpO1xuXG4gICAgICAgIC8vIExheW91dFxuICAgICAgICB0aGlzLmxheW91dCA9IHRoaXMuX2dldE9wdGlvbignbGF5b3V0Jyk7XG5cbiAgICAgICAgLy8gLy8gTWVudVxuICAgICAgICBpZiAoIHRoaXMubGF5b3V0ID09PSAnbWVudScgKSB7XG4gICAgICAgICAgICB0aGlzLm1lbnVPYmplY3QgPSBuZXcgS1RNZW51KHRoaXMuY29udGVudEVsZW1lbnQpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgdGhpcy5tZW51T2JqZWN0ID0gbnVsbDtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIFVwZGF0ZVxuICAgICAgIHRoaXMuX3VwZGF0ZSgpO1xuXG4gICAgICAgIC8vIEV2ZW50IEhhbmRsZXJzXG4gICAgICAgdGhpcy5faGFuZGxlcnMoKTtcblxuICAgICAgICAvLyBCaW5kIEluc3RhbmNlXG4gICAgICAgIEtUVXRpbC5kYXRhKHRoaXMuZWxlbWVudCkuc2V0KCdzZWFyY2gnLCB0aGlzKTtcbiAgICB9XG5cbiAgICBfaGFuZGxlcnMgPSBmdW5jdGlvbigpIHtcbiAgICAgICAgLy8gRm9jdXNcbiAgICAgICAgdGhpcy5pbnB1dEVsZW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignZm9jdXMnLHRoaXMuX2ZvY3VzLmJpbmQodGhpcykpO1xuXG4gICAgICAgIC8vIEJsdXJcbiAgICAgICAgdGhpcy5pbnB1dEVsZW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignYmx1cicsdGhpcy5fYmx1ci5iaW5kKHRoaXMpKTtcblxuICAgICAgICAvLyBLZXlwcmVzc1xuICAgICAgICBpZiAodGhpcy5fZ2V0T3B0aW9uKCdrZXlwcmVzcycpID09PSB0cnVlICkge1xuICAgICAgICAgICAgdGhpcy5pbnB1dEVsZW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignaW5wdXQnLHRoaXMuX2lucHV0LmJpbmQodGhpcykpO1xuICAgICAgICB9XG5cbiAgICAgICAgLy8gU3VibWl0XG4gICAgICAgIGlmICggdGhpcy5zdWJtaXRFbGVtZW50ICkge1xuICAgICAgICAgICAgdGhpcy5zdWJtaXRFbGVtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJyx0aGlzLl9zZWFyY2guYmluZCh0aGlzKSk7XG4gICAgICAgIH1cblxuICAgICAgICAvLyBFbnRlclxuICAgICAgICBpZiAodGhpcy5fZ2V0T3B0aW9uKCdlbnRlcicpID09PSB0cnVlICkge1xuICAgICAgICAgICAgdGhpcy5pbnB1dEVsZW1lbnQuYWRkRXZlbnRMaXN0ZW5lcigna2V5cHJlc3MnLHRoaXMuX2VudGVyLmJpbmQodGhpcykpO1xuICAgICAgICB9XG5cbiAgICAgICAgLy8gQ2xlYXJcbiAgICAgICAgaWYgKCB0aGlzLmNsZWFyRWxlbWVudCApIHtcbiAgICAgICAgICAgIHRoaXMuY2xlYXJFbGVtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJyx0aGlzLl9jbGVhci5iaW5kKHRoaXMpKTtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIE1lbnVcbiAgICAgICAgaWYgKCB0aGlzLm1lbnVPYmplY3QgKSB7XG4gICAgICAgICAgICAvLyBUb2dnbGUgbWVudVxuICAgICAgICAgICAgaWYgKCB0aGlzLnRvZ2dsZUVsZW1lbnQgKSB7XG4gICAgICAgICAgICAgICAgdGhpcy50b2dnbGVFbGVtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJyx0aGlzLl9zaG93LmJpbmQodGhpcykpO1xuXG4gICAgICAgICAgICAgICAgdGhpcy5tZW51T2JqZWN0Lm9uKCdrdC5tZW51LmRyb3Bkb3duLnNob3cnLCAoaXRlbSkgPT57XG4gICAgICAgICAgICAgICAgICAgIGlmIChLVFV0aWwudmlzaWJsZSh0aGlzLnRvZ2dsZUVsZW1lbnQpKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICB0aGlzLnRvZ2dsZUVsZW1lbnQuY2xhc3NMaXN0LmFkZCgnYWN0aXZlJyk7XG4gICAgICAgICAgICAgICAgICAgICAgICB0aGlzLnRvZ2dsZUVsZW1lbnQuY2xhc3NMaXN0LmFkZCgnc2hvdycpO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfSk7XG5cbiAgICAgICAgICAgICAgICB0aGlzLm1lbnVPYmplY3Qub24oJ2t0Lm1lbnUuZHJvcGRvd24uaGlkZScsIChpdGVtKSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIGlmIChLVFV0aWwudmlzaWJsZSh0aGlzLnRvZ2dsZUVsZW1lbnQpKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICB0aGlzLnRvZ2dsZUVsZW1lbnQuY2xhc3NMaXN0LnJlbW92ZSgnYWN0aXZlJyk7XG4gICAgICAgICAgICAgICAgICAgICAgICB0aGlzLnRvZ2dsZUVsZW1lbnQuY2xhc3NMaXN0LnJlbW92ZSgnc2hvdycpO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIHRoaXMubWVudU9iamVjdC5vbigna3QubWVudS5kcm9wZG93bi5zaG93bicsICgpID0+IHtcbiAgICAgICAgICAgICAgICB0aGlzLmlucHV0RWxlbWVudC5mb2N1cygpO1xuICAgICAgICAgICAgfSk7XG4gICAgICAgIH1cblxuICAgICAgICAvLyBXaW5kb3cgcmVzaXplIGhhbmRsaW5nXG4gICAgICAgIHdpbmRvdy5hZGRFdmVudExpc3RlbmVyKCdyZXNpemUnLCAoKSA9PiB7XG4gICAgICAgICAgICBsZXQgdGltZXI7XG5cbiAgICAgICAgICAgIEtUVXRpbC50aHJvdHRsZSh0aW1lciwgKCkgPT4ge1xuICAgICAgICAgICAgICAgdGhpcy5fdXBkYXRlKCk7XG4gICAgICAgICAgICB9LCAyMDApO1xuICAgICAgICB9KTtcbiAgICB9XG5cbiAgICBfZm9jdXMoKSB7XG5cbiAgICAgICAgdGhpcy5lbGVtZW50LmNsYXNzTGlzdC5hZGQoJ2ZvY3VzJyk7XG5cbiAgICAgICAgaWYgKHRoaXMuX2dldE9wdGlvbignc2hvdy1vbi1mb2N1cycpID09PSB0cnVlIHx8IHRoaXMuaW5wdXRFbGVtZW50LnZhbHVlLmxlbmd0aCA+PSBtaW5MZW5ndGggKSB7XG4gICAgICAgICAgIHRoaXMuX3Nob3coKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8vIEJsdXJcbiAgICBfYmx1cigpIHtcbiAgICAgICAgdGhpcy5lbGVtZW50LmNsYXNzTGlzdC5yZW1vdmUoJ2ZvY3VzJyk7XG4gICAgfVxuXG4gICAgLy8gRW50ZXJcbiAgICBfZW50ZXIoZSkge1xuICAgICAgICB2YXIga2V5ID0gZS5jaGFyQ29kZSB8fCBlLmtleUNvZGUgfHwgMDtcblxuICAgICAgICBpZiAoa2V5ID09IDEzKSB7XG4gICAgICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG5cbiAgICAgICAgICAgdGhpcy5fc2VhcmNoKCk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBfaW5wdXQoKSB7XG4gICAgICAgIGlmICggdGhpcy5fZ2V0T3B0aW9uKCdtaW4tbGVuZ3RoJykgKSAge1xuICAgICAgICAgICAgdmFyIG1pbkxlbmd0aCA9IHBhcnNlSW50KHRoaXMuX2dldE9wdGlvbignbWluLWxlbmd0aCcpKTtcblxuXG5cbiAgICAgICAgICAgIGlmICggdGhpcy5pbnB1dEVsZW1lbnQudmFsdWUubGVuZ3RoID49IG1pbkxlbmd0aCApIHtcblxuICAgICAgICAgICAgICAgIGNsZWFyVGltZW91dCh0aGlzLnRpbWVyKTtcblxuICAgICAgICAgICAgICAgIHRoaXMudGltZXIgPSBzZXRUaW1lb3V0KCgpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgdGhpcy5fc2VhcmNoKCk7XG4gICAgICAgICAgICAgICAgfSwgMTAwMCk7XG5cbiAgICAgICAgICAgIH0gZWxzZSBpZiAoIHRoaXMuaW5wdXRFbGVtZW50LnZhbHVlLmxlbmd0aCA9PT0gMCApIHtcbiAgICAgICAgICAgICAgICB0aGlzLl9jbGVhcigpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfVxuXG4gICAgX3NlYXJjaCgpIHtcbiAgICAgICAgaWYgKHRoaXMucHJvY2Vzc2luZyA9PT0gZmFsc2UpIHtcbiAgICAgICAgICAgIC8vIFNob3cgc2VhcmNoIHNwaW5uZXJcbiAgICAgICAgICAgIGlmICh0aGlzLnNwaW5uZXJFbGVtZW50KSB7XG4gICAgICAgICAgICAgICAgdGhpcy5zcGlubmVyRWxlbWVudC5jbGFzc0xpc3QucmVtb3ZlKFwiZC1ub25lXCIpO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAvLyBIaWRlIHNlYXJjaCBjbGVhciBidXR0b25cbiAgICAgICAgICAgIGlmICh0aGlzLmNsZWFyRWxlbWVudCkge1xuICAgICAgICAgICAgICAgIHRoaXMuY2xlYXJFbGVtZW50LmNsYXNzTGlzdC5hZGQoXCJkLW5vbmVcIik7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIC8vIEhpZGUgc2VhcmNoIHRvb2xiYXJcbiAgICAgICAgICAgIGlmICh0aGlzLnRvb2xiYXJFbGVtZW50ICYmIHRoaXMuZm9ybUVsZW1lbnQuY29udGFpbnModGhpcy50b29sYmFyRWxlbWVudCkpIHtcbiAgICAgICAgICAgICAgICB0aGlzLnRvb2xiYXJFbGVtZW50LmNsYXNzTGlzdC5hZGQoXCJkLW5vbmVcIik7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIC8vIEZvY3VzIGlucHV0XG4gICAgICAgICAgICB0aGlzLmlucHV0RWxlbWVudC5mb2N1cygpO1xuXG4gICAgICAgICAgICB0aGlzLnByb2Nlc3NpbmcgPSB0cnVlO1xuICAgICAgICAgICAgS1RFdmVudEhhbmRsZXIudHJpZ2dlcih0aGlzLmVsZW1lbnQsICdrdC5zZWFyY2gucHJvY2VzcycsIHRoaXMpO1xuICAgICAgICB9XG4gICAgfVxuXG5cbiAgICAvLyBTZWFyY2hcblxuICAgIC8vIENvbXBsZXRlXG4gICAgIF9jb21wbGV0ZSgpIHtcbiAgICAgICAgaWYgKHRoaXMuc3Bpbm5lckVsZW1lbnQpIHtcbiAgICAgICAgICAgIHRoaXMuc3Bpbm5lckVsZW1lbnQuY2xhc3NMaXN0LmFkZChcImQtbm9uZVwiKTtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIFNob3cgc2VhcmNoIHRvb2xiYXJcbiAgICAgICAgaWYgKHRoaXMuY2xlYXJFbGVtZW50KSB7XG4gICAgICAgICAgICB0aGlzLmNsZWFyRWxlbWVudC5jbGFzc0xpc3QucmVtb3ZlKFwiZC1ub25lXCIpO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCB0aGlzLmlucHV0RWxlbWVudC52YWx1ZS5sZW5ndGggPT09IDAgKSB7XG4gICAgICAgICAgIHRoaXMuX2NsZWFyKCk7XG4gICAgICAgIH1cblxuICAgICAgICAvLyBGb2N1cyBpbnB1dFxuICAgICAgICB0aGlzLmlucHV0RWxlbWVudC5mb2N1cygpO1xuXG4gICAgICAgdGhpcy5fc2hvdygpO1xuXG4gICAgICAgIHRoaXMucHJvY2Vzc2luZyA9IGZhbHNlO1xuICAgIH1cblxuICAgIC8vIENsZWFyXG4gICAgIF9jbGVhcigpIHtcbiAgICAgICAgaWYgKCBLVEV2ZW50SGFuZGxlci50cmlnZ2VyKHRoaXMuZWxlbWVudCwgJ2t0LnNlYXJjaC5jbGVhcicsIHRoaXMpID09PSBmYWxzZSApICB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICAvLyBDbGVhciBhbmQgZm9jdXMgaW5wdXRcbiAgICAgICAgdGhpcy5pbnB1dEVsZW1lbnQudmFsdWUgPSBcIlwiO1xuICAgICAgICB0aGlzLmlucHV0RWxlbWVudC5mb2N1cygpO1xuXG4gICAgICAgIC8vIEhpZGUgY2xlYXIgaWNvblxuICAgICAgICBpZiAodGhpcy5jbGVhckVsZW1lbnQpIHtcbiAgICAgICAgICAgIHRoaXMuY2xlYXJFbGVtZW50LmNsYXNzTGlzdC5hZGQoXCJkLW5vbmVcIik7XG4gICAgICAgIH1cblxuICAgICAgICAvLyBTaG93IHNlYXJjaCB0b29sYmFyXG4gICAgICAgIGlmICh0aGlzLnRvb2xiYXJFbGVtZW50ICYmIHRoaXMuZm9ybUVsZW1lbnQuY29udGFpbnModGhpcy50b29sYmFyRWxlbWVudCkpIHtcbiAgICAgICAgICAgIHRoaXMudG9vbGJhckVsZW1lbnQuY2xhc3NMaXN0LnJlbW92ZShcImQtbm9uZVwiKTtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIEhpZGUgbWVudVxuICAgICAgICBpZiAodGhpcy5fZ2V0T3B0aW9uKCdzaG93LW9uLWZvY3VzJykgPT09IGZhbHNlICkge1xuICAgICAgICAgICB0aGlzLl9oaWRlKCk7XG4gICAgICAgIH1cblxuICAgICAgICBLVEV2ZW50SGFuZGxlci50cmlnZ2VyKHRoaXMuZWxlbWVudCwgJ2t0LnNlYXJjaC5jbGVhcmVkJywgdGhpcyk7XG4gICAgfVxuXG4gICAgLy8gVXBkYXRlXG4gICAgIF91cGRhdGUoKSB7XG4gICAgICAgIC8vIEhhbmRsZSByZXNwb25zaXZlIGZvcm1cbiAgICAgICAgaWYgKHRoaXMubGF5b3V0ID09PSAnbWVudScpIHtcbiAgICAgICAgICAgIHZhciByZXNwb25zaXZlRm9ybU1vZGUgPXRoaXMuX2dldFJlc3BvbnNpdmVGb3JtTW9kZSgpO1xuXG4gICAgICAgICAgICBpZiAoIHJlc3BvbnNpdmVGb3JtTW9kZSA9PT0gJ29uJyAmJiB0aGlzLmNvbnRlbnRFbGVtZW50LmNvbnRhaW5zKHRoaXMuZm9ybUVsZW1lbnQpID09PSBmYWxzZSApIHtcbiAgICAgICAgICAgICAgICB0aGlzLmNvbnRlbnRFbGVtZW50LnByZXBlbmQodGhpcy5mb3JtRWxlbWVudCk7XG4gICAgICAgICAgICAgICAgdGhpcy5mb3JtRWxlbWVudC5jbGFzc0xpc3QucmVtb3ZlKCdkLW5vbmUnKTtcbiAgICAgICAgICAgIH0gZWxzZSBpZiAoIHJlc3BvbnNpdmVGb3JtTW9kZSA9PT0gJ29mZicgJiYgdGhpcy5jb250ZW50RWxlbWVudC5jb250YWlucyh0aGlzLmZvcm1FbGVtZW50KSA9PT0gdHJ1ZSApIHtcbiAgICAgICAgICAgICAgICB0aGlzLmVsZW1lbnQucHJlcGVuZCh0aGlzLmZvcm1FbGVtZW50KTtcbiAgICAgICAgICAgICAgICB0aGlzLmZvcm1FbGVtZW50LmNsYXNzTGlzdC5hZGQoJ2Qtbm9uZScpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfVxuXG4gICAgLy8gU2hvdyBtZW51XG4gICAgIF9zaG93KCkge1xuICAgICAgICBpZiAoIHRoaXMubWVudU9iamVjdCApIHtcbiAgICAgICAgICAgdGhpcy5fdXBkYXRlKCk7XG5cbiAgICAgICAgICAgIHRoaXMubWVudU9iamVjdC5zaG93KHRoaXMuZWxlbWVudCk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvLyBIaWRlIG1lbnVcbiAgICAgX2hpZGUoKSB7XG4gICAgICAgIGlmICggdGhpcy5tZW51T2JqZWN0ICkge1xuICAgICAgICAgICB0aGlzLl91cGRhdGUoKTtcblxuICAgICAgICAgICAgdGhpcy5tZW51T2JqZWN0LmhpZGUodGhpcy5lbGVtZW50KTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8vIEdldCBvcHRpb25cbiAgICAgX2dldE9wdGlvbihuYW1lKSB7XG4gICAgICAgIGlmICggdGhpcy5lbGVtZW50Lmhhc0F0dHJpYnV0ZSgnZGF0YS1rdC1zZWFyY2gtJyArIG5hbWUpID09PSB0cnVlICkge1xuICAgICAgICAgICAgdmFyIGF0dHIgPSB0aGlzLmVsZW1lbnQuZ2V0QXR0cmlidXRlKCdkYXRhLWt0LXNlYXJjaC0nICsgbmFtZSk7XG4gICAgICAgICAgICB2YXIgdmFsdWUgPSBLVFV0aWwuZ2V0UmVzcG9uc2l2ZVZhbHVlKGF0dHIpO1xuXG4gICAgICAgICAgICBpZiAoIHZhbHVlICE9PSBudWxsICYmIFN0cmluZyh2YWx1ZSkgPT09ICd0cnVlJyApIHtcbiAgICAgICAgICAgICAgICB2YWx1ZSA9IHRydWU7XG4gICAgICAgICAgICB9IGVsc2UgaWYgKCB2YWx1ZSAhPT0gbnVsbCAmJiBTdHJpbmcodmFsdWUpID09PSAnZmFsc2UnICkge1xuICAgICAgICAgICAgICAgIHZhbHVlID0gZmFsc2U7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIHJldHVybiB2YWx1ZTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHZhciBvcHRpb25OYW1lID0gS1RVdGlsLnNuYWtlVG9DYW1lbChuYW1lKTtcblxuICAgICAgICAgICAgaWYgKCB0aGlzLm9wdGlvbnNbb3B0aW9uTmFtZV0gKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuIEtUVXRpbC5nZXRSZXNwb25zaXZlVmFsdWUodGhpcy5vcHRpb25zW29wdGlvbk5hbWVdKTtcbiAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuIG51bGw7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvLyBHZXQgZWxlbWVudFxuICAgICBfZ2V0RWxlbWVudChuYW1lKSB7XG4gICAgICAgIHJldHVybiB0aGlzLmVsZW1lbnQucXVlcnlTZWxlY3RvcignW2RhdGEta3Qtc2VhcmNoLWVsZW1lbnQ9XCInICsgbmFtZSArICdcIl0nKTtcbiAgICB9XG5cbiAgICAvLyBDaGVjayBpZiByZXNwb25zaXZlIGZvcm0gbW9kZSBpcyBlbmFibGVkXG4gICAgIF9nZXRSZXNwb25zaXZlRm9ybU1vZGUoKSB7XG4gICAgICAgIHZhciByZXNwb25zaXZlID10aGlzLl9nZXRPcHRpb24oJ3Jlc3BvbnNpdmUnKTtcbiAgICAgICAgdmFyIHdpZHRoID0gS1RVdGlsLmdldFZpZXdQb3J0KCkud2lkdGg7XG5cbiAgICAgICAgaWYgKCFyZXNwb25zaXZlKSB7XG4gICAgICAgICAgICByZXR1cm4gbnVsbDtcbiAgICAgICAgfVxuXG4gICAgICAgIHZhciBicmVha3BvaW50ID0gS1RVdGlsLmdldEJyZWFrcG9pbnQocmVzcG9uc2l2ZSk7XG5cbiAgICAgICAgaWYgKCFicmVha3BvaW50ICkge1xuICAgICAgICAgICAgYnJlYWtwb2ludCA9IHBhcnNlSW50KHJlc3BvbnNpdmUpO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHdpZHRoIDwgYnJlYWtwb2ludCkge1xuICAgICAgICAgICAgcmV0dXJuIFwib25cIjtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHJldHVybiBcIm9mZlwiO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgIF9kZXN0cm95KCkge1xuICAgICAgICBLVFV0aWwuZGF0YSh0aGlzLmVsZW1lbnQpLnJlbW92ZSgnc2VhcmNoJyk7XG4gICAgfVxuXG4gICAgLy8gUGx1Z2luIEFQSVxuICAgIHNob3coKSB7XG4gICAgICAgIHJldHVybiB0aGlzLl9zaG93KCk7XG4gICAgfVxuXG4gICAgZm9jdXMoKSB7XG4gICAgICAgIHJldHVybiB0aGlzLmlucHV0RWxlbWVudC5mb2N1cygpO1xuICAgIH1cblxuICAgIGhpZGUoKSB7XG4gICAgICAgIHJldHVybiB0aGlzLl9oaWRlKCk7XG4gICAgfVxuXG4gICAgdXBkYXRlKCkge1xuICAgICAgICByZXR1cm4gdGhpcy5fdXBkYXRlKCk7XG4gICAgfVxuXG4gICAgc2VhcmNoKCkge1xuICAgICAgICByZXR1cm4gdGhpcy5fc2VhcmNoKCk7XG4gICAgfVxuXG4gICAgY29tcGxldGUoKSB7XG4gICAgICAgIHJldHVybiB0aGlzLl9jb21wbGV0ZSgpO1xuICAgIH1cblxuICAgIGNsZWFyKCkge1xuICAgICAgICByZXR1cm4gdGhpcy5fY2xlYXIoKTtcbiAgICB9XG5cbiAgICBpc1Byb2Nlc3NpbmcoKSB7XG4gICAgICAgIHJldHVybiB0aGlzLnByb2Nlc3Npbmc7XG4gICAgfVxuXG4gICAgZ2V0UXVlcnkoKSB7XG4gICAgICAgIHJldHVybiB0aGlzLmlucHV0RWxlbWVudC52YWx1ZTtcbiAgICB9XG5cbiAgICBnZXRNZW51KCkge1xuICAgICAgICByZXR1cm4gdGhpcy5tZW51T2JqZWN0O1xuICAgIH1cblxuICAgIGdldEZvcm1FbGVtZW50KCkge1xuICAgICAgICByZXR1cm4gdGhpcy5mb3JtRWxlbWVudDtcbiAgICB9XG5cbiAgICBnZXRJbnB1dEVsZW1lbnQoKSB7XG4gICAgICAgIHJldHVybiB0aGlzLmlucHV0RWxlbWVudDtcbiAgICB9XG5cbiAgICBnZXRDb250ZW50RWxlbWVudCgpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuY29udGVudEVsZW1lbnQ7XG4gICAgfVxuXG4gICAgZ2V0RWxlbWVudCgpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuZWxlbWVudDtcbiAgICB9XG5cbiAgICBkZXN0cm95KCkge1xuICAgICAgICByZXR1cm4gdGhpcy5fZGVzdHJveSgpO1xuICAgIH1cblxuICAgIC8vIEV2ZW50IEFQSVxuICAgIG9uKG5hbWUsIGhhbmRsZXIpIHtcbiAgICAgICAgcmV0dXJuIEtURXZlbnRIYW5kbGVyLm9uKHRoaXMuZWxlbWVudCwgbmFtZSwgaGFuZGxlcik7XG4gICAgfVxuXG4gICAgb25lKG5hbWUsIGhhbmRsZXIpIHtcbiAgICAgICAgcmV0dXJuIEtURXZlbnRIYW5kbGVyLm9uZSh0aGlzLmVsZW1lbnQsIG5hbWUsIGhhbmRsZXIpO1xuICAgIH1cblxuICAgIG9mZihuYW1lLCBoYW5kbGVySWQpIHtcbiAgICAgICAgcmV0dXJuIEtURXZlbnRIYW5kbGVyLm9mZih0aGlzLmVsZW1lbnQsIG5hbWUsIGhhbmRsZXJJZCk7XG4gICAgfVxufVxuXG5leHBvcnQgZGVmYXVsdCBTZWFyY2hNb2R1bGU7IiwiLy8gVGhlIG1vZHVsZSBjYWNoZVxudmFyIF9fd2VicGFja19tb2R1bGVfY2FjaGVfXyA9IHt9O1xuXG4vLyBUaGUgcmVxdWlyZSBmdW5jdGlvblxuZnVuY3Rpb24gX193ZWJwYWNrX3JlcXVpcmVfXyhtb2R1bGVJZCkge1xuXHQvLyBDaGVjayBpZiBtb2R1bGUgaXMgaW4gY2FjaGVcblx0dmFyIGNhY2hlZE1vZHVsZSA9IF9fd2VicGFja19tb2R1bGVfY2FjaGVfX1ttb2R1bGVJZF07XG5cdGlmIChjYWNoZWRNb2R1bGUgIT09IHVuZGVmaW5lZCkge1xuXHRcdHJldHVybiBjYWNoZWRNb2R1bGUuZXhwb3J0cztcblx0fVxuXHQvLyBDaGVjayBpZiBtb2R1bGUgZXhpc3RzIChkZXZlbG9wbWVudCBvbmx5KVxuXHRpZiAoX193ZWJwYWNrX21vZHVsZXNfX1ttb2R1bGVJZF0gPT09IHVuZGVmaW5lZCkge1xuXHRcdHZhciBlID0gbmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIiArIG1vZHVsZUlkICsgXCInXCIpO1xuXHRcdGUuY29kZSA9ICdNT0RVTEVfTk9UX0ZPVU5EJztcblx0XHR0aHJvdyBlO1xuXHR9XG5cdC8vIENyZWF0ZSBhIG5ldyBtb2R1bGUgKGFuZCBwdXQgaXQgaW50byB0aGUgY2FjaGUpXG5cdHZhciBtb2R1bGUgPSBfX3dlYnBhY2tfbW9kdWxlX2NhY2hlX19bbW9kdWxlSWRdID0ge1xuXHRcdC8vIG5vIG1vZHVsZS5pZCBuZWVkZWRcblx0XHQvLyBubyBtb2R1bGUubG9hZGVkIG5lZWRlZFxuXHRcdGV4cG9ydHM6IHt9XG5cdH07XG5cblx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG5cdF9fd2VicGFja19tb2R1bGVzX19bbW9kdWxlSWRdKG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG5cdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG5cdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbn1cblxuIiwiLy8gZGVmaW5lIGdldHRlciBmdW5jdGlvbnMgZm9yIGhhcm1vbnkgZXhwb3J0c1xuX193ZWJwYWNrX3JlcXVpcmVfXy5kID0gKGV4cG9ydHMsIGRlZmluaXRpb24pID0+IHtcblx0Zm9yKHZhciBrZXkgaW4gZGVmaW5pdGlvbikge1xuXHRcdGlmKF9fd2VicGFja19yZXF1aXJlX18ubyhkZWZpbml0aW9uLCBrZXkpICYmICFfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZXhwb3J0cywga2V5KSkge1xuXHRcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIGtleSwgeyBlbnVtZXJhYmxlOiB0cnVlLCBnZXQ6IGRlZmluaXRpb25ba2V5XSB9KTtcblx0XHR9XG5cdH1cbn07IiwiX193ZWJwYWNrX3JlcXVpcmVfXy5vID0gKG9iaiwgcHJvcCkgPT4gKE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbChvYmosIHByb3ApKSIsIi8vIGRlZmluZSBfX2VzTW9kdWxlIG9uIGV4cG9ydHNcbl9fd2VicGFja19yZXF1aXJlX18uciA9IChleHBvcnRzKSA9PiB7XG5cdGlmKHR5cGVvZiBTeW1ib2wgIT09ICd1bmRlZmluZWQnICYmIFN5bWJvbC50b1N0cmluZ1RhZykge1xuXHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCBTeW1ib2wudG9TdHJpbmdUYWcsIHsgdmFsdWU6ICdNb2R1bGUnIH0pO1xuXHR9XG5cdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCAnX19lc01vZHVsZScsIHsgdmFsdWU6IHRydWUgfSk7XG59OyIsIlwidXNlIHN0cmljdFwiO1xuaW1wb3J0IFNlYXJjaE1vZHVsZSBmcm9tICcuL3NlYXJjaCc7XG5cbi8vIENsYXNzIGRlZmluaXRpb25cbnZhciBLVExheW91dFNlYXJjaCA9IGZ1bmN0aW9uKCkge1xuICAgIC8vIFByaXZhdGUgdmFyaWFibGVzXG4gICAgbGV0IGVsZW1lbnQ7XG4gICAgbGV0IGZvcm1FbGVtZW50O1xuICAgIGxldCBtYWluRWxlbWVudDtcbiAgICBsZXQgcmVzdWx0c0VsZW1lbnQ7XG4gICAgbGV0IHdyYXBwZXJFbGVtZW50O1xuICAgIGxldCBlbXB0eUVsZW1lbnQ7XG5cbiAgICBsZXQgcHJlZmVyZW5jZXNFbGVtZW50O1xuICAgIGxldCBwcmVmZXJlbmNlc1Nob3dFbGVtZW50O1xuICAgIGxldCBwcmVmZXJlbmNlc0Rpc21pc3NFbGVtZW50O1xuICAgIGxldCBwcmVmZXJlbmNlc1NhdmVFbGVtZW50O1xuXG4gICAgbGV0IGFkdmFuY2VkT3B0aW9uc0Zvcm1FbGVtZW50O1xuICAgIGxldCBhZHZhbmNlZE9wdGlvbnNGb3JtU2hvd0VsZW1lbnQ7XG4gICAgbGV0IGFkdmFuY2VkT3B0aW9uc0Zvcm1DYW5jZWxFbGVtZW50O1xuICAgIGxldCBhZHZhbmNlZE9wdGlvbnNGb3JtU2VhcmNoRWxlbWVudDtcblxuICAgIGxldCBhZG1pbkdyb3VwcyA9IFtdO1xuICAgIGxldCBhZG1pbnMgPSBuZXcgTWFwKCk7XG5cbiAgICBsZXQgdHJ5QWRtaW5zID0gW107XG5cbiAgICBsZXQgc2VhcmNoT2JqZWN0O1xuXG4gICAgbGV0IHByb2Nlc3NzQWpheCA9IChzZWFyY2gpID0+IHtcblxuICAgICAgICBzZXRUcnlBZG1pbnMoKTtcblxuICAgICAgICAvLyBMZWFybiBtb3JlOiBodHRwczovL2F4aW9zLWh0dHAuY29tL2RvY3MvaW50cm9cbiAgICAgICAgYXhpb3MuZ2V0KCcvYWRtaW4vc2VhcmNoJyx7XG4gICAgICAgICAgICAgICAgLi4uYXhpb3NDb25maWcsXG4gICAgICAgICAgICAgICAgcGFyYW1zOiB7XG4gICAgICAgICAgICAgICAgICAgIHE6IHNlYXJjaC5nZXRRdWVyeSgpLFxuICAgICAgICAgICAgICAgICAgICBhZG1pbnM6IHRyeUFkbWluc1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pXG4gICAgICAgICAgICAudGhlbihmdW5jdGlvbiAocmVzcG9uc2UpIHtcblxuICAgICAgICAgICAgICAgIC8vIFBvcHVsYXRlIHJlc3VsdHNcbiAgICAgICAgICAgICAgICByZXN1bHRzRWxlbWVudC5pbm5lckhUTUwgPSByZXNwb25zZS5kYXRhO1xuICAgICAgICAgICAgICAgIC8vIFNob3cgcmVzdWx0c1xuICAgICAgICAgICAgICAgIHJlc3VsdHNFbGVtZW50LmNsYXNzTGlzdC5yZW1vdmUoJ2Qtbm9uZScpO1xuICAgICAgICAgICAgICAgIC8vIEhpZGUgZW1wdHkgbWVzc2FnZVxuICAgICAgICAgICAgICAgIGVtcHR5RWxlbWVudC5jbGFzc0xpc3QuYWRkKCdkLW5vbmUnKTtcblxuICAgICAgICAgICAgICAgIC8vIENvbXBsZXRlIHNlYXJjaFxuICAgICAgICAgICAgICAgIHNlYXJjaC5jb21wbGV0ZSgpO1xuICAgICAgICAgICAgfSlcbiAgICAgICAgICAgIC5jYXRjaChmdW5jdGlvbiAoZXJyb3IpIHtcbiAgICAgICAgICAgICAgICAvLyBIaWRlIHJlc3VsdHNcbiAgICAgICAgICAgICAgICByZXN1bHRzRWxlbWVudC5jbGFzc0xpc3QuYWRkKCdkLW5vbmUnKTtcbiAgICAgICAgICAgICAgICAvLyBTaG93IGVtcHR5IG1lc3NhZ2VcbiAgICAgICAgICAgICAgICBlbXB0eUVsZW1lbnQuY2xhc3NMaXN0LnJlbW92ZSgnZC1ub25lJyk7XG5cbiAgICAgICAgICAgICAgICAvLyBDb21wbGV0ZSBzZWFyY2hcbiAgICAgICAgICAgICAgICBzZWFyY2guY29tcGxldGUoKTtcbiAgICAgICAgICAgIH0pO1xuICAgIH1cblxuICAgIGxldCBjbGVhciA9IChzZWFyY2gpID0+IHtcbiAgICAgICAgLy8gU2hvdyByZWNlbnRseSB2aWV3ZWRcbiAgICAgICAgLy8gbWFpbkVsZW1lbnQuY2xhc3NMaXN0LnJlbW92ZSgnZC1ub25lJyk7XG4gICAgICAgIC8vIEhpZGUgcmVzdWx0c1xuICAgICAgICByZXN1bHRzRWxlbWVudC5jbGFzc0xpc3QuYWRkKCdkLW5vbmUnKTtcbiAgICAgICAgLy8gLy8gSGlkZSBlbXB0eSBtZXNzYWdlXG4gICAgICAgIGVtcHR5RWxlbWVudC5jbGFzc0xpc3QuYWRkKCdkLW5vbmUnKTtcbiAgICB9XG5cbiAgICBsZXQgaGFuZGxlUHJlZmVyZW5jZXMgPSAoKSA9PiB7XG5cbiAgICAgICAgYWRtaW5Hcm91cHMgPSBKU09OLnBhcnNlKHByZWZlcmVuY2VzRWxlbWVudC5kYXRhc2V0LmNtc0FkbWluR3JvdXBzKTtcbiAgICAgICAgYWRtaW5Hcm91cHMuZm9yRWFjaCgoYWRtaW4pID0+IHtcbiAgICAgICAgICAgIGlmKGxvY2FsU3RvcmFnZS5nZXRJdGVtKGFkbWluLmNvZGUpID09PSBudWxsKXtcbiAgICAgICAgICAgICAgICBsb2NhbFN0b3JhZ2Uuc2V0SXRlbShhZG1pbi5jb2RlLCAndHJ1ZScpXG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBhZG1pbnMuc2V0KGFkbWluLmNvZGUsIGxvY2FsU3RvcmFnZS5nZXRJdGVtKGFkbWluLmNvZGUpID09PSAndHJ1ZScpXG4gICAgICAgIH0pO1xuXG4gICAgICAgIGxldCBjaGVja2JveGVzID0gcHJlZmVyZW5jZXNFbGVtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJ2lucHV0W3R5cGU9XCJjaGVja2JveFwiXScpO1xuXG4gICAgICAgIGNoZWNrYm94ZXMuZm9yRWFjaCgoY2hlY2tib3gpID0+IHtcbiAgICAgICAgICAgIGxldCBpc0NoZWNrZWQgPSBhZG1pbnMuZ2V0KGNoZWNrYm94LmRhdGFzZXQuYWRtaW5Db2RlKTtcbiAgICAgICAgICAgIGNoZWNrYm94LmNoZWNrZWQgPSBpc0NoZWNrZWQ7XG5cbiAgICAgICAgICAgIGNoZWNrYm94LmFkZEV2ZW50TGlzdGVuZXIoJ2NoYW5nZScsICgpID0+IHtcbiAgICAgICAgICAgICAgICBhZG1pbnMuc2V0KGNoZWNrYm94LmRhdGFzZXQuYWRtaW5Db2RlLCBjaGVja2JveC5jaGVja2VkKTtcbiAgICAgICAgICAgICAgICBsb2NhbFN0b3JhZ2Uuc2V0SXRlbShjaGVja2JveC5kYXRhc2V0LmFkbWluQ29kZSwgY2hlY2tib3guY2hlY2tlZClcbiAgICAgICAgICAgIH0pXG4gICAgICAgIH0pXG5cblxuICAgICAgICBzZXRUcnlBZG1pbnMoKTtcblxuICAgICAgICAvLyBQcmVmZXJlbmNlIHNob3cgaGFuZGxlclxuICAgICAgICBpZiAocHJlZmVyZW5jZXNTaG93RWxlbWVudCkge1xuICAgICAgICAgICAgcHJlZmVyZW5jZXNTaG93RWxlbWVudC5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgIHdyYXBwZXJFbGVtZW50LmNsYXNzTGlzdC5hZGQoJ2Qtbm9uZScpO1xuICAgICAgICAgICAgICAgIHByZWZlcmVuY2VzRWxlbWVudC5jbGFzc0xpc3QucmVtb3ZlKCdkLW5vbmUnKTtcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG5cbiAgICAgICAgLy8gUHJlZmVyZW5jZSBkaXNtaXNzIGhhbmRsZXJcbiAgICAgICAgaWYgKHByZWZlcmVuY2VzRGlzbWlzc0VsZW1lbnQpIHtcbiAgICAgICAgICAgIHByZWZlcmVuY2VzRGlzbWlzc0VsZW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgICAgICB3cmFwcGVyRWxlbWVudC5jbGFzc0xpc3QucmVtb3ZlKCdkLW5vbmUnKTtcbiAgICAgICAgICAgICAgICBwcmVmZXJlbmNlc0VsZW1lbnQuY2xhc3NMaXN0LmFkZCgnZC1ub25lJyk7XG4gICAgICAgICAgICAgICAgc2VhcmNoT2JqZWN0LmZvY3VzKClcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG5cbiAgICAgICAgLy8gUHJlZmVyZW5jZSBkaXNtaXNzIGhhbmRsZXJcbiAgICAgICAgaWYgKHByZWZlcmVuY2VzU2F2ZUVsZW1lbnQpIHtcbiAgICAgICAgICAgIHByZWZlcmVuY2VzU2F2ZUVsZW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBmdW5jdGlvbihlKSB7XG4gICAgICAgICAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICAgICAgICAgIHNldFRyeUFkbWlucygpO1xuICAgICAgICAgICAgICAgIHNlYXJjaE9iamVjdC5zZWFyY2goKTtcbiAgICAgICAgICAgICAgICB3cmFwcGVyRWxlbWVudC5jbGFzc0xpc3QucmVtb3ZlKCdkLW5vbmUnKTtcbiAgICAgICAgICAgICAgICBwcmVmZXJlbmNlc0VsZW1lbnQuY2xhc3NMaXN0LmFkZCgnZC1ub25lJyk7XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuXG4gICAgfVxuXG4gICAgbGV0IHNldFRyeUFkbWlucyA9ICgpID0+IHtcbiAgICAgICAgdHJ5QWRtaW5zID0gQXJyYXkuZnJvbShhZG1pbnMuZW50cmllcygpKS5maWx0ZXIoKHYpID0+IHtcbiAgICAgICAgICAgIHJldHVybiB2WzFdO1xuICAgICAgICB9KS5tYXAoKHYpID0+IHtcbiAgICAgICAgICAgIHJldHVybiB2WzBdO1xuICAgICAgICB9KVxuICAgICAgICBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCdpbnB1dFtuYW1lPVwiYWRtaW5zXCJdJykudmFsdWUgPSBKU09OLnN0cmluZ2lmeSh0cnlBZG1pbnMpO1xuICAgIH1cblxuICAgIC8vIFB1YmxpYyBtZXRob2RzXG4gICAgcmV0dXJuIHtcbiAgICAgICAgaW5pdDogZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICAvLyBFbGVtZW50c1xuICAgICAgICAgICAgZWxlbWVudCA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJyNrdF9jbXNfc2VhcmNoJyk7XG5cbiAgICAgICAgICAgIGlmICghZWxlbWVudCkge1xuICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgd3JhcHBlckVsZW1lbnQgPSBlbGVtZW50LnF1ZXJ5U2VsZWN0b3IoJ1tkYXRhLWt0LXNlYXJjaC1lbGVtZW50PVwid3JhcHBlclwiXScpO1xuICAgICAgICAgICAgZm9ybUVsZW1lbnQgPSBlbGVtZW50LnF1ZXJ5U2VsZWN0b3IoJ1tkYXRhLWt0LXNlYXJjaC1lbGVtZW50PVwiZm9ybVwiXScpO1xuICAgICAgICAgICAgcmVzdWx0c0VsZW1lbnQgPSBlbGVtZW50LnF1ZXJ5U2VsZWN0b3IoJ1tkYXRhLWt0LXNlYXJjaC1lbGVtZW50PVwicmVzdWx0c1wiXScpO1xuICAgICAgICAgICAgZW1wdHlFbGVtZW50ID0gZWxlbWVudC5xdWVyeVNlbGVjdG9yKCdbZGF0YS1rdC1zZWFyY2gtZWxlbWVudD1cImVtcHR5XCJdJyk7XG5cbiAgICAgICAgICAgIHByZWZlcmVuY2VzRWxlbWVudCA9IGVsZW1lbnQucXVlcnlTZWxlY3RvcignW2RhdGEta3Qtc2VhcmNoLWVsZW1lbnQ9XCJwcmVmZXJlbmNlc1wiXScpO1xuICAgICAgICAgICAgcHJlZmVyZW5jZXNTaG93RWxlbWVudCA9IGVsZW1lbnQucXVlcnlTZWxlY3RvcignW2RhdGEta3Qtc2VhcmNoLWVsZW1lbnQ9XCJwcmVmZXJlbmNlcy1zaG93XCJdJyk7XG4gICAgICAgICAgICBwcmVmZXJlbmNlc0Rpc21pc3NFbGVtZW50ID0gZWxlbWVudC5xdWVyeVNlbGVjdG9yKCdbZGF0YS1rdC1zZWFyY2gtZWxlbWVudD1cInByZWZlcmVuY2VzLWRpc21pc3NcIl0nKTtcbiAgICAgICAgICAgIHByZWZlcmVuY2VzU2F2ZUVsZW1lbnQgPSBlbGVtZW50LnF1ZXJ5U2VsZWN0b3IoJ1tkYXRhLWt0LXNlYXJjaC1lbGVtZW50PVwicHJlZmVyZW5jZXMtc2F2ZVwiXScpO1xuXG4gICAgICAgICAgICAvLyBJbml0aWFsaXplIHNlYXJjaCBoYW5kbGVyXG4gICAgICAgICAgICBzZWFyY2hPYmplY3QgPSBuZXcgU2VhcmNoTW9kdWxlKGVsZW1lbnQpXG5cbiAgICAgICAgICAgIC8vIERlbW8gc2VhcmNoIGhhbmRsZXJcbiAgICAgICAgICAgIHNlYXJjaE9iamVjdC5vbigna3Quc2VhcmNoLnByb2Nlc3MnLCBwcm9jZXNzc0FqYXgpO1xuXG4gICAgICAgICAgICAvLyBDbGVhciBoYW5kbGVyXG4gICAgICAgICAgICBzZWFyY2hPYmplY3Qub24oJ2t0LnNlYXJjaC5jbGVhcicsIGNsZWFyKTtcblxuICAgICAgICAgICAgLy8gQ3VzdG9tIGhhbmRsZXJzXG4gICAgICAgICAgICBpZiAocHJlZmVyZW5jZXNFbGVtZW50KSB7XG4gICAgICAgICAgICAgICAgaGFuZGxlUHJlZmVyZW5jZXMoKTtcblxuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfTtcbn0oKTtcblxuLy8gT24gZG9jdW1lbnQgcmVhZHlcbktUVXRpbC5vbkRPTUNvbnRlbnRMb2FkZWQoZnVuY3Rpb24oKSB7XG4gICAgS1RMYXlvdXRTZWFyY2guaW5pdCgpO1xufSk7Il0sIm5hbWVzIjpbXSwic291cmNlUm9vdCI6IiJ9