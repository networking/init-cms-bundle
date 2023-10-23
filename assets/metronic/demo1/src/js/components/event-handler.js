"use strict";

// Class definition
var KTEventHandler = function() {
    ////////////////////////////
    // ** Private Variables  ** //
    ////////////////////////////
    var _handlers = {};

    ////////////////////////////
    // ** Private Methods  ** //
    ////////////////////////////
    var _triggerEvent = function(element, name, target) {
        var returnValue = true;
        var eventValue;

        if ( KTUtil.data(element).has(name) === true ) {
            var handlerIds = KTUtil.data(element).get(name);
            var handlerId;

            for (var i = 0; i < handlerIds.length; i++) {
                handlerId = handlerIds[i];
                
                if ( _handlers[name] && _handlers[name][handlerId] ) {
                    var handler = _handlers[name][handlerId];
                    var value;
    
                    if ( handler.name === name ) {
                        if ( handler.one == true ) {
                            if ( handler.fired == false ) {
                                _handlers[name][handlerId].fired = true;
    
                                eventValue = handler.callback.call(this, target);
                            }
                        } else {
                            eventValue = handler.callback.call(this, target);
                        }

                        if ( eventValue === false ) {
                            returnValue = false;
                        }
                    }
                }
            }            
        }

        return returnValue;
    }

    var _addEvent = function(element, name, callback, one) {
        var handlerId = KTUtil.getUniqueId('event');
        var handlerIds = KTUtil.data(element).get(name);

        if ( !handlerIds ) {
            handlerIds = [];
        } 

        handlerIds.push(handlerId);

        KTUtil.data(element).set(name, handlerIds);

        if ( !_handlers[name] ) {
            _handlers[name] = {};
        }

        _handlers[name][handlerId] = {
            name: name,
            callback: callback,
            one: one,
            fired: false
        };

        return handlerId;
    }

    var _removeEvent = function(element, name, handlerId) {
        var handlerIds = KTUtil.data(element).get(name);
        var index = handlerIds && handlerIds.indexOf(handlerId);
        
        if (index !== -1) {
            handlerIds.splice(index, 1);
            KTUtil.data(element).set(name, handlerIds);
        }

        if (_handlers[name] && _handlers[name][handlerId]) {
            delete _handlers[name][handlerId];
        }
    }

    ////////////////////////////
    // ** Public Methods  ** //
    ////////////////////////////
    return {
        trigger: function(element, name, target) {
            return _triggerEvent(element, name, target);
        },

        on: function(element, name, handler) {
            return _addEvent(element, name, handler);
        },

        one: function(element, name, handler) {
            return _addEvent(element, name, handler, true);
        },

        off: function(element, name, handlerId) {
            return _removeEvent(element, name, handlerId);
        },

        debug: function() {
            for (var b in _handlers) {
                if ( _handlers.hasOwnProperty(b) ) console.log(b);
            }
        }
    }
}();

// Webpack support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = KTEventHandler;
}
