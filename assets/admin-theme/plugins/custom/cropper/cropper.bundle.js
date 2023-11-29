/*!
 * Cropper.js v1.5.13
 * https://fengyuanchen.github.io/cropperjs
 *
 * Copyright 2015-present Chen Fengyuan
 * Released under the MIT license
 *
 * Date: 2022-11-20T05:30:46.114Z
 */
!function(t,e){"object"==typeof exports&&"undefined"!=typeof module?module.exports=e():"function"==typeof define&&define.amd?define(e):(t="undefined"!=typeof globalThis?globalThis:t||self).Cropper=e()}(this,(function(){"use strict";function t(t,e){var i=Object.keys(t);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(t);e&&(a=a.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),i.push.apply(i,a)}return i}function e(e){for(var i=1;i<arguments.length;i++){var a=null!=arguments[i]?arguments[i]:{};i%2?t(Object(a),!0).forEach((function(t){n(e,t,a[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(a)):t(Object(a)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(a,t))}))}return e}function i(t){return i="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},i(t)}function a(t,e){for(var i=0;i<e.length;i++){var a=e[i];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(t,a.key,a)}}function n(t,e,i){return e in t?Object.defineProperty(t,e,{value:i,enumerable:!0,configurable:!0,writable:!0}):t[e]=i,t}function o(t){return function(t){if(Array.isArray(t))return r(t)}(t)||function(t){if("undefined"!=typeof Symbol&&null!=t[Symbol.iterator]||null!=t["@@iterator"])return Array.from(t)}(t)||function(t,e){if(!t)return;if("string"==typeof t)return r(t,e);var i=Object.prototype.toString.call(t).slice(8,-1);"Object"===i&&t.constructor&&(i=t.constructor.name);if("Map"===i||"Set"===i)return Array.from(t);if("Arguments"===i||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(i))return r(t,e)}(t)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function r(t,e){(null==e||e>t.length)&&(e=t.length);for(var i=0,a=new Array(e);i<e;i++)a[i]=t[i];return a}var h="undefined"!=typeof window&&void 0!==window.document,s=h?window:{},c=!(!h||!s.document.documentElement)&&"ontouchstart"in s.document.documentElement,l=!!h&&"PointerEvent"in s,d="cropper",p="all",m="crop",u="move",g="zoom",f="e",v="w",w="s",b="n",y="ne",x="nw",M="se",C="sw",D="".concat(d,"-crop"),B="".concat(d,"-disabled"),k="".concat(d,"-hidden"),O="".concat(d,"-hide"),T="".concat(d,"-invisible"),E="".concat(d,"-modal"),W="".concat(d,"-move"),H="".concat(d,"Action"),N="".concat(d,"Preview"),L="crop",z="move",Y="none",X="crop",R="cropend",S="cropmove",A="cropstart",j="dblclick",P=l?"pointerdown":c?"touchstart":"mousedown",I=l?"pointermove":c?"touchmove":"mousemove",U=l?"pointerup pointercancel":c?"touchend touchcancel":"mouseup",q="ready",$="resize",Q="wheel",K="zoom",Z="image/jpeg",G=/^e|w|s|n|se|sw|ne|nw|all|crop|move|zoom$/,V=/^data:/,F=/^data:image\/jpeg;base64,/,J=/^img|canvas$/i,_={viewMode:0,dragMode:L,initialAspectRatio:NaN,aspectRatio:NaN,data:null,preview:"",responsive:!0,restore:!0,checkCrossOrigin:!0,checkOrientation:!0,modal:!0,guides:!0,center:!0,highlight:!0,background:!0,autoCrop:!0,autoCropArea:.8,movable:!0,rotatable:!0,scalable:!0,zoomable:!0,zoomOnTouch:!0,zoomOnWheel:!0,wheelZoomRatio:.1,cropBoxMovable:!0,cropBoxResizable:!0,toggleDragModeOnDblclick:!0,minCanvasWidth:0,minCanvasHeight:0,minCropBoxWidth:0,minCropBoxHeight:0,minContainerWidth:200,minContainerHeight:100,ready:null,cropstart:null,cropmove:null,cropend:null,crop:null,zoom:null},tt=Number.isNaN||s.isNaN;function et(t){return"number"==typeof t&&!tt(t)}var it=function(t){return t>0&&t<1/0};function at(t){return void 0===t}function nt(t){return"object"===i(t)&&null!==t}var ot=Object.prototype.hasOwnProperty;function rt(t){if(!nt(t))return!1;try{var e=t.constructor,i=e.prototype;return e&&i&&ot.call(i,"isPrototypeOf")}catch(t){return!1}}function ht(t){return"function"==typeof t}var st=Array.prototype.slice;function ct(t){return Array.from?Array.from(t):st.call(t)}function lt(t,e){return t&&ht(e)&&(Array.isArray(t)||et(t.length)?ct(t).forEach((function(i,a){e.call(t,i,a,t)})):nt(t)&&Object.keys(t).forEach((function(i){e.call(t,t[i],i,t)}))),t}var dt=Object.assign||function(t){for(var e=arguments.length,i=new Array(e>1?e-1:0),a=1;a<e;a++)i[a-1]=arguments[a];return nt(t)&&i.length>0&&i.forEach((function(e){nt(e)&&Object.keys(e).forEach((function(i){t[i]=e[i]}))})),t},pt=/\.\d*(?:0|9){12}\d*$/;function mt(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:1e11;return pt.test(t)?Math.round(t*e)/e:t}var ut=/^width|height|left|top|marginLeft|marginTop$/;function gt(t,e){var i=t.style;lt(e,(function(t,e){ut.test(e)&&et(t)&&(t="".concat(t,"px")),i[e]=t}))}function ft(t,e){if(e)if(et(t.length))lt(t,(function(t){ft(t,e)}));else if(t.classList)t.classList.add(e);else{var i=t.className.trim();i?i.indexOf(e)<0&&(t.className="".concat(i," ").concat(e)):t.className=e}}function vt(t,e){e&&(et(t.length)?lt(t,(function(t){vt(t,e)})):t.classList?t.classList.remove(e):t.className.indexOf(e)>=0&&(t.className=t.className.replace(e,"")))}function wt(t,e,i){e&&(et(t.length)?lt(t,(function(t){wt(t,e,i)})):i?ft(t,e):vt(t,e))}var bt=/([a-z\d])([A-Z])/g;function yt(t){return t.replace(bt,"$1-$2").toLowerCase()}function xt(t,e){return nt(t[e])?t[e]:t.dataset?t.dataset[e]:t.getAttribute("data-".concat(yt(e)))}function Mt(t,e,i){nt(i)?t[e]=i:t.dataset?t.dataset[e]=i:t.setAttribute("data-".concat(yt(e)),i)}var Ct=/\s\s*/,Dt=function(){var t=!1;if(h){var e=!1,i=function(){},a=Object.defineProperty({},"once",{get:function(){return t=!0,e},set:function(t){e=t}});s.addEventListener("test",i,a),s.removeEventListener("test",i,a)}return t}();function Bt(t,e,i){var a=arguments.length>3&&void 0!==arguments[3]?arguments[3]:{},n=i;e.trim().split(Ct).forEach((function(e){if(!Dt){var o=t.listeners;o&&o[e]&&o[e][i]&&(n=o[e][i],delete o[e][i],0===Object.keys(o[e]).length&&delete o[e],0===Object.keys(o).length&&delete t.listeners)}t.removeEventListener(e,n,a)}))}function kt(t,e,i){var a=arguments.length>3&&void 0!==arguments[3]?arguments[3]:{},n=i;e.trim().split(Ct).forEach((function(e){if(a.once&&!Dt){var o=t.listeners,r=void 0===o?{}:o;n=function(){delete r[e][i],t.removeEventListener(e,n,a);for(var o=arguments.length,h=new Array(o),s=0;s<o;s++)h[s]=arguments[s];i.apply(t,h)},r[e]||(r[e]={}),r[e][i]&&t.removeEventListener(e,r[e][i],a),r[e][i]=n,t.listeners=r}t.addEventListener(e,n,a)}))}function Ot(t,e,i){var a;return ht(Event)&&ht(CustomEvent)?a=new CustomEvent(e,{detail:i,bubbles:!0,cancelable:!0}):(a=document.createEvent("CustomEvent")).initCustomEvent(e,!0,!0,i),t.dispatchEvent(a)}function Tt(t){var e=t.getBoundingClientRect();return{left:e.left+(window.pageXOffset-document.documentElement.clientLeft),top:e.top+(window.pageYOffset-document.documentElement.clientTop)}}var Et=s.location,Wt=/^(\w+:)\/\/([^:/?#]*):?(\d*)/i;function Ht(t){var e=t.match(Wt);return null!==e&&(e[1]!==Et.protocol||e[2]!==Et.hostname||e[3]!==Et.port)}function Nt(t){var e="timestamp=".concat((new Date).getTime());return t+(-1===t.indexOf("?")?"?":"&")+e}function Lt(t){var e=t.rotate,i=t.scaleX,a=t.scaleY,n=t.translateX,o=t.translateY,r=[];et(n)&&0!==n&&r.push("translateX(".concat(n,"px)")),et(o)&&0!==o&&r.push("translateY(".concat(o,"px)")),et(e)&&0!==e&&r.push("rotate(".concat(e,"deg)")),et(i)&&1!==i&&r.push("scaleX(".concat(i,")")),et(a)&&1!==a&&r.push("scaleY(".concat(a,")"));var h=r.length?r.join(" "):"none";return{WebkitTransform:h,msTransform:h,transform:h}}function zt(t,i){var a=t.pageX,n=t.pageY,o={endX:a,endY:n};return i?o:e({startX:a,startY:n},o)}function Yt(t){var e=t.aspectRatio,i=t.height,a=t.width,n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"contain",o=it(a),r=it(i);if(o&&r){var h=i*e;"contain"===n&&h>a||"cover"===n&&h<a?i=a/e:a=i*e}else o?i=a/e:r&&(a=i*e);return{width:a,height:i}}var Xt=String.fromCharCode;var Rt=/^data:.*,/;function St(t){var e,i=new DataView(t);try{var a,n,o;if(255===i.getUint8(0)&&216===i.getUint8(1))for(var r=i.byteLength,h=2;h+1<r;){if(255===i.getUint8(h)&&225===i.getUint8(h+1)){n=h;break}h+=1}if(n){var s=n+10;if("Exif"===function(t,e,i){var a="";i+=e;for(var n=e;n<i;n+=1)a+=Xt(t.getUint8(n));return a}(i,n+4,4)){var c=i.getUint16(s);if(((a=18761===c)||19789===c)&&42===i.getUint16(s+2,a)){var l=i.getUint32(s+4,a);l>=8&&(o=s+l)}}}if(o){var d,p,m=i.getUint16(o,a);for(p=0;p<m;p+=1)if(d=o+12*p+2,274===i.getUint16(d,a)){d+=8,e=i.getUint16(d,a),i.setUint16(d,1,a);break}}}catch(t){e=1}return e}var At={render:function(){this.initContainer(),this.initCanvas(),this.initCropBox(),this.renderCanvas(),this.cropped&&this.renderCropBox()},initContainer:function(){var t=this.element,e=this.options,i=this.container,a=this.cropper,n=Number(e.minContainerWidth),o=Number(e.minContainerHeight);ft(a,k),vt(t,k);var r={width:Math.max(i.offsetWidth,n>=0?n:200),height:Math.max(i.offsetHeight,o>=0?o:100)};this.containerData=r,gt(a,{width:r.width,height:r.height}),ft(t,k),vt(a,k)},initCanvas:function(){var t=this.containerData,e=this.imageData,i=this.options.viewMode,a=Math.abs(e.rotate)%180==90,n=a?e.naturalHeight:e.naturalWidth,o=a?e.naturalWidth:e.naturalHeight,r=n/o,h=t.width,s=t.height;t.height*r>t.width?3===i?h=t.height*r:s=t.width/r:3===i?s=t.width/r:h=t.height*r;var c={aspectRatio:r,naturalWidth:n,naturalHeight:o,width:h,height:s};this.canvasData=c,this.limited=1===i||2===i,this.limitCanvas(!0,!0),c.width=Math.min(Math.max(c.width,c.minWidth),c.maxWidth),c.height=Math.min(Math.max(c.height,c.minHeight),c.maxHeight),c.left=(t.width-c.width)/2,c.top=(t.height-c.height)/2,c.oldLeft=c.left,c.oldTop=c.top,this.initialCanvasData=dt({},c)},limitCanvas:function(t,e){var i=this.options,a=this.containerData,n=this.canvasData,o=this.cropBoxData,r=i.viewMode,h=n.aspectRatio,s=this.cropped&&o;if(t){var c=Number(i.minCanvasWidth)||0,l=Number(i.minCanvasHeight)||0;r>1?(c=Math.max(c,a.width),l=Math.max(l,a.height),3===r&&(l*h>c?c=l*h:l=c/h)):r>0&&(c?c=Math.max(c,s?o.width:0):l?l=Math.max(l,s?o.height:0):s&&(c=o.width,(l=o.height)*h>c?c=l*h:l=c/h));var d=Yt({aspectRatio:h,width:c,height:l});c=d.width,l=d.height,n.minWidth=c,n.minHeight=l,n.maxWidth=1/0,n.maxHeight=1/0}if(e)if(r>(s?0:1)){var p=a.width-n.width,m=a.height-n.height;n.minLeft=Math.min(0,p),n.minTop=Math.min(0,m),n.maxLeft=Math.max(0,p),n.maxTop=Math.max(0,m),s&&this.limited&&(n.minLeft=Math.min(o.left,o.left+(o.width-n.width)),n.minTop=Math.min(o.top,o.top+(o.height-n.height)),n.maxLeft=o.left,n.maxTop=o.top,2===r&&(n.width>=a.width&&(n.minLeft=Math.min(0,p),n.maxLeft=Math.max(0,p)),n.height>=a.height&&(n.minTop=Math.min(0,m),n.maxTop=Math.max(0,m))))}else n.minLeft=-n.width,n.minTop=-n.height,n.maxLeft=a.width,n.maxTop=a.height},renderCanvas:function(t,e){var i=this.canvasData,a=this.imageData;if(e){var n=function(t){var e=t.width,i=t.height,a=t.degree;if(90==(a=Math.abs(a)%180))return{width:i,height:e};var n=a%90*Math.PI/180,o=Math.sin(n),r=Math.cos(n),h=e*r+i*o,s=e*o+i*r;return a>90?{width:s,height:h}:{width:h,height:s}}({width:a.naturalWidth*Math.abs(a.scaleX||1),height:a.naturalHeight*Math.abs(a.scaleY||1),degree:a.rotate||0}),o=n.width,r=n.height,h=i.width*(o/i.naturalWidth),s=i.height*(r/i.naturalHeight);i.left-=(h-i.width)/2,i.top-=(s-i.height)/2,i.width=h,i.height=s,i.aspectRatio=o/r,i.naturalWidth=o,i.naturalHeight=r,this.limitCanvas(!0,!1)}(i.width>i.maxWidth||i.width<i.minWidth)&&(i.left=i.oldLeft),(i.height>i.maxHeight||i.height<i.minHeight)&&(i.top=i.oldTop),i.width=Math.min(Math.max(i.width,i.minWidth),i.maxWidth),i.height=Math.min(Math.max(i.height,i.minHeight),i.maxHeight),this.limitCanvas(!1,!0),i.left=Math.min(Math.max(i.left,i.minLeft),i.maxLeft),i.top=Math.min(Math.max(i.top,i.minTop),i.maxTop),i.oldLeft=i.left,i.oldTop=i.top,gt(this.canvas,dt({width:i.width,height:i.height},Lt({translateX:i.left,translateY:i.top}))),this.renderImage(t),this.cropped&&this.limited&&this.limitCropBox(!0,!0)},renderImage:function(t){var e=this.canvasData,i=this.imageData,a=i.naturalWidth*(e.width/e.naturalWidth),n=i.naturalHeight*(e.height/e.naturalHeight);dt(i,{width:a,height:n,left:(e.width-a)/2,top:(e.height-n)/2}),gt(this.image,dt({width:i.width,height:i.height},Lt(dt({translateX:i.left,translateY:i.top},i)))),t&&this.output()},initCropBox:function(){var t=this.options,e=this.canvasData,i=t.aspectRatio||t.initialAspectRatio,a=Number(t.autoCropArea)||.8,n={width:e.width,height:e.height};i&&(e.height*i>e.width?n.height=n.width/i:n.width=n.height*i),this.cropBoxData=n,this.limitCropBox(!0,!0),n.width=Math.min(Math.max(n.width,n.minWidth),n.maxWidth),n.height=Math.min(Math.max(n.height,n.minHeight),n.maxHeight),n.width=Math.max(n.minWidth,n.width*a),n.height=Math.max(n.minHeight,n.height*a),n.left=e.left+(e.width-n.width)/2,n.top=e.top+(e.height-n.height)/2,n.oldLeft=n.left,n.oldTop=n.top,this.initialCropBoxData=dt({},n)},limitCropBox:function(t,e){var i=this.options,a=this.containerData,n=this.canvasData,o=this.cropBoxData,r=this.limited,h=i.aspectRatio;if(t){var s=Number(i.minCropBoxWidth)||0,c=Number(i.minCropBoxHeight)||0,l=r?Math.min(a.width,n.width,n.width+n.left,a.width-n.left):a.width,d=r?Math.min(a.height,n.height,n.height+n.top,a.height-n.top):a.height;s=Math.min(s,a.width),c=Math.min(c,a.height),h&&(s&&c?c*h>s?c=s/h:s=c*h:s?c=s/h:c&&(s=c*h),d*h>l?d=l/h:l=d*h),o.minWidth=Math.min(s,l),o.minHeight=Math.min(c,d),o.maxWidth=l,o.maxHeight=d}e&&(r?(o.minLeft=Math.max(0,n.left),o.minTop=Math.max(0,n.top),o.maxLeft=Math.min(a.width,n.left+n.width)-o.width,o.maxTop=Math.min(a.height,n.top+n.height)-o.height):(o.minLeft=0,o.minTop=0,o.maxLeft=a.width-o.width,o.maxTop=a.height-o.height))},renderCropBox:function(){var t=this.options,e=this.containerData,i=this.cropBoxData;(i.width>i.maxWidth||i.width<i.minWidth)&&(i.left=i.oldLeft),(i.height>i.maxHeight||i.height<i.minHeight)&&(i.top=i.oldTop),i.width=Math.min(Math.max(i.width,i.minWidth),i.maxWidth),i.height=Math.min(Math.max(i.height,i.minHeight),i.maxHeight),this.limitCropBox(!1,!0),i.left=Math.min(Math.max(i.left,i.minLeft),i.maxLeft),i.top=Math.min(Math.max(i.top,i.minTop),i.maxTop),i.oldLeft=i.left,i.oldTop=i.top,t.movable&&t.cropBoxMovable&&Mt(this.face,H,i.width>=e.width&&i.height>=e.height?u:p),gt(this.cropBox,dt({width:i.width,height:i.height},Lt({translateX:i.left,translateY:i.top}))),this.cropped&&this.limited&&this.limitCanvas(!0,!0),this.disabled||this.output()},output:function(){this.preview(),Ot(this.element,X,this.getData())}},jt={initPreview:function(){var t=this.element,e=this.crossOrigin,i=this.options.preview,a=e?this.crossOriginUrl:this.url,n=t.alt||"The image to preview",o=document.createElement("img");if(e&&(o.crossOrigin=e),o.src=a,o.alt=n,this.viewBox.appendChild(o),this.viewBoxImage=o,i){var r=i;"string"==typeof i?r=t.ownerDocument.querySelectorAll(i):i.querySelector&&(r=[i]),this.previews=r,lt(r,(function(t){var i=document.createElement("img");Mt(t,N,{width:t.offsetWidth,height:t.offsetHeight,html:t.innerHTML}),e&&(i.crossOrigin=e),i.src=a,i.alt=n,i.style.cssText='display:block;width:100%;height:auto;min-width:0!important;min-height:0!important;max-width:none!important;max-height:none!important;image-orientation:0deg!important;"',t.innerHTML="",t.appendChild(i)}))}},resetPreview:function(){lt(this.previews,(function(t){var e=xt(t,N);gt(t,{width:e.width,height:e.height}),t.innerHTML=e.html,function(t,e){if(nt(t[e]))try{delete t[e]}catch(i){t[e]=void 0}else if(t.dataset)try{delete t.dataset[e]}catch(i){t.dataset[e]=void 0}else t.removeAttribute("data-".concat(yt(e)))}(t,N)}))},preview:function(){var t=this.imageData,e=this.canvasData,i=this.cropBoxData,a=i.width,n=i.height,o=t.width,r=t.height,h=i.left-e.left-t.left,s=i.top-e.top-t.top;this.cropped&&!this.disabled&&(gt(this.viewBoxImage,dt({width:o,height:r},Lt(dt({translateX:-h,translateY:-s},t)))),lt(this.previews,(function(e){var i=xt(e,N),c=i.width,l=i.height,d=c,p=l,m=1;a&&(p=n*(m=c/a)),n&&p>l&&(d=a*(m=l/n),p=l),gt(e,{width:d,height:p}),gt(e.getElementsByTagName("img")[0],dt({width:o*m,height:r*m},Lt(dt({translateX:-h*m,translateY:-s*m},t))))})))}},Pt={bind:function(){var t=this.element,e=this.options,i=this.cropper;ht(e.cropstart)&&kt(t,A,e.cropstart),ht(e.cropmove)&&kt(t,S,e.cropmove),ht(e.cropend)&&kt(t,R,e.cropend),ht(e.crop)&&kt(t,X,e.crop),ht(e.zoom)&&kt(t,K,e.zoom),kt(i,P,this.onCropStart=this.cropStart.bind(this)),e.zoomable&&e.zoomOnWheel&&kt(i,Q,this.onWheel=this.wheel.bind(this),{passive:!1,capture:!0}),e.toggleDragModeOnDblclick&&kt(i,j,this.onDblclick=this.dblclick.bind(this)),kt(t.ownerDocument,I,this.onCropMove=this.cropMove.bind(this)),kt(t.ownerDocument,U,this.onCropEnd=this.cropEnd.bind(this)),e.responsive&&kt(window,$,this.onResize=this.resize.bind(this))},unbind:function(){var t=this.element,e=this.options,i=this.cropper;ht(e.cropstart)&&Bt(t,A,e.cropstart),ht(e.cropmove)&&Bt(t,S,e.cropmove),ht(e.cropend)&&Bt(t,R,e.cropend),ht(e.crop)&&Bt(t,X,e.crop),ht(e.zoom)&&Bt(t,K,e.zoom),Bt(i,P,this.onCropStart),e.zoomable&&e.zoomOnWheel&&Bt(i,Q,this.onWheel,{passive:!1,capture:!0}),e.toggleDragModeOnDblclick&&Bt(i,j,this.onDblclick),Bt(t.ownerDocument,I,this.onCropMove),Bt(t.ownerDocument,U,this.onCropEnd),e.responsive&&Bt(window,$,this.onResize)}},It={resize:function(){if(!this.disabled){var t,e,i=this.options,a=this.container,n=this.containerData,o=a.offsetWidth/n.width,r=a.offsetHeight/n.height,h=Math.abs(o-1)>Math.abs(r-1)?o:r;if(1!==h)i.restore&&(t=this.getCanvasData(),e=this.getCropBoxData()),this.render(),i.restore&&(this.setCanvasData(lt(t,(function(e,i){t[i]=e*h}))),this.setCropBoxData(lt(e,(function(t,i){e[i]=t*h}))))}},dblclick:function(){var t,e;this.disabled||this.options.dragMode===Y||this.setDragMode((t=this.dragBox,e=D,(t.classList?t.classList.contains(e):t.className.indexOf(e)>-1)?z:L))},wheel:function(t){var e=this,i=Number(this.options.wheelZoomRatio)||.1,a=1;this.disabled||(t.preventDefault(),this.wheeling||(this.wheeling=!0,setTimeout((function(){e.wheeling=!1}),50),t.deltaY?a=t.deltaY>0?1:-1:t.wheelDelta?a=-t.wheelDelta/120:t.detail&&(a=t.detail>0?1:-1),this.zoom(-a*i,t)))},cropStart:function(t){var e=t.buttons,i=t.button;if(!(this.disabled||("mousedown"===t.type||"pointerdown"===t.type&&"mouse"===t.pointerType)&&(et(e)&&1!==e||et(i)&&0!==i||t.ctrlKey))){var a,n=this.options,o=this.pointers;t.changedTouches?lt(t.changedTouches,(function(t){o[t.identifier]=zt(t)})):o[t.pointerId||0]=zt(t),a=Object.keys(o).length>1&&n.zoomable&&n.zoomOnTouch?g:xt(t.target,H),G.test(a)&&!1!==Ot(this.element,A,{originalEvent:t,action:a})&&(t.preventDefault(),this.action=a,this.cropping=!1,a===m&&(this.cropping=!0,ft(this.dragBox,E)))}},cropMove:function(t){var e=this.action;if(!this.disabled&&e){var i=this.pointers;t.preventDefault(),!1!==Ot(this.element,S,{originalEvent:t,action:e})&&(t.changedTouches?lt(t.changedTouches,(function(t){dt(i[t.identifier]||{},zt(t,!0))})):dt(i[t.pointerId||0]||{},zt(t,!0)),this.change(t))}},cropEnd:function(t){if(!this.disabled){var e=this.action,i=this.pointers;t.changedTouches?lt(t.changedTouches,(function(t){delete i[t.identifier]})):delete i[t.pointerId||0],e&&(t.preventDefault(),Object.keys(i).length||(this.action=""),this.cropping&&(this.cropping=!1,wt(this.dragBox,E,this.cropped&&this.options.modal)),Ot(this.element,R,{originalEvent:t,action:e}))}}},Ut={change:function(t){var i,a=this.options,n=this.canvasData,o=this.containerData,r=this.cropBoxData,h=this.pointers,s=this.action,c=a.aspectRatio,l=r.left,d=r.top,D=r.width,B=r.height,O=l+D,T=d+B,E=0,W=0,H=o.width,N=o.height,L=!0;!c&&t.shiftKey&&(c=D&&B?D/B:1),this.limited&&(E=r.minLeft,W=r.minTop,H=E+Math.min(o.width,n.width,n.left+n.width),N=W+Math.min(o.height,n.height,n.top+n.height));var z=h[Object.keys(h)[0]],Y={x:z.endX-z.startX,y:z.endY-z.startY},X=function(t){switch(t){case f:O+Y.x>H&&(Y.x=H-O);break;case v:l+Y.x<E&&(Y.x=E-l);break;case b:d+Y.y<W&&(Y.y=W-d);break;case w:T+Y.y>N&&(Y.y=N-T)}};switch(s){case p:l+=Y.x,d+=Y.y;break;case f:if(Y.x>=0&&(O>=H||c&&(d<=W||T>=N))){L=!1;break}X(f),(D+=Y.x)<0&&(s=v,l-=D=-D),c&&(B=D/c,d+=(r.height-B)/2);break;case b:if(Y.y<=0&&(d<=W||c&&(l<=E||O>=H))){L=!1;break}X(b),B-=Y.y,d+=Y.y,B<0&&(s=w,d-=B=-B),c&&(D=B*c,l+=(r.width-D)/2);break;case v:if(Y.x<=0&&(l<=E||c&&(d<=W||T>=N))){L=!1;break}X(v),D-=Y.x,l+=Y.x,D<0&&(s=f,l-=D=-D),c&&(B=D/c,d+=(r.height-B)/2);break;case w:if(Y.y>=0&&(T>=N||c&&(l<=E||O>=H))){L=!1;break}X(w),(B+=Y.y)<0&&(s=b,d-=B=-B),c&&(D=B*c,l+=(r.width-D)/2);break;case y:if(c){if(Y.y<=0&&(d<=W||O>=H)){L=!1;break}X(b),B-=Y.y,d+=Y.y,D=B*c}else X(b),X(f),Y.x>=0?O<H?D+=Y.x:Y.y<=0&&d<=W&&(L=!1):D+=Y.x,Y.y<=0?d>W&&(B-=Y.y,d+=Y.y):(B-=Y.y,d+=Y.y);D<0&&B<0?(s=C,d-=B=-B,l-=D=-D):D<0?(s=x,l-=D=-D):B<0&&(s=M,d-=B=-B);break;case x:if(c){if(Y.y<=0&&(d<=W||l<=E)){L=!1;break}X(b),B-=Y.y,d+=Y.y,D=B*c,l+=r.width-D}else X(b),X(v),Y.x<=0?l>E?(D-=Y.x,l+=Y.x):Y.y<=0&&d<=W&&(L=!1):(D-=Y.x,l+=Y.x),Y.y<=0?d>W&&(B-=Y.y,d+=Y.y):(B-=Y.y,d+=Y.y);D<0&&B<0?(s=M,d-=B=-B,l-=D=-D):D<0?(s=y,l-=D=-D):B<0&&(s=C,d-=B=-B);break;case C:if(c){if(Y.x<=0&&(l<=E||T>=N)){L=!1;break}X(v),D-=Y.x,l+=Y.x,B=D/c}else X(w),X(v),Y.x<=0?l>E?(D-=Y.x,l+=Y.x):Y.y>=0&&T>=N&&(L=!1):(D-=Y.x,l+=Y.x),Y.y>=0?T<N&&(B+=Y.y):B+=Y.y;D<0&&B<0?(s=y,d-=B=-B,l-=D=-D):D<0?(s=M,l-=D=-D):B<0&&(s=x,d-=B=-B);break;case M:if(c){if(Y.x>=0&&(O>=H||T>=N)){L=!1;break}X(f),B=(D+=Y.x)/c}else X(w),X(f),Y.x>=0?O<H?D+=Y.x:Y.y>=0&&T>=N&&(L=!1):D+=Y.x,Y.y>=0?T<N&&(B+=Y.y):B+=Y.y;D<0&&B<0?(s=x,d-=B=-B,l-=D=-D):D<0?(s=C,l-=D=-D):B<0&&(s=y,d-=B=-B);break;case u:this.move(Y.x,Y.y),L=!1;break;case g:this.zoom(function(t){var i=e({},t),a=0;return lt(t,(function(t,e){delete i[e],lt(i,(function(e){var i=Math.abs(t.startX-e.startX),n=Math.abs(t.startY-e.startY),o=Math.abs(t.endX-e.endX),r=Math.abs(t.endY-e.endY),h=Math.sqrt(i*i+n*n),s=(Math.sqrt(o*o+r*r)-h)/h;Math.abs(s)>Math.abs(a)&&(a=s)}))})),a}(h),t),L=!1;break;case m:if(!Y.x||!Y.y){L=!1;break}i=Tt(this.cropper),l=z.startX-i.left,d=z.startY-i.top,D=r.minWidth,B=r.minHeight,Y.x>0?s=Y.y>0?M:y:Y.x<0&&(l-=D,s=Y.y>0?C:x),Y.y<0&&(d-=B),this.cropped||(vt(this.cropBox,k),this.cropped=!0,this.limited&&this.limitCropBox(!0,!0))}L&&(r.width=D,r.height=B,r.left=l,r.top=d,this.action=s,this.renderCropBox()),lt(h,(function(t){t.startX=t.endX,t.startY=t.endY}))}},qt={crop:function(){return!this.ready||this.cropped||this.disabled||(this.cropped=!0,this.limitCropBox(!0,!0),this.options.modal&&ft(this.dragBox,E),vt(this.cropBox,k),this.setCropBoxData(this.initialCropBoxData)),this},reset:function(){return this.ready&&!this.disabled&&(this.imageData=dt({},this.initialImageData),this.canvasData=dt({},this.initialCanvasData),this.cropBoxData=dt({},this.initialCropBoxData),this.renderCanvas(),this.cropped&&this.renderCropBox()),this},clear:function(){return this.cropped&&!this.disabled&&(dt(this.cropBoxData,{left:0,top:0,width:0,height:0}),this.cropped=!1,this.renderCropBox(),this.limitCanvas(!0,!0),this.renderCanvas(),vt(this.dragBox,E),ft(this.cropBox,k)),this},replace:function(t){var e=arguments.length>1&&void 0!==arguments[1]&&arguments[1];return!this.disabled&&t&&(this.isImg&&(this.element.src=t),e?(this.url=t,this.image.src=t,this.ready&&(this.viewBoxImage.src=t,lt(this.previews,(function(e){e.getElementsByTagName("img")[0].src=t})))):(this.isImg&&(this.replaced=!0),this.options.data=null,this.uncreate(),this.load(t))),this},enable:function(){return this.ready&&this.disabled&&(this.disabled=!1,vt(this.cropper,B)),this},disable:function(){return this.ready&&!this.disabled&&(this.disabled=!0,ft(this.cropper,B)),this},destroy:function(){var t=this.element;return t[d]?(t[d]=void 0,this.isImg&&this.replaced&&(t.src=this.originalUrl),this.uncreate(),this):this},move:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:t,i=this.canvasData,a=i.left,n=i.top;return this.moveTo(at(t)?t:a+Number(t),at(e)?e:n+Number(e))},moveTo:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:t,i=this.canvasData,a=!1;return t=Number(t),e=Number(e),this.ready&&!this.disabled&&this.options.movable&&(et(t)&&(i.left=t,a=!0),et(e)&&(i.top=e,a=!0),a&&this.renderCanvas(!0)),this},zoom:function(t,e){var i=this.canvasData;return t=(t=Number(t))<0?1/(1-t):1+t,this.zoomTo(i.width*t/i.naturalWidth,null,e)},zoomTo:function(t,e,i){var a=this.options,n=this.canvasData,o=n.width,r=n.height,h=n.naturalWidth,s=n.naturalHeight;if((t=Number(t))>=0&&this.ready&&!this.disabled&&a.zoomable){var c=h*t,l=s*t;if(!1===Ot(this.element,K,{ratio:t,oldRatio:o/h,originalEvent:i}))return this;if(i){var d=this.pointers,p=Tt(this.cropper),m=d&&Object.keys(d).length?function(t){var e=0,i=0,a=0;return lt(t,(function(t){var n=t.startX,o=t.startY;e+=n,i+=o,a+=1})),{pageX:e/=a,pageY:i/=a}}(d):{pageX:i.pageX,pageY:i.pageY};n.left-=(c-o)*((m.pageX-p.left-n.left)/o),n.top-=(l-r)*((m.pageY-p.top-n.top)/r)}else rt(e)&&et(e.x)&&et(e.y)?(n.left-=(c-o)*((e.x-n.left)/o),n.top-=(l-r)*((e.y-n.top)/r)):(n.left-=(c-o)/2,n.top-=(l-r)/2);n.width=c,n.height=l,this.renderCanvas(!0)}return this},rotate:function(t){return this.rotateTo((this.imageData.rotate||0)+Number(t))},rotateTo:function(t){return et(t=Number(t))&&this.ready&&!this.disabled&&this.options.rotatable&&(this.imageData.rotate=t%360,this.renderCanvas(!0,!0)),this},scaleX:function(t){var e=this.imageData.scaleY;return this.scale(t,et(e)?e:1)},scaleY:function(t){var e=this.imageData.scaleX;return this.scale(et(e)?e:1,t)},scale:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:t,i=this.imageData,a=!1;return t=Number(t),e=Number(e),this.ready&&!this.disabled&&this.options.scalable&&(et(t)&&(i.scaleX=t,a=!0),et(e)&&(i.scaleY=e,a=!0),a&&this.renderCanvas(!0,!0)),this},getData:function(){var t,e=arguments.length>0&&void 0!==arguments[0]&&arguments[0],i=this.options,a=this.imageData,n=this.canvasData,o=this.cropBoxData;if(this.ready&&this.cropped){t={x:o.left-n.left,y:o.top-n.top,width:o.width,height:o.height};var r=a.width/a.naturalWidth;if(lt(t,(function(e,i){t[i]=e/r})),e){var h=Math.round(t.y+t.height),s=Math.round(t.x+t.width);t.x=Math.round(t.x),t.y=Math.round(t.y),t.width=s-t.x,t.height=h-t.y}}else t={x:0,y:0,width:0,height:0};return i.rotatable&&(t.rotate=a.rotate||0),i.scalable&&(t.scaleX=a.scaleX||1,t.scaleY=a.scaleY||1),t},setData:function(t){var e=this.options,i=this.imageData,a=this.canvasData,n={};if(this.ready&&!this.disabled&&rt(t)){var o=!1;e.rotatable&&et(t.rotate)&&t.rotate!==i.rotate&&(i.rotate=t.rotate,o=!0),e.scalable&&(et(t.scaleX)&&t.scaleX!==i.scaleX&&(i.scaleX=t.scaleX,o=!0),et(t.scaleY)&&t.scaleY!==i.scaleY&&(i.scaleY=t.scaleY,o=!0)),o&&this.renderCanvas(!0,!0);var r=i.width/i.naturalWidth;et(t.x)&&(n.left=t.x*r+a.left),et(t.y)&&(n.top=t.y*r+a.top),et(t.width)&&(n.width=t.width*r),et(t.height)&&(n.height=t.height*r),this.setCropBoxData(n)}return this},getContainerData:function(){return this.ready?dt({},this.containerData):{}},getImageData:function(){return this.sized?dt({},this.imageData):{}},getCanvasData:function(){var t=this.canvasData,e={};return this.ready&&lt(["left","top","width","height","naturalWidth","naturalHeight"],(function(i){e[i]=t[i]})),e},setCanvasData:function(t){var e=this.canvasData,i=e.aspectRatio;return this.ready&&!this.disabled&&rt(t)&&(et(t.left)&&(e.left=t.left),et(t.top)&&(e.top=t.top),et(t.width)?(e.width=t.width,e.height=t.width/i):et(t.height)&&(e.height=t.height,e.width=t.height*i),this.renderCanvas(!0)),this},getCropBoxData:function(){var t,e=this.cropBoxData;return this.ready&&this.cropped&&(t={left:e.left,top:e.top,width:e.width,height:e.height}),t||{}},setCropBoxData:function(t){var e,i,a=this.cropBoxData,n=this.options.aspectRatio;return this.ready&&this.cropped&&!this.disabled&&rt(t)&&(et(t.left)&&(a.left=t.left),et(t.top)&&(a.top=t.top),et(t.width)&&t.width!==a.width&&(e=!0,a.width=t.width),et(t.height)&&t.height!==a.height&&(i=!0,a.height=t.height),n&&(e?a.height=a.width/n:i&&(a.width=a.height*n)),this.renderCropBox()),this},getCroppedCanvas:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};if(!this.ready||!window.HTMLCanvasElement)return null;var e=this.canvasData,i=function(t,e,i,a){var n=e.aspectRatio,r=e.naturalWidth,h=e.naturalHeight,s=e.rotate,c=void 0===s?0:s,l=e.scaleX,d=void 0===l?1:l,p=e.scaleY,m=void 0===p?1:p,u=i.aspectRatio,g=i.naturalWidth,f=i.naturalHeight,v=a.fillColor,w=void 0===v?"transparent":v,b=a.imageSmoothingEnabled,y=void 0===b||b,x=a.imageSmoothingQuality,M=void 0===x?"low":x,C=a.maxWidth,D=void 0===C?1/0:C,B=a.maxHeight,k=void 0===B?1/0:B,O=a.minWidth,T=void 0===O?0:O,E=a.minHeight,W=void 0===E?0:E,H=document.createElement("canvas"),N=H.getContext("2d"),L=Yt({aspectRatio:u,width:D,height:k}),z=Yt({aspectRatio:u,width:T,height:W},"cover"),Y=Math.min(L.width,Math.max(z.width,g)),X=Math.min(L.height,Math.max(z.height,f)),R=Yt({aspectRatio:n,width:D,height:k}),S=Yt({aspectRatio:n,width:T,height:W},"cover"),A=Math.min(R.width,Math.max(S.width,r)),j=Math.min(R.height,Math.max(S.height,h)),P=[-A/2,-j/2,A,j];return H.width=mt(Y),H.height=mt(X),N.fillStyle=w,N.fillRect(0,0,Y,X),N.save(),N.translate(Y/2,X/2),N.rotate(c*Math.PI/180),N.scale(d,m),N.imageSmoothingEnabled=y,N.imageSmoothingQuality=M,N.drawImage.apply(N,[t].concat(o(P.map((function(t){return Math.floor(mt(t))}))))),N.restore(),H}(this.image,this.imageData,e,t);if(!this.cropped)return i;var a=this.getData(),n=a.x,r=a.y,h=a.width,s=a.height,c=i.width/Math.floor(e.naturalWidth);1!==c&&(n*=c,r*=c,h*=c,s*=c);var l=h/s,d=Yt({aspectRatio:l,width:t.maxWidth||1/0,height:t.maxHeight||1/0}),p=Yt({aspectRatio:l,width:t.minWidth||0,height:t.minHeight||0},"cover"),m=Yt({aspectRatio:l,width:t.width||(1!==c?i.width:h),height:t.height||(1!==c?i.height:s)}),u=m.width,g=m.height;u=Math.min(d.width,Math.max(p.width,u)),g=Math.min(d.height,Math.max(p.height,g));var f=document.createElement("canvas"),v=f.getContext("2d");f.width=mt(u),f.height=mt(g),v.fillStyle=t.fillColor||"transparent",v.fillRect(0,0,u,g);var w=t.imageSmoothingEnabled,b=void 0===w||w,y=t.imageSmoothingQuality;v.imageSmoothingEnabled=b,y&&(v.imageSmoothingQuality=y);var x,M,C,D,B,k,O=i.width,T=i.height,E=n,W=r;E<=-h||E>O?(E=0,x=0,C=0,B=0):E<=0?(C=-E,E=0,B=x=Math.min(O,h+E)):E<=O&&(C=0,B=x=Math.min(h,O-E)),x<=0||W<=-s||W>T?(W=0,M=0,D=0,k=0):W<=0?(D=-W,W=0,k=M=Math.min(T,s+W)):W<=T&&(D=0,k=M=Math.min(s,T-W));var H=[E,W,x,M];if(B>0&&k>0){var N=u/h;H.push(C*N,D*N,B*N,k*N)}return v.drawImage.apply(v,[i].concat(o(H.map((function(t){return Math.floor(mt(t))}))))),f},setAspectRatio:function(t){var e=this.options;return this.disabled||at(t)||(e.aspectRatio=Math.max(0,t)||NaN,this.ready&&(this.initCropBox(),this.cropped&&this.renderCropBox())),this},setDragMode:function(t){var e=this.options,i=this.dragBox,a=this.face;if(this.ready&&!this.disabled){var n=t===L,o=e.movable&&t===z;t=n||o?t:Y,e.dragMode=t,Mt(i,H,t),wt(i,D,n),wt(i,W,o),e.cropBoxMovable||(Mt(a,H,t),wt(a,D,n),wt(a,W,o))}return this}},$t=s.Cropper,Qt=function(){function t(e){var i=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};if(function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t),!e||!J.test(e.tagName))throw new Error("The first argument is required and must be an <img> or <canvas> element.");this.element=e,this.options=dt({},_,rt(i)&&i),this.cropped=!1,this.disabled=!1,this.pointers={},this.ready=!1,this.reloading=!1,this.replaced=!1,this.sized=!1,this.sizing=!1,this.init()}var e,i,n;return e=t,n=[{key:"noConflict",value:function(){return window.Cropper=$t,t}},{key:"setDefaults",value:function(t){dt(_,rt(t)&&t)}}],(i=[{key:"init",value:function(){var t,e=this.element,i=e.tagName.toLowerCase();if(!e[d]){if(e[d]=this,"img"===i){if(this.isImg=!0,t=e.getAttribute("src")||"",this.originalUrl=t,!t)return;t=e.src}else"canvas"===i&&window.HTMLCanvasElement&&(t=e.toDataURL());this.load(t)}}},{key:"load",value:function(t){var e=this;if(t){this.url=t,this.imageData={};var i=this.element,a=this.options;if(a.rotatable||a.scalable||(a.checkOrientation=!1),a.checkOrientation&&window.ArrayBuffer)if(V.test(t))F.test(t)?this.read((n=t.replace(Rt,""),o=atob(n),r=new ArrayBuffer(o.length),lt(h=new Uint8Array(r),(function(t,e){h[e]=o.charCodeAt(e)})),r)):this.clone();else{var n,o,r,h,s=new XMLHttpRequest,c=this.clone.bind(this);this.reloading=!0,this.xhr=s,s.onabort=c,s.onerror=c,s.ontimeout=c,s.onprogress=function(){s.getResponseHeader("content-type")!==Z&&s.abort()},s.onload=function(){e.read(s.response)},s.onloadend=function(){e.reloading=!1,e.xhr=null},a.checkCrossOrigin&&Ht(t)&&i.crossOrigin&&(t=Nt(t)),s.open("GET",t,!0),s.responseType="arraybuffer",s.withCredentials="use-credentials"===i.crossOrigin,s.send()}else this.clone()}}},{key:"read",value:function(t){var e=this.options,i=this.imageData,a=St(t),n=0,o=1,r=1;if(a>1){this.url=function(t,e){for(var i=[],a=new Uint8Array(t);a.length>0;)i.push(Xt.apply(null,ct(a.subarray(0,8192)))),a=a.subarray(8192);return"data:".concat(e,";base64,").concat(btoa(i.join("")))}(t,Z);var h=function(t){var e=0,i=1,a=1;switch(t){case 2:i=-1;break;case 3:e=-180;break;case 4:a=-1;break;case 5:e=90,a=-1;break;case 6:e=90;break;case 7:e=90,i=-1;break;case 8:e=-90}return{rotate:e,scaleX:i,scaleY:a}}(a);n=h.rotate,o=h.scaleX,r=h.scaleY}e.rotatable&&(i.rotate=n),e.scalable&&(i.scaleX=o,i.scaleY=r),this.clone()}},{key:"clone",value:function(){var t=this.element,e=this.url,i=t.crossOrigin,a=e;this.options.checkCrossOrigin&&Ht(e)&&(i||(i="anonymous"),a=Nt(e)),this.crossOrigin=i,this.crossOriginUrl=a;var n=document.createElement("img");i&&(n.crossOrigin=i),n.src=a||e,n.alt=t.alt||"The image to crop",this.image=n,n.onload=this.start.bind(this),n.onerror=this.stop.bind(this),ft(n,O),t.parentNode.insertBefore(n,t.nextSibling)}},{key:"start",value:function(){var t=this,e=this.image;e.onload=null,e.onerror=null,this.sizing=!0;var i=s.navigator&&/(?:iPad|iPhone|iPod).*?AppleWebKit/i.test(s.navigator.userAgent),a=function(e,i){dt(t.imageData,{naturalWidth:e,naturalHeight:i,aspectRatio:e/i}),t.initialImageData=dt({},t.imageData),t.sizing=!1,t.sized=!0,t.build()};if(!e.naturalWidth||i){var n=document.createElement("img"),o=document.body||document.documentElement;this.sizingImage=n,n.onload=function(){a(n.width,n.height),i||o.removeChild(n)},n.src=e.src,i||(n.style.cssText="left:0;max-height:none!important;max-width:none!important;min-height:0!important;min-width:0!important;opacity:0;position:absolute;top:0;z-index:-1;",o.appendChild(n))}else a(e.naturalWidth,e.naturalHeight)}},{key:"stop",value:function(){var t=this.image;t.onload=null,t.onerror=null,t.parentNode.removeChild(t),this.image=null}},{key:"build",value:function(){if(this.sized&&!this.ready){var t=this.element,e=this.options,i=this.image,a=t.parentNode,n=document.createElement("div");n.innerHTML='<div class="cropper-container" touch-action="none"><div class="cropper-wrap-box"><div class="cropper-canvas"></div></div><div class="cropper-drag-box"></div><div class="cropper-crop-box"><span class="cropper-view-box"></span><span class="cropper-dashed dashed-h"></span><span class="cropper-dashed dashed-v"></span><span class="cropper-center"></span><span class="cropper-face"></span><span class="cropper-line line-e" data-cropper-action="e"></span><span class="cropper-line line-n" data-cropper-action="n"></span><span class="cropper-line line-w" data-cropper-action="w"></span><span class="cropper-line line-s" data-cropper-action="s"></span><span class="cropper-point point-e" data-cropper-action="e"></span><span class="cropper-point point-n" data-cropper-action="n"></span><span class="cropper-point point-w" data-cropper-action="w"></span><span class="cropper-point point-s" data-cropper-action="s"></span><span class="cropper-point point-ne" data-cropper-action="ne"></span><span class="cropper-point point-nw" data-cropper-action="nw"></span><span class="cropper-point point-sw" data-cropper-action="sw"></span><span class="cropper-point point-se" data-cropper-action="se"></span></div></div>';var o=n.querySelector(".".concat(d,"-container")),r=o.querySelector(".".concat(d,"-canvas")),h=o.querySelector(".".concat(d,"-drag-box")),s=o.querySelector(".".concat(d,"-crop-box")),c=s.querySelector(".".concat(d,"-face"));this.container=a,this.cropper=o,this.canvas=r,this.dragBox=h,this.cropBox=s,this.viewBox=o.querySelector(".".concat(d,"-view-box")),this.face=c,r.appendChild(i),ft(t,k),a.insertBefore(o,t.nextSibling),vt(i,O),this.initPreview(),this.bind(),e.initialAspectRatio=Math.max(0,e.initialAspectRatio)||NaN,e.aspectRatio=Math.max(0,e.aspectRatio)||NaN,e.viewMode=Math.max(0,Math.min(3,Math.round(e.viewMode)))||0,ft(s,k),e.guides||ft(s.getElementsByClassName("".concat(d,"-dashed")),k),e.center||ft(s.getElementsByClassName("".concat(d,"-center")),k),e.background&&ft(o,"".concat(d,"-bg")),e.highlight||ft(c,T),e.cropBoxMovable&&(ft(c,W),Mt(c,H,p)),e.cropBoxResizable||(ft(s.getElementsByClassName("".concat(d,"-line")),k),ft(s.getElementsByClassName("".concat(d,"-point")),k)),this.render(),this.ready=!0,this.setDragMode(e.dragMode),e.autoCrop&&this.crop(),this.setData(e.data),ht(e.ready)&&kt(t,q,e.ready,{once:!0}),Ot(t,q)}}},{key:"unbuild",value:function(){if(this.ready){this.ready=!1,this.unbind(),this.resetPreview();var t=this.cropper.parentNode;t&&t.removeChild(this.cropper),vt(this.element,k)}}},{key:"uncreate",value:function(){this.ready?(this.unbuild(),this.ready=!1,this.cropped=!1):this.sizing?(this.sizingImage.onload=null,this.sizing=!1,this.sized=!1):this.reloading?(this.xhr.onabort=null,this.xhr.abort()):this.image&&this.stop()}}])&&a(e.prototype,i),n&&a(e,n),Object.defineProperty(e,"prototype",{writable:!1}),t}();return dt(Qt.prototype,At,jt,Pt,It,Ut,qt),Qt}));
//# sourceMappingURL=cropper.bundle.js.map
