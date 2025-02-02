"use strict";


import {authenticate} from "../webauthn/client";
import { browserSupportsWebAuthnAutofill, platformAuthenticatorIsAvailable } from '@simplewebauthn/browser';
let KTSigninGeneral = function () {
    // Elements
    let form;
    let submitButton;
    let validator;
    let signInWithPasskeyButton;
    let signInWithUsernameAndPasswordLink;
    let signInWithPasskeyContainer;
    let signInWithUsernameAndPasswordContainer;

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

    let webauthnSignin = async function(username){
        try{
            // Is conditional UI available in this browser?
            const cma = await PublicKeyCredential.isConditionalMediationAvailable();
            if (cma) {
                const result = await authenticate(username);
                if (result && 'ok' === result.status) {
                    return location.href = document.querySelector('#redirect_route').value
                }


                throw new Error(result)
            }
        }catch (e) {
            let message = e.message;

            if(e.name === 'Error'){
                message = translate('login_error');
            }

            if(e.name === 'NotAllowedError'){
                return signInWithUsernameAndPasswordLink.click()
            }

            if(e.message === 'no_credentials'){
                return signInWithUsernameAndPasswordLink.click()
            }

            Swal.fire({
                text: message,
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: translate('ok'),
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
        }
    }

    // Public functions
    return {
        // Initialization
        init: function () {
            form = document.querySelector('#kt_sign_in_form');
            submitButton = document.querySelector('#kt_sign_in_submit');
            signInWithUsernameAndPasswordContainer = document.querySelector('#kt_sign_in_with_username_and_password_container');
            let webauthnEnabled = document.querySelector("meta[name='webauthn-enabled']").getAttribute("content");

            if (
                webauthnEnabled &&
                window.PublicKeyCredential &&
                PublicKeyCredential.isConditionalMediationAvailable
            ) {
                signInWithPasskeyButton = document.querySelector('#kt_sign_in_with_passkey');
                signInWithUsernameAndPasswordLink = document.querySelector('#kt_sign_in_with_username_and_password');
                signInWithPasskeyContainer = document.querySelector('#kt_sign_in_with_passkey_container');


                let typingTimer;                //timer identifier
                let doneTypingInterval = 1000;  //time in ms (5 seconds)
                let usernameInput = document.querySelector('#username');
                const usernameEvent = () => {
                    clearTimeout(typingTimer);
                    if (usernameInput.value) {
                        typingTimer = setTimeout(doneTyping, doneTypingInterval);
                    }
                }

                let eventListener = usernameInput.addEventListener('keyup', usernameEvent);

                function doneTyping () {
                    signInWithPasskeyButton.click()
                }

                signInWithPasskeyButton.addEventListener('click', function (e) {
                    e.preventDefault();
                    try {
                        webauthnSignin(usernameInput.value);
                    } catch (e) {

                        // A NotAllowedError indicates that the user canceled the operation.
                        if (e.name !== "NotAllowedError") {
                            //CMSAdmin.createInitCmsMessageBox('error', e.message);
                        }
                    }
                })

                signInWithUsernameAndPasswordLink.addEventListener('click', function (e) {
                    e.preventDefault();
                    usernameInput.removeEventListener('keyup', usernameEvent);
                    signInWithPasskeyContainer.classList.add('d-none');
                    signInWithUsernameAndPasswordContainer.classList.remove('d-none');

                })
            }

            handleValidation();

            if (isValidUrl(submitButton.closest('form').getAttribute('action'))) {
                handleSubmitAjax(); // use for ajax submit
            }
        }
    };
}();

// On document ready
document.addEventListener('DOMContentLoaded', () => {
    KTSigninGeneral.init();
});
