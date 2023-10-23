/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
var __webpack_exports__ = {};
/*!*********************************************************!*\
  !*** ./assets/cms/authentication/sign-in/two-factor.js ***!
  \*********************************************************/


// Class Definition
let KTSigninTwoFactor = function() {
    // Elements
    var form;
    var submitButton;

    const lang = localStorage.getItem("kt_auth_lang")??document.getElementsByTagName('html')[0].getAttribute('lang');

    const texts = {
        'en': {
            'login_success': 'You have been successfully verified!',
            'login_error': 'Sorry, please enter valid securtiy code and try again.',
            'ok': 'Ok, got it!'
        },
        'de': {
            'login_success': 'Sie wurden erfolgreich verifiziert!',
            'login_error': 'Entschuldigung, bitte geben Sie einen gültigen Sicherheitscode ein und versuchen Sie es erneut.',
            'ok': 'Ok, verstanden!'
        },
        'fr': {
            'login_success': 'Vous avez été vérifié avec succès !',
            'login_error': 'Désolé, veuillez saisir un code de sécurité valide et réessayer.',
            'ok': 'Ok, compris!'
        },
        'it': {
            'login_success': 'Sei stato verificato con successo!',
            'login_error': 'Spiacente, inserire un codice di sicurezza valido e riprovare.',
            'ok': 'Ok, capito!'
        },
    }

    let translate = function (text) {

        switch (lang) {
            case 'German':
            case 'de':
                return texts['de'][text];
            case 'Italian':
            case 'it':
                return texts['it'][text];
            case 'French':
            case 'fr':
                return texts['fr'][text];
            default:
                return texts['en'][text];

        }
    }

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
                        text: translate('login_success'),
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
                    text: translate('login_error'),
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: translate('ok'),
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidHdvRmFjdG9yU2lnbmluLmpzIiwibWFwcGluZ3MiOiI7Ozs7OztBQUFhOztBQUViO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVDtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVDtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVDtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVDs7QUFFQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFhOztBQUViO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBOztBQUVBLHVDQUF1QyxhQUFhLEdBQUcsZUFBZTtBQUN0RTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHFCQUFxQjtBQUNyQjtBQUNBLHFCQUFxQjtBQUNyQixpQkFBaUI7QUFDakI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHFCQUFxQjtBQUNyQjtBQUNBLHFCQUFxQjtBQUNyQixpQkFBaUI7QUFDakIsY0FBYztBQUNkO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxpQkFBaUI7QUFDakI7QUFDQSxpQkFBaUI7QUFDakI7QUFDQSxTQUFTO0FBQ1Q7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTOztBQUVUO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUzs7QUFFVDtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7O0FBRVQ7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTOztBQUVUO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNUO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTOztBQUVUO0FBQ0E7O0FBRUE7O0FBRUE7O0FBRUE7QUFDQSw2REFBNkQsVUFBVTtBQUN2RTtBQUNBLGFBQWE7QUFDYixTQUFTO0FBQ1Q7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsQ0FBQzs7QUFFRDtBQUNBO0FBQ0E7QUFDQSxDQUFDLEUiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9hc3NldHMvY21zL2F1dGhlbnRpY2F0aW9uL3NpZ24taW4vdHdvLWZhY3Rvci5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyJcInVzZSBzdHJpY3RcIjtcblxuLy8gQ2xhc3MgRGVmaW5pdGlvblxubGV0IEtUU2lnbmluVHdvRmFjdG9yID0gZnVuY3Rpb24oKSB7XG4gICAgLy8gRWxlbWVudHNcbiAgICB2YXIgZm9ybTtcbiAgICB2YXIgc3VibWl0QnV0dG9uO1xuXG4gICAgY29uc3QgbGFuZyA9IGxvY2FsU3RvcmFnZS5nZXRJdGVtKFwia3RfYXV0aF9sYW5nXCIpPz9kb2N1bWVudC5nZXRFbGVtZW50c0J5VGFnTmFtZSgnaHRtbCcpWzBdLmdldEF0dHJpYnV0ZSgnbGFuZycpO1xuXG4gICAgY29uc3QgdGV4dHMgPSB7XG4gICAgICAgICdlbic6IHtcbiAgICAgICAgICAgICdsb2dpbl9zdWNjZXNzJzogJ1lvdSBoYXZlIGJlZW4gc3VjY2Vzc2Z1bGx5IHZlcmlmaWVkIScsXG4gICAgICAgICAgICAnbG9naW5fZXJyb3InOiAnU29ycnksIHBsZWFzZSBlbnRlciB2YWxpZCBzZWN1cnRpeSBjb2RlIGFuZCB0cnkgYWdhaW4uJyxcbiAgICAgICAgICAgICdvayc6ICdPaywgZ290IGl0ISdcbiAgICAgICAgfSxcbiAgICAgICAgJ2RlJzoge1xuICAgICAgICAgICAgJ2xvZ2luX3N1Y2Nlc3MnOiAnU2llIHd1cmRlbiBlcmZvbGdyZWljaCB2ZXJpZml6aWVydCEnLFxuICAgICAgICAgICAgJ2xvZ2luX2Vycm9yJzogJ0VudHNjaHVsZGlndW5nLCBiaXR0ZSBnZWJlbiBTaWUgZWluZW4gZ8O8bHRpZ2VuIFNpY2hlcmhlaXRzY29kZSBlaW4gdW5kIHZlcnN1Y2hlbiBTaWUgZXMgZXJuZXV0LicsXG4gICAgICAgICAgICAnb2snOiAnT2ssIHZlcnN0YW5kZW4hJ1xuICAgICAgICB9LFxuICAgICAgICAnZnInOiB7XG4gICAgICAgICAgICAnbG9naW5fc3VjY2Vzcyc6ICdWb3VzIGF2ZXogw6l0w6kgdsOpcmlmacOpIGF2ZWMgc3VjY8OocyAhJyxcbiAgICAgICAgICAgICdsb2dpbl9lcnJvcic6ICdEw6lzb2zDqSwgdmV1aWxsZXogc2Fpc2lyIHVuIGNvZGUgZGUgc8OpY3VyaXTDqSB2YWxpZGUgZXQgcsOpZXNzYXllci4nLFxuICAgICAgICAgICAgJ29rJzogJ09rLCBjb21wcmlzISdcbiAgICAgICAgfSxcbiAgICAgICAgJ2l0Jzoge1xuICAgICAgICAgICAgJ2xvZ2luX3N1Y2Nlc3MnOiAnU2VpIHN0YXRvIHZlcmlmaWNhdG8gY29uIHN1Y2Nlc3NvIScsXG4gICAgICAgICAgICAnbG9naW5fZXJyb3InOiAnU3BpYWNlbnRlLCBpbnNlcmlyZSB1biBjb2RpY2UgZGkgc2ljdXJlenphIHZhbGlkbyBlIHJpcHJvdmFyZS4nLFxuICAgICAgICAgICAgJ29rJzogJ09rLCBjYXBpdG8hJ1xuICAgICAgICB9LFxuICAgIH1cblxuICAgIGxldCB0cmFuc2xhdGUgPSBmdW5jdGlvbiAodGV4dCkge1xuXG4gICAgICAgIHN3aXRjaCAobGFuZykge1xuICAgICAgICAgICAgY2FzZSAnR2VybWFuJzpcbiAgICAgICAgICAgIGNhc2UgJ2RlJzpcbiAgICAgICAgICAgICAgICByZXR1cm4gdGV4dHNbJ2RlJ11bdGV4dF07XG4gICAgICAgICAgICBjYXNlICdJdGFsaWFuJzpcbiAgICAgICAgICAgIGNhc2UgJ2l0JzpcbiAgICAgICAgICAgICAgICByZXR1cm4gdGV4dHNbJ2l0J11bdGV4dF07XG4gICAgICAgICAgICBjYXNlICdGcmVuY2gnOlxuICAgICAgICAgICAgY2FzZSAnZnInOlxuICAgICAgICAgICAgICAgIHJldHVybiB0ZXh0c1snZnInXVt0ZXh0XTtcbiAgICAgICAgICAgIGRlZmF1bHQ6XG4gICAgICAgICAgICAgICAgcmV0dXJuIHRleHRzWydlbiddW3RleHRdO1xuXG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvLyBIYW5kbGUgZm9ybVxuICAgIHZhciBoYW5kbGVGb3JtID0gZnVuY3Rpb24oZSkgeyAgICAgICAgXG4gICAgICAgIC8vIEhhbmRsZSBmb3JtIHN1Ym1pdFxuICAgICAgICBzdWJtaXRCdXR0b24uYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBmdW5jdGlvbiAoZSkge1xuICAgICAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG4gICAgICAgICAgICBsZXQgdmFsaWRhdGVkID0gdHJ1ZTtcblxuICAgICAgICAgICAgbGV0IGlucHV0cyA9IFtdLnNsaWNlLmNhbGwoZm9ybS5xdWVyeVNlbGVjdG9yQWxsKCdpbnB1dFttYXhsZW5ndGg9XCIxXCJdJykpO1xuICAgICAgICAgICAgaW5wdXRzLm1hcChmdW5jdGlvbiAoaW5wdXQpIHtcbiAgICAgICAgICAgICAgICBpZiAoaW5wdXQudmFsdWUgPT09ICcnIHx8IGlucHV0LnZhbHVlLmxlbmd0aCA9PT0gMCkge1xuICAgICAgICAgICAgICAgICAgICB2YWxpZGF0ZWQgPSBmYWxzZTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KTtcblxuICAgICAgICAgICAgaWYgKHZhbGlkYXRlZCA9PT0gdHJ1ZSkge1xuICAgICAgICAgICAgICAgIC8vIFNob3cgbG9hZGluZyBpbmRpY2F0aW9uXG4gICAgICAgICAgICAgICAgc3VibWl0QnV0dG9uLnNldEF0dHJpYnV0ZSgnZGF0YS1rdC1pbmRpY2F0b3InLCAnb24nKTtcblxuICAgICAgICAgICAgICAgIC8vIERpc2FibGUgYnV0dG9uIHRvIGF2b2lkIG11bHRpcGxlIGNsaWNrIFxuICAgICAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbi5kaXNhYmxlZCA9IHRydWU7XG5cbiAgICAgICAgICAgICAgICBsZXQgY29kZSA9IGlucHV0cy5yZWR1Y2UoKGFjYywgaW5wdXQpID0+IGFjYyArIGlucHV0LnZhbHVlLCAnJylcblxuICAgICAgICAgICAgICAgIGF4aW9zLnBvc3QoJy9hZG1pbicsIHsgX2NvZGU6IGNvZGUgfSwgey4uLmF4aW9zQ29uZmlnfSkudGhlbigocmVzcG9uc2UpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgc3VibWl0QnV0dG9uLmRpc2FibGVkID0gZmFsc2U7XG4gICAgICAgICAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbi5yZW1vdmVBdHRyaWJ1dGUoJ2RhdGEta3QtaW5kaWNhdG9yJyk7XG4gICAgICAgICAgICAgICAgICAgIFN3YWwuZmlyZSh7XG4gICAgICAgICAgICAgICAgICAgICAgICB0ZXh0OiB0cmFuc2xhdGUoJ2xvZ2luX3N1Y2Nlc3MnKSxcbiAgICAgICAgICAgICAgICAgICAgICAgIGljb246IFwic3VjY2Vzc1wiLFxuICAgICAgICAgICAgICAgICAgICAgICAgdGltZXI6IDEwMDAsXG4gICAgICAgICAgICAgICAgICAgICAgICBzaG93Q29uZmlybUJ1dHRvbjogZmFsc2UsXG4gICAgICAgICAgICAgICAgICAgIH0pLnRoZW4oZnVuY3Rpb24gKHJlc3VsdCkge1xuICAgICAgICAgICAgICAgICAgICAgICBsb2NhdGlvbi5ocmVmID0gd2luZG93LmxvY2F0aW9uLmhyZWY7XG4gICAgICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICAgIH0pLmNhdGNoKChlcnJvcikgPT4ge1xuICAgICAgICAgICAgICAgICAgICBzdWJtaXRCdXR0b24uZGlzYWJsZWQgPSBmYWxzZTtcbiAgICAgICAgICAgICAgICAgICAgc3VibWl0QnV0dG9uLnJlbW92ZUF0dHJpYnV0ZSgnZGF0YS1rdC1pbmRpY2F0b3InKTtcbiAgICAgICAgICAgICAgICAgICAgaW5wdXRzLmZvckVhY2goKGlucHV0KSA9PiBpbnB1dC52YWx1ZSA9ICcnKVxuICAgICAgICAgICAgICAgICAgICBzd2FsLmZpcmUoe1xuICAgICAgICAgICAgICAgICAgICAgICAgdGV4dDogXCJQbGVhc2UgZW50ZXIgdmFsaWQgc2VjdXJ0aXkgY29kZSBhbmQgdHJ5IGFnYWluLlwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgaWNvbjogXCJlcnJvclwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgYnV0dG9uc1N0eWxpbmc6IGZhbHNlLFxuICAgICAgICAgICAgICAgICAgICAgICAgY29uZmlybUJ1dHRvblRleHQ6IFwiT2ssIGdvdCBpdCFcIixcbiAgICAgICAgICAgICAgICAgICAgICAgIGN1c3RvbUNsYXNzOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgY29uZmlybUJ1dHRvbjogXCJidG4gZnctYm9sZCBidG4tbGlnaHQtcHJpbWFyeVwiXG4gICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIH0pLnRoZW4oZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBLVFV0aWwuc2Nyb2xsVG9wKCk7XG4gICAgICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICAgIH0pXG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIHN3YWwuZmlyZSh7XG4gICAgICAgICAgICAgICAgICAgIHRleHQ6IHRyYW5zbGF0ZSgnbG9naW5fZXJyb3InKSxcbiAgICAgICAgICAgICAgICAgICAgaWNvbjogXCJlcnJvclwiLFxuICAgICAgICAgICAgICAgICAgICBidXR0b25zU3R5bGluZzogZmFsc2UsXG4gICAgICAgICAgICAgICAgICAgIGNvbmZpcm1CdXR0b25UZXh0OiB0cmFuc2xhdGUoJ29rJyksXG4gICAgICAgICAgICAgICAgICAgIGN1c3RvbUNsYXNzOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICBjb25maXJtQnV0dG9uOiBcImJ0biBmdy1ib2xkIGJ0bi1saWdodC1wcmltYXJ5XCJcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH0pLnRoZW4oZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICAgICAgICAgIEtUVXRpbC5zY3JvbGxUb3AoKTtcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgfVxuXG4gICAgdmFyIGhhbmRsZVR5cGUgPSBmdW5jdGlvbigpIHtcbiAgICAgICAgdmFyIGlucHV0MSA9IGZvcm0ucXVlcnlTZWxlY3RvcihcIltuYW1lPWNvZGVfMV1cIik7XG4gICAgICAgIHZhciBpbnB1dDIgPSBmb3JtLnF1ZXJ5U2VsZWN0b3IoXCJbbmFtZT1jb2RlXzJdXCIpO1xuICAgICAgICB2YXIgaW5wdXQzID0gZm9ybS5xdWVyeVNlbGVjdG9yKFwiW25hbWU9Y29kZV8zXVwiKTtcbiAgICAgICAgdmFyIGlucHV0NCA9IGZvcm0ucXVlcnlTZWxlY3RvcihcIltuYW1lPWNvZGVfNF1cIik7XG4gICAgICAgIHZhciBpbnB1dDUgPSBmb3JtLnF1ZXJ5U2VsZWN0b3IoXCJbbmFtZT1jb2RlXzVdXCIpO1xuICAgICAgICB2YXIgaW5wdXQ2ID0gZm9ybS5xdWVyeVNlbGVjdG9yKFwiW25hbWU9Y29kZV82XVwiKTtcblxuICAgICAgICBpbnB1dDEuZm9jdXMoKTtcblxuICAgICAgICBpbnB1dDEuYWRkRXZlbnRMaXN0ZW5lcihcImtleXVwXCIsIGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgaWYgKHRoaXMudmFsdWUubGVuZ3RoID09PSAxKSB7XG4gICAgICAgICAgICAgICAgaW5wdXQyLmZvY3VzKCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuXG4gICAgICAgIGlucHV0Mi5hZGRFdmVudExpc3RlbmVyKFwia2V5dXBcIiwgZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICBpZiAodGhpcy52YWx1ZS5sZW5ndGggPT09IDEpIHtcbiAgICAgICAgICAgICAgICBpbnB1dDMuZm9jdXMoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG5cbiAgICAgICAgaW5wdXQzLmFkZEV2ZW50TGlzdGVuZXIoXCJrZXl1cFwiLCBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIGlmICh0aGlzLnZhbHVlLmxlbmd0aCA9PT0gMSkge1xuICAgICAgICAgICAgICAgIGlucHV0NC5mb2N1cygpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcblxuICAgICAgICBpbnB1dDQuYWRkRXZlbnRMaXN0ZW5lcihcImtleXVwXCIsIGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgaWYgKHRoaXMudmFsdWUubGVuZ3RoID09PSAxKSB7XG4gICAgICAgICAgICAgICAgaW5wdXQ1LmZvY3VzKCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuXG4gICAgICAgIGlucHV0NS5hZGRFdmVudExpc3RlbmVyKFwia2V5dXBcIiwgZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICBpZiAodGhpcy52YWx1ZS5sZW5ndGggPT09IDEpIHtcbiAgICAgICAgICAgICAgICBpbnB1dDYuZm9jdXMoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgICAgIFxuICAgICAgICBpbnB1dDYuYWRkRXZlbnRMaXN0ZW5lcihcImtleXVwXCIsIGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgaWYgKHRoaXMudmFsdWUubGVuZ3RoID09PSAxKSB7XG4gICAgICAgICAgICAgICAgaW5wdXQ2LmJsdXIoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG5cbiAgICAgICAgZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcihcInBhc3RlXCIsIChldmVudCkgPT4ge1xuICAgICAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcblxuICAgICAgICAgICAgbGV0IHBhc3RlID0gKGV2ZW50LmNsaXBib2FyZERhdGEgfHwgd2luZG93LmNsaXBib2FyZERhdGEpLmdldERhdGEoXCJ0ZXh0XCIpO1xuXG4gICAgICAgICAgICBsZXQgY29kZSA9IHBhc3RlLnNwbGl0KCcnKS5zbGljZSgwLCA2KTtcblxuICAgICAgICAgICAgY29kZS5mb3JFYWNoKCh2YWx1ZSwgaW5kZXgpID0+IHtcbiAgICAgICAgICAgICAgICBsZXQgaW5wdXQgPSBmb3JtLnF1ZXJ5U2VsZWN0b3IoYFtuYW1lPWNvZGVfJHtpbmRleCArIDF9XWApO1xuICAgICAgICAgICAgICAgIGlucHV0LnZhbHVlID0gdmFsdWU7XG4gICAgICAgICAgICB9KVxuICAgICAgICB9KVxuICAgIH0gICAgXG5cbiAgICAvLyBQdWJsaWMgZnVuY3Rpb25zXG4gICAgcmV0dXJuIHtcbiAgICAgICAgLy8gSW5pdGlhbGl6YXRpb25cbiAgICAgICAgaW5pdDogZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICBmb3JtID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI2t0X3NpbmdfaW5fdHdvX2ZhY3Rvcl9mb3JtJyk7XG4gICAgICAgICAgICBzdWJtaXRCdXR0b24gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcja3Rfc2luZ19pbl90d29fZmFjdG9yX3N1Ym1pdCcpO1xuXG4gICAgICAgICAgICBoYW5kbGVGb3JtKCk7XG4gICAgICAgICAgICBoYW5kbGVUeXBlKCk7XG4gICAgICAgIH1cbiAgICB9O1xufSgpO1xuXG4vLyBPbiBkb2N1bWVudCByZWFkeVxuS1RVdGlsLm9uRE9NQ29udGVudExvYWRlZChmdW5jdGlvbigpIHtcbiAgICBLVFNpZ25pblR3b0ZhY3Rvci5pbml0KCk7XG59KTsiXSwibmFtZXMiOltdLCJzb3VyY2VSb290IjoiIn0=