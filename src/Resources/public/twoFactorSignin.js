/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
var __webpack_exports__ = {};
/*!*********************************************************!*\
  !*** ./assets/cms/authentication/sign-in/two-factor.js ***!
  \*********************************************************/


// Class Definition
var KTSigninTwoFactor = function() {
    // Elements
    var form;
    var submitButton;

    // Handle form
    var handleForm = function(e) {        
        // Handle form submit
        submitButton.addEventListener('click', function (e) {
            e.preventDefault();

            let validated = true;

            let inputs = [].slice.call(form.querySelectorAll('input[maxlength="1"]'));
            inputs.map(function (input) {
                if (input.value === '' || input.value.length === 0) {
                    validated = false;
                }
            });

            if (validated === true) {
                // Show loading indication
                submitButton.setAttribute('data-kt-indicator', 'on');

                // Disable button to avoid multiple click 
                submitButton.disabled = true;

                let code = inputs.reduce((acc, input) => acc + input.value, '')

                axios.post('/admin', { _code: code }, {...axiosConfig}).then((response) => {
                    submitButton.disabled = false;
                    submitButton.removeAttribute('data-kt-indicator');
                    Swal.fire({
                        text: "You have been successfully verified!",
                        icon: "success",
                        timer: 1000,
                        showConfirmButton: false,
                    }).then(function (result) {
                       location.href = window.location.href;
                    });
                }).catch((error) => {
                    submitButton.disabled = false;
                    submitButton.removeAttribute('data-kt-indicator');
                    inputs.forEach((input) => input.value = '')
                    swal.fire({
                        text: "Please enter valid securtiy code and try again.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn fw-bold btn-light-primary"
                        }
                    }).then(function() {
                        KTUtil.scrollTop();
                    });
                })
            } else {
                swal.fire({
                    text: "Please enter valid securtiy code and try again.",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn fw-bold btn-light-primary"
                    }
                }).then(function() {
                    KTUtil.scrollTop();
                });
            }
        });
    }

    var handleType = function() {
        var input1 = form.querySelector("[name=code_1]");
        var input2 = form.querySelector("[name=code_2]");
        var input3 = form.querySelector("[name=code_3]");
        var input4 = form.querySelector("[name=code_4]");
        var input5 = form.querySelector("[name=code_5]");
        var input6 = form.querySelector("[name=code_6]");

        input1.focus();

        input1.addEventListener("keyup", function() {
            if (this.value.length === 1) {
                input2.focus();
            }
        });

        input2.addEventListener("keyup", function() {
            if (this.value.length === 1) {
                input3.focus();
            }
        });

        input3.addEventListener("keyup", function() {
            if (this.value.length === 1) {
                input4.focus();
            }
        });

        input4.addEventListener("keyup", function() {
            if (this.value.length === 1) {
                input5.focus();
            }
        });

        input5.addEventListener("keyup", function() {
            if (this.value.length === 1) {
                input6.focus();
            }
        });
        
        input6.addEventListener("keyup", function() {
            if (this.value.length === 1) {
                input6.blur();
            }
        });

        document.addEventListener("paste", (event) => {
            event.preventDefault();

            let paste = (event.clipboardData || window.clipboardData).getData("text");

            let code = paste.split('').slice(0, 6);

            code.forEach((value, index) => {
                let input = form.querySelector(`[name=code_${index + 1}]`);
                input.value = value;
            })
        })
    }    

    // Public functions
    return {
        // Initialization
        init: function() {
            form = document.querySelector('#kt_sing_in_two_factor_form');
            submitButton = document.querySelector('#kt_sing_in_two_factor_submit');

            handleForm();
            handleType();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTSigninTwoFactor.init();
});
/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidHdvRmFjdG9yU2lnbmluLmpzIiwibWFwcGluZ3MiOiI7Ozs7OztBQUFhOztBQUViO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsYUFBYTs7QUFFYjtBQUNBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTs7QUFFQSx1Q0FBdUMsYUFBYSxHQUFHLGVBQWU7QUFDdEU7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxxQkFBcUI7QUFDckI7QUFDQSxxQkFBcUI7QUFDckIsaUJBQWlCO0FBQ2pCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxxQkFBcUI7QUFDckI7QUFDQSxxQkFBcUI7QUFDckIsaUJBQWlCO0FBQ2pCLGNBQWM7QUFDZDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsaUJBQWlCO0FBQ2pCO0FBQ0EsaUJBQWlCO0FBQ2pCO0FBQ0EsU0FBUztBQUNUOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUzs7QUFFVDtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7O0FBRVQ7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTOztBQUVUO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUzs7QUFFVDtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUzs7QUFFVDtBQUNBOztBQUVBOztBQUVBOztBQUVBO0FBQ0EsNkRBQTZELFVBQVU7QUFDdkU7QUFDQSxhQUFhO0FBQ2IsU0FBUztBQUNUOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLENBQUM7O0FBRUQ7QUFDQTtBQUNBO0FBQ0EsQ0FBQyxFIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vYXNzZXRzL2Ntcy9hdXRoZW50aWNhdGlvbi9zaWduLWluL3R3by1mYWN0b3IuanMiXSwic291cmNlc0NvbnRlbnQiOlsiXCJ1c2Ugc3RyaWN0XCI7XG5cbi8vIENsYXNzIERlZmluaXRpb25cbnZhciBLVFNpZ25pblR3b0ZhY3RvciA9IGZ1bmN0aW9uKCkge1xuICAgIC8vIEVsZW1lbnRzXG4gICAgdmFyIGZvcm07XG4gICAgdmFyIHN1Ym1pdEJ1dHRvbjtcblxuICAgIC8vIEhhbmRsZSBmb3JtXG4gICAgdmFyIGhhbmRsZUZvcm0gPSBmdW5jdGlvbihlKSB7ICAgICAgICBcbiAgICAgICAgLy8gSGFuZGxlIGZvcm0gc3VibWl0XG4gICAgICAgIHN1Ym1pdEJ1dHRvbi5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIGZ1bmN0aW9uIChlKSB7XG4gICAgICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG5cbiAgICAgICAgICAgIGxldCB2YWxpZGF0ZWQgPSB0cnVlO1xuXG4gICAgICAgICAgICBsZXQgaW5wdXRzID0gW10uc2xpY2UuY2FsbChmb3JtLnF1ZXJ5U2VsZWN0b3JBbGwoJ2lucHV0W21heGxlbmd0aD1cIjFcIl0nKSk7XG4gICAgICAgICAgICBpbnB1dHMubWFwKGZ1bmN0aW9uIChpbnB1dCkge1xuICAgICAgICAgICAgICAgIGlmIChpbnB1dC52YWx1ZSA9PT0gJycgfHwgaW5wdXQudmFsdWUubGVuZ3RoID09PSAwKSB7XG4gICAgICAgICAgICAgICAgICAgIHZhbGlkYXRlZCA9IGZhbHNlO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pO1xuXG4gICAgICAgICAgICBpZiAodmFsaWRhdGVkID09PSB0cnVlKSB7XG4gICAgICAgICAgICAgICAgLy8gU2hvdyBsb2FkaW5nIGluZGljYXRpb25cbiAgICAgICAgICAgICAgICBzdWJtaXRCdXR0b24uc2V0QXR0cmlidXRlKCdkYXRhLWt0LWluZGljYXRvcicsICdvbicpO1xuXG4gICAgICAgICAgICAgICAgLy8gRGlzYWJsZSBidXR0b24gdG8gYXZvaWQgbXVsdGlwbGUgY2xpY2sgXG4gICAgICAgICAgICAgICAgc3VibWl0QnV0dG9uLmRpc2FibGVkID0gdHJ1ZTtcblxuICAgICAgICAgICAgICAgIGxldCBjb2RlID0gaW5wdXRzLnJlZHVjZSgoYWNjLCBpbnB1dCkgPT4gYWNjICsgaW5wdXQudmFsdWUsICcnKVxuXG4gICAgICAgICAgICAgICAgYXhpb3MucG9zdCgnL2FkbWluJywgeyBfY29kZTogY29kZSB9LCB7Li4uYXhpb3NDb25maWd9KS50aGVuKChyZXNwb25zZSkgPT4ge1xuICAgICAgICAgICAgICAgICAgICBzdWJtaXRCdXR0b24uZGlzYWJsZWQgPSBmYWxzZTtcbiAgICAgICAgICAgICAgICAgICAgc3VibWl0QnV0dG9uLnJlbW92ZUF0dHJpYnV0ZSgnZGF0YS1rdC1pbmRpY2F0b3InKTtcbiAgICAgICAgICAgICAgICAgICAgU3dhbC5maXJlKHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHRleHQ6IFwiWW91IGhhdmUgYmVlbiBzdWNjZXNzZnVsbHkgdmVyaWZpZWQhXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICBpY29uOiBcInN1Y2Nlc3NcIixcbiAgICAgICAgICAgICAgICAgICAgICAgIHRpbWVyOiAxMDAwLFxuICAgICAgICAgICAgICAgICAgICAgICAgc2hvd0NvbmZpcm1CdXR0b246IGZhbHNlLFxuICAgICAgICAgICAgICAgICAgICB9KS50aGVuKGZ1bmN0aW9uIChyZXN1bHQpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgbG9jYXRpb24uaHJlZiA9IHdpbmRvdy5sb2NhdGlvbi5ocmVmO1xuICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICB9KS5jYXRjaCgoZXJyb3IpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgc3VibWl0QnV0dG9uLmRpc2FibGVkID0gZmFsc2U7XG4gICAgICAgICAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbi5yZW1vdmVBdHRyaWJ1dGUoJ2RhdGEta3QtaW5kaWNhdG9yJyk7XG4gICAgICAgICAgICAgICAgICAgIGlucHV0cy5mb3JFYWNoKChpbnB1dCkgPT4gaW5wdXQudmFsdWUgPSAnJylcbiAgICAgICAgICAgICAgICAgICAgc3dhbC5maXJlKHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHRleHQ6IFwiUGxlYXNlIGVudGVyIHZhbGlkIHNlY3VydGl5IGNvZGUgYW5kIHRyeSBhZ2Fpbi5cIixcbiAgICAgICAgICAgICAgICAgICAgICAgIGljb246IFwiZXJyb3JcIixcbiAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbnNTdHlsaW5nOiBmYWxzZSxcbiAgICAgICAgICAgICAgICAgICAgICAgIGNvbmZpcm1CdXR0b25UZXh0OiBcIk9rLCBnb3QgaXQhXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICBjdXN0b21DbGFzczoge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNvbmZpcm1CdXR0b246IFwiYnRuIGZ3LWJvbGQgYnRuLWxpZ2h0LXByaW1hcnlcIlxuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICB9KS50aGVuKGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgS1RVdGlsLnNjcm9sbFRvcCgpO1xuICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICB9KVxuICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICBzd2FsLmZpcmUoe1xuICAgICAgICAgICAgICAgICAgICB0ZXh0OiBcIlBsZWFzZSBlbnRlciB2YWxpZCBzZWN1cnRpeSBjb2RlIGFuZCB0cnkgYWdhaW4uXCIsXG4gICAgICAgICAgICAgICAgICAgIGljb246IFwiZXJyb3JcIixcbiAgICAgICAgICAgICAgICAgICAgYnV0dG9uc1N0eWxpbmc6IGZhbHNlLFxuICAgICAgICAgICAgICAgICAgICBjb25maXJtQnV0dG9uVGV4dDogXCJPaywgZ290IGl0IVwiLFxuICAgICAgICAgICAgICAgICAgICBjdXN0b21DbGFzczoge1xuICAgICAgICAgICAgICAgICAgICAgICAgY29uZmlybUJ1dHRvbjogXCJidG4gZnctYm9sZCBidG4tbGlnaHQtcHJpbWFyeVwiXG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB9KS50aGVuKGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICBLVFV0aWwuc2Nyb2xsVG9wKCk7XG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgIH1cblxuICAgIHZhciBoYW5kbGVUeXBlID0gZnVuY3Rpb24oKSB7XG4gICAgICAgIHZhciBpbnB1dDEgPSBmb3JtLnF1ZXJ5U2VsZWN0b3IoXCJbbmFtZT1jb2RlXzFdXCIpO1xuICAgICAgICB2YXIgaW5wdXQyID0gZm9ybS5xdWVyeVNlbGVjdG9yKFwiW25hbWU9Y29kZV8yXVwiKTtcbiAgICAgICAgdmFyIGlucHV0MyA9IGZvcm0ucXVlcnlTZWxlY3RvcihcIltuYW1lPWNvZGVfM11cIik7XG4gICAgICAgIHZhciBpbnB1dDQgPSBmb3JtLnF1ZXJ5U2VsZWN0b3IoXCJbbmFtZT1jb2RlXzRdXCIpO1xuICAgICAgICB2YXIgaW5wdXQ1ID0gZm9ybS5xdWVyeVNlbGVjdG9yKFwiW25hbWU9Y29kZV81XVwiKTtcbiAgICAgICAgdmFyIGlucHV0NiA9IGZvcm0ucXVlcnlTZWxlY3RvcihcIltuYW1lPWNvZGVfNl1cIik7XG5cbiAgICAgICAgaW5wdXQxLmZvY3VzKCk7XG5cbiAgICAgICAgaW5wdXQxLmFkZEV2ZW50TGlzdGVuZXIoXCJrZXl1cFwiLCBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIGlmICh0aGlzLnZhbHVlLmxlbmd0aCA9PT0gMSkge1xuICAgICAgICAgICAgICAgIGlucHV0Mi5mb2N1cygpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcblxuICAgICAgICBpbnB1dDIuYWRkRXZlbnRMaXN0ZW5lcihcImtleXVwXCIsIGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgaWYgKHRoaXMudmFsdWUubGVuZ3RoID09PSAxKSB7XG4gICAgICAgICAgICAgICAgaW5wdXQzLmZvY3VzKCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuXG4gICAgICAgIGlucHV0My5hZGRFdmVudExpc3RlbmVyKFwia2V5dXBcIiwgZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICBpZiAodGhpcy52YWx1ZS5sZW5ndGggPT09IDEpIHtcbiAgICAgICAgICAgICAgICBpbnB1dDQuZm9jdXMoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG5cbiAgICAgICAgaW5wdXQ0LmFkZEV2ZW50TGlzdGVuZXIoXCJrZXl1cFwiLCBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIGlmICh0aGlzLnZhbHVlLmxlbmd0aCA9PT0gMSkge1xuICAgICAgICAgICAgICAgIGlucHV0NS5mb2N1cygpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcblxuICAgICAgICBpbnB1dDUuYWRkRXZlbnRMaXN0ZW5lcihcImtleXVwXCIsIGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgaWYgKHRoaXMudmFsdWUubGVuZ3RoID09PSAxKSB7XG4gICAgICAgICAgICAgICAgaW5wdXQ2LmZvY3VzKCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgICAgICBcbiAgICAgICAgaW5wdXQ2LmFkZEV2ZW50TGlzdGVuZXIoXCJrZXl1cFwiLCBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIGlmICh0aGlzLnZhbHVlLmxlbmd0aCA9PT0gMSkge1xuICAgICAgICAgICAgICAgIGlucHV0Ni5ibHVyKCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuXG4gICAgICAgIGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoXCJwYXN0ZVwiLCAoZXZlbnQpID0+IHtcbiAgICAgICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG5cbiAgICAgICAgICAgIGxldCBwYXN0ZSA9IChldmVudC5jbGlwYm9hcmREYXRhIHx8IHdpbmRvdy5jbGlwYm9hcmREYXRhKS5nZXREYXRhKFwidGV4dFwiKTtcblxuICAgICAgICAgICAgbGV0IGNvZGUgPSBwYXN0ZS5zcGxpdCgnJykuc2xpY2UoMCwgNik7XG5cbiAgICAgICAgICAgIGNvZGUuZm9yRWFjaCgodmFsdWUsIGluZGV4KSA9PiB7XG4gICAgICAgICAgICAgICAgbGV0IGlucHV0ID0gZm9ybS5xdWVyeVNlbGVjdG9yKGBbbmFtZT1jb2RlXyR7aW5kZXggKyAxfV1gKTtcbiAgICAgICAgICAgICAgICBpbnB1dC52YWx1ZSA9IHZhbHVlO1xuICAgICAgICAgICAgfSlcbiAgICAgICAgfSlcbiAgICB9ICAgIFxuXG4gICAgLy8gUHVibGljIGZ1bmN0aW9uc1xuICAgIHJldHVybiB7XG4gICAgICAgIC8vIEluaXRpYWxpemF0aW9uXG4gICAgICAgIGluaXQ6IGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgZm9ybSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJyNrdF9zaW5nX2luX3R3b19mYWN0b3JfZm9ybScpO1xuICAgICAgICAgICAgc3VibWl0QnV0dG9uID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI2t0X3NpbmdfaW5fdHdvX2ZhY3Rvcl9zdWJtaXQnKTtcblxuICAgICAgICAgICAgaGFuZGxlRm9ybSgpO1xuICAgICAgICAgICAgaGFuZGxlVHlwZSgpO1xuICAgICAgICB9XG4gICAgfTtcbn0oKTtcblxuLy8gT24gZG9jdW1lbnQgcmVhZHlcbktUVXRpbC5vbkRPTUNvbnRlbnRMb2FkZWQoZnVuY3Rpb24oKSB7XG4gICAgS1RTaWduaW5Ud29GYWN0b3IuaW5pdCgpO1xufSk7Il0sIm5hbWVzIjpbXSwic291cmNlUm9vdCI6IiJ9