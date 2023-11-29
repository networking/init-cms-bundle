//
// 3rd-Party Plugins JavaScript Includes
//


//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////
////  Mandatory Plugins Includes(do not remove or change order!)  ////
//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////

// Jquery - jQuery is a popular and feature-rich JavaScript library. Learn more: https://jquery.com/
window.jQuery = window.$ = require('jquery');

// Bootstrap - The most popular framework uses as the foundation. Learn more: http://getbootstrap.com
window.bootstrap = require('bootstrap');

// Popper.js - Tooltip & Popover Positioning Engine used by Bootstrap. Learn more: https://popper.js.org
window.Popper = require('@popperjs/core');

// Wnumb - Number & Money formatting. Learn more: https://refreshless.com/wnumb/
window.wNumb = require('wnumb');

// Moment - Parse, validate, manipulate, and display dates and times in JavaScript. Learn more: https://momentjs.com/
window.moment = require('moment');

// ES6-Shim - ECMAScript 6 compatibility shims for legacy JS engines.  Learn more: https://github.com/paulmillr/es6-shim
require("es6-shim/es6-shim.min.js");

//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////
///  Optional Plugins Includes(you can remove or add)  ///////////////
//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////

// Apexcharts - modern charting library that helps developers to create beautiful and interactive visualizations for web pages: https://apexcharts.com/
window.ApexCharts = require('apexcharts/dist/apexcharts.min.js');

// FormValidation - Best premium validation library for JavaScript. Zero dependencies. Learn more: https://formvalidation.io/
window.FormValidation = require('@/src/plugins/@form-validation/umd/bundle/popular.min.js');
require('@/src/plugins/@form-validation/umd/bundle/full.min.js');
import { Bootstrap5 } from '@form-validation/plugin-bootstrap5';
window.FormValidation.plugins.Bootstrap5 = Bootstrap5;

// Date Range Picker - A JavaScript component for choosing date ranges, dates and times: https://www.daterangepicker.com/
require('bootstrap-daterangepicker/daterangepicker.js');

// Bootstrap Maxlength - This plugin integrates by default with Twitter bootstrap using badges to display the maximum length of the field where the user is inserting text: https://github.com/mimo84/bootstrap-maxlength
require('bootstrap-maxlength/src/bootstrap-maxlength.js');

// Bootstrap Mmultiselectsplitter - Transforms a <select> containing one or more <optgroup> in two chained <select>: https://github.com/poolerMF/bootstrap-multiselectsplitter/
require('bootstrap-multiselectsplitter/bootstrap-multiselectsplitter.min.js');

// Select2 - Select2 is a jQuery based replacement for select boxes: https://select2.org/
require('select2/dist/js/select2.full.min.js');
require('@/src/js/vendors/plugins/select2.init.js');

// Tempus Dominus is the successor to the very popular Eonasdan/bootstrap-datetimepicker. The plugin provide a robust date and time picker designed to integrate into your Bootstrap project.
window.tempusDominus = require('@eonasdan/tempus-dominus/dist/js/tempus-dominus.min.js');
require('@/src/js/vendors/plugins/tempus-dominus.init.js');
require('@eonasdan/tempus-dominus/dist/locales/de.js');
require('@eonasdan/tempus-dominus/dist/plugins/customDateFormat.js');

// Flatpickr - is a lightweight and powerful datetime picker.
require('flatpickr/dist/flatpickr.js');
require('flatpickr/dist/l10n/ar.js');

// Inputmask - is a javascript library which creates an input mask: https://github.com/RobinHerbots/Inputmask
require('inputmask/dist/inputmask.js');
require('inputmask/dist/bindings/inputmask.binding.js');

// noUiSlider - is a lightweight range slider with multi-touch support and a ton of features. It supports non-linear ranges, requires no external dependencies: https://refreshless.com/nouislider/
// window.noUiSlider = require('nouislider/dist/nouislider.min.js');

// The autosize - function accepts a single textarea element, or an array or array-like object (such as a NodeList or jQuery collection) of textarea elements: https://www.jacklmoore.com/autosize/
window.autosize = require('autosize/dist/autosize.min.js');

// Clipboard - Copy text to the clipboard shouldn't be hard. It shouldn't require dozens of steps to configure or hundreds of KBs to load: https://clipboardjs.com/
window.ClipboardJS = require('clipboard/dist/clipboard.min.js');

// DropzoneJS -  is an open source library that provides drag'n'drop file uploads with image previews: https://www.dropzonejs.com/
window.Dropzone = require('dropzone/dist/min/dropzone.min.js').default;
require('@/src/js/vendors/plugins/dropzone.init.js');

// Quill - is a free, open source WYSIWYG editor built for the modern web. Completely customize it for any need with its modular architecture and expressive API: https://quilljs.com/
// window.Quill = require('quill/dist/quill.js');

// Tagify - Transforms an input field or a textarea into a Tags component, in an easy, customizable way, with great performance and small code footprint, exploded with features: https://github.com/yairEO/tagify
require('@yaireo/tagify/dist/tagify.polyfills.min.js');
window.Tagify = require('@yaireo/tagify/dist/tagify.min.js');

// Toastr - is a Javascript library for non-blocking notifications. jQuery is required. The goal is to create a simple core library that can be customized and extended: https://github.com/CodeSeven/toastr
window.toastr = require('@/src/plugins/toastr/build/toastr.min.js');

// ES6 Promise Polyfill - This is a polyfill of the ES6 Promise: https://github.com/lahmatiy/es6-promise-polyfill
require('es6-promise-polyfill/promise.min.js');

// Sweetalert2 - a beautiful, responsive, customizable and accessible (WAI-ARIA) replacement for JavaScript's popup boxes: https://sweetalert2.github.io/
window.Swal = window.swal = require('sweetalert2/dist/sweetalert2.min.js');
require('@/src/js/vendors/plugins/sweetalert2.init.js');

// CountUp.js - is a dependency-free, lightweight JavaScript class that can be used to quickly create animations that display numerical data in a more interesting way.
window.countUp = require('countup.js/dist/countUp.min.js');

// Chart.js - Simple yet flexible JavaScript charting for designers & developers
import Chart from 'chart.js/auto';
window.Chart = Chart;

// Tiny slider - for all purposes, inspired by Owl Carousel.
window.tns = require('tiny-slider/src/tiny-slider.js').tns;

// A lightweight script to animate scrolling to anchor links
window.SmoothScroll = require('smooth-scroll/dist/smooth-scroll.js');

// Highly performant, light and configurable lazy loader in pure JS using IntersectionObserver API: https://apoorv.pro/lozad.js/
window.lozad = require('lozad/dist/lozad.min.js');

window.axios = require('axios');
