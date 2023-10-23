/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
var __webpack_exports__ = {};
/*!******************************************************!*\
  !*** ./assets/cms/authentication/sign-in/general.js ***!
  \******************************************************/



// Class definition
let KTSigninGeneral = function () {
    // Elements
    let form;
    let submitButton;
    let validator;

    const lang = localStorage.getItem("kt_auth_lang")??document.getElementsByTagName('html')[0].getAttribute('lang');

    const texts = {
        'en': {
            'email_invalid': 'The value is not a valid email address',
            'email_empty': 'Email address is required',
            'password_empty': 'The password is required',
            'login_success': 'You have successfully logged in!',
            'login_error': 'Sorry, looks like there are some errors detected, please try again.',
            'ok': 'Ok, got it!'
        },
        'de': {
            'email_invalid': 'Der Wert ist keine gültige E-Mail-Adresse',
            'email_empty': 'E-Mail-Adresse ist erforderlich',
            'password_empty': 'Das Passwort ist erforderlich',
            'login_success': 'Sie haben sich erfolgreich angemeldet!',
            'login_error': 'Entschuldigung, es scheint, dass einige Fehler erkannt wurden, bitte versuchen Sie es erneut.',
            'ok': 'Ok, verstanden!'
        },
        'fr': {
            'email_invalid': 'La valeur n\'est pas une adresse e-mail valide',
            'email_empty': 'L\'adresse e-mail est requise',
            'password_empty': 'Le mot de passe est requis',
            'login_success': 'Vous vous êtes connecté avec succès!',
            'login_error': 'Désolé, il semble qu\'il y ait des erreurs détectées, veuillez réessayer.',
            'ok': 'Ok, compris!'
        },
        'it': {
            'email_invalid': 'Il valore non è un indirizzo e-mail valido',
            'email_empty': 'L\'indirizzo e-mail è richiesto',
            'password_empty': 'La password è richiesta',
            'login_success': 'L\'accesso è stato effettuato con successo!',
            'login_error': 'Spiacente, sembra che siano stati rilevati degli errori, si prega di riprovare.',
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
    let handleValidation = function (e) {
        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    '_username': {
                        validators: {
                            regexp: {
                                regexp: /^[^\s@]+@[^\s@]+\.[^\s@]+$|^sysadmin$/,
                                message: translate('email_invalid'),
                            },
                            notEmpty: {
                                message: translate('email_empty')
                            }
                        }
                    },
                    '_password': {
                        validators: {
                            notEmpty: {
                                message: translate('password_empty')
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',  // comment to enable invalid state icons
                        eleValidClass: '' // comment to enable valid state icons
                    })
                }
            }
        );
    }


    let handleSubmitAjax = async function (e) {
        let CMSRouting = await CMSAdmin.getRouting();
        // Handle form submit
        submitButton.addEventListener('click', function (e) {
            // Prevent button default action
            e.preventDefault();

            // Validate form
            validator.validate().then(function (status) {
                if (status === 'Valid') {
                    // Show loading indication
                    submitButton.setAttribute('data-kt-indicator', 'on');

                    // Disable button to avoid multiple click
                    submitButton.disabled = true;
                    // form.submit();
                    // return;

                    let formData = new FormData(form);

                    let loginInfo = {}
                    formData.forEach( (value, key) => {
                        loginInfo[key] = value;
                    })

                    axios.post(CMSRouting.generate('cms_api_login'), loginInfo, {...axiosConfig})
                        .then((response) => {
                            submitButton.disabled = false;
                            submitButton.removeAttribute('data-kt-indicator');
                            let redirect = response.data.redirect;
                            Swal.fire({
                                    text: translate('login_success'),
                                    type: "success",
                                    icon: "success",
                                    timer: 1000,
                                    showConfirmButton: false,
                                }).then(() => {
                                    let host =  window.location.protocol + '//' + window.location.host
                                    if(redirect.indexOf(host) === -1 || redirect.indexOf('http') === -1) {
                                        redirect = host + redirect
                                    }
                                    location.href = redirect
                                });
                        })
                        .catch((error) => {
                            submitButton.disabled = false;
                            submitButton.removeAttribute('data-kt-indicator');
                            let data = error.response.data

                            Swal.fire({
                                text: data.error,
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: translate('ok'),
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                            submitButton.disabled = false;
                        })

                } else {
                    // Show error popup. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                    Swal.fire({
                        text: translate('login_error'),
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: translate('ok'),
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                }
            });
        });
    }

    let isValidUrl = function(url) {
        try {
            new URL(url);
            return true;
        } catch (e) {
            return false;
        }
    }

    // Public functions
    return {
        // Initialization
        init: function () {
            form = document.querySelector('#kt_sign_in_form');
            submitButton = document.querySelector('#kt_sign_in_submit');

            handleValidation();

            if (isValidUrl(submitButton.closest('form').getAttribute('action'))) {
                handleSubmitAjax(); // use for ajax submit
            }
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTSigninGeneral.init();
});

/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZ2VuZXJhbFNpZ25pbi5qcyIsIm1hcHBpbmdzIjoiOzs7Ozs7QUFBYTs7O0FBR2I7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7O0FBRUE7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsNkJBQTZCO0FBQzdCO0FBQ0E7QUFDQTtBQUNBO0FBQ0EscUJBQXFCO0FBQ3JCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsaUJBQWlCO0FBQ2pCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHFCQUFxQjtBQUNyQjtBQUNBO0FBQ0E7QUFDQTs7O0FBR0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7O0FBRUE7QUFDQTtBQUNBO0FBQ0EscUJBQXFCOztBQUVyQixpRkFBaUYsZUFBZTtBQUNoRztBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGlDQUFpQztBQUNqQztBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsaUNBQWlDO0FBQ2pDLHlCQUF5QjtBQUN6QjtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsNkJBQTZCO0FBQzdCO0FBQ0EseUJBQXlCOztBQUV6QixrQkFBa0I7QUFDbEI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EscUJBQXFCO0FBQ3JCO0FBQ0EsYUFBYTtBQUNiLFNBQVM7QUFDVDs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFVBQVU7QUFDVjtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBOztBQUVBO0FBQ0Esb0NBQW9DO0FBQ3BDO0FBQ0E7QUFDQTtBQUNBLENBQUM7O0FBRUQ7QUFDQTtBQUNBO0FBQ0EsQ0FBQyIsInNvdXJjZXMiOlsid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS8uL2Fzc2V0cy9jbXMvYXV0aGVudGljYXRpb24vc2lnbi1pbi9nZW5lcmFsLmpzIl0sInNvdXJjZXNDb250ZW50IjpbIlwidXNlIHN0cmljdFwiO1xuXG5cbi8vIENsYXNzIGRlZmluaXRpb25cbmxldCBLVFNpZ25pbkdlbmVyYWwgPSBmdW5jdGlvbiAoKSB7XG4gICAgLy8gRWxlbWVudHNcbiAgICBsZXQgZm9ybTtcbiAgICBsZXQgc3VibWl0QnV0dG9uO1xuICAgIGxldCB2YWxpZGF0b3I7XG5cbiAgICBjb25zdCBsYW5nID0gbG9jYWxTdG9yYWdlLmdldEl0ZW0oXCJrdF9hdXRoX2xhbmdcIik/P2RvY3VtZW50LmdldEVsZW1lbnRzQnlUYWdOYW1lKCdodG1sJylbMF0uZ2V0QXR0cmlidXRlKCdsYW5nJyk7XG5cbiAgICBjb25zdCB0ZXh0cyA9IHtcbiAgICAgICAgJ2VuJzoge1xuICAgICAgICAgICAgJ2VtYWlsX2ludmFsaWQnOiAnVGhlIHZhbHVlIGlzIG5vdCBhIHZhbGlkIGVtYWlsIGFkZHJlc3MnLFxuICAgICAgICAgICAgJ2VtYWlsX2VtcHR5JzogJ0VtYWlsIGFkZHJlc3MgaXMgcmVxdWlyZWQnLFxuICAgICAgICAgICAgJ3Bhc3N3b3JkX2VtcHR5JzogJ1RoZSBwYXNzd29yZCBpcyByZXF1aXJlZCcsXG4gICAgICAgICAgICAnbG9naW5fc3VjY2Vzcyc6ICdZb3UgaGF2ZSBzdWNjZXNzZnVsbHkgbG9nZ2VkIGluIScsXG4gICAgICAgICAgICAnbG9naW5fZXJyb3InOiAnU29ycnksIGxvb2tzIGxpa2UgdGhlcmUgYXJlIHNvbWUgZXJyb3JzIGRldGVjdGVkLCBwbGVhc2UgdHJ5IGFnYWluLicsXG4gICAgICAgICAgICAnb2snOiAnT2ssIGdvdCBpdCEnXG4gICAgICAgIH0sXG4gICAgICAgICdkZSc6IHtcbiAgICAgICAgICAgICdlbWFpbF9pbnZhbGlkJzogJ0RlciBXZXJ0IGlzdCBrZWluZSBnw7xsdGlnZSBFLU1haWwtQWRyZXNzZScsXG4gICAgICAgICAgICAnZW1haWxfZW1wdHknOiAnRS1NYWlsLUFkcmVzc2UgaXN0IGVyZm9yZGVybGljaCcsXG4gICAgICAgICAgICAncGFzc3dvcmRfZW1wdHknOiAnRGFzIFBhc3N3b3J0IGlzdCBlcmZvcmRlcmxpY2gnLFxuICAgICAgICAgICAgJ2xvZ2luX3N1Y2Nlc3MnOiAnU2llIGhhYmVuIHNpY2ggZXJmb2xncmVpY2ggYW5nZW1lbGRldCEnLFxuICAgICAgICAgICAgJ2xvZ2luX2Vycm9yJzogJ0VudHNjaHVsZGlndW5nLCBlcyBzY2hlaW50LCBkYXNzIGVpbmlnZSBGZWhsZXIgZXJrYW5udCB3dXJkZW4sIGJpdHRlIHZlcnN1Y2hlbiBTaWUgZXMgZXJuZXV0LicsXG4gICAgICAgICAgICAnb2snOiAnT2ssIHZlcnN0YW5kZW4hJ1xuICAgICAgICB9LFxuICAgICAgICAnZnInOiB7XG4gICAgICAgICAgICAnZW1haWxfaW52YWxpZCc6ICdMYSB2YWxldXIgblxcJ2VzdCBwYXMgdW5lIGFkcmVzc2UgZS1tYWlsIHZhbGlkZScsXG4gICAgICAgICAgICAnZW1haWxfZW1wdHknOiAnTFxcJ2FkcmVzc2UgZS1tYWlsIGVzdCByZXF1aXNlJyxcbiAgICAgICAgICAgICdwYXNzd29yZF9lbXB0eSc6ICdMZSBtb3QgZGUgcGFzc2UgZXN0IHJlcXVpcycsXG4gICAgICAgICAgICAnbG9naW5fc3VjY2Vzcyc6ICdWb3VzIHZvdXMgw6p0ZXMgY29ubmVjdMOpIGF2ZWMgc3VjY8OocyEnLFxuICAgICAgICAgICAgJ2xvZ2luX2Vycm9yJzogJ0TDqXNvbMOpLCBpbCBzZW1ibGUgcXVcXCdpbCB5IGFpdCBkZXMgZXJyZXVycyBkw6l0ZWN0w6llcywgdmV1aWxsZXogcsOpZXNzYXllci4nLFxuICAgICAgICAgICAgJ29rJzogJ09rLCBjb21wcmlzISdcbiAgICAgICAgfSxcbiAgICAgICAgJ2l0Jzoge1xuICAgICAgICAgICAgJ2VtYWlsX2ludmFsaWQnOiAnSWwgdmFsb3JlIG5vbiDDqCB1biBpbmRpcml6em8gZS1tYWlsIHZhbGlkbycsXG4gICAgICAgICAgICAnZW1haWxfZW1wdHknOiAnTFxcJ2luZGlyaXp6byBlLW1haWwgw6ggcmljaGllc3RvJyxcbiAgICAgICAgICAgICdwYXNzd29yZF9lbXB0eSc6ICdMYSBwYXNzd29yZCDDqCByaWNoaWVzdGEnLFxuICAgICAgICAgICAgJ2xvZ2luX3N1Y2Nlc3MnOiAnTFxcJ2FjY2Vzc28gw6ggc3RhdG8gZWZmZXR0dWF0byBjb24gc3VjY2Vzc28hJyxcbiAgICAgICAgICAgICdsb2dpbl9lcnJvcic6ICdTcGlhY2VudGUsIHNlbWJyYSBjaGUgc2lhbm8gc3RhdGkgcmlsZXZhdGkgZGVnbGkgZXJyb3JpLCBzaSBwcmVnYSBkaSByaXByb3ZhcmUuJyxcbiAgICAgICAgICAgICdvayc6ICdPaywgY2FwaXRvISdcbiAgICAgICAgfSxcbiAgICB9XG5cbiAgICBsZXQgdHJhbnNsYXRlID0gZnVuY3Rpb24gKHRleHQpIHtcblxuICAgICAgICBzd2l0Y2ggKGxhbmcpIHtcbiAgICAgICAgICAgIGNhc2UgJ0dlcm1hbic6XG4gICAgICAgICAgICBjYXNlICdkZSc6XG4gICAgICAgICAgICAgICAgcmV0dXJuIHRleHRzWydkZSddW3RleHRdO1xuICAgICAgICAgICAgY2FzZSAnSXRhbGlhbic6XG4gICAgICAgICAgICBjYXNlICdpdCc6XG4gICAgICAgICAgICAgICAgcmV0dXJuIHRleHRzWydpdCddW3RleHRdO1xuICAgICAgICAgICAgY2FzZSAnRnJlbmNoJzpcbiAgICAgICAgICAgIGNhc2UgJ2ZyJzpcbiAgICAgICAgICAgICAgICByZXR1cm4gdGV4dHNbJ2ZyJ11bdGV4dF07XG4gICAgICAgICAgICBkZWZhdWx0OlxuICAgICAgICAgICAgICAgIHJldHVybiB0ZXh0c1snZW4nXVt0ZXh0XTtcblxuICAgICAgICB9XG4gICAgfVxuXG4gICAgLy8gSGFuZGxlIGZvcm1cbiAgICBsZXQgaGFuZGxlVmFsaWRhdGlvbiA9IGZ1bmN0aW9uIChlKSB7XG4gICAgICAgIC8vIEluaXQgZm9ybSB2YWxpZGF0aW9uIHJ1bGVzLiBGb3IgbW9yZSBpbmZvIGNoZWNrIHRoZSBGb3JtVmFsaWRhdGlvbiBwbHVnaW4ncyBvZmZpY2lhbCBkb2N1bWVudGF0aW9uOmh0dHBzOi8vZm9ybXZhbGlkYXRpb24uaW8vXG4gICAgICAgIHZhbGlkYXRvciA9IEZvcm1WYWxpZGF0aW9uLmZvcm1WYWxpZGF0aW9uKFxuICAgICAgICAgICAgZm9ybSxcbiAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICBmaWVsZHM6IHtcbiAgICAgICAgICAgICAgICAgICAgJ191c2VybmFtZSc6IHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHZhbGlkYXRvcnM6IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICByZWdleHA6IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgcmVnZXhwOiAvXlteXFxzQF0rQFteXFxzQF0rXFwuW15cXHNAXSskfF5zeXNhZG1pbiQvLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBtZXNzYWdlOiB0cmFuc2xhdGUoJ2VtYWlsX2ludmFsaWQnKSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIG5vdEVtcHR5OiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG1lc3NhZ2U6IHRyYW5zbGF0ZSgnZW1haWxfZW1wdHknKVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICAgICAgJ19wYXNzd29yZCc6IHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHZhbGlkYXRvcnM6IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBub3RFbXB0eToge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBtZXNzYWdlOiB0cmFuc2xhdGUoJ3Bhc3N3b3JkX2VtcHR5JylcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgIHBsdWdpbnM6IHtcbiAgICAgICAgICAgICAgICAgICAgdHJpZ2dlcjogbmV3IEZvcm1WYWxpZGF0aW9uLnBsdWdpbnMuVHJpZ2dlcigpLFxuICAgICAgICAgICAgICAgICAgICBib290c3RyYXA6IG5ldyBGb3JtVmFsaWRhdGlvbi5wbHVnaW5zLkJvb3RzdHJhcDUoe1xuICAgICAgICAgICAgICAgICAgICAgICAgcm93U2VsZWN0b3I6ICcuZnYtcm93JyxcbiAgICAgICAgICAgICAgICAgICAgICAgIGVsZUludmFsaWRDbGFzczogJycsICAvLyBjb21tZW50IHRvIGVuYWJsZSBpbnZhbGlkIHN0YXRlIGljb25zXG4gICAgICAgICAgICAgICAgICAgICAgICBlbGVWYWxpZENsYXNzOiAnJyAvLyBjb21tZW50IHRvIGVuYWJsZSB2YWxpZCBzdGF0ZSBpY29uc1xuICAgICAgICAgICAgICAgICAgICB9KVxuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgKTtcbiAgICB9XG5cblxuICAgIGxldCBoYW5kbGVTdWJtaXRBamF4ID0gYXN5bmMgZnVuY3Rpb24gKGUpIHtcbiAgICAgICAgbGV0IENNU1JvdXRpbmcgPSBhd2FpdCBDTVNBZG1pbi5nZXRSb3V0aW5nKCk7XG4gICAgICAgIC8vIEhhbmRsZSBmb3JtIHN1Ym1pdFxuICAgICAgICBzdWJtaXRCdXR0b24uYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBmdW5jdGlvbiAoZSkge1xuICAgICAgICAgICAgLy8gUHJldmVudCBidXR0b24gZGVmYXVsdCBhY3Rpb25cbiAgICAgICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcblxuICAgICAgICAgICAgLy8gVmFsaWRhdGUgZm9ybVxuICAgICAgICAgICAgdmFsaWRhdG9yLnZhbGlkYXRlKCkudGhlbihmdW5jdGlvbiAoc3RhdHVzKSB7XG4gICAgICAgICAgICAgICAgaWYgKHN0YXR1cyA9PT0gJ1ZhbGlkJykge1xuICAgICAgICAgICAgICAgICAgICAvLyBTaG93IGxvYWRpbmcgaW5kaWNhdGlvblxuICAgICAgICAgICAgICAgICAgICBzdWJtaXRCdXR0b24uc2V0QXR0cmlidXRlKCdkYXRhLWt0LWluZGljYXRvcicsICdvbicpO1xuXG4gICAgICAgICAgICAgICAgICAgIC8vIERpc2FibGUgYnV0dG9uIHRvIGF2b2lkIG11bHRpcGxlIGNsaWNrXG4gICAgICAgICAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbi5kaXNhYmxlZCA9IHRydWU7XG4gICAgICAgICAgICAgICAgICAgIC8vIGZvcm0uc3VibWl0KCk7XG4gICAgICAgICAgICAgICAgICAgIC8vIHJldHVybjtcblxuICAgICAgICAgICAgICAgICAgICBsZXQgZm9ybURhdGEgPSBuZXcgRm9ybURhdGEoZm9ybSk7XG5cbiAgICAgICAgICAgICAgICAgICAgbGV0IGxvZ2luSW5mbyA9IHt9XG4gICAgICAgICAgICAgICAgICAgIGZvcm1EYXRhLmZvckVhY2goICh2YWx1ZSwga2V5KSA9PiB7XG4gICAgICAgICAgICAgICAgICAgICAgICBsb2dpbkluZm9ba2V5XSA9IHZhbHVlO1xuICAgICAgICAgICAgICAgICAgICB9KVxuXG4gICAgICAgICAgICAgICAgICAgIGF4aW9zLnBvc3QoQ01TUm91dGluZy5nZW5lcmF0ZSgnY21zX2FwaV9sb2dpbicpLCBsb2dpbkluZm8sIHsuLi5heGlvc0NvbmZpZ30pXG4gICAgICAgICAgICAgICAgICAgICAgICAudGhlbigocmVzcG9uc2UpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdWJtaXRCdXR0b24uZGlzYWJsZWQgPSBmYWxzZTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdWJtaXRCdXR0b24ucmVtb3ZlQXR0cmlidXRlKCdkYXRhLWt0LWluZGljYXRvcicpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCByZWRpcmVjdCA9IHJlc3BvbnNlLmRhdGEucmVkaXJlY3Q7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgU3dhbC5maXJlKHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRleHQ6IHRyYW5zbGF0ZSgnbG9naW5fc3VjY2VzcycpLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdHlwZTogXCJzdWNjZXNzXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpY29uOiBcInN1Y2Nlc3NcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRpbWVyOiAxMDAwLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc2hvd0NvbmZpcm1CdXR0b246IGZhbHNlLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KS50aGVuKCgpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBob3N0ID0gIHdpbmRvdy5sb2NhdGlvbi5wcm90b2NvbCArICcvLycgKyB3aW5kb3cubG9jYXRpb24uaG9zdFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYocmVkaXJlY3QuaW5kZXhPZihob3N0KSA9PT0gLTEgfHwgcmVkaXJlY3QuaW5kZXhPZignaHR0cCcpID09PSAtMSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJlZGlyZWN0ID0gaG9zdCArIHJlZGlyZWN0XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsb2NhdGlvbi5ocmVmID0gcmVkaXJlY3RcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9KVxuICAgICAgICAgICAgICAgICAgICAgICAgLmNhdGNoKChlcnJvcikgPT4ge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbi5kaXNhYmxlZCA9IGZhbHNlO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbi5yZW1vdmVBdHRyaWJ1dGUoJ2RhdGEta3QtaW5kaWNhdG9yJyk7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IGRhdGEgPSBlcnJvci5yZXNwb25zZS5kYXRhXG5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBTd2FsLmZpcmUoe1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0ZXh0OiBkYXRhLmVycm9yLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpY29uOiBcImVycm9yXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbnNTdHlsaW5nOiBmYWxzZSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY29uZmlybUJ1dHRvblRleHQ6IHRyYW5zbGF0ZSgnb2snKSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY3VzdG9tQ2xhc3M6IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNvbmZpcm1CdXR0b246IFwiYnRuIGJ0bi1wcmltYXJ5XCJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbi5kaXNhYmxlZCA9IGZhbHNlO1xuICAgICAgICAgICAgICAgICAgICAgICAgfSlcblxuICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgIC8vIFNob3cgZXJyb3IgcG9wdXAuIEZvciBtb3JlIGluZm8gY2hlY2sgdGhlIHBsdWdpbidzIG9mZmljaWFsIGRvY3VtZW50YXRpb246IGh0dHBzOi8vc3dlZXRhbGVydDIuZ2l0aHViLmlvL1xuICAgICAgICAgICAgICAgICAgICBTd2FsLmZpcmUoe1xuICAgICAgICAgICAgICAgICAgICAgICAgdGV4dDogdHJhbnNsYXRlKCdsb2dpbl9lcnJvcicpLFxuICAgICAgICAgICAgICAgICAgICAgICAgaWNvbjogXCJlcnJvclwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgYnV0dG9uc1N0eWxpbmc6IGZhbHNlLFxuICAgICAgICAgICAgICAgICAgICAgICAgY29uZmlybUJ1dHRvblRleHQ6IHRyYW5zbGF0ZSgnb2snKSxcbiAgICAgICAgICAgICAgICAgICAgICAgIGN1c3RvbUNsYXNzOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgY29uZmlybUJ1dHRvbjogXCJidG4gYnRuLXByaW1hcnlcIlxuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfSk7XG4gICAgfVxuXG4gICAgbGV0IGlzVmFsaWRVcmwgPSBmdW5jdGlvbih1cmwpIHtcbiAgICAgICAgdHJ5IHtcbiAgICAgICAgICAgIG5ldyBVUkwodXJsKTtcbiAgICAgICAgICAgIHJldHVybiB0cnVlO1xuICAgICAgICB9IGNhdGNoIChlKSB7XG4gICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvLyBQdWJsaWMgZnVuY3Rpb25zXG4gICAgcmV0dXJuIHtcbiAgICAgICAgLy8gSW5pdGlhbGl6YXRpb25cbiAgICAgICAgaW5pdDogZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgZm9ybSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJyNrdF9zaWduX2luX2Zvcm0nKTtcbiAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJyNrdF9zaWduX2luX3N1Ym1pdCcpO1xuXG4gICAgICAgICAgICBoYW5kbGVWYWxpZGF0aW9uKCk7XG5cbiAgICAgICAgICAgIGlmIChpc1ZhbGlkVXJsKHN1Ym1pdEJ1dHRvbi5jbG9zZXN0KCdmb3JtJykuZ2V0QXR0cmlidXRlKCdhY3Rpb24nKSkpIHtcbiAgICAgICAgICAgICAgICBoYW5kbGVTdWJtaXRBamF4KCk7IC8vIHVzZSBmb3IgYWpheCBzdWJtaXRcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH07XG59KCk7XG5cbi8vIE9uIGRvY3VtZW50IHJlYWR5XG5LVFV0aWwub25ET01Db250ZW50TG9hZGVkKGZ1bmN0aW9uICgpIHtcbiAgICBLVFNpZ25pbkdlbmVyYWwuaW5pdCgpO1xufSk7XG4iXSwibmFtZXMiOltdLCJzb3VyY2VSb290IjoiIn0=