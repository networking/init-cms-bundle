"use strict";


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