"use strict";

// Class definition
var KTFormsWidget1 = (function () {
    // Private methods
    var initForm1 = function () {
        var element = document.querySelector('#kt_forms_widget_1_select_1');

        if ( !element ) {
            return;
        }

        var optionFormat = function(item) {
            if ( !item.id ) {
                return item.text;
            }

            var span = document.createElement('span');
            var template = '';

            template += '<img src="' + item.element.getAttribute('data-kt-select2-icon') + '" class="rounded-circle h-20px me-2" alt="image"/>';
            template += item.text;

            span.innerHTML = template;

            return $(span);
        }

        // Init Select2 --- more info: https://select2.org/
        $(element).select2({
            placeholder: "Select coin",
            minimumResultsForSearch: Infinity,
            templateSelection: optionFormat,
            templateResult: optionFormat
        });
    };

    var initForm2 = function () {
        var element = document.querySelector('#kt_forms_widget_1_select_2');

        if ( !element ) {
            return;
        }

        var optionFormat = function(item) {
            if ( !item.id ) {
                return item.text;
            }

            var span = document.createElement('span');
            var template = '';

            template += '<img src="' + item.element.getAttribute('data-kt-select2-icon') + '" class="rounded-circle h-20px me-2" alt="image"/>';
            template += item.text;

            span.innerHTML = template;

            return $(span);
        }

        // Init Select2 --- more info: https://select2.org/
        $(element).select2({
            placeholder: "Select coin",
            minimumResultsForSearch: Infinity,
            templateSelection: optionFormat,
            templateResult: optionFormat
        });
    };

    // Public methods
    return {
        init: function () {
            initForm1();
            initForm2();
        },
    };
})();

// Webpack support
if (typeof module !== "undefined") {
    module.exports = KTFormsWidget1;
}

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTFormsWidget1.init();
});
