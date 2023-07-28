"use strict";

// Class definition
var KTToggle = function(element, options) {
    ////////////////////////////
    // ** Private variables  ** //
    ////////////////////////////
    var the = this;

    if (!element) {
        return;
    }

    // Default Options
    var defaultOptions = {
        saveState: true
    };

    ////////////////////////////
    // ** Private methods  ** //
    ////////////////////////////

    var _construct = function() {
        if ( KTUtil.data(element).has('toggle') === true ) {
            the = KTUtil.data(element).get('toggle');
        } else {
            _init();
        }
    }

    var _init = function() {
        // Variables
        the.options = KTUtil.deepExtend({}, defaultOptions, options);
        the.uid = KTUtil.getUniqueId('toggle');

        // Elements
        the.element = element;

        the.target = document.querySelector(the.element.getAttribute('data-kt-toggle-target')) ? document.querySelector(the.element.getAttribute('data-kt-toggle-target')) : the.element;
        the.state = the.element.hasAttribute('data-kt-toggle-state') ? the.element.getAttribute('data-kt-toggle-state') : '';
        the.mode = the.element.hasAttribute('data-kt-toggle-mode') ? the.element.getAttribute('data-kt-toggle-mode') : '';
        the.attribute = 'data-kt-' + the.element.getAttribute('data-kt-toggle-name');

        // Event Handlers
        _handlers();

        // Bind Instance
        KTUtil.data(the.element).set('toggle', the);
    }

    var _handlers = function() {
        KTUtil.addEvent(the.element, 'click', function(e) {
            e.preventDefault();

            if ( the.mode !== '' ) {
                if ( the.mode === 'off' && _isEnabled() === false ) {
                    _toggle();
                } else if ( the.mode === 'on' && _isEnabled() === true ) {
                    _toggle();
                }
            } else {
                _toggle();
            }
        });
    }

    // Event handlers
    var _toggle = function() {
        // Trigger "after.toggle" event
        KTEventHandler.trigger(the.element, 'kt.toggle.change', the);

        if ( _isEnabled() ) {
            _disable();
        } else {
            _enable();
        }       

        // Trigger "before.toggle" event
        KTEventHandler.trigger(the.element, 'kt.toggle.changed', the);

        return the;
    }

    var _enable = function() {
        if ( _isEnabled() === true ) {
            return;
        }

        KTEventHandler.trigger(the.element, 'kt.toggle.enable', the);

        the.target.setAttribute(the.attribute, 'on');

        if (the.state.length > 0) {
            the.element.classList.add(the.state);
        }        

        if ( typeof KTCookie !== 'undefined' && the.options.saveState === true ) {
            KTCookie.set(the.attribute, 'on');
        }

        KTEventHandler.trigger(the.element, 'kt.toggle.enabled', the);

        return the;
    }

    var _disable = function() {
        if ( _isEnabled() === false ) {
            return;
        }

        KTEventHandler.trigger(the.element, 'kt.toggle.disable', the);

        the.target.removeAttribute(the.attribute);

        if (the.state.length > 0) {
            the.element.classList.remove(the.state);
        } 

        if ( typeof KTCookie !== 'undefined' && the.options.saveState === true ) {
            KTCookie.remove(the.attribute);
        }

        KTEventHandler.trigger(the.element, 'kt.toggle.disabled', the);

        return the;
    }

    var _isEnabled = function() {
        return (String(the.target.getAttribute(the.attribute)).toLowerCase() === 'on');
    }

    var _destroy = function() {
        KTUtil.data(the.element).remove('toggle');
    }

    // Construct class
    _construct();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////

    // Plugin API
    the.toggle = function() {
        return _toggle();
    }

    the.enable = function() {
        return _enable();
    }

    the.disable = function() {
        return _disable();
    }

    the.isEnabled = function() {
        return _isEnabled();
    }

    the.goElement = function() {
        return the.element;
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
KTToggle.getInstance = function(element) {
    if ( element !== null && KTUtil.data(element).has('toggle') ) {
        return KTUtil.data(element).get('toggle');
    } else {
        return null;
    }
}

// Create instances
KTToggle.createInstances = function(selector = '[data-kt-toggle]') {
    // Get instances
    var elements = document.body.querySelectorAll(selector);

    if ( elements && elements.length > 0 ) {
        for (var i = 0, len = elements.length; i < len; i++) {
            // Initialize instances
            new KTToggle(elements[i]);
        }
    }
}

// Global initialization
KTToggle.init = function() {
    KTToggle.createInstances();
};

// Webpack support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = KTToggle;
}