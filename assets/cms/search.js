"use strict";

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

export default SearchModule;