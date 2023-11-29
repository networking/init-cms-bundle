!function(e,t){var o=function(){var e={__esModule:!0};e.Routing=e.Router=void 0;var t=function(){function t(e,t){this.context_=e||{base_url:"",prefix:"",host:"",port:"",scheme:"",locale:""},this.setRoutes(t||{})}return t.getInstance=function(){return e.Routing},t.setData=function(e){t.getInstance().setRoutingData(e)},t.prototype.setRoutingData=function(e){this.setBaseUrl(e.base_url),this.setRoutes(e.routes),void 0!==e.prefix&&this.setPrefix(e.prefix),void 0!==e.port&&this.setPort(e.port),void 0!==e.locale&&this.setLocale(e.locale),this.setHost(e.host),void 0!==e.scheme&&this.setScheme(e.scheme)},t.prototype.setRoutes=function(e){this.routes_=Object.freeze(e)},t.prototype.getRoutes=function(){return this.routes_},t.prototype.setBaseUrl=function(e){this.context_.base_url=e},t.prototype.getBaseUrl=function(){return this.context_.base_url},t.prototype.setPrefix=function(e){this.context_.prefix=e},t.prototype.setScheme=function(e){this.context_.scheme=e},t.prototype.getScheme=function(){return this.context_.scheme},t.prototype.setHost=function(e){this.context_.host=e},t.prototype.getHost=function(){return this.context_.host},t.prototype.setPort=function(e){this.context_.port=e},t.prototype.getPort=function(){return this.context_.port},t.prototype.setLocale=function(e){this.context_.locale=e},t.prototype.getLocale=function(){return this.context_.locale},t.prototype.buildQueryParams=function(e,t,o){var n,r=this,s=new RegExp(/\[\]$/);if(t instanceof Array)t.forEach((function(t,n){s.test(e)?o(e,t):r.buildQueryParams(e+"["+("object"==typeof t?n:"")+"]",t,o)}));else if("object"==typeof t)for(n in t)this.buildQueryParams(e+"["+n+"]",t[n],o);else o(e,t)},t.prototype.getRoute=function(e){var t=[this.context_.prefix+e,e+"."+this.context_.locale,this.context_.prefix+e+"."+this.context_.locale,e];for(var o in t)if(t[o]in this.routes_)return this.routes_[t[o]];throw new Error('The route "'+e+'" does not exist.')},t.prototype.generate=function(e,o,n){var r=this.getRoute(e),s=o||{},i=Object.assign({},s),u="",c=!0,a="",f=void 0===this.getPort()||null===this.getPort()?"":this.getPort();if(r.tokens.forEach((function(o){if("text"===o[0]&&"string"==typeof o[1])return u=t.encodePathComponent(o[1])+u,void(c=!1);if("variable"!==o[0])throw new Error('The token type "'+o[0]+'" is not supported.');6===o.length&&!0===o[5]&&(c=!1);var n=r.defaults&&!Array.isArray(r.defaults)&&"string"==typeof o[3]&&o[3]in r.defaults;if(!1===c||!n||"string"==typeof o[3]&&o[3]in s&&!Array.isArray(r.defaults)&&s[o[3]]!=r.defaults[o[3]]){var a=void 0;if("string"==typeof o[3]&&o[3]in s)a=s[o[3]],delete i[o[3]];else{if("string"!=typeof o[3]||!n||Array.isArray(r.defaults)){if(c)return;throw new Error('The route "'+e+'" requires the parameter "'+o[3]+'".')}a=r.defaults[o[3]]}if(!0!==a&&!1!==a&&""!==a||!c){var f=t.encodePathComponent(a);"null"===f&&null===a&&(f=""),u=o[1]+f+u}c=!1}else n&&"string"==typeof o[3]&&o[3]in i&&delete i[o[3]]})),""===u&&(u="/"),r.hosttokens.forEach((function(e){var t;"text"!==e[0]?"variable"===e[0]&&(e[3]in s?(t=s[e[3]],delete i[e[3]]):r.defaults&&!Array.isArray(r.defaults)&&e[3]in r.defaults&&(t=r.defaults[e[3]]),a=e[1]+t+a):a=e[1]+a})),u=this.context_.base_url+u,r.requirements&&"_scheme"in r.requirements&&this.getScheme()!=r.requirements._scheme){var p=a||this.getHost();u=r.requirements._scheme+"://"+p+(p.indexOf(":"+f)>-1||""===f?"":":"+f)+u}else void 0!==r.schemes&&void 0!==r.schemes[0]&&this.getScheme()!==r.schemes[0]?(p=a||this.getHost(),u=r.schemes[0]+"://"+p+(p.indexOf(":"+f)>-1||""===f?"":":"+f)+u):a&&this.getHost()!==a+(a.indexOf(":"+f)>-1||""===f?"":":"+f)?u=this.getScheme()+"://"+a+(a.indexOf(":"+f)>-1||""===f?"":":"+f)+u:!0===n&&(u=this.getScheme()+"://"+this.getHost()+(this.getHost().indexOf(":"+f)>-1||""===f?"":":"+f)+u);if(Object.keys(i).length>0){var h=[],l=function(e,o){o=null===(o="function"==typeof o?o():o)?"":o,h.push(t.encodeQueryComponent(e)+"="+t.encodeQueryComponent(o))};for(var g in i)i.hasOwnProperty(g)&&this.buildQueryParams(g,i[g],l);u=u+"?"+h.join("&")}return u},t.customEncodeURIComponent=function(e){return encodeURIComponent(e).replace(/%2F/g,"/").replace(/%40/g,"@").replace(/%3A/g,":").replace(/%21/g,"!").replace(/%3B/g,";").replace(/%2C/g,",").replace(/%2A/g,"*").replace(/\(/g,"%28").replace(/\)/g,"%29").replace(/'/g,"%27")},t.encodePathComponent=function(e){return t.customEncodeURIComponent(e).replace(/%3D/g,"=").replace(/%2B/g,"+").replace(/%21/g,"!").replace(/%7C/g,"|")},t.encodeQueryComponent=function(e){return t.customEncodeURIComponent(e).replace(/%3F/g,"?")},t}();return e.Router=t,e.Routing=new t,e.default=e.Routing,{Router:e.Router,Routing:e.Routing}}();"function"==typeof define&&define.amd?define([],o.Routing):"object"==typeof module&&module.exports?module.exports=o.Routing:(e.Routing=o.Routing,e.fos={Router:o.Router})}(this);