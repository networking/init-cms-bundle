"use strict";

var KTScrollHandlersInitialized = false;

// Class definition
var KTScroll = function(element, options) {
    ////////////////////////////
    // ** Private Variables  ** //
    ////////////////////////////
    var the = this;

    if (!element) {
        return;
    }

    // Default options
    var defaultOptions = {
        saveState: true
    };

    ////////////////////////////
    // ** Private Methods  ** //
    ////////////////////////////

    var _construct = function() {
        if ( KTUtil.data(element).has('scroll') ) {
            the = KTUtil.data(element).get('scroll');
        } else {
            _init();
        }
    }

    var _init = function() {
        // Variables
        the.options = KTUtil.deepExtend({}, defaultOptions, options);

        // Elements
        the.element = element;        
        the.id = the.element.getAttribute('id');

        // Set initialized
        the.element.setAttribute('data-kt-scroll', 'true');

        // Update
        _update();

        // Bind Instance
        KTUtil.data(the.element).set('scroll', the);
    }

    var _setupHeight = function() {
        var heightType = _getHeightType();
        var height = _getHeight();

        // Set height
        if ( height !== null && height.length > 0 ) {
            KTUtil.css(the.element, heightType, height);
        } else {
            KTUtil.css(the.element, heightType, '');
        }
    }

    var _setupState = function () {
        var namespace = _getStorageNamespace();

        if ( _getOption('save-state') === true && the.id ) {
            if ( localStorage.getItem(namespace + the.id + 'st') ) {
                var pos = parseInt(localStorage.getItem(namespace + the.id + 'st'));

                if ( pos > 0 ) {
                    the.element.scroll({
                        top: pos,
                        behavior: 'instant'
                    });
                }
            }
        }
    }

    var _getStorageNamespace = function(postfix) {
        return document.body.hasAttribute("data-kt-name") ? document.body.getAttribute("data-kt-name") + "_" : "";
    }

    var _setupScrollHandler = function() {
        if ( _getOption('save-state') === true && the.id ) {
            the.element.addEventListener('scroll', _scrollHandler);
        } else {
            the.element.removeEventListener('scroll', _scrollHandler);
        }
    }

    var _destroyScrollHandler = function() {
        the.element.removeEventListener('scroll', _scrollHandler);
    }

    var _resetHeight = function() {
        KTUtil.css(the.element, _getHeightType(), '');
    }

    var _scrollHandler = function () {
        var namespace = _getStorageNamespace();
        localStorage.setItem(namespace + the.id + 'st', the.element.scrollTop);
    }

    var _update = function() {
        // Activate/deactivate
        if ( _getOption('activate') === true || the.element.hasAttribute('data-kt-scroll-activate') === false ) {
            _setupHeight();
            _setupStretchHeight();
            _setupScrollHandler();
            _setupState();
        } else {
            _resetHeight()
            _destroyScrollHandler();
        }        
    }

    var _setupStretchHeight = function() {
        var stretch = _getOption('stretch');

        // Stretch
        if ( stretch !== null ) {
            var elements = document.querySelectorAll(stretch);

            if ( elements && elements.length == 2 ) {
                var element1 = elements[0];
                var element2 = elements[1];
                var diff = _getElementHeight(element2) - _getElementHeight(element1);

                if (diff > 0) {
                    var height = parseInt(KTUtil.css(the.element, _getHeightType())) + diff;

                    KTUtil.css(the.element, _getHeightType(), String(height) + 'px');
                }
            }
        }
    }

    var _getHeight = function() {
        var height = _getOption(_getHeightType());

        if ( height instanceof Function ) {
            return height.call();
        } else if ( height !== null && typeof height === 'string' && height.toLowerCase() === 'auto' ) {
            return _getAutoHeight();
        } else {
            return height;
        }
    }

    var _getAutoHeight = function() {
        var height = KTUtil.getViewPort().height;
        var dependencies = _getOption('dependencies');
        var wrappers = _getOption('wrappers');
        var offset = _getOption('offset');

        // Spacings
        height = height - _getElementSpacing(the.element); 

        // Height dependencies
        //console.log('Q:' + JSON.stringify(dependencies));

        if ( dependencies !== null ) {
            var elements = document.querySelectorAll(dependencies);

            if ( elements && elements.length > 0 ) {
                for ( var i = 0, len = elements.length; i < len; i++ ) {
                    if ( KTUtil.visible(elements[i]) === false ) {
                        continue;
                    }

                    height = height - _getElementHeight(elements[i]);
                }
            }
        }

        // Wrappers
        if ( wrappers !== null ) {
            var elements = document.querySelectorAll(wrappers);
            if ( elements && elements.length > 0 ) {
                for ( var i = 0, len = elements.length; i < len; i++ ) {
                    if ( KTUtil.visible(elements[i]) === false ) {
                        continue;
                    }

                    height = height - _getElementSpacing(elements[i]);
                }
            }
        }

        // Custom offset
        if ( offset !== null && typeof offset !== 'object') {
            height = height - parseInt(offset);
        }

        return String(height) + 'px';
    }

    var _getElementHeight = function(element) {
        var height = 0;

        if (element !== null) {
            height = height + parseInt(KTUtil.css(element, 'height'));
            height = height + parseInt(KTUtil.css(element, 'margin-top'));
            height = height + parseInt(KTUtil.css(element, 'margin-bottom'));

            if (KTUtil.css(element, 'border-top')) {
                height = height + parseInt(KTUtil.css(element, 'border-top'));
            }

            if (KTUtil.css(element, 'border-bottom')) {
                height = height + parseInt(KTUtil.css(element, 'border-bottom'));
            }
        } 

        return height;
    }

    var _getElementSpacing = function(element) {
        var spacing = 0;

        if (element !== null) {
            spacing = spacing + parseInt(KTUtil.css(element, 'margin-top'));
            spacing = spacing + parseInt(KTUtil.css(element, 'margin-bottom'));
            spacing = spacing + parseInt(KTUtil.css(element, 'padding-top'));
            spacing = spacing + parseInt(KTUtil.css(element, 'padding-bottom'));

            if (KTUtil.css(element, 'border-top')) {
                spacing = spacing + parseInt(KTUtil.css(element, 'border-top'));
            }

            if (KTUtil.css(element, 'border-bottom')) {
                spacing = spacing + parseInt(KTUtil.css(element, 'border-bottom'));
            }
        } 

        return spacing;
    }

    var _getOption = function(name) {
        if ( the.element.hasAttribute('data-kt-scroll-' + name) === true ) {
            var attr = the.element.getAttribute('data-kt-scroll-' + name);

            var value = KTUtil.getResponsiveValue(attr);

            if ( value !== null && String(value) === 'true' ) {
                value = true;
            } else if ( value !== null && String(value) === 'false' ) {
                value = false;
            }

            return value;
        } else {
            var optionName = KTUtil.snakeToCamel(name);

            if ( the.options[optionName] ) {
                return KTUtil.getResponsiveValue(the.options[optionName]);
            } else {
                return null;
            }
        }
    }

    var _getHeightType = function() {
        if (_getOption('height')) {
            return 'height';
        } if (_getOption('min-height')) {
            return 'min-height';
        } if (_getOption('max-height')) {
            return 'max-height';
        }
    }

    var _destroy = function() {
        KTUtil.data(the.element).remove('scroll');
    }

    // Construct Class
    _construct();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////

    the.update = function() {
        return _update();
    }

    the.getHeight = function() {
        return _getHeight();
    }

    the.getElement = function() {
        return the.element;
    }

    the.destroy = function() {
        return _destroy();
    }
};

// Static methods
KTScroll.getInstance = function(element) {
    if ( element !== null && KTUtil.data(element).has('scroll') ) {
        return KTUtil.data(element).get('scroll');
    } else {
        return null;
    }
}

// Create instances
KTScroll.createInstances = function(selector = '[data-kt-scroll="true"]') {
    // Initialize Menus
    var elements = document.body.querySelectorAll(selector);

    if ( elements && elements.length > 0 ) {
        for (var i = 0, len = elements.length; i < len; i++) {
            new KTScroll(elements[i]);
        }
    }
}

// Window resize handling
KTScroll.handleResize = function() {
    window.addEventListener('resize', function() {
        var timer;
    
        KTUtil.throttle(timer, function() {
            // Locate and update Offcanvas instances on window resize
            var elements = document.body.querySelectorAll('[data-kt-scroll="true"]');
    
            if ( elements && elements.length > 0 ) {
                for (var i = 0, len = elements.length; i < len; i++) {
                    var scroll = KTScroll.getInstance(elements[i]);
                    if (scroll) {
                        scroll.update();
                    }
                }
            }
        }, 200);
    });
}

// Global initialization
KTScroll.init = function() {
    KTScroll.createInstances();

    if (KTScrollHandlersInitialized === false) {
        KTScroll.handleResize();

        KTScrollHandlersInitialized = true;
    }    
};

// Webpack Support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = KTScroll;
}
