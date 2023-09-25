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
            'email_invalid': 'La valeur n\'est pas une adresse e-mail valide',
            'email_empty': 'L\'adresse e-mail est requise',
            'password_empty': 'Le mot de passe est requis',
            'login_success': 'Vous vous êtes connecté avec succès!',
            'login_error': 'Désolé, il semble qu\'il y ait des erreurs détectées, veuillez réessayer.',
            'ok': 'Ok, compris!'
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

                    axios.post(CMSRouting.generate('api_login'), loginInfo, {...axiosConfig})
                        .then((response) => {
                            submitButton.disabled = false;
                            submitButton.removeAttribute('data-kt-indicator');
                            let redirect = response.data.redirect;
                            Swal.fire({
                                    text: "Authentication successful!",
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZ2VuZXJhbFNpZ25pbi5qcyIsIm1hcHBpbmdzIjoiOzs7Ozs7QUFBYTs7O0FBR2I7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7O0FBRUE7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsNkJBQTZCO0FBQzdCO0FBQ0E7QUFDQTtBQUNBO0FBQ0EscUJBQXFCO0FBQ3JCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsaUJBQWlCO0FBQ2pCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHFCQUFxQjtBQUNyQjtBQUNBO0FBQ0E7QUFDQTs7O0FBR0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7O0FBRUE7QUFDQTtBQUNBO0FBQ0EscUJBQXFCOztBQUVyQiw2RUFBNkUsZUFBZTtBQUM1RjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGlDQUFpQztBQUNqQztBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsaUNBQWlDO0FBQ2pDLHlCQUF5QjtBQUN6QjtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsNkJBQTZCO0FBQzdCO0FBQ0EseUJBQXlCOztBQUV6QixrQkFBa0I7QUFDbEI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EscUJBQXFCO0FBQ3JCO0FBQ0EsYUFBYTtBQUNiLFNBQVM7QUFDVDs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFVBQVU7QUFDVjtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBOztBQUVBO0FBQ0Esb0NBQW9DO0FBQ3BDO0FBQ0E7QUFDQTtBQUNBLENBQUM7O0FBRUQ7QUFDQTtBQUNBO0FBQ0EsQ0FBQyIsInNvdXJjZXMiOlsid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS8uL2Fzc2V0cy9jbXMvYXV0aGVudGljYXRpb24vc2lnbi1pbi9nZW5lcmFsLmpzIl0sInNvdXJjZXNDb250ZW50IjpbIlwidXNlIHN0cmljdFwiO1xuXG5cbi8vIENsYXNzIGRlZmluaXRpb25cbmxldCBLVFNpZ25pbkdlbmVyYWwgPSBmdW5jdGlvbiAoKSB7XG4gICAgLy8gRWxlbWVudHNcbiAgICBsZXQgZm9ybTtcbiAgICBsZXQgc3VibWl0QnV0dG9uO1xuICAgIGxldCB2YWxpZGF0b3I7XG5cbiAgICBjb25zdCBsYW5nID0gbG9jYWxTdG9yYWdlLmdldEl0ZW0oXCJrdF9hdXRoX2xhbmdcIik/P2RvY3VtZW50LmdldEVsZW1lbnRzQnlUYWdOYW1lKCdodG1sJylbMF0uZ2V0QXR0cmlidXRlKCdsYW5nJyk7XG5cbiAgICBjb25zdCB0ZXh0cyA9IHtcbiAgICAgICAgJ2VuJzoge1xuICAgICAgICAgICAgJ2VtYWlsX2ludmFsaWQnOiAnVGhlIHZhbHVlIGlzIG5vdCBhIHZhbGlkIGVtYWlsIGFkZHJlc3MnLFxuICAgICAgICAgICAgJ2VtYWlsX2VtcHR5JzogJ0VtYWlsIGFkZHJlc3MgaXMgcmVxdWlyZWQnLFxuICAgICAgICAgICAgJ3Bhc3N3b3JkX2VtcHR5JzogJ1RoZSBwYXNzd29yZCBpcyByZXF1aXJlZCcsXG4gICAgICAgICAgICAnbG9naW5fc3VjY2Vzcyc6ICdZb3UgaGF2ZSBzdWNjZXNzZnVsbHkgbG9nZ2VkIGluIScsXG4gICAgICAgICAgICAnbG9naW5fZXJyb3InOiAnU29ycnksIGxvb2tzIGxpa2UgdGhlcmUgYXJlIHNvbWUgZXJyb3JzIGRldGVjdGVkLCBwbGVhc2UgdHJ5IGFnYWluLicsXG4gICAgICAgICAgICAnb2snOiAnT2ssIGdvdCBpdCEnXG4gICAgICAgIH0sXG4gICAgICAgICdkZSc6IHtcbiAgICAgICAgICAgICdlbWFpbF9pbnZhbGlkJzogJ0RlciBXZXJ0IGlzdCBrZWluZSBnw7xsdGlnZSBFLU1haWwtQWRyZXNzZScsXG4gICAgICAgICAgICAnZW1haWxfZW1wdHknOiAnRS1NYWlsLUFkcmVzc2UgaXN0IGVyZm9yZGVybGljaCcsXG4gICAgICAgICAgICAncGFzc3dvcmRfZW1wdHknOiAnRGFzIFBhc3N3b3J0IGlzdCBlcmZvcmRlcmxpY2gnLFxuICAgICAgICAgICAgJ2xvZ2luX3N1Y2Nlc3MnOiAnU2llIGhhYmVuIHNpY2ggZXJmb2xncmVpY2ggYW5nZW1lbGRldCEnLFxuICAgICAgICAgICAgJ2xvZ2luX2Vycm9yJzogJ0VudHNjaHVsZGlndW5nLCBlcyBzY2hlaW50LCBkYXNzIGVpbmlnZSBGZWhsZXIgZXJrYW5udCB3dXJkZW4sIGJpdHRlIHZlcnN1Y2hlbiBTaWUgZXMgZXJuZXV0LicsXG4gICAgICAgICAgICAnb2snOiAnT2ssIHZlcnN0YW5kZW4hJ1xuICAgICAgICB9LFxuICAgICAgICAnZnInOiB7XG4gICAgICAgICAgICAnZW1haWxfaW52YWxpZCc6ICdMYSB2YWxldXIgblxcJ2VzdCBwYXMgdW5lIGFkcmVzc2UgZS1tYWlsIHZhbGlkZScsXG4gICAgICAgICAgICAnZW1haWxfZW1wdHknOiAnTFxcJ2FkcmVzc2UgZS1tYWlsIGVzdCByZXF1aXNlJyxcbiAgICAgICAgICAgICdwYXNzd29yZF9lbXB0eSc6ICdMZSBtb3QgZGUgcGFzc2UgZXN0IHJlcXVpcycsXG4gICAgICAgICAgICAnbG9naW5fc3VjY2Vzcyc6ICdWb3VzIHZvdXMgw6p0ZXMgY29ubmVjdMOpIGF2ZWMgc3VjY8OocyEnLFxuICAgICAgICAgICAgJ2xvZ2luX2Vycm9yJzogJ0TDqXNvbMOpLCBpbCBzZW1ibGUgcXVcXCdpbCB5IGFpdCBkZXMgZXJyZXVycyBkw6l0ZWN0w6llcywgdmV1aWxsZXogcsOpZXNzYXllci4nLFxuICAgICAgICAgICAgJ29rJzogJ09rLCBjb21wcmlzISdcbiAgICAgICAgfSxcbiAgICAgICAgJ2l0Jzoge1xuICAgICAgICAgICAgJ2VtYWlsX2ludmFsaWQnOiAnTGEgdmFsZXVyIG5cXCdlc3QgcGFzIHVuZSBhZHJlc3NlIGUtbWFpbCB2YWxpZGUnLFxuICAgICAgICAgICAgJ2VtYWlsX2VtcHR5JzogJ0xcXCdhZHJlc3NlIGUtbWFpbCBlc3QgcmVxdWlzZScsXG4gICAgICAgICAgICAncGFzc3dvcmRfZW1wdHknOiAnTGUgbW90IGRlIHBhc3NlIGVzdCByZXF1aXMnLFxuICAgICAgICAgICAgJ2xvZ2luX3N1Y2Nlc3MnOiAnVm91cyB2b3VzIMOqdGVzIGNvbm5lY3TDqSBhdmVjIHN1Y2PDqHMhJyxcbiAgICAgICAgICAgICdsb2dpbl9lcnJvcic6ICdEw6lzb2zDqSwgaWwgc2VtYmxlIHF1XFwnaWwgeSBhaXQgZGVzIGVycmV1cnMgZMOpdGVjdMOpZXMsIHZldWlsbGV6IHLDqWVzc2F5ZXIuJyxcbiAgICAgICAgICAgICdvayc6ICdPaywgY29tcHJpcyEnXG4gICAgICAgIH0sXG4gICAgfVxuXG4gICAgbGV0IHRyYW5zbGF0ZSA9IGZ1bmN0aW9uICh0ZXh0KSB7XG5cbiAgICAgICAgc3dpdGNoIChsYW5nKSB7XG4gICAgICAgICAgICBjYXNlICdHZXJtYW4nOlxuICAgICAgICAgICAgY2FzZSAnZGUnOlxuICAgICAgICAgICAgICAgIHJldHVybiB0ZXh0c1snZGUnXVt0ZXh0XTtcbiAgICAgICAgICAgIGNhc2UgJ0l0YWxpYW4nOlxuICAgICAgICAgICAgY2FzZSAnaXQnOlxuICAgICAgICAgICAgICAgIHJldHVybiB0ZXh0c1snaXQnXVt0ZXh0XTtcbiAgICAgICAgICAgIGNhc2UgJ0ZyZW5jaCc6XG4gICAgICAgICAgICBjYXNlICdmcic6XG4gICAgICAgICAgICAgICAgcmV0dXJuIHRleHRzWydmciddW3RleHRdO1xuICAgICAgICAgICAgZGVmYXVsdDpcbiAgICAgICAgICAgICAgICByZXR1cm4gdGV4dHNbJ2VuJ11bdGV4dF07XG5cbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8vIEhhbmRsZSBmb3JtXG4gICAgbGV0IGhhbmRsZVZhbGlkYXRpb24gPSBmdW5jdGlvbiAoZSkge1xuICAgICAgICAvLyBJbml0IGZvcm0gdmFsaWRhdGlvbiBydWxlcy4gRm9yIG1vcmUgaW5mbyBjaGVjayB0aGUgRm9ybVZhbGlkYXRpb24gcGx1Z2luJ3Mgb2ZmaWNpYWwgZG9jdW1lbnRhdGlvbjpodHRwczovL2Zvcm12YWxpZGF0aW9uLmlvL1xuICAgICAgICB2YWxpZGF0b3IgPSBGb3JtVmFsaWRhdGlvbi5mb3JtVmFsaWRhdGlvbihcbiAgICAgICAgICAgIGZvcm0sXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgZmllbGRzOiB7XG4gICAgICAgICAgICAgICAgICAgICdfdXNlcm5hbWUnOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICB2YWxpZGF0b3JzOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgcmVnZXhwOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJlZ2V4cDogL15bXlxcc0BdK0BbXlxcc0BdK1xcLlteXFxzQF0rJHxec3lzYWRtaW4kLyxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbWVzc2FnZTogdHJhbnNsYXRlKCdlbWFpbF9pbnZhbGlkJyksXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBub3RFbXB0eToge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBtZXNzYWdlOiB0cmFuc2xhdGUoJ2VtYWlsX2VtcHR5JylcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgICAgICdfcGFzc3dvcmQnOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICB2YWxpZGF0b3JzOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgbm90RW1wdHk6IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbWVzc2FnZTogdHJhbnNsYXRlKCdwYXNzd29yZF9lbXB0eScpXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICBwbHVnaW5zOiB7XG4gICAgICAgICAgICAgICAgICAgIHRyaWdnZXI6IG5ldyBGb3JtVmFsaWRhdGlvbi5wbHVnaW5zLlRyaWdnZXIoKSxcbiAgICAgICAgICAgICAgICAgICAgYm9vdHN0cmFwOiBuZXcgRm9ybVZhbGlkYXRpb24ucGx1Z2lucy5Cb290c3RyYXA1KHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHJvd1NlbGVjdG9yOiAnLmZ2LXJvdycsXG4gICAgICAgICAgICAgICAgICAgICAgICBlbGVJbnZhbGlkQ2xhc3M6ICcnLCAgLy8gY29tbWVudCB0byBlbmFibGUgaW52YWxpZCBzdGF0ZSBpY29uc1xuICAgICAgICAgICAgICAgICAgICAgICAgZWxlVmFsaWRDbGFzczogJycgLy8gY29tbWVudCB0byBlbmFibGUgdmFsaWQgc3RhdGUgaWNvbnNcbiAgICAgICAgICAgICAgICAgICAgfSlcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG4gICAgICAgICk7XG4gICAgfVxuXG5cbiAgICBsZXQgaGFuZGxlU3VibWl0QWpheCA9IGFzeW5jIGZ1bmN0aW9uIChlKSB7XG4gICAgICAgIGxldCBDTVNSb3V0aW5nID0gYXdhaXQgQ01TQWRtaW4uZ2V0Um91dGluZygpO1xuICAgICAgICAvLyBIYW5kbGUgZm9ybSBzdWJtaXRcbiAgICAgICAgc3VibWl0QnV0dG9uLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgZnVuY3Rpb24gKGUpIHtcbiAgICAgICAgICAgIC8vIFByZXZlbnQgYnV0dG9uIGRlZmF1bHQgYWN0aW9uXG4gICAgICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG5cbiAgICAgICAgICAgIC8vIFZhbGlkYXRlIGZvcm1cbiAgICAgICAgICAgIHZhbGlkYXRvci52YWxpZGF0ZSgpLnRoZW4oZnVuY3Rpb24gKHN0YXR1cykge1xuICAgICAgICAgICAgICAgIGlmIChzdGF0dXMgPT09ICdWYWxpZCcpIHtcbiAgICAgICAgICAgICAgICAgICAgLy8gU2hvdyBsb2FkaW5nIGluZGljYXRpb25cbiAgICAgICAgICAgICAgICAgICAgc3VibWl0QnV0dG9uLnNldEF0dHJpYnV0ZSgnZGF0YS1rdC1pbmRpY2F0b3InLCAnb24nKTtcblxuICAgICAgICAgICAgICAgICAgICAvLyBEaXNhYmxlIGJ1dHRvbiB0byBhdm9pZCBtdWx0aXBsZSBjbGlja1xuICAgICAgICAgICAgICAgICAgICBzdWJtaXRCdXR0b24uZGlzYWJsZWQgPSB0cnVlO1xuICAgICAgICAgICAgICAgICAgICAvLyBmb3JtLnN1Ym1pdCgpO1xuICAgICAgICAgICAgICAgICAgICAvLyByZXR1cm47XG5cbiAgICAgICAgICAgICAgICAgICAgbGV0IGZvcm1EYXRhID0gbmV3IEZvcm1EYXRhKGZvcm0pO1xuXG4gICAgICAgICAgICAgICAgICAgIGxldCBsb2dpbkluZm8gPSB7fVxuICAgICAgICAgICAgICAgICAgICBmb3JtRGF0YS5mb3JFYWNoKCAodmFsdWUsIGtleSkgPT4ge1xuICAgICAgICAgICAgICAgICAgICAgICAgbG9naW5JbmZvW2tleV0gPSB2YWx1ZTtcbiAgICAgICAgICAgICAgICAgICAgfSlcblxuICAgICAgICAgICAgICAgICAgICBheGlvcy5wb3N0KENNU1JvdXRpbmcuZ2VuZXJhdGUoJ2FwaV9sb2dpbicpLCBsb2dpbkluZm8sIHsuLi5heGlvc0NvbmZpZ30pXG4gICAgICAgICAgICAgICAgICAgICAgICAudGhlbigocmVzcG9uc2UpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdWJtaXRCdXR0b24uZGlzYWJsZWQgPSBmYWxzZTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdWJtaXRCdXR0b24ucmVtb3ZlQXR0cmlidXRlKCdkYXRhLWt0LWluZGljYXRvcicpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCByZWRpcmVjdCA9IHJlc3BvbnNlLmRhdGEucmVkaXJlY3Q7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgU3dhbC5maXJlKHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRleHQ6IFwiQXV0aGVudGljYXRpb24gc3VjY2Vzc2Z1bCFcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHR5cGU6IFwic3VjY2Vzc1wiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWNvbjogXCJzdWNjZXNzXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aW1lcjogMTAwMCxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHNob3dDb25maXJtQnV0dG9uOiBmYWxzZSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSkudGhlbigoKSA9PiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgaG9zdCA9ICB3aW5kb3cubG9jYXRpb24ucHJvdG9jb2wgKyAnLy8nICsgd2luZG93LmxvY2F0aW9uLmhvc3RcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmKHJlZGlyZWN0LmluZGV4T2YoaG9zdCkgPT09IC0xIHx8IHJlZGlyZWN0LmluZGV4T2YoJ2h0dHAnKSA9PT0gLTEpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICByZWRpcmVjdCA9IGhvc3QgKyByZWRpcmVjdFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbG9jYXRpb24uaHJlZiA9IHJlZGlyZWN0XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICAgICAgICAgICAgfSlcbiAgICAgICAgICAgICAgICAgICAgICAgIC5jYXRjaCgoZXJyb3IpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdWJtaXRCdXR0b24uZGlzYWJsZWQgPSBmYWxzZTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdWJtaXRCdXR0b24ucmVtb3ZlQXR0cmlidXRlKCdkYXRhLWt0LWluZGljYXRvcicpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBkYXRhID0gZXJyb3IucmVzcG9uc2UuZGF0YVxuXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgU3dhbC5maXJlKHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGV4dDogZGF0YS5lcnJvcixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWNvbjogXCJlcnJvclwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBidXR0b25zU3R5bGluZzogZmFsc2UsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNvbmZpcm1CdXR0b25UZXh0OiB0cmFuc2xhdGUoJ29rJyksXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGN1c3RvbUNsYXNzOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBjb25maXJtQnV0dG9uOiBcImJ0biBidG4tcHJpbWFyeVwiXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdWJtaXRCdXR0b24uZGlzYWJsZWQgPSBmYWxzZTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH0pXG5cbiAgICAgICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgICAgICAvLyBTaG93IGVycm9yIHBvcHVwLiBGb3IgbW9yZSBpbmZvIGNoZWNrIHRoZSBwbHVnaW4ncyBvZmZpY2lhbCBkb2N1bWVudGF0aW9uOiBodHRwczovL3N3ZWV0YWxlcnQyLmdpdGh1Yi5pby9cbiAgICAgICAgICAgICAgICAgICAgU3dhbC5maXJlKHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHRleHQ6IHRyYW5zbGF0ZSgnbG9naW5fZXJyb3InKSxcbiAgICAgICAgICAgICAgICAgICAgICAgIGljb246IFwiZXJyb3JcIixcbiAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbnNTdHlsaW5nOiBmYWxzZSxcbiAgICAgICAgICAgICAgICAgICAgICAgIGNvbmZpcm1CdXR0b25UZXh0OiB0cmFuc2xhdGUoJ29rJyksXG4gICAgICAgICAgICAgICAgICAgICAgICBjdXN0b21DbGFzczoge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNvbmZpcm1CdXR0b246IFwiYnRuIGJ0bi1wcmltYXJ5XCJcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfSk7XG4gICAgICAgIH0pO1xuICAgIH1cblxuICAgIGxldCBpc1ZhbGlkVXJsID0gZnVuY3Rpb24odXJsKSB7XG4gICAgICAgIHRyeSB7XG4gICAgICAgICAgICBuZXcgVVJMKHVybCk7XG4gICAgICAgICAgICByZXR1cm4gdHJ1ZTtcbiAgICAgICAgfSBjYXRjaCAoZSkge1xuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgLy8gUHVibGljIGZ1bmN0aW9uc1xuICAgIHJldHVybiB7XG4gICAgICAgIC8vIEluaXRpYWxpemF0aW9uXG4gICAgICAgIGluaXQ6IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgIGZvcm0gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcja3Rfc2lnbl9pbl9mb3JtJyk7XG4gICAgICAgICAgICBzdWJtaXRCdXR0b24gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcja3Rfc2lnbl9pbl9zdWJtaXQnKTtcblxuICAgICAgICAgICAgaGFuZGxlVmFsaWRhdGlvbigpO1xuXG4gICAgICAgICAgICBpZiAoaXNWYWxpZFVybChzdWJtaXRCdXR0b24uY2xvc2VzdCgnZm9ybScpLmdldEF0dHJpYnV0ZSgnYWN0aW9uJykpKSB7XG4gICAgICAgICAgICAgICAgaGFuZGxlU3VibWl0QWpheCgpOyAvLyB1c2UgZm9yIGFqYXggc3VibWl0XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICB9O1xufSgpO1xuXG4vLyBPbiBkb2N1bWVudCByZWFkeVxuS1RVdGlsLm9uRE9NQ29udGVudExvYWRlZChmdW5jdGlvbiAoKSB7XG4gICAgS1RTaWduaW5HZW5lcmFsLmluaXQoKTtcbn0pO1xuIl0sIm5hbWVzIjpbXSwic291cmNlUm9vdCI6IiJ9