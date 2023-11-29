"use strict";

// Class definition
var KTTimelineWidget2 = function () {
    // Private methods
    var handleCheckbox = function() {
        var card = document.querySelector('#kt_timeline_widget_2_card');        
        
        if (!card) {
            return;
        }

        // Checkbox Handler
        KTUtil.on(card, '[data-kt-element="checkbox"]', 'change', function (e) {
            var check = this.closest('.form-check');
            var tr = this.closest('tr');
            var bullet = tr.querySelector('[data-kt-element="bullet"]');
            var status = tr.querySelector('[data-kt-element="status"]');

            if ( this.checked === true ) {
                check.classList.add('form-check-success');

                bullet.classList.remove('bg-primary');
                bullet.classList.add('bg-success');

                status.innerText = 'Done';
                status.classList.remove('badge-light-primary');
                status.classList.add('badge-light-success');
            } else {
                check.classList.remove('form-check-success');

                bullet.classList.remove('bg-success');
                bullet.classList.add('bg-primary');

                status.innerText = 'In Process';
                status.classList.remove('badge-light-success');
                status.classList.add('badge-light-primary');
            }
        });
    }

    // Public methods
    return {
        init: function () {           
            handleCheckbox();             
        }   
    }
}();

// Webpack support
if (typeof module !== 'undefined') {
    module.exports = KTTimelineWidget2;
}

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTTimelineWidget2.init();
});


 