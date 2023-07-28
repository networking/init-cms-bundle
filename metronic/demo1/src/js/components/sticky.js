"use strict";

var KTStickyHandlersInitialized = false;

// Class definition
var KTSticky = function(element, options) {
    ////////////////////////////
    // ** Private Variables  ** //
    ////////////////////////////
    var the = this;

    if ( typeof element === "undefined" || element === null ) {
        return;
    }

    // Default Options
    var defaultOptions = {
        offset: 200,
        reverse: false,
        release: null,
        animation: true,
        animationSpeed: '0.3s',
        animationClass: 'animation-slide-in-down'
    };
    ////////////////////////////
    // ** Private Methods  ** //
    ////////////////////////////

    var _construct = function() {
        if ( KTUtil.data(element).has('sticky') === true ) {
            the = KTUtil.data(element).get('sticky');
        } else {
            _init();
        }
    }

    var _init = function() {
        the.element = element;
        the.options = KTUtil.deepExtend({}, defaultOptions, options);
        the.uid = KTUtil.getUniqueId('sticky');
        the.name = the.element.getAttribute('data-kt-sticky-name');
        the.attributeName = 'data-kt-sticky-' + the.name;
        the.attributeName2 = 'data-kt-' + the.name;
        the.eventTriggerState = true;
        the.lastScrollTop = 0;
        the.scrollHandler;

        // Set initialized
        the.element.setAttribute('data-kt-sticky', 'true');

        // Event Handlers
        window.addEventListener('scroll', _scroll);

        // Initial Launch
        _scroll();

        // Bind Instance
        KTUtil.data(the.element).set('sticky', the);
    }

    var _scroll = function(e) {
        var offset = _getOption('offset');
        var release = _getOption('release');
        var reverse = _getOption('reverse');
        var st;
        var attrName;
        var diff;

        // Exit if false
        if ( offset === false ) {
            _disable();
            return;
        }

        offset = parseInt(offset);
        release = release ? document.querySelector(release) : null;

        st = KTUtil.getScrollTop();
        diff = document.documentElement.scrollHeight - window.innerHeight - KTUtil.getScrollTop();
        
        var proceed = (!release || (release.offsetTop - release.clientHeight) > st);

        if ( reverse === true ) {  // Release on reverse scroll mode
            if ( st > offset && proceed ) {
                if ( document.body.hasAttribute(the.attributeName) === false) {
                    
                    if (_enable() === false) {
                        return;
                    }

                    document.body.setAttribute(the.attributeName, 'on');
                    document.body.setAttribute(the.attributeName2, 'on');
                    the.element.setAttribute("data-kt-sticky-enabled", "true");
                }

                if ( the.eventTriggerState === true ) {
                    KTEventHandler.trigger(the.element, 'kt.sticky.on', the);
                    KTEventHandler.trigger(the.element, 'kt.sticky.change', the);

                    the.eventTriggerState = false;
                }
            } else { // Back scroll mode
                if ( document.body.hasAttribute(the.attributeName) === true) {
                    _disable();
                    document.body.removeAttribute(the.attributeName);
                    document.body.removeAttribute(the.attributeName2);
                    the.element.removeAttribute("data-kt-sticky-enabled");
                }

                if ( the.eventTriggerState === false ) {
                    KTEventHandler.trigger(the.element, 'kt.sticky.off', the);
                    KTEventHandler.trigger(the.element, 'kt.sticky.change', the);
                    the.eventTriggerState = true;
                }
            }

            the.lastScrollTop = st;
        } else { // Classic scroll mode
            if ( st > offset && proceed ) {
                if ( document.body.hasAttribute(the.attributeName) === false) {
                    
                    if (_enable() === false) {
                        return;
                    } 
                    
                    document.body.setAttribute(the.attributeName, 'on');
                    document.body.setAttribute(the.attributeName2, 'on');
                    the.element.setAttribute("data-kt-sticky-enabled", "true");
                }

                if ( the.eventTriggerState === true ) {
                    KTEventHandler.trigger(the.element, 'kt.sticky.on', the);
                    KTEventHandler.trigger(the.element, 'kt.sticky.change', the);
                    the.eventTriggerState = false;
                }
            } else { // back scroll mode
                if ( document.body.hasAttribute(the.attributeName) === true ) {
                    _disable();
                    document.body.removeAttribute(the.attributeName);
                    document.body.removeAttribute(the.attributeName2);
                    the.element.removeAttribute("data-kt-sticky-enabled");
                }

                if ( the.eventTriggerState === false ) {
                    KTEventHandler.trigger(the.element, 'kt.sticky.off', the);
                    KTEventHandler.trigger(the.element, 'kt.sticky.change', the);
                    the.eventTriggerState = true;
                }
            }
        }      

        if (release) {
            if ( release.offsetTop - release.clientHeight > st ) {
                the.element.setAttribute('data-kt-sticky-released', 'true');
            } else {
                the.element.removeAttribute('data-kt-sticky-released');
            }
        } 
    }

    var _enable = function(update) {
        var top = _getOption('top');
        top = top ? parseInt(top) : 0;

        var left = _getOption('left');
        var right = _getOption('right');
        var width = _getOption('width');
        var zindex = _getOption('zindex');
        var dependencies = _getOption('dependencies');
        var classes = _getOption('class');

        var height = _calculateHeight();
        var heightOffset = _getOption('height-offset');
        heightOffset = heightOffset ? parseInt(heightOffset) : 0;

        if (height + heightOffset + top > KTUtil.getViewPort().height) {
            return false;
        }
        
        if ( update !== true && _getOption('animation') === true ) {
            KTUtil.css(the.element, 'animationDuration', _getOption('animationSpeed'));
            KTUtil.animateClass(the.element, 'animation ' + _getOption('animationClass'));
        }

        if ( classes !== null ) {
            KTUtil.addClass(the.element, classes);
        }

        if ( zindex !== null ) {
            KTUtil.css(the.element, 'z-index', zindex);
            KTUtil.css(the.element, 'position', 'fixed');
        }

        if ( top >= 0 ) {
            KTUtil.css(the.element, 'top', String(top) + 'px');
        }

        if ( width !== null ) {
            if (width['target']) {
                var targetElement = document.querySelector(width['target']);
                if (targetElement) {
                    width = KTUtil.css(targetElement, 'width');
                }
            }

            KTUtil.css(the.element, 'width', width);
        }

        if ( left !== null ) {
            if ( String(left).toLowerCase() === 'auto' ) {
                var offsetLeft = KTUtil.offset(the.element).left;

                if ( offsetLeft >= 0 ) {
                    KTUtil.css(the.element, 'left', String(offsetLeft) + 'px');
                }
            } else {
                KTUtil.css(the.element, 'left', left);
            }
        }

        if ( right !== null ) {
            KTUtil.css(the.element, 'right', right);
        }        

        // Height dependencies
        if ( dependencies !== null ) {
            var dependencyElements = document.querySelectorAll(dependencies);
            
            if ( dependencyElements && dependencyElements.length > 0 ) {
                for ( var i = 0, len = dependencyElements.length; i < len; i++ ) {
                    KTUtil.css(dependencyElements[i], 'padding-top', String(height) + 'px');
                }
            }
        }
    }

    var _disable = function() {
        KTUtil.css(the.element, 'top', '');
        KTUtil.css(the.element, 'width', '');
        KTUtil.css(the.element, 'left', '');
        KTUtil.css(the.element, 'right', '');
        KTUtil.css(the.element, 'z-index', '');
        KTUtil.css(the.element, 'position', '');

        var dependencies = _getOption('dependencies');
        var classes = _getOption('class');

        if ( classes !== null ) {
            KTUtil.removeClass(the.element, classes);
        }

        // Height dependencies
        if ( dependencies !== null ) {
            var dependencyElements = document.querySelectorAll(dependencies);

            if ( dependencyElements && dependencyElements.length > 0 ) {
                for ( var i = 0, len = dependencyElements.length; i < len; i++ ) {
                    KTUtil.css(dependencyElements[i], 'padding-top', '');
                }
            }
        }
    }

    var _check = function() {

    }

    var _calculateHeight = function() {
        var height = parseFloat(KTUtil.css(the.element, 'height'));

        height = height + parseFloat(KTUtil.css(the.element, 'margin-top'));
        height = height + parseFloat(KTUtil.css(the.element, 'margin-bottom'));
        
        if (KTUtil.css(element, 'border-top')) {
            height = height + parseFloat(KTUtil.css(the.element, 'border-top'));
        }

        if (KTUtil.css(element, 'border-bottom')) {
            height = height + parseFloat(KTUtil.css(the.element, 'border-bottom'));
        }

        return height;
    }

    var _getOption = function(name) {
        if ( the.element.hasAttribute('data-kt-sticky-' + name) === true ) {
            var attr = the.element.getAttribute('data-kt-sticky-' + name);
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

    var _destroy = function() {
        window.removeEventListener('scroll', _scroll);
        KTUtil.data(the.element).remove('sticky');
    }

    // Construct Class
    _construct();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////

    // Methods
    the.update = function() {
        if ( document.body.hasAttribute(the.attributeName) === true ) {
            _disable();
            document.body.removeAttribute(the.attributeName);
            document.body.removeAttribute(the.attributeName2);
            _enable(true);
            document.body.setAttribute(the.attributeName, 'on');
            document.body.setAttribute(the.attributeName2, 'on');
        }
    }

    the.destroy = function() {
        return _destroy();
    }

    // Event API
    the.on = function(name, handler) {
        return KTEventHandler.on(the.element, name, handler);
    }

    the.one = function(name, handler) {
        return KTEventHandler.one(the.element, name, handler);
    }

    the.off = function(name, handlerId) {
        return KTEventHandler.off(the.element, name, handlerId);
    }

    the.trigger = function(name, event) {
        return KTEventHandler.trigger(the.element, name, event, the, event);
    }
};

// Static methods
KTSticky.getInstance = function(element) {
    if ( element !== null && KTUtil.data(element).has('sticky') ) {
        return KTUtil.data(element).get('sticky');
    } else {
        return null;
    }
}

// Create instances
KTSticky.createInstances = function(selector = '[data-kt-sticky="true"]') {
    // Initialize Menus
    var elements = document.body.querySelectorAll(selector);
    var sticky;

    if ( elements && elements.length > 0 ) {
        for (var i = 0, len = elements.length; i < len; i++) {
            sticky = new KTSticky(elements[i]);
        }
    }
}

// Window resize handler
KTSticky.handleResize = function() {
    window.addEventListener('resize', function() {
        var timer;
    
        KTUtil.throttle(timer, function() {
            // Locate and update Offcanvas instances on window resize
            var elements = document.body.querySelectorAll('[data-kt-sticky="true"]');
    
            if ( elements && elements.length > 0 ) {
                for (var i = 0, len = elements.length; i < len; i++) {
                    var sticky = KTSticky.getInstance(elements[i]);
                    if (sticky) {
                        sticky.update();
                    }
                }
            }
        }, 200);
    });
}

// Global initialization
KTSticky.init = function() {
    KTSticky.createInstances();

    if (KTStickyHandlersInitialized === false) {
        KTSticky.handleResize();
        KTStickyHandlersInitialized = true;
    }    
};

// Webpack support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = KTSticky;
}
