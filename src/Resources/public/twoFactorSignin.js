/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/*!*********************************************************!*\
  !*** ./assets/cms/authentication/sign-in/two-factor.js ***!
  \*********************************************************/



// Class Definition
let KTSigninTwoFactor = function() {
    // Elements
    let form;
    let submitButton;
    let requestCodeLink

    const lang = localStorage.getItem("kt_auth_lang")??document.getElementsByTagName('html')[0].getAttribute('lang');

    const texts = {
        'en': {
            'login_success': 'You have been successfully verified!',
            'login_error': 'Sorry, please enter valid securtiy code and try again.',
            'ok': 'Ok, got it!',
            'request_code_sent': 'Security code has been sent to your email address.',
            'request_code_not_sent': 'Security code could not be sent to your email address. Please try again later.'
        },
        'de': {
            'login_success': 'Sie wurden erfolgreich verifiziert!',
            'login_error': 'Entschuldigung, bitte geben Sie einen gültigen Sicherheitscode ein und versuchen Sie es erneut.',
            'ok': 'Ok, verstanden!',
            'request_code_sent': 'Sicherheitscode wurde an Ihre E-Mail-Adresse gesendet.',
            'request_code_not_sent': 'Sicherheitscode konnte nicht an Ihre E-Mail-Adresse gesendet werden. Bitte versuchen Sie es später erneut.'
        },
        'fr': {
            'login_success': 'Vous avez été vérifié avec succès !',
            'login_error': 'Désolé, veuillez saisir un code de sécurité valide et réessayer.',
            'ok': 'Ok, compris!',
            'request_code_sent': 'Le code de sécurité a été envoyé à votre adresse e-mail.',
            'request_code_not_sent': 'Le code de sécurité n\'a pas pu être envoyé à votre adresse e-mail. Veuillez réessayer plus tard.'
        },
        'it': {
            'login_success': 'Sei stato verificato con successo!',
            'login_error': 'Spiacente, inserire un codice di sicurezza valido e riprolete.',
            'ok': 'Ok, capito!',
            'request_code_sent': 'Il codice di sicurezza è stato inviato al tuo indirizzo email.',
            'request_code_not_sent': 'Il codice di sicurezza non è stato inviato al tuo indirizzo email. Riprova più tardi.'
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
    let handleForm = async function(e) {
        // Handle form submit
        submitButton.addEventListener('click', function (e) {
            e.preventDefault();

            let validated = true;

            let inputs = [].slice.call(form.querySelectorAll('[data-inputmask]'));
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

                axios.post(CMSRouting.generate('networking_init_cms_admin_no_slash'), { _code: code }, {...axiosConfig}).then((response) => {
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

    let handleType = function() {
        let input1 = form.querySelector("[name=code_1]");
        let input2 = form.querySelector("[name=code_2]");
        let input3 = form.querySelector("[name=code_3]");
        let input4 = form.querySelector("[name=code_4]");
        let input5 = form.querySelector("[name=code_5]");
        let input6 = form.querySelector("[name=code_6]");

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

    let handleSendLink = function(){
        requestCodeLink.addEventListener('click', function (e) {
            e.preventDefault()
            let link = e.target;

            axios.get(link.attributes.href.value, {...axiosConfig}).then((response) => {
                Swal.fire({
                    text: translate('request_code_sent'),
                    icon: "success",
                    timer: 2000,
                    showConfirmButton: false,
                })
            }).catch((error) => {
                let message = translate('request_code_not_sent')
                if(error.response.data && error.response.data.message){
                    message = error.response.data.message;
                }
                Swal.fire({
                    text: message,
                    icon: "error",
                    timer: 4000,
                    showConfirmButton: false,
                })
            })
        })
    }

    // Public functions
    return {
        // Initialization
        init: function() {
            form = document.querySelector('#kt_sign_in_two_factor_form');
            submitButton = document.querySelector('#kt_sign_in_two_factor_submit');
            requestCodeLink = document.querySelector('#kt_sign_in_two_factor_request_code');

            handleForm();
            handleType();
            handleSendLink();
        }
    };
}();

// On document ready
document.addEventListener('DOMContentLoaded', () => {
    KTSigninTwoFactor.init();
});
/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidHdvRmFjdG9yU2lnbmluLmpzIiwibWFwcGluZ3MiOiI7Ozs7O0FBQWE7OztBQUdiO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNUO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVDs7QUFFQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFhOztBQUViO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBOztBQUVBLHdGQUF3RixhQUFhLEdBQUcsZUFBZTtBQUN2SDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHFCQUFxQjtBQUNyQjtBQUNBLHFCQUFxQjtBQUNyQixpQkFBaUI7QUFDakI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHFCQUFxQjtBQUNyQjtBQUNBLHFCQUFxQjtBQUNyQixpQkFBaUI7QUFDakIsY0FBYztBQUNkO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxpQkFBaUI7QUFDakI7QUFDQSxpQkFBaUI7QUFDakI7QUFDQSxTQUFTO0FBQ1Q7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTOztBQUVUO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUzs7QUFFVDtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7O0FBRVQ7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTOztBQUVUO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNUO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTOztBQUVUO0FBQ0E7O0FBRUE7O0FBRUE7O0FBRUE7QUFDQSw2REFBNkQsVUFBVTtBQUN2RTtBQUNBLGFBQWE7QUFDYixTQUFTO0FBQ1Q7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7O0FBRUEsbURBQW1ELGVBQWU7QUFDbEU7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGlCQUFpQjtBQUNqQixhQUFhO0FBQ2I7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsaUJBQWlCO0FBQ2pCLGFBQWE7QUFDYixTQUFTO0FBQ1Q7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLENBQUM7O0FBRUQ7QUFDQTtBQUNBO0FBQ0EsQ0FBQyxFIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vYXNzZXRzL2Ntcy9hdXRoZW50aWNhdGlvbi9zaWduLWluL3R3by1mYWN0b3IuanMiXSwic291cmNlc0NvbnRlbnQiOlsiXCJ1c2Ugc3RyaWN0XCI7XG5cblxuLy8gQ2xhc3MgRGVmaW5pdGlvblxubGV0IEtUU2lnbmluVHdvRmFjdG9yID0gZnVuY3Rpb24oKSB7XG4gICAgLy8gRWxlbWVudHNcbiAgICBsZXQgZm9ybTtcbiAgICBsZXQgc3VibWl0QnV0dG9uO1xuICAgIGxldCByZXF1ZXN0Q29kZUxpbmtcblxuICAgIGNvbnN0IGxhbmcgPSBsb2NhbFN0b3JhZ2UuZ2V0SXRlbShcImt0X2F1dGhfbGFuZ1wiKT8/ZG9jdW1lbnQuZ2V0RWxlbWVudHNCeVRhZ05hbWUoJ2h0bWwnKVswXS5nZXRBdHRyaWJ1dGUoJ2xhbmcnKTtcblxuICAgIGNvbnN0IHRleHRzID0ge1xuICAgICAgICAnZW4nOiB7XG4gICAgICAgICAgICAnbG9naW5fc3VjY2Vzcyc6ICdZb3UgaGF2ZSBiZWVuIHN1Y2Nlc3NmdWxseSB2ZXJpZmllZCEnLFxuICAgICAgICAgICAgJ2xvZ2luX2Vycm9yJzogJ1NvcnJ5LCBwbGVhc2UgZW50ZXIgdmFsaWQgc2VjdXJ0aXkgY29kZSBhbmQgdHJ5IGFnYWluLicsXG4gICAgICAgICAgICAnb2snOiAnT2ssIGdvdCBpdCEnLFxuICAgICAgICAgICAgJ3JlcXVlc3RfY29kZV9zZW50JzogJ1NlY3VyaXR5IGNvZGUgaGFzIGJlZW4gc2VudCB0byB5b3VyIGVtYWlsIGFkZHJlc3MuJyxcbiAgICAgICAgICAgICdyZXF1ZXN0X2NvZGVfbm90X3NlbnQnOiAnU2VjdXJpdHkgY29kZSBjb3VsZCBub3QgYmUgc2VudCB0byB5b3VyIGVtYWlsIGFkZHJlc3MuIFBsZWFzZSB0cnkgYWdhaW4gbGF0ZXIuJ1xuICAgICAgICB9LFxuICAgICAgICAnZGUnOiB7XG4gICAgICAgICAgICAnbG9naW5fc3VjY2Vzcyc6ICdTaWUgd3VyZGVuIGVyZm9sZ3JlaWNoIHZlcmlmaXppZXJ0IScsXG4gICAgICAgICAgICAnbG9naW5fZXJyb3InOiAnRW50c2NodWxkaWd1bmcsIGJpdHRlIGdlYmVuIFNpZSBlaW5lbiBnw7xsdGlnZW4gU2ljaGVyaGVpdHNjb2RlIGVpbiB1bmQgdmVyc3VjaGVuIFNpZSBlcyBlcm5ldXQuJyxcbiAgICAgICAgICAgICdvayc6ICdPaywgdmVyc3RhbmRlbiEnLFxuICAgICAgICAgICAgJ3JlcXVlc3RfY29kZV9zZW50JzogJ1NpY2hlcmhlaXRzY29kZSB3dXJkZSBhbiBJaHJlIEUtTWFpbC1BZHJlc3NlIGdlc2VuZGV0LicsXG4gICAgICAgICAgICAncmVxdWVzdF9jb2RlX25vdF9zZW50JzogJ1NpY2hlcmhlaXRzY29kZSBrb25udGUgbmljaHQgYW4gSWhyZSBFLU1haWwtQWRyZXNzZSBnZXNlbmRldCB3ZXJkZW4uIEJpdHRlIHZlcnN1Y2hlbiBTaWUgZXMgc3DDpHRlciBlcm5ldXQuJ1xuICAgICAgICB9LFxuICAgICAgICAnZnInOiB7XG4gICAgICAgICAgICAnbG9naW5fc3VjY2Vzcyc6ICdWb3VzIGF2ZXogw6l0w6kgdsOpcmlmacOpIGF2ZWMgc3VjY8OocyAhJyxcbiAgICAgICAgICAgICdsb2dpbl9lcnJvcic6ICdEw6lzb2zDqSwgdmV1aWxsZXogc2Fpc2lyIHVuIGNvZGUgZGUgc8OpY3VyaXTDqSB2YWxpZGUgZXQgcsOpZXNzYXllci4nLFxuICAgICAgICAgICAgJ29rJzogJ09rLCBjb21wcmlzIScsXG4gICAgICAgICAgICAncmVxdWVzdF9jb2RlX3NlbnQnOiAnTGUgY29kZSBkZSBzw6ljdXJpdMOpIGEgw6l0w6kgZW52b3nDqSDDoCB2b3RyZSBhZHJlc3NlIGUtbWFpbC4nLFxuICAgICAgICAgICAgJ3JlcXVlc3RfY29kZV9ub3Rfc2VudCc6ICdMZSBjb2RlIGRlIHPDqWN1cml0w6kgblxcJ2EgcGFzIHB1IMOqdHJlIGVudm95w6kgw6Agdm90cmUgYWRyZXNzZSBlLW1haWwuIFZldWlsbGV6IHLDqWVzc2F5ZXIgcGx1cyB0YXJkLidcbiAgICAgICAgfSxcbiAgICAgICAgJ2l0Jzoge1xuICAgICAgICAgICAgJ2xvZ2luX3N1Y2Nlc3MnOiAnU2VpIHN0YXRvIHZlcmlmaWNhdG8gY29uIHN1Y2Nlc3NvIScsXG4gICAgICAgICAgICAnbG9naW5fZXJyb3InOiAnU3BpYWNlbnRlLCBpbnNlcmlyZSB1biBjb2RpY2UgZGkgc2ljdXJlenphIHZhbGlkbyBlIHJpcHJvbGV0ZS4nLFxuICAgICAgICAgICAgJ29rJzogJ09rLCBjYXBpdG8hJyxcbiAgICAgICAgICAgICdyZXF1ZXN0X2NvZGVfc2VudCc6ICdJbCBjb2RpY2UgZGkgc2ljdXJlenphIMOoIHN0YXRvIGludmlhdG8gYWwgdHVvIGluZGlyaXp6byBlbWFpbC4nLFxuICAgICAgICAgICAgJ3JlcXVlc3RfY29kZV9ub3Rfc2VudCc6ICdJbCBjb2RpY2UgZGkgc2ljdXJlenphIG5vbiDDqCBzdGF0byBpbnZpYXRvIGFsIHR1byBpbmRpcml6em8gZW1haWwuIFJpcHJvdmEgcGnDuSB0YXJkaS4nXG4gICAgICAgIH0sXG4gICAgfVxuXG4gICAgbGV0IHRyYW5zbGF0ZSA9IGZ1bmN0aW9uICh0ZXh0KSB7XG5cbiAgICAgICAgc3dpdGNoIChsYW5nKSB7XG4gICAgICAgICAgICBjYXNlICdHZXJtYW4nOlxuICAgICAgICAgICAgY2FzZSAnZGUnOlxuICAgICAgICAgICAgICAgIHJldHVybiB0ZXh0c1snZGUnXVt0ZXh0XTtcbiAgICAgICAgICAgIGNhc2UgJ0l0YWxpYW4nOlxuICAgICAgICAgICAgY2FzZSAnaXQnOlxuICAgICAgICAgICAgICAgIHJldHVybiB0ZXh0c1snaXQnXVt0ZXh0XTtcbiAgICAgICAgICAgIGNhc2UgJ0ZyZW5jaCc6XG4gICAgICAgICAgICBjYXNlICdmcic6XG4gICAgICAgICAgICAgICAgcmV0dXJuIHRleHRzWydmciddW3RleHRdO1xuICAgICAgICAgICAgZGVmYXVsdDpcbiAgICAgICAgICAgICAgICByZXR1cm4gdGV4dHNbJ2VuJ11bdGV4dF07XG5cbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8vIEhhbmRsZSBmb3JtXG4gICAgbGV0IGhhbmRsZUZvcm0gPSBhc3luYyBmdW5jdGlvbihlKSB7XG4gICAgICAgIC8vIEhhbmRsZSBmb3JtIHN1Ym1pdFxuICAgICAgICBzdWJtaXRCdXR0b24uYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBmdW5jdGlvbiAoZSkge1xuICAgICAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG4gICAgICAgICAgICBsZXQgdmFsaWRhdGVkID0gdHJ1ZTtcblxuICAgICAgICAgICAgbGV0IGlucHV0cyA9IFtdLnNsaWNlLmNhbGwoZm9ybS5xdWVyeVNlbGVjdG9yQWxsKCdbZGF0YS1pbnB1dG1hc2tdJykpO1xuICAgICAgICAgICAgaW5wdXRzLm1hcChmdW5jdGlvbiAoaW5wdXQpIHtcbiAgICAgICAgICAgICAgICBpZiAoaW5wdXQudmFsdWUgPT09ICcnIHx8IGlucHV0LnZhbHVlLmxlbmd0aCA9PT0gMCkge1xuICAgICAgICAgICAgICAgICAgICB2YWxpZGF0ZWQgPSBmYWxzZTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KTtcblxuICAgICAgICAgICAgaWYgKHZhbGlkYXRlZCA9PT0gdHJ1ZSkge1xuICAgICAgICAgICAgICAgIC8vIFNob3cgbG9hZGluZyBpbmRpY2F0aW9uXG4gICAgICAgICAgICAgICAgc3VibWl0QnV0dG9uLnNldEF0dHJpYnV0ZSgnZGF0YS1rdC1pbmRpY2F0b3InLCAnb24nKTtcblxuICAgICAgICAgICAgICAgIC8vIERpc2FibGUgYnV0dG9uIHRvIGF2b2lkIG11bHRpcGxlIGNsaWNrIFxuICAgICAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbi5kaXNhYmxlZCA9IHRydWU7XG5cbiAgICAgICAgICAgICAgICBsZXQgY29kZSA9IGlucHV0cy5yZWR1Y2UoKGFjYywgaW5wdXQpID0+IGFjYyArIGlucHV0LnZhbHVlLCAnJylcblxuICAgICAgICAgICAgICAgIGF4aW9zLnBvc3QoQ01TUm91dGluZy5nZW5lcmF0ZSgnbmV0d29ya2luZ19pbml0X2Ntc19hZG1pbl9ub19zbGFzaCcpLCB7IF9jb2RlOiBjb2RlIH0sIHsuLi5heGlvc0NvbmZpZ30pLnRoZW4oKHJlc3BvbnNlKSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbi5kaXNhYmxlZCA9IGZhbHNlO1xuICAgICAgICAgICAgICAgICAgICBzdWJtaXRCdXR0b24ucmVtb3ZlQXR0cmlidXRlKCdkYXRhLWt0LWluZGljYXRvcicpO1xuICAgICAgICAgICAgICAgICAgICBTd2FsLmZpcmUoe1xuICAgICAgICAgICAgICAgICAgICAgICAgdGV4dDogdHJhbnNsYXRlKCdsb2dpbl9zdWNjZXNzJyksXG4gICAgICAgICAgICAgICAgICAgICAgICBpY29uOiBcInN1Y2Nlc3NcIixcbiAgICAgICAgICAgICAgICAgICAgICAgIHRpbWVyOiAxMDAwLFxuICAgICAgICAgICAgICAgICAgICAgICAgc2hvd0NvbmZpcm1CdXR0b246IGZhbHNlLFxuICAgICAgICAgICAgICAgICAgICB9KS50aGVuKGZ1bmN0aW9uIChyZXN1bHQpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgbG9jYXRpb24uaHJlZiA9IHdpbmRvdy5sb2NhdGlvbi5ocmVmO1xuICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICB9KS5jYXRjaCgoZXJyb3IpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgc3VibWl0QnV0dG9uLmRpc2FibGVkID0gZmFsc2U7XG4gICAgICAgICAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbi5yZW1vdmVBdHRyaWJ1dGUoJ2RhdGEta3QtaW5kaWNhdG9yJyk7XG4gICAgICAgICAgICAgICAgICAgIGlucHV0cy5mb3JFYWNoKChpbnB1dCkgPT4gaW5wdXQudmFsdWUgPSAnJylcbiAgICAgICAgICAgICAgICAgICAgc3dhbC5maXJlKHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHRleHQ6IFwiUGxlYXNlIGVudGVyIHZhbGlkIHNlY3VydGl5IGNvZGUgYW5kIHRyeSBhZ2Fpbi5cIixcbiAgICAgICAgICAgICAgICAgICAgICAgIGljb246IFwiZXJyb3JcIixcbiAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbnNTdHlsaW5nOiBmYWxzZSxcbiAgICAgICAgICAgICAgICAgICAgICAgIGNvbmZpcm1CdXR0b25UZXh0OiBcIk9rLCBnb3QgaXQhXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICBjdXN0b21DbGFzczoge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNvbmZpcm1CdXR0b246IFwiYnRuIGZ3LWJvbGQgYnRuLWxpZ2h0LXByaW1hcnlcIlxuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICB9KS50aGVuKGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgS1RVdGlsLnNjcm9sbFRvcCgpO1xuICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICB9KVxuICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICBzd2FsLmZpcmUoe1xuICAgICAgICAgICAgICAgICAgICB0ZXh0OiB0cmFuc2xhdGUoJ2xvZ2luX2Vycm9yJyksXG4gICAgICAgICAgICAgICAgICAgIGljb246IFwiZXJyb3JcIixcbiAgICAgICAgICAgICAgICAgICAgYnV0dG9uc1N0eWxpbmc6IGZhbHNlLFxuICAgICAgICAgICAgICAgICAgICBjb25maXJtQnV0dG9uVGV4dDogdHJhbnNsYXRlKCdvaycpLFxuICAgICAgICAgICAgICAgICAgICBjdXN0b21DbGFzczoge1xuICAgICAgICAgICAgICAgICAgICAgICAgY29uZmlybUJ1dHRvbjogXCJidG4gZnctYm9sZCBidG4tbGlnaHQtcHJpbWFyeVwiXG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB9KS50aGVuKGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICBLVFV0aWwuc2Nyb2xsVG9wKCk7XG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgIH1cblxuICAgIGxldCBoYW5kbGVUeXBlID0gZnVuY3Rpb24oKSB7XG4gICAgICAgIGxldCBpbnB1dDEgPSBmb3JtLnF1ZXJ5U2VsZWN0b3IoXCJbbmFtZT1jb2RlXzFdXCIpO1xuICAgICAgICBsZXQgaW5wdXQyID0gZm9ybS5xdWVyeVNlbGVjdG9yKFwiW25hbWU9Y29kZV8yXVwiKTtcbiAgICAgICAgbGV0IGlucHV0MyA9IGZvcm0ucXVlcnlTZWxlY3RvcihcIltuYW1lPWNvZGVfM11cIik7XG4gICAgICAgIGxldCBpbnB1dDQgPSBmb3JtLnF1ZXJ5U2VsZWN0b3IoXCJbbmFtZT1jb2RlXzRdXCIpO1xuICAgICAgICBsZXQgaW5wdXQ1ID0gZm9ybS5xdWVyeVNlbGVjdG9yKFwiW25hbWU9Y29kZV81XVwiKTtcbiAgICAgICAgbGV0IGlucHV0NiA9IGZvcm0ucXVlcnlTZWxlY3RvcihcIltuYW1lPWNvZGVfNl1cIik7XG5cbiAgICAgICAgaW5wdXQxLmZvY3VzKCk7XG5cbiAgICAgICAgaW5wdXQxLmFkZEV2ZW50TGlzdGVuZXIoXCJrZXl1cFwiLCBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIGlmICh0aGlzLnZhbHVlLmxlbmd0aCA9PT0gMSkge1xuICAgICAgICAgICAgICAgIGlucHV0Mi5mb2N1cygpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcblxuICAgICAgICBpbnB1dDIuYWRkRXZlbnRMaXN0ZW5lcihcImtleXVwXCIsIGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgaWYgKHRoaXMudmFsdWUubGVuZ3RoID09PSAxKSB7XG4gICAgICAgICAgICAgICAgaW5wdXQzLmZvY3VzKCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuXG4gICAgICAgIGlucHV0My5hZGRFdmVudExpc3RlbmVyKFwia2V5dXBcIiwgZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICBpZiAodGhpcy52YWx1ZS5sZW5ndGggPT09IDEpIHtcbiAgICAgICAgICAgICAgICBpbnB1dDQuZm9jdXMoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG5cbiAgICAgICAgaW5wdXQ0LmFkZEV2ZW50TGlzdGVuZXIoXCJrZXl1cFwiLCBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIGlmICh0aGlzLnZhbHVlLmxlbmd0aCA9PT0gMSkge1xuICAgICAgICAgICAgICAgIGlucHV0NS5mb2N1cygpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcblxuICAgICAgICBpbnB1dDUuYWRkRXZlbnRMaXN0ZW5lcihcImtleXVwXCIsIGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgaWYgKHRoaXMudmFsdWUubGVuZ3RoID09PSAxKSB7XG4gICAgICAgICAgICAgICAgaW5wdXQ2LmZvY3VzKCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgICAgICBcbiAgICAgICAgaW5wdXQ2LmFkZEV2ZW50TGlzdGVuZXIoXCJrZXl1cFwiLCBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIGlmICh0aGlzLnZhbHVlLmxlbmd0aCA9PT0gMSkge1xuICAgICAgICAgICAgICAgIGlucHV0Ni5ibHVyKCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuXG4gICAgICAgIGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoXCJwYXN0ZVwiLCAoZXZlbnQpID0+IHtcbiAgICAgICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG5cbiAgICAgICAgICAgIGxldCBwYXN0ZSA9IChldmVudC5jbGlwYm9hcmREYXRhIHx8IHdpbmRvdy5jbGlwYm9hcmREYXRhKS5nZXREYXRhKFwidGV4dFwiKTtcblxuICAgICAgICAgICAgbGV0IGNvZGUgPSBwYXN0ZS5zcGxpdCgnJykuc2xpY2UoMCwgNik7XG5cbiAgICAgICAgICAgIGNvZGUuZm9yRWFjaCgodmFsdWUsIGluZGV4KSA9PiB7XG4gICAgICAgICAgICAgICAgbGV0IGlucHV0ID0gZm9ybS5xdWVyeVNlbGVjdG9yKGBbbmFtZT1jb2RlXyR7aW5kZXggKyAxfV1gKTtcbiAgICAgICAgICAgICAgICBpbnB1dC52YWx1ZSA9IHZhbHVlO1xuICAgICAgICAgICAgfSlcbiAgICAgICAgfSlcbiAgICB9XG5cbiAgICBsZXQgaGFuZGxlU2VuZExpbmsgPSBmdW5jdGlvbigpe1xuICAgICAgICByZXF1ZXN0Q29kZUxpbmsuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBmdW5jdGlvbiAoZSkge1xuICAgICAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpXG4gICAgICAgICAgICBsZXQgbGluayA9IGUudGFyZ2V0O1xuXG4gICAgICAgICAgICBheGlvcy5nZXQobGluay5hdHRyaWJ1dGVzLmhyZWYudmFsdWUsIHsuLi5heGlvc0NvbmZpZ30pLnRoZW4oKHJlc3BvbnNlKSA9PiB7XG4gICAgICAgICAgICAgICAgU3dhbC5maXJlKHtcbiAgICAgICAgICAgICAgICAgICAgdGV4dDogdHJhbnNsYXRlKCdyZXF1ZXN0X2NvZGVfc2VudCcpLFxuICAgICAgICAgICAgICAgICAgICBpY29uOiBcInN1Y2Nlc3NcIixcbiAgICAgICAgICAgICAgICAgICAgdGltZXI6IDIwMDAsXG4gICAgICAgICAgICAgICAgICAgIHNob3dDb25maXJtQnV0dG9uOiBmYWxzZSxcbiAgICAgICAgICAgICAgICB9KVxuICAgICAgICAgICAgfSkuY2F0Y2goKGVycm9yKSA9PiB7XG4gICAgICAgICAgICAgICAgbGV0IG1lc3NhZ2UgPSB0cmFuc2xhdGUoJ3JlcXVlc3RfY29kZV9ub3Rfc2VudCcpXG4gICAgICAgICAgICAgICAgaWYoZXJyb3IucmVzcG9uc2UuZGF0YSAmJiBlcnJvci5yZXNwb25zZS5kYXRhLm1lc3NhZ2Upe1xuICAgICAgICAgICAgICAgICAgICBtZXNzYWdlID0gZXJyb3IucmVzcG9uc2UuZGF0YS5tZXNzYWdlO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICBTd2FsLmZpcmUoe1xuICAgICAgICAgICAgICAgICAgICB0ZXh0OiBtZXNzYWdlLFxuICAgICAgICAgICAgICAgICAgICBpY29uOiBcImVycm9yXCIsXG4gICAgICAgICAgICAgICAgICAgIHRpbWVyOiA0MDAwLFxuICAgICAgICAgICAgICAgICAgICBzaG93Q29uZmlybUJ1dHRvbjogZmFsc2UsXG4gICAgICAgICAgICAgICAgfSlcbiAgICAgICAgICAgIH0pXG4gICAgICAgIH0pXG4gICAgfVxuXG4gICAgLy8gUHVibGljIGZ1bmN0aW9uc1xuICAgIHJldHVybiB7XG4gICAgICAgIC8vIEluaXRpYWxpemF0aW9uXG4gICAgICAgIGluaXQ6IGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgZm9ybSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJyNrdF9zaWduX2luX3R3b19mYWN0b3JfZm9ybScpO1xuICAgICAgICAgICAgc3VibWl0QnV0dG9uID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI2t0X3NpZ25faW5fdHdvX2ZhY3Rvcl9zdWJtaXQnKTtcbiAgICAgICAgICAgIHJlcXVlc3RDb2RlTGluayA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJyNrdF9zaWduX2luX3R3b19mYWN0b3JfcmVxdWVzdF9jb2RlJyk7XG5cbiAgICAgICAgICAgIGhhbmRsZUZvcm0oKTtcbiAgICAgICAgICAgIGhhbmRsZVR5cGUoKTtcbiAgICAgICAgICAgIGhhbmRsZVNlbmRMaW5rKCk7XG4gICAgICAgIH1cbiAgICB9O1xufSgpO1xuXG4vLyBPbiBkb2N1bWVudCByZWFkeVxuZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsICgpID0+IHtcbiAgICBLVFNpZ25pblR3b0ZhY3Rvci5pbml0KCk7XG59KTsiXSwibmFtZXMiOltdLCJzb3VyY2VSb290IjoiIn0=