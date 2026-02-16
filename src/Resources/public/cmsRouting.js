/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "../../friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js"
/*!*****************************************************************************!*\
  !*** ../../friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js ***!
  \*****************************************************************************/
(module, exports) {

var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;(function (root, factory) {
    var routing = factory();
    if (true) {
        // AMD. Register as an anonymous module.
        !(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_FACTORY__ = (routing.Routing),
		__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
		(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
		__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
    } else // removed by dead control flow
{}
}(this, function () {
    var exports = {};
    "use strict";
exports.__esModule = true;
exports.Routing = exports.Router = void 0;
var Router = /** @class */ (function () {
    function Router(context, routes) {
        this.context_ = context || { base_url: '', prefix: '', host: '', port: '', scheme: '', locale: '' };
        this.setRoutes(routes || {});
    }
    Router.getInstance = function () {
        return exports.Routing;
    };
    Router.setData = function (data) {
        var router = Router.getInstance();
        router.setRoutingData(data);
    };
    Router.prototype.setRoutingData = function (data) {
        this.setBaseUrl(data['base_url']);
        this.setRoutes(data['routes']);
        if (typeof data.prefix !== 'undefined') {
            this.setPrefix(data['prefix']);
        }
        if (typeof data.port !== 'undefined') {
            this.setPort(data['port']);
        }
        if (typeof data.locale !== 'undefined') {
            this.setLocale(data['locale']);
        }
        this.setHost(data['host']);
        if (typeof data.scheme !== 'undefined') {
            this.setScheme(data['scheme']);
        }
    };
    Router.prototype.setRoutes = function (routes) {
        this.routes_ = Object.freeze(routes);
    };
    Router.prototype.getRoutes = function () {
        return this.routes_;
    };
    Router.prototype.setBaseUrl = function (baseUrl) {
        this.context_.base_url = baseUrl;
    };
    Router.prototype.getBaseUrl = function () {
        return this.context_.base_url;
    };
    Router.prototype.setPrefix = function (prefix) {
        this.context_.prefix = prefix;
    };
    Router.prototype.setScheme = function (scheme) {
        this.context_.scheme = scheme;
    };
    Router.prototype.getScheme = function () {
        return this.context_.scheme;
    };
    Router.prototype.setHost = function (host) {
        this.context_.host = host;
    };
    Router.prototype.getHost = function () {
        return this.context_.host;
    };
    Router.prototype.setPort = function (port) {
        this.context_.port = port;
    };
    Router.prototype.getPort = function () {
        return this.context_.port;
    };
    ;
    Router.prototype.setLocale = function (locale) {
        this.context_.locale = locale;
    };
    Router.prototype.getLocale = function () {
        return this.context_.locale;
    };
    ;
    /**
     * Builds query string params added to a URL.
     * Port of jQuery's $.param() function, so credit is due there.
     */
    Router.prototype.buildQueryParams = function (prefix, params, add) {
        var _this = this;
        var name;
        var rbracket = new RegExp(/\[\]$/);
        if (params instanceof Array) {
            params.forEach(function (val, i) {
                if (rbracket.test(prefix)) {
                    add(prefix, val);
                }
                else {
                    _this.buildQueryParams(prefix + '[' + (typeof val === 'object' ? i : '') + ']', val, add);
                }
            });
        }
        else if (typeof params === 'object') {
            for (name in params) {
                this.buildQueryParams(prefix + '[' + name + ']', params[name], add);
            }
        }
        else {
            add(prefix, params);
        }
    };
    /**
     * Returns a raw route object.
     */
    Router.prototype.getRoute = function (name) {
        var prefixedName = this.context_.prefix + name;
        var sf41i18nName = name + '.' + this.context_.locale;
        var prefixedSf41i18nName = this.context_.prefix + name + '.' + this.context_.locale;
        var variants = [prefixedName, sf41i18nName, prefixedSf41i18nName, name];
        for (var i in variants) {
            if (variants[i] in this.routes_) {
                return this.routes_[variants[i]];
            }
        }
        throw new Error('The route "' + name + '" does not exist.');
    };
    /**
     * Generates the URL for a route.
     */
    Router.prototype.generate = function (name, opt_params, absolute) {
        var route = (this.getRoute(name));
        var params = opt_params || {};
        var unusedParams = Object.assign({}, params);
        var url = '';
        var optional = true;
        var host = '';
        var port = (typeof this.getPort() == 'undefined' || this.getPort() === null) ? '' : this.getPort();
        route.tokens.forEach(function (token) {
            if ('text' === token[0] && typeof token[1] === 'string') {
                url = Router.encodePathComponent(token[1]) + url;
                optional = false;
                return;
            }
            if ('variable' === token[0]) {
                if (token.length === 6 && token[5] === true) { // Sixth part of the token array indicates if it should be included in case of defaults
                    optional = false;
                }
                var hasDefault = route.defaults && !Array.isArray(route.defaults) && typeof token[3] === 'string' && (token[3] in route.defaults);
                if (false === optional || !hasDefault || ((typeof token[3] === 'string' && token[3] in params) && !Array.isArray(route.defaults) && params[token[3]] != route.defaults[token[3]])) {
                    var value = void 0;
                    if (typeof token[3] === 'string' && token[3] in params) {
                        value = params[token[3]];
                        delete unusedParams[token[3]];
                    }
                    else if (typeof token[3] === 'string' && hasDefault && !Array.isArray(route.defaults)) {
                        value = route.defaults[token[3]];
                    }
                    else if (optional) {
                        return;
                    }
                    else {
                        throw new Error('The route "' + name + '" requires the parameter "' + token[3] + '".');
                    }
                    var empty = true === value || false === value || '' === value;
                    if (!empty || !optional) {
                        var encodedValue = Router.encodePathComponent(value);
                        if ('null' === encodedValue && null === value) {
                            encodedValue = '';
                        }
                        url = token[1] + encodedValue + url;
                    }
                    optional = false;
                }
                else if (hasDefault && (typeof token[3] === 'string' && token[3] in unusedParams)) {
                    delete unusedParams[token[3]];
                }
                return;
            }
            throw new Error('The token type "' + token[0] + '" is not supported.');
        });
        if (url === '') {
            url = '/';
        }
        route.hosttokens.forEach(function (token) {
            var value;
            if ('text' === token[0]) {
                host = token[1] + host;
                return;
            }
            if ('variable' === token[0]) {
                if (token[3] in params) {
                    value = params[token[3]];
                    delete unusedParams[token[3]];
                }
                else if (route.defaults && !Array.isArray(route.defaults) && (token[3] in route.defaults)) {
                    value = route.defaults[token[3]];
                }
                host = token[1] + value + host;
            }
        });
        url = this.context_.base_url + url;
        if (route.requirements && ('_scheme' in route.requirements) && this.getScheme() != route.requirements['_scheme']) {
            var currentHost = host || this.getHost();
            url = route.requirements['_scheme'] + '://' + currentHost + (currentHost.indexOf(':' + port) > -1 || '' === port ? '' : ':' + port) + url;
        }
        else if ('undefined' !== typeof route.schemes && 'undefined' !== typeof route.schemes[0] && this.getScheme() !== route.schemes[0]) {
            var currentHost = host || this.getHost();
            url = route.schemes[0] + '://' + currentHost + (currentHost.indexOf(':' + port) > -1 || '' === port ? '' : ':' + port) + url;
        }
        else if (host && this.getHost() !== host + (host.indexOf(':' + port) > -1 || '' === port ? '' : ':' + port)) {
            url = this.getScheme() + '://' + host + (host.indexOf(':' + port) > -1 || '' === port ? '' : ':' + port) + url;
        }
        else if (absolute === true) {
            url = this.getScheme() + '://' + this.getHost() + (this.getHost().indexOf(':' + port) > -1 || '' === port ? '' : ':' + port) + url;
        }
        if (Object.keys(unusedParams).length > 0) {
            var queryParams_1 = [];
            var add = function (key, value) {
                // if value is a function then call it and assign it's return value as value
                value = (typeof value === 'function') ? value() : value;
                // change null to empty string
                value = (value === null) ? '' : value;
                queryParams_1.push(Router.encodeQueryComponent(key) + '=' + Router.encodeQueryComponent(value));
            };
            for (var prefix in unusedParams) {
                if (unusedParams.hasOwnProperty(prefix)) {
                    this.buildQueryParams(prefix, unusedParams[prefix], add);
                }
            }
            url = url + '?' + queryParams_1.join('&');
        }
        return url;
    };
    /**
     * Returns the given string encoded to mimic Symfony URL generator.
     */
    Router.customEncodeURIComponent = function (value) {
        return encodeURIComponent(value)
            .replace(/%2F/g, '/')
            .replace(/%40/g, '@')
            .replace(/%3A/g, ':')
            .replace(/%21/g, '!')
            .replace(/%3B/g, ';')
            .replace(/%2C/g, ',')
            .replace(/%2A/g, '*')
            .replace(/\(/g, '%28')
            .replace(/\)/g, '%29')
            .replace(/'/g, '%27');
    };
    /**
     * Returns the given path properly encoded to mimic Symfony URL generator.
     */
    Router.encodePathComponent = function (value) {
        return Router.customEncodeURIComponent(value)
            .replace(/%3D/g, '=')
            .replace(/%2B/g, '+')
            .replace(/%21/g, '!')
            .replace(/%7C/g, '|');
    };
    /**
     * Returns the given query parameter or value properly encoded to mimic Symfony URL generator.
     */
    Router.encodeQueryComponent = function (value) {
        return Router.customEncodeURIComponent(value)
            .replace(/%3F/g, '?');
    };
    return Router;
}());
exports.Router = Router;
exports.Routing = new Router();
exports["default"] = exports.Routing;


    return { Router: exports.Router, Routing: exports.Routing };
}));

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
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
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
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
/*!***********************************!*\
  !*** ./assets/cms/cms-routing.js ***!
  \***********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var fos_router__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! fos-router */ "../../friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js");
/* harmony import */ var fos_router__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(fos_router__WEBPACK_IMPORTED_MODULE_0__);


class CMSRoutingObject {
    constructor() {
        this.instance = null;
    }
    setRouting(routing){
        fos_router__WEBPACK_IMPORTED_MODULE_0___default().setRoutingData(routing);
        this.instance = (fos_router__WEBPACK_IMPORTED_MODULE_0___default());
    }
    generate(name, params = {}, absolute = false){
        return this.instance.generate(name, params, absolute);
    }
}

window.CMSRouting = new CMSRoutingObject()



})();

/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiY21zUm91dGluZy5qcyIsIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7QUFBQTtBQUNBO0FBQ0EsUUFBUSxJQUEwQztBQUNsRDtBQUNBLFFBQVEsaUNBQU8sRUFBRSxvQ0FBRSxlQUFlO0FBQUE7QUFBQTtBQUFBLGtHQUFDO0FBQ25DLE1BQU0sS0FBSztBQUFBLEVBV047QUFDTCxDQUFDO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EscUNBQXFDO0FBQ3JDLG1DQUFtQztBQUNuQztBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGFBQWE7QUFDYjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLDJDQUEyQztBQUMzQztBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsK0RBQStEO0FBQy9EO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNUO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNUO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLCtCQUErQjtBQUMvQjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxDQUFDO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGFBQWE7QUFDYixDQUFDLEk7Ozs7OztVQzFSRDtVQUNBOztVQUVBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBOztVQUVBO1VBQ0E7O1VBRUE7VUFDQTtVQUNBOzs7OztXQzVCQTtXQUNBO1dBQ0E7V0FDQTtXQUNBO1dBQ0EsaUNBQWlDLFdBQVc7V0FDNUM7V0FDQSxFOzs7OztXQ1BBO1dBQ0E7V0FDQTtXQUNBO1dBQ0EseUNBQXlDLHdDQUF3QztXQUNqRjtXQUNBO1dBQ0EsRTs7Ozs7V0NQQSx3Rjs7Ozs7V0NBQTtXQUNBO1dBQ0E7V0FDQSx1REFBdUQsaUJBQWlCO1dBQ3hFO1dBQ0EsZ0RBQWdELGFBQWE7V0FDN0QsRTs7Ozs7Ozs7Ozs7Ozs7QUNOaUM7O0FBRWpDO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxRQUFRLGdFQUFzQjtBQUM5Qix3QkFBd0IsbURBQU87QUFDL0I7QUFDQSw4QkFBOEI7QUFDOUI7QUFDQTtBQUNBOztBQUVBIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4uLy4uL2ZyaWVuZHNvZnN5bWZvbnkvanNyb3V0aW5nLWJ1bmRsZS9SZXNvdXJjZXMvcHVibGljL2pzL3JvdXRlci5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlL3dlYnBhY2svcnVudGltZS9jb21wYXQgZ2V0IGRlZmF1bHQgZXhwb3J0Iiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS93ZWJwYWNrL3J1bnRpbWUvZGVmaW5lIHByb3BlcnR5IGdldHRlcnMiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlL3dlYnBhY2svcnVudGltZS9oYXNPd25Qcm9wZXJ0eSBzaG9ydGhhbmQiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlL3dlYnBhY2svcnVudGltZS9tYWtlIG5hbWVzcGFjZSBvYmplY3QiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vYXNzZXRzL2Ntcy9jbXMtcm91dGluZy5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24gKHJvb3QsIGZhY3RvcnkpIHtcclxuICAgIHZhciByb3V0aW5nID0gZmFjdG9yeSgpO1xyXG4gICAgaWYgKHR5cGVvZiBkZWZpbmUgPT09ICdmdW5jdGlvbicgJiYgZGVmaW5lLmFtZCkge1xyXG4gICAgICAgIC8vIEFNRC4gUmVnaXN0ZXIgYXMgYW4gYW5vbnltb3VzIG1vZHVsZS5cclxuICAgICAgICBkZWZpbmUoW10sIHJvdXRpbmcuUm91dGluZyk7XHJcbiAgICB9IGVsc2UgaWYgKHR5cGVvZiBtb2R1bGUgPT09ICdvYmplY3QnICYmIG1vZHVsZS5leHBvcnRzKSB7XHJcbiAgICAgICAgLy8gTm9kZS4gRG9lcyBub3Qgd29yayB3aXRoIHN0cmljdCBDb21tb25KUywgYnV0XHJcbiAgICAgICAgLy8gb25seSBDb21tb25KUy1saWtlIGVudmlyb25tZW50cyB0aGF0IHN1cHBvcnQgbW9kdWxlLmV4cG9ydHMsXHJcbiAgICAgICAgLy8gbGlrZSBOb2RlLlxyXG4gICAgICAgIG1vZHVsZS5leHBvcnRzID0gcm91dGluZy5Sb3V0aW5nO1xyXG4gICAgfSBlbHNlIHtcclxuICAgICAgICAvLyBCcm93c2VyIGdsb2JhbHMgKHJvb3QgaXMgd2luZG93KVxyXG4gICAgICAgIHJvb3QuUm91dGluZyA9IHJvdXRpbmcuUm91dGluZztcclxuICAgICAgICByb290LmZvcyA9IHtcclxuICAgICAgICAgICAgUm91dGVyOiByb3V0aW5nLlJvdXRlclxyXG4gICAgICAgIH07XHJcbiAgICB9XHJcbn0odGhpcywgZnVuY3Rpb24gKCkge1xyXG4gICAgdmFyIGV4cG9ydHMgPSB7fTtcclxuICAgIFwidXNlIHN0cmljdFwiO1xuZXhwb3J0cy5fX2VzTW9kdWxlID0gdHJ1ZTtcbmV4cG9ydHMuUm91dGluZyA9IGV4cG9ydHMuUm91dGVyID0gdm9pZCAwO1xudmFyIFJvdXRlciA9IC8qKiBAY2xhc3MgKi8gKGZ1bmN0aW9uICgpIHtcbiAgICBmdW5jdGlvbiBSb3V0ZXIoY29udGV4dCwgcm91dGVzKSB7XG4gICAgICAgIHRoaXMuY29udGV4dF8gPSBjb250ZXh0IHx8IHsgYmFzZV91cmw6ICcnLCBwcmVmaXg6ICcnLCBob3N0OiAnJywgcG9ydDogJycsIHNjaGVtZTogJycsIGxvY2FsZTogJycgfTtcbiAgICAgICAgdGhpcy5zZXRSb3V0ZXMocm91dGVzIHx8IHt9KTtcbiAgICB9XG4gICAgUm91dGVyLmdldEluc3RhbmNlID0gZnVuY3Rpb24gKCkge1xuICAgICAgICByZXR1cm4gZXhwb3J0cy5Sb3V0aW5nO1xuICAgIH07XG4gICAgUm91dGVyLnNldERhdGEgPSBmdW5jdGlvbiAoZGF0YSkge1xuICAgICAgICB2YXIgcm91dGVyID0gUm91dGVyLmdldEluc3RhbmNlKCk7XG4gICAgICAgIHJvdXRlci5zZXRSb3V0aW5nRGF0YShkYXRhKTtcbiAgICB9O1xuICAgIFJvdXRlci5wcm90b3R5cGUuc2V0Um91dGluZ0RhdGEgPSBmdW5jdGlvbiAoZGF0YSkge1xuICAgICAgICB0aGlzLnNldEJhc2VVcmwoZGF0YVsnYmFzZV91cmwnXSk7XG4gICAgICAgIHRoaXMuc2V0Um91dGVzKGRhdGFbJ3JvdXRlcyddKTtcbiAgICAgICAgaWYgKHR5cGVvZiBkYXRhLnByZWZpeCAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICAgICAgICAgIHRoaXMuc2V0UHJlZml4KGRhdGFbJ3ByZWZpeCddKTtcbiAgICAgICAgfVxuICAgICAgICBpZiAodHlwZW9mIGRhdGEucG9ydCAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICAgICAgICAgIHRoaXMuc2V0UG9ydChkYXRhWydwb3J0J10pO1xuICAgICAgICB9XG4gICAgICAgIGlmICh0eXBlb2YgZGF0YS5sb2NhbGUgIT09ICd1bmRlZmluZWQnKSB7XG4gICAgICAgICAgICB0aGlzLnNldExvY2FsZShkYXRhWydsb2NhbGUnXSk7XG4gICAgICAgIH1cbiAgICAgICAgdGhpcy5zZXRIb3N0KGRhdGFbJ2hvc3QnXSk7XG4gICAgICAgIGlmICh0eXBlb2YgZGF0YS5zY2hlbWUgIT09ICd1bmRlZmluZWQnKSB7XG4gICAgICAgICAgICB0aGlzLnNldFNjaGVtZShkYXRhWydzY2hlbWUnXSk7XG4gICAgICAgIH1cbiAgICB9O1xuICAgIFJvdXRlci5wcm90b3R5cGUuc2V0Um91dGVzID0gZnVuY3Rpb24gKHJvdXRlcykge1xuICAgICAgICB0aGlzLnJvdXRlc18gPSBPYmplY3QuZnJlZXplKHJvdXRlcyk7XG4gICAgfTtcbiAgICBSb3V0ZXIucHJvdG90eXBlLmdldFJvdXRlcyA9IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMucm91dGVzXztcbiAgICB9O1xuICAgIFJvdXRlci5wcm90b3R5cGUuc2V0QmFzZVVybCA9IGZ1bmN0aW9uIChiYXNlVXJsKSB7XG4gICAgICAgIHRoaXMuY29udGV4dF8uYmFzZV91cmwgPSBiYXNlVXJsO1xuICAgIH07XG4gICAgUm91dGVyLnByb3RvdHlwZS5nZXRCYXNlVXJsID0gZnVuY3Rpb24gKCkge1xuICAgICAgICByZXR1cm4gdGhpcy5jb250ZXh0Xy5iYXNlX3VybDtcbiAgICB9O1xuICAgIFJvdXRlci5wcm90b3R5cGUuc2V0UHJlZml4ID0gZnVuY3Rpb24gKHByZWZpeCkge1xuICAgICAgICB0aGlzLmNvbnRleHRfLnByZWZpeCA9IHByZWZpeDtcbiAgICB9O1xuICAgIFJvdXRlci5wcm90b3R5cGUuc2V0U2NoZW1lID0gZnVuY3Rpb24gKHNjaGVtZSkge1xuICAgICAgICB0aGlzLmNvbnRleHRfLnNjaGVtZSA9IHNjaGVtZTtcbiAgICB9O1xuICAgIFJvdXRlci5wcm90b3R5cGUuZ2V0U2NoZW1lID0gZnVuY3Rpb24gKCkge1xuICAgICAgICByZXR1cm4gdGhpcy5jb250ZXh0Xy5zY2hlbWU7XG4gICAgfTtcbiAgICBSb3V0ZXIucHJvdG90eXBlLnNldEhvc3QgPSBmdW5jdGlvbiAoaG9zdCkge1xuICAgICAgICB0aGlzLmNvbnRleHRfLmhvc3QgPSBob3N0O1xuICAgIH07XG4gICAgUm91dGVyLnByb3RvdHlwZS5nZXRIb3N0ID0gZnVuY3Rpb24gKCkge1xuICAgICAgICByZXR1cm4gdGhpcy5jb250ZXh0Xy5ob3N0O1xuICAgIH07XG4gICAgUm91dGVyLnByb3RvdHlwZS5zZXRQb3J0ID0gZnVuY3Rpb24gKHBvcnQpIHtcbiAgICAgICAgdGhpcy5jb250ZXh0Xy5wb3J0ID0gcG9ydDtcbiAgICB9O1xuICAgIFJvdXRlci5wcm90b3R5cGUuZ2V0UG9ydCA9IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuY29udGV4dF8ucG9ydDtcbiAgICB9O1xuICAgIDtcbiAgICBSb3V0ZXIucHJvdG90eXBlLnNldExvY2FsZSA9IGZ1bmN0aW9uIChsb2NhbGUpIHtcbiAgICAgICAgdGhpcy5jb250ZXh0Xy5sb2NhbGUgPSBsb2NhbGU7XG4gICAgfTtcbiAgICBSb3V0ZXIucHJvdG90eXBlLmdldExvY2FsZSA9IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuY29udGV4dF8ubG9jYWxlO1xuICAgIH07XG4gICAgO1xuICAgIC8qKlxuICAgICAqIEJ1aWxkcyBxdWVyeSBzdHJpbmcgcGFyYW1zIGFkZGVkIHRvIGEgVVJMLlxuICAgICAqIFBvcnQgb2YgalF1ZXJ5J3MgJC5wYXJhbSgpIGZ1bmN0aW9uLCBzbyBjcmVkaXQgaXMgZHVlIHRoZXJlLlxuICAgICAqL1xuICAgIFJvdXRlci5wcm90b3R5cGUuYnVpbGRRdWVyeVBhcmFtcyA9IGZ1bmN0aW9uIChwcmVmaXgsIHBhcmFtcywgYWRkKSB7XG4gICAgICAgIHZhciBfdGhpcyA9IHRoaXM7XG4gICAgICAgIHZhciBuYW1lO1xuICAgICAgICB2YXIgcmJyYWNrZXQgPSBuZXcgUmVnRXhwKC9cXFtcXF0kLyk7XG4gICAgICAgIGlmIChwYXJhbXMgaW5zdGFuY2VvZiBBcnJheSkge1xuICAgICAgICAgICAgcGFyYW1zLmZvckVhY2goZnVuY3Rpb24gKHZhbCwgaSkge1xuICAgICAgICAgICAgICAgIGlmIChyYnJhY2tldC50ZXN0KHByZWZpeCkpIHtcbiAgICAgICAgICAgICAgICAgICAgYWRkKHByZWZpeCwgdmFsKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgIF90aGlzLmJ1aWxkUXVlcnlQYXJhbXMocHJlZml4ICsgJ1snICsgKHR5cGVvZiB2YWwgPT09ICdvYmplY3QnID8gaSA6ICcnKSArICddJywgdmFsLCBhZGQpO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgICAgIGVsc2UgaWYgKHR5cGVvZiBwYXJhbXMgPT09ICdvYmplY3QnKSB7XG4gICAgICAgICAgICBmb3IgKG5hbWUgaW4gcGFyYW1zKSB7XG4gICAgICAgICAgICAgICAgdGhpcy5idWlsZFF1ZXJ5UGFyYW1zKHByZWZpeCArICdbJyArIG5hbWUgKyAnXScsIHBhcmFtc1tuYW1lXSwgYWRkKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgIGFkZChwcmVmaXgsIHBhcmFtcyk7XG4gICAgICAgIH1cbiAgICB9O1xuICAgIC8qKlxuICAgICAqIFJldHVybnMgYSByYXcgcm91dGUgb2JqZWN0LlxuICAgICAqL1xuICAgIFJvdXRlci5wcm90b3R5cGUuZ2V0Um91dGUgPSBmdW5jdGlvbiAobmFtZSkge1xuICAgICAgICB2YXIgcHJlZml4ZWROYW1lID0gdGhpcy5jb250ZXh0Xy5wcmVmaXggKyBuYW1lO1xuICAgICAgICB2YXIgc2Y0MWkxOG5OYW1lID0gbmFtZSArICcuJyArIHRoaXMuY29udGV4dF8ubG9jYWxlO1xuICAgICAgICB2YXIgcHJlZml4ZWRTZjQxaTE4bk5hbWUgPSB0aGlzLmNvbnRleHRfLnByZWZpeCArIG5hbWUgKyAnLicgKyB0aGlzLmNvbnRleHRfLmxvY2FsZTtcbiAgICAgICAgdmFyIHZhcmlhbnRzID0gW3ByZWZpeGVkTmFtZSwgc2Y0MWkxOG5OYW1lLCBwcmVmaXhlZFNmNDFpMThuTmFtZSwgbmFtZV07XG4gICAgICAgIGZvciAodmFyIGkgaW4gdmFyaWFudHMpIHtcbiAgICAgICAgICAgIGlmICh2YXJpYW50c1tpXSBpbiB0aGlzLnJvdXRlc18pIHtcbiAgICAgICAgICAgICAgICByZXR1cm4gdGhpcy5yb3V0ZXNfW3ZhcmlhbnRzW2ldXTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICB0aHJvdyBuZXcgRXJyb3IoJ1RoZSByb3V0ZSBcIicgKyBuYW1lICsgJ1wiIGRvZXMgbm90IGV4aXN0LicpO1xuICAgIH07XG4gICAgLyoqXG4gICAgICogR2VuZXJhdGVzIHRoZSBVUkwgZm9yIGEgcm91dGUuXG4gICAgICovXG4gICAgUm91dGVyLnByb3RvdHlwZS5nZW5lcmF0ZSA9IGZ1bmN0aW9uIChuYW1lLCBvcHRfcGFyYW1zLCBhYnNvbHV0ZSkge1xuICAgICAgICB2YXIgcm91dGUgPSAodGhpcy5nZXRSb3V0ZShuYW1lKSk7XG4gICAgICAgIHZhciBwYXJhbXMgPSBvcHRfcGFyYW1zIHx8IHt9O1xuICAgICAgICB2YXIgdW51c2VkUGFyYW1zID0gT2JqZWN0LmFzc2lnbih7fSwgcGFyYW1zKTtcbiAgICAgICAgdmFyIHVybCA9ICcnO1xuICAgICAgICB2YXIgb3B0aW9uYWwgPSB0cnVlO1xuICAgICAgICB2YXIgaG9zdCA9ICcnO1xuICAgICAgICB2YXIgcG9ydCA9ICh0eXBlb2YgdGhpcy5nZXRQb3J0KCkgPT0gJ3VuZGVmaW5lZCcgfHwgdGhpcy5nZXRQb3J0KCkgPT09IG51bGwpID8gJycgOiB0aGlzLmdldFBvcnQoKTtcbiAgICAgICAgcm91dGUudG9rZW5zLmZvckVhY2goZnVuY3Rpb24gKHRva2VuKSB7XG4gICAgICAgICAgICBpZiAoJ3RleHQnID09PSB0b2tlblswXSAmJiB0eXBlb2YgdG9rZW5bMV0gPT09ICdzdHJpbmcnKSB7XG4gICAgICAgICAgICAgICAgdXJsID0gUm91dGVyLmVuY29kZVBhdGhDb21wb25lbnQodG9rZW5bMV0pICsgdXJsO1xuICAgICAgICAgICAgICAgIG9wdGlvbmFsID0gZmFsc2U7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgaWYgKCd2YXJpYWJsZScgPT09IHRva2VuWzBdKSB7XG4gICAgICAgICAgICAgICAgaWYgKHRva2VuLmxlbmd0aCA9PT0gNiAmJiB0b2tlbls1XSA9PT0gdHJ1ZSkgeyAvLyBTaXh0aCBwYXJ0IG9mIHRoZSB0b2tlbiBhcnJheSBpbmRpY2F0ZXMgaWYgaXQgc2hvdWxkIGJlIGluY2x1ZGVkIGluIGNhc2Ugb2YgZGVmYXVsdHNcbiAgICAgICAgICAgICAgICAgICAgb3B0aW9uYWwgPSBmYWxzZTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgdmFyIGhhc0RlZmF1bHQgPSByb3V0ZS5kZWZhdWx0cyAmJiAhQXJyYXkuaXNBcnJheShyb3V0ZS5kZWZhdWx0cykgJiYgdHlwZW9mIHRva2VuWzNdID09PSAnc3RyaW5nJyAmJiAodG9rZW5bM10gaW4gcm91dGUuZGVmYXVsdHMpO1xuICAgICAgICAgICAgICAgIGlmIChmYWxzZSA9PT0gb3B0aW9uYWwgfHwgIWhhc0RlZmF1bHQgfHwgKCh0eXBlb2YgdG9rZW5bM10gPT09ICdzdHJpbmcnICYmIHRva2VuWzNdIGluIHBhcmFtcykgJiYgIUFycmF5LmlzQXJyYXkocm91dGUuZGVmYXVsdHMpICYmIHBhcmFtc1t0b2tlblszXV0gIT0gcm91dGUuZGVmYXVsdHNbdG9rZW5bM11dKSkge1xuICAgICAgICAgICAgICAgICAgICB2YXIgdmFsdWUgPSB2b2lkIDA7XG4gICAgICAgICAgICAgICAgICAgIGlmICh0eXBlb2YgdG9rZW5bM10gPT09ICdzdHJpbmcnICYmIHRva2VuWzNdIGluIHBhcmFtcykge1xuICAgICAgICAgICAgICAgICAgICAgICAgdmFsdWUgPSBwYXJhbXNbdG9rZW5bM11dO1xuICAgICAgICAgICAgICAgICAgICAgICAgZGVsZXRlIHVudXNlZFBhcmFtc1t0b2tlblszXV07XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgZWxzZSBpZiAodHlwZW9mIHRva2VuWzNdID09PSAnc3RyaW5nJyAmJiBoYXNEZWZhdWx0ICYmICFBcnJheS5pc0FycmF5KHJvdXRlLmRlZmF1bHRzKSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgdmFsdWUgPSByb3V0ZS5kZWZhdWx0c1t0b2tlblszXV07XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgZWxzZSBpZiAob3B0aW9uYWwpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcignVGhlIHJvdXRlIFwiJyArIG5hbWUgKyAnXCIgcmVxdWlyZXMgdGhlIHBhcmFtZXRlciBcIicgKyB0b2tlblszXSArICdcIi4nKTtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICB2YXIgZW1wdHkgPSB0cnVlID09PSB2YWx1ZSB8fCBmYWxzZSA9PT0gdmFsdWUgfHwgJycgPT09IHZhbHVlO1xuICAgICAgICAgICAgICAgICAgICBpZiAoIWVtcHR5IHx8ICFvcHRpb25hbCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgdmFyIGVuY29kZWRWYWx1ZSA9IFJvdXRlci5lbmNvZGVQYXRoQ29tcG9uZW50KHZhbHVlKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmICgnbnVsbCcgPT09IGVuY29kZWRWYWx1ZSAmJiBudWxsID09PSB2YWx1ZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGVuY29kZWRWYWx1ZSA9ICcnO1xuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgdXJsID0gdG9rZW5bMV0gKyBlbmNvZGVkVmFsdWUgKyB1cmw7XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgb3B0aW9uYWwgPSBmYWxzZTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgZWxzZSBpZiAoaGFzRGVmYXVsdCAmJiAodHlwZW9mIHRva2VuWzNdID09PSAnc3RyaW5nJyAmJiB0b2tlblszXSBpbiB1bnVzZWRQYXJhbXMpKSB7XG4gICAgICAgICAgICAgICAgICAgIGRlbGV0ZSB1bnVzZWRQYXJhbXNbdG9rZW5bM11dO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICB0aHJvdyBuZXcgRXJyb3IoJ1RoZSB0b2tlbiB0eXBlIFwiJyArIHRva2VuWzBdICsgJ1wiIGlzIG5vdCBzdXBwb3J0ZWQuJyk7XG4gICAgICAgIH0pO1xuICAgICAgICBpZiAodXJsID09PSAnJykge1xuICAgICAgICAgICAgdXJsID0gJy8nO1xuICAgICAgICB9XG4gICAgICAgIHJvdXRlLmhvc3R0b2tlbnMuZm9yRWFjaChmdW5jdGlvbiAodG9rZW4pIHtcbiAgICAgICAgICAgIHZhciB2YWx1ZTtcbiAgICAgICAgICAgIGlmICgndGV4dCcgPT09IHRva2VuWzBdKSB7XG4gICAgICAgICAgICAgICAgaG9zdCA9IHRva2VuWzFdICsgaG9zdDtcbiAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBpZiAoJ3ZhcmlhYmxlJyA9PT0gdG9rZW5bMF0pIHtcbiAgICAgICAgICAgICAgICBpZiAodG9rZW5bM10gaW4gcGFyYW1zKSB7XG4gICAgICAgICAgICAgICAgICAgIHZhbHVlID0gcGFyYW1zW3Rva2VuWzNdXTtcbiAgICAgICAgICAgICAgICAgICAgZGVsZXRlIHVudXNlZFBhcmFtc1t0b2tlblszXV07XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIGVsc2UgaWYgKHJvdXRlLmRlZmF1bHRzICYmICFBcnJheS5pc0FycmF5KHJvdXRlLmRlZmF1bHRzKSAmJiAodG9rZW5bM10gaW4gcm91dGUuZGVmYXVsdHMpKSB7XG4gICAgICAgICAgICAgICAgICAgIHZhbHVlID0gcm91dGUuZGVmYXVsdHNbdG9rZW5bM11dO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICBob3N0ID0gdG9rZW5bMV0gKyB2YWx1ZSArIGhvc3Q7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgICAgICB1cmwgPSB0aGlzLmNvbnRleHRfLmJhc2VfdXJsICsgdXJsO1xuICAgICAgICBpZiAocm91dGUucmVxdWlyZW1lbnRzICYmICgnX3NjaGVtZScgaW4gcm91dGUucmVxdWlyZW1lbnRzKSAmJiB0aGlzLmdldFNjaGVtZSgpICE9IHJvdXRlLnJlcXVpcmVtZW50c1snX3NjaGVtZSddKSB7XG4gICAgICAgICAgICB2YXIgY3VycmVudEhvc3QgPSBob3N0IHx8IHRoaXMuZ2V0SG9zdCgpO1xuICAgICAgICAgICAgdXJsID0gcm91dGUucmVxdWlyZW1lbnRzWydfc2NoZW1lJ10gKyAnOi8vJyArIGN1cnJlbnRIb3N0ICsgKGN1cnJlbnRIb3N0LmluZGV4T2YoJzonICsgcG9ydCkgPiAtMSB8fCAnJyA9PT0gcG9ydCA/ICcnIDogJzonICsgcG9ydCkgKyB1cmw7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSBpZiAoJ3VuZGVmaW5lZCcgIT09IHR5cGVvZiByb3V0ZS5zY2hlbWVzICYmICd1bmRlZmluZWQnICE9PSB0eXBlb2Ygcm91dGUuc2NoZW1lc1swXSAmJiB0aGlzLmdldFNjaGVtZSgpICE9PSByb3V0ZS5zY2hlbWVzWzBdKSB7XG4gICAgICAgICAgICB2YXIgY3VycmVudEhvc3QgPSBob3N0IHx8IHRoaXMuZ2V0SG9zdCgpO1xuICAgICAgICAgICAgdXJsID0gcm91dGUuc2NoZW1lc1swXSArICc6Ly8nICsgY3VycmVudEhvc3QgKyAoY3VycmVudEhvc3QuaW5kZXhPZignOicgKyBwb3J0KSA+IC0xIHx8ICcnID09PSBwb3J0ID8gJycgOiAnOicgKyBwb3J0KSArIHVybDtcbiAgICAgICAgfVxuICAgICAgICBlbHNlIGlmIChob3N0ICYmIHRoaXMuZ2V0SG9zdCgpICE9PSBob3N0ICsgKGhvc3QuaW5kZXhPZignOicgKyBwb3J0KSA+IC0xIHx8ICcnID09PSBwb3J0ID8gJycgOiAnOicgKyBwb3J0KSkge1xuICAgICAgICAgICAgdXJsID0gdGhpcy5nZXRTY2hlbWUoKSArICc6Ly8nICsgaG9zdCArIChob3N0LmluZGV4T2YoJzonICsgcG9ydCkgPiAtMSB8fCAnJyA9PT0gcG9ydCA/ICcnIDogJzonICsgcG9ydCkgKyB1cmw7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSBpZiAoYWJzb2x1dGUgPT09IHRydWUpIHtcbiAgICAgICAgICAgIHVybCA9IHRoaXMuZ2V0U2NoZW1lKCkgKyAnOi8vJyArIHRoaXMuZ2V0SG9zdCgpICsgKHRoaXMuZ2V0SG9zdCgpLmluZGV4T2YoJzonICsgcG9ydCkgPiAtMSB8fCAnJyA9PT0gcG9ydCA/ICcnIDogJzonICsgcG9ydCkgKyB1cmw7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKE9iamVjdC5rZXlzKHVudXNlZFBhcmFtcykubGVuZ3RoID4gMCkge1xuICAgICAgICAgICAgdmFyIHF1ZXJ5UGFyYW1zXzEgPSBbXTtcbiAgICAgICAgICAgIHZhciBhZGQgPSBmdW5jdGlvbiAoa2V5LCB2YWx1ZSkge1xuICAgICAgICAgICAgICAgIC8vIGlmIHZhbHVlIGlzIGEgZnVuY3Rpb24gdGhlbiBjYWxsIGl0IGFuZCBhc3NpZ24gaXQncyByZXR1cm4gdmFsdWUgYXMgdmFsdWVcbiAgICAgICAgICAgICAgICB2YWx1ZSA9ICh0eXBlb2YgdmFsdWUgPT09ICdmdW5jdGlvbicpID8gdmFsdWUoKSA6IHZhbHVlO1xuICAgICAgICAgICAgICAgIC8vIGNoYW5nZSBudWxsIHRvIGVtcHR5IHN0cmluZ1xuICAgICAgICAgICAgICAgIHZhbHVlID0gKHZhbHVlID09PSBudWxsKSA/ICcnIDogdmFsdWU7XG4gICAgICAgICAgICAgICAgcXVlcnlQYXJhbXNfMS5wdXNoKFJvdXRlci5lbmNvZGVRdWVyeUNvbXBvbmVudChrZXkpICsgJz0nICsgUm91dGVyLmVuY29kZVF1ZXJ5Q29tcG9uZW50KHZhbHVlKSk7XG4gICAgICAgICAgICB9O1xuICAgICAgICAgICAgZm9yICh2YXIgcHJlZml4IGluIHVudXNlZFBhcmFtcykge1xuICAgICAgICAgICAgICAgIGlmICh1bnVzZWRQYXJhbXMuaGFzT3duUHJvcGVydHkocHJlZml4KSkge1xuICAgICAgICAgICAgICAgICAgICB0aGlzLmJ1aWxkUXVlcnlQYXJhbXMocHJlZml4LCB1bnVzZWRQYXJhbXNbcHJlZml4XSwgYWRkKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICB1cmwgPSB1cmwgKyAnPycgKyBxdWVyeVBhcmFtc18xLmpvaW4oJyYnKTtcbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gdXJsO1xuICAgIH07XG4gICAgLyoqXG4gICAgICogUmV0dXJucyB0aGUgZ2l2ZW4gc3RyaW5nIGVuY29kZWQgdG8gbWltaWMgU3ltZm9ueSBVUkwgZ2VuZXJhdG9yLlxuICAgICAqL1xuICAgIFJvdXRlci5jdXN0b21FbmNvZGVVUklDb21wb25lbnQgPSBmdW5jdGlvbiAodmFsdWUpIHtcbiAgICAgICAgcmV0dXJuIGVuY29kZVVSSUNvbXBvbmVudCh2YWx1ZSlcbiAgICAgICAgICAgIC5yZXBsYWNlKC8lMkYvZywgJy8nKVxuICAgICAgICAgICAgLnJlcGxhY2UoLyU0MC9nLCAnQCcpXG4gICAgICAgICAgICAucmVwbGFjZSgvJTNBL2csICc6JylcbiAgICAgICAgICAgIC5yZXBsYWNlKC8lMjEvZywgJyEnKVxuICAgICAgICAgICAgLnJlcGxhY2UoLyUzQi9nLCAnOycpXG4gICAgICAgICAgICAucmVwbGFjZSgvJTJDL2csICcsJylcbiAgICAgICAgICAgIC5yZXBsYWNlKC8lMkEvZywgJyonKVxuICAgICAgICAgICAgLnJlcGxhY2UoL1xcKC9nLCAnJTI4JylcbiAgICAgICAgICAgIC5yZXBsYWNlKC9cXCkvZywgJyUyOScpXG4gICAgICAgICAgICAucmVwbGFjZSgvJy9nLCAnJTI3Jyk7XG4gICAgfTtcbiAgICAvKipcbiAgICAgKiBSZXR1cm5zIHRoZSBnaXZlbiBwYXRoIHByb3Blcmx5IGVuY29kZWQgdG8gbWltaWMgU3ltZm9ueSBVUkwgZ2VuZXJhdG9yLlxuICAgICAqL1xuICAgIFJvdXRlci5lbmNvZGVQYXRoQ29tcG9uZW50ID0gZnVuY3Rpb24gKHZhbHVlKSB7XG4gICAgICAgIHJldHVybiBSb3V0ZXIuY3VzdG9tRW5jb2RlVVJJQ29tcG9uZW50KHZhbHVlKVxuICAgICAgICAgICAgLnJlcGxhY2UoLyUzRC9nLCAnPScpXG4gICAgICAgICAgICAucmVwbGFjZSgvJTJCL2csICcrJylcbiAgICAgICAgICAgIC5yZXBsYWNlKC8lMjEvZywgJyEnKVxuICAgICAgICAgICAgLnJlcGxhY2UoLyU3Qy9nLCAnfCcpO1xuICAgIH07XG4gICAgLyoqXG4gICAgICogUmV0dXJucyB0aGUgZ2l2ZW4gcXVlcnkgcGFyYW1ldGVyIG9yIHZhbHVlIHByb3Blcmx5IGVuY29kZWQgdG8gbWltaWMgU3ltZm9ueSBVUkwgZ2VuZXJhdG9yLlxuICAgICAqL1xuICAgIFJvdXRlci5lbmNvZGVRdWVyeUNvbXBvbmVudCA9IGZ1bmN0aW9uICh2YWx1ZSkge1xuICAgICAgICByZXR1cm4gUm91dGVyLmN1c3RvbUVuY29kZVVSSUNvbXBvbmVudCh2YWx1ZSlcbiAgICAgICAgICAgIC5yZXBsYWNlKC8lM0YvZywgJz8nKTtcbiAgICB9O1xuICAgIHJldHVybiBSb3V0ZXI7XG59KCkpO1xuZXhwb3J0cy5Sb3V0ZXIgPSBSb3V0ZXI7XG5leHBvcnRzLlJvdXRpbmcgPSBuZXcgUm91dGVyKCk7XG5leHBvcnRzW1wiZGVmYXVsdFwiXSA9IGV4cG9ydHMuUm91dGluZztcblxyXG5cclxuICAgIHJldHVybiB7IFJvdXRlcjogZXhwb3J0cy5Sb3V0ZXIsIFJvdXRpbmc6IGV4cG9ydHMuUm91dGluZyB9O1xyXG59KSk7XHIiLCIvLyBUaGUgbW9kdWxlIGNhY2hlXG52YXIgX193ZWJwYWNrX21vZHVsZV9jYWNoZV9fID0ge307XG5cbi8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG5mdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cdC8vIENoZWNrIGlmIG1vZHVsZSBpcyBpbiBjYWNoZVxuXHR2YXIgY2FjaGVkTW9kdWxlID0gX193ZWJwYWNrX21vZHVsZV9jYWNoZV9fW21vZHVsZUlkXTtcblx0aWYgKGNhY2hlZE1vZHVsZSAhPT0gdW5kZWZpbmVkKSB7XG5cdFx0cmV0dXJuIGNhY2hlZE1vZHVsZS5leHBvcnRzO1xuXHR9XG5cdC8vIENoZWNrIGlmIG1vZHVsZSBleGlzdHMgKGRldmVsb3BtZW50IG9ubHkpXG5cdGlmIChfX3dlYnBhY2tfbW9kdWxlc19fW21vZHVsZUlkXSA9PT0gdW5kZWZpbmVkKSB7XG5cdFx0dmFyIGUgPSBuZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiICsgbW9kdWxlSWQgKyBcIidcIik7XG5cdFx0ZS5jb2RlID0gJ01PRFVMRV9OT1RfRk9VTkQnO1xuXHRcdHRocm93IGU7XG5cdH1cblx0Ly8gQ3JlYXRlIGEgbmV3IG1vZHVsZSAoYW5kIHB1dCBpdCBpbnRvIHRoZSBjYWNoZSlcblx0dmFyIG1vZHVsZSA9IF9fd2VicGFja19tb2R1bGVfY2FjaGVfX1ttb2R1bGVJZF0gPSB7XG5cdFx0Ly8gbm8gbW9kdWxlLmlkIG5lZWRlZFxuXHRcdC8vIG5vIG1vZHVsZS5sb2FkZWQgbmVlZGVkXG5cdFx0ZXhwb3J0czoge31cblx0fTtcblxuXHQvLyBFeGVjdXRlIHRoZSBtb2R1bGUgZnVuY3Rpb25cblx0X193ZWJwYWNrX21vZHVsZXNfX1ttb2R1bGVJZF0uY2FsbChtb2R1bGUuZXhwb3J0cywgbW9kdWxlLCBtb2R1bGUuZXhwb3J0cywgX193ZWJwYWNrX3JlcXVpcmVfXyk7XG5cblx0Ly8gUmV0dXJuIHRoZSBleHBvcnRzIG9mIHRoZSBtb2R1bGVcblx0cmV0dXJuIG1vZHVsZS5leHBvcnRzO1xufVxuXG4iLCIvLyBnZXREZWZhdWx0RXhwb3J0IGZ1bmN0aW9uIGZvciBjb21wYXRpYmlsaXR5IHdpdGggbm9uLWhhcm1vbnkgbW9kdWxlc1xuX193ZWJwYWNrX3JlcXVpcmVfXy5uID0gKG1vZHVsZSkgPT4ge1xuXHR2YXIgZ2V0dGVyID0gbW9kdWxlICYmIG1vZHVsZS5fX2VzTW9kdWxlID9cblx0XHQoKSA9PiAobW9kdWxlWydkZWZhdWx0J10pIDpcblx0XHQoKSA9PiAobW9kdWxlKTtcblx0X193ZWJwYWNrX3JlcXVpcmVfXy5kKGdldHRlciwgeyBhOiBnZXR0ZXIgfSk7XG5cdHJldHVybiBnZXR0ZXI7XG59OyIsIi8vIGRlZmluZSBnZXR0ZXIgZnVuY3Rpb25zIGZvciBoYXJtb255IGV4cG9ydHNcbl9fd2VicGFja19yZXF1aXJlX18uZCA9IChleHBvcnRzLCBkZWZpbml0aW9uKSA9PiB7XG5cdGZvcih2YXIga2V5IGluIGRlZmluaXRpb24pIHtcblx0XHRpZihfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZGVmaW5pdGlvbiwga2V5KSAmJiAhX193ZWJwYWNrX3JlcXVpcmVfXy5vKGV4cG9ydHMsIGtleSkpIHtcblx0XHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCBrZXksIHsgZW51bWVyYWJsZTogdHJ1ZSwgZ2V0OiBkZWZpbml0aW9uW2tleV0gfSk7XG5cdFx0fVxuXHR9XG59OyIsIl9fd2VicGFja19yZXF1aXJlX18ubyA9IChvYmosIHByb3ApID0+IChPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGwob2JqLCBwcm9wKSkiLCIvLyBkZWZpbmUgX19lc01vZHVsZSBvbiBleHBvcnRzXG5fX3dlYnBhY2tfcmVxdWlyZV9fLnIgPSAoZXhwb3J0cykgPT4ge1xuXHRpZih0eXBlb2YgU3ltYm9sICE9PSAndW5kZWZpbmVkJyAmJiBTeW1ib2wudG9TdHJpbmdUYWcpIHtcblx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgU3ltYm9sLnRvU3RyaW5nVGFnLCB7IHZhbHVlOiAnTW9kdWxlJyB9KTtcblx0fVxuXHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgJ19fZXNNb2R1bGUnLCB7IHZhbHVlOiB0cnVlIH0pO1xufTsiLCJpbXBvcnQgUm91dGluZyBmcm9tICdmb3Mtcm91dGVyJztcblxuY2xhc3MgQ01TUm91dGluZ09iamVjdCB7XG4gICAgY29uc3RydWN0b3IoKSB7XG4gICAgICAgIHRoaXMuaW5zdGFuY2UgPSBudWxsO1xuICAgIH1cbiAgICBzZXRSb3V0aW5nKHJvdXRpbmcpe1xuICAgICAgICBSb3V0aW5nLnNldFJvdXRpbmdEYXRhKHJvdXRpbmcpO1xuICAgICAgICB0aGlzLmluc3RhbmNlID0gUm91dGluZztcbiAgICB9XG4gICAgZ2VuZXJhdGUobmFtZSwgcGFyYW1zID0ge30sIGFic29sdXRlID0gZmFsc2Upe1xuICAgICAgICByZXR1cm4gdGhpcy5pbnN0YW5jZS5nZW5lcmF0ZShuYW1lLCBwYXJhbXMsIGFic29sdXRlKTtcbiAgICB9XG59XG5cbndpbmRvdy5DTVNSb3V0aW5nID0gbmV3IENNU1JvdXRpbmdPYmplY3QoKVxuXG5cbiJdLCJuYW1lcyI6W10sInNvdXJjZVJvb3QiOiIifQ==