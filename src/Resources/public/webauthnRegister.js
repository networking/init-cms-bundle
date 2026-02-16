/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/cms/authentication/webauthn/client.js"
/*!******************************************************!*\
  !*** ./assets/cms/authentication/webauthn/client.js ***!
  \******************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   _fetch: () => (/* binding */ _fetch),
/* harmony export */   authenticate: () => (/* binding */ authenticate),
/* harmony export */   registerCredential: () => (/* binding */ registerCredential),
/* harmony export */   unregisterCredential: () => (/* binding */ unregisterCredential),
/* harmony export */   updateCredential: () => (/* binding */ updateCredential)
/* harmony export */ });
/* harmony import */ var _simplewebauthn_browser__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @simplewebauthn/browser */ "./node_modules/@simplewebauthn/browser/esm/index.js");


async function _fetch(path, payload = '') {
    const headers = {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json'
    };
    if (payload && !(payload instanceof FormData)) {
        payload = JSON.stringify(payload);
    }
    const res = await fetch(path, {
        method: 'POST',
        credentials: 'same-origin',
        headers: headers,
        body: payload,
    });
    if (res.status === 200 || res.status === 201) {
        // Server authentication succeeded
        return res.json();
    } else {
        // Server authentication failed
        const result = await res.json();

        if(result.message){
            throw new Error(result.message);
        }

        throw new Error(result.error);
    }
}


async function registerCredential(username, displayName) {

    let data = {
        username: username,
        displayName: displayName,
        authenticatorSelection: {
            userVerification: 'preferred',
        },
    }

    const options = await _fetch('/admin/register/options', data);

    let attResp;
    try {
        // Pass the options to the authenticator and wait for a response
        attResp = await (0,_simplewebauthn_browser__WEBPACK_IMPORTED_MODULE_0__.startRegistration)(options);
    } catch (error) {

        throw error;
    }

    return await _fetch(CMSRouting.generate('initcms_webauthn_register_response'), attResp);

}
async function authenticate(username) {
    let payload = {
        requireUserVerification: 'preferred',
    }

    if(username){
        payload.username = username;
    }
    const options = await _fetch('/admin/assertion/options', payload);
    if(username && options.allowCredentials === undefined){
        throw new Error('no_credentials');
    }


    let asseResp;
    try {
        // Pass the options to the authenticator and wait for a response
        asseResp = await (0,_simplewebauthn_browser__WEBPACK_IMPORTED_MODULE_0__.startAuthentication)(options);
    } catch (error) {
        throw error;
    }

    return _fetch('/admin/assertion', asseResp);
}

async function unregisterCredential(credId) {
    return _fetch(CMSRouting.generate('admin_networking_initcms_user_remove_webauthn_key') + `?credId=${encodeURIComponent(credId)}`);
}

async function updateCredential(credId, name) {
    return _fetch(CMSRouting.generate('admin_networking_initcms_user_rename_webauthn_key') + `?credId=${encodeURIComponent(credId)}&name=${encodeURIComponent(name)}`);
}


/***/ },

/***/ "./assets/cms/authentication/webauthn/register.js"
/*!********************************************************!*\
  !*** ./assets/cms/authentication/webauthn/register.js ***!
  \********************************************************/
(module, __webpack_exports__, __webpack_require__) {

__webpack_require__.a(module, async (__webpack_handle_async_dependencies__, __webpack_async_result__) => { try {
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var lit_html__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! lit-html */ "./node_modules/lit-html/development/lit-html.js");
/* harmony import */ var _simplewebauthn_browser__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @simplewebauthn/browser */ "./node_modules/@simplewebauthn/browser/esm/index.js");
/* harmony import */ var _client_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./client.js */ "./assets/cms/authentication/webauthn/client.js");



let Translator = await CMSAdmin.getTranslator();
const createPasskeyButtons = document.querySelectorAll('.create-passkey');
const testSigninButton = document.querySelector('#test-signin');
const deleteAuthenticatorButton = document.querySelector('#delete-authenticator');
const createAuthenticatorButton = document.querySelector('#create-authenticator');
const currentAuthenticator= document.querySelector('#current-authenticator');
const webauthnEnabled = document.querySelector("meta[name='webauthn-enabled']").getAttribute("content");
const googleAuthenticatorEnabled = document.querySelector("meta[name='google-authenticator-enabled']").getAttribute("content");


async function rename(e) {
    let el = e.target
    if(!el.classList.contains('btn')) {
        el = el.closest('.btn');
    }
    const { credId, name } = el.dataset;
    try{
        const { value: newName, isConfirmed: isConfirmed } = await Swal.fire({
            title: Translator.trans('passkey_rename.title', [], 'security'),
            inputLabel: Translator.trans('passkey_rename.input_label', [], 'security'),
            input: 'text',
            inputValue: name,
            inputValidator: (value) => {
                if (!value) {
                    return Translator.trans('passkey_rename.input_invalid', [], 'security');
                }
            },
            buttonsStyling: false,
            showCancelButton: true,
            confirmButtonText: Translator.trans('submit', [], 'security'),
            cancelButtonText: Translator.trans('cancel', [], 'security'),
            reverseButtons: true,
            customClass: {
                confirmButton: "btn btn-sm btn-primary",
                cancelButton: "btn btn-sm btn-light"
            }
        })

        if (isConfirmed) {
            await (0,_client_js__WEBPACK_IMPORTED_MODULE_2__.updateCredential)(credId, newName);
            renderCredentials();
        }
    }catch (e) {
        CMSAdmin.createInitCmsMessageBox('error', e.message);
    }
};

async function remove(e) {
    Swal.fire({
        title: Translator.trans('passkey_delete.title', [], 'security'),
        text: Translator.trans('passkey_delete.text', [], 'security'),
        icon: 'warning',
        buttonsStyling: false,
        showCancelButton: true,
        confirmButtonText: Translator.trans('delete', [], 'security'),
        cancelButtonText: Translator.trans('cancel', [], 'security'),
        reverseButtons: true,
        customClass: {
            confirmButton: "btn btn-sm btn-danger",
            cancelButton: "btn btn-sm btn-light"
        }
    }).then(async (result) => {

        if (!result.isConfirmed) {
            return;
        }
        let el = e.target
        if(!el.classList.contains('btn')) {
            el = el.closest('.btn');
        }
        try {
            await (0,_client_js__WEBPACK_IMPORTED_MODULE_2__.unregisterCredential)(el.dataset.credId);
            renderCredentials();
        } catch (e) {
            alert(e.message);
        }
    })
}

async function renderCredentials() {
    try {
        let res = await (0,_client_js__WEBPACK_IMPORTED_MODULE_2__._fetch)(CMSRouting.generate('admin_networking_initcms_user_get_webauthn_keys'))
        const list = document.querySelector('#list');
        document.querySelector('#passkeys-setup').classList.toggle('d-none', res.length < 1);
        document.querySelector('#passkeys-not-setup').classList.toggle('d-none', res.length > 0);
        const creds = (0,lit_html__WEBPACK_IMPORTED_MODULE_0__.html)`${res.length > 0 ? (0,lit_html__WEBPACK_IMPORTED_MODULE_0__.html)`

                <ul class="list-group">
                      ${res.map(cred => (0,lit_html__WEBPACK_IMPORTED_MODULE_0__.html)`
                          <li class="list-group-item d-flex justify-content-between align-items-start">
                              <div>
                                 <img src="${cred.icon}" class="w-25px">
                              </div>
                              <div class="w-75">
                                  <div>${cred.name || 'Unnamed'}</div>
                                  <div class="text-muted">${cred.createdAt}</div>
                              </div>
                              <div class="buttons">

                                  <button data-cred-id="${cred.id}" @click="${rename}" data-name="${cred.name}"
                                          class=" btn btn-light btn-sm "
                                          data-bs-toggle="tooltip"
                                          data-bs-placement="bottom" aria-label="${Translator.trans("action_edit", [], "SonataAdminBundle")}"
                                          data-bs-original-title="${Translator.trans("action_edit", [], "SonataAdminBundle")}"
                                          title="${Translator.trans("action_edit", [], "SonataAdminBundle")}">
                                      <i class="ki-outline ki-pencil fs-3"></i>
                                  </button>

                                  <button data-cred-id="${cred.id}" @click="${remove}"
                                          class="btn btn-light btn-sm"
                                          data-bs-toggle="tooltip"
                                          data-bs-placement="bottom"
                                          aria-label="${Translator.trans("action_delete", [], "SonataAdminBundle")}" 
                                          data-bs-original-title="${Translator.trans("action_delete", [], "SonataAdminBundle")}"
                                          title="${Translator.trans("action_delete", [], "SonataAdminBundle")}">
                                      <i class="ki-outline ki-trash fs-3 "></i>
                                  </button>
                              </div>
                          </li>`)}
                  </ul>` : (0,lit_html__WEBPACK_IMPORTED_MODULE_0__.html)`
                <ul class="list-group">
                    <li  class="list-group-item">${Translator.trans('passkey_create.no_credentials_found', [], 'security')}</li>
                  </ul>`}`;
        (0,lit_html__WEBPACK_IMPORTED_MODULE_0__.render)(creds, list);

        createPasskeyButtons.forEach((button) => {
            button.classList.remove('d-none');
        })
    }catch(e) {
        document.querySelector('#show-passkeys').classList.add('d-none')
        CMSAdmin.createInitCmsMessageBox('error', e.message);
    }
}

async function renderAutheticator() {
    try {
        let res = await (0,_client_js__WEBPACK_IMPORTED_MODULE_2__._fetch)(CMSRouting.generate('admin_networking_initcms_user_profile_security_get_authenticator'))
        const list = document.querySelector('#list-authenticator');
        createAuthenticatorButton.classList.toggle('d-none', res.hasStepVerificationCode);
        const creds = (0,lit_html__WEBPACK_IMPORTED_MODULE_0__.html)`${res.hasStepVerificationCode ? (0,lit_html__WEBPACK_IMPORTED_MODULE_0__.html)`
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div>
                        <img src="${res.qrCodeUrl}" class="w-150px">
                    </div>
                    <div class="w-75">
                        <div><strong>${Translator.trans('authenticator_edit.current_authenticator', [], 'security')}</strong></div>
                        <div class="text-muted">${res.secret}</div>
                    </div>
                    <div class="buttons">
                        <button id="edit-authenticator" type="button" @click="${editAuthenticator}"
                                class="btn btn-light btn-sm"
                                data-bs-toggle="tooltip"
                                data-bs-placement="bottom"
                                aria-label="${Translator.trans('action_edit', [], 'SonataAdminBundle')}"
                                data-bs-original-title="${Translator.trans('action_edit', [], 'SonataAdminBundle')}">
                            <i class="ki-outline ki-pencil fs-3 "></i>
                        </button>
                    </div>
                </li>
              </ul>` : (0,lit_html__WEBPACK_IMPORTED_MODULE_0__.html)`
            <ul class="list-group">
                <li class="list-group-item">${Translator.trans('authenticator_edit.no_authenticator_found', [], 'security')}</li>
              </ul>`}`;
        (0,lit_html__WEBPACK_IMPORTED_MODULE_0__.render)(creds, list);
    }catch(e) {
        CMSAdmin.createInitCmsMessageBox('error', e.message);
    }
}


async function register() {
    try {
        let username = document.querySelector('meta[name="username"]').getAttribute('content')
        let displayName = document.querySelector('meta[name="displayName"]').getAttribute('content')

        // Start the loading UI.

        // Start creating a passkey.
        await (0,_client_js__WEBPACK_IMPORTED_MODULE_2__.registerCredential)(username, displayName);

        // Stop the loading UI.

        // Render the updated passkey list.
        renderCredentials();
    } catch (e) {

        // Stop the loading UI.

        // An InvalidStateError indicates that a passkey already exists on the device.
        if (e.name === 'InvalidStateError') {
            Swal.fire({
                title: Translator.trans('passkey_create.passkey_exists_for_device_method', [], 'security'),
                html: `<div class="d-flex flex-column align-items-center">
                    <div class="stack w-150px position-relative">
                        <img src="/bundles/networkinginitcms/img/fido/face-id.svg" alt="WebAuthn" class="position-absolute top-50 w-25px">
                        <img src="/bundles/networkinginitcms/img/fido/fingerprint.svg" alt="WebAuthn" class="position-absolute top-25 end-0 w-30px">
                        <img src="/bundles/networkinginitcms/img/fido/passkey-local.svg" alt="WebAuthn" class="w-50px position-absolute top-50 start-50 translate-middle">
                        <img src="/bundles/networkinginitcms/img/fido/phone.svg" alt="WebAuthn" class="w-150px">
                        <img src="/bundles/networkinginitcms/img/fido/password.svg" alt="WebAuthn" class="w-30px position-absolute bottom-25 end-0">
                    </div>
                </div>
`,
                buttonsStyling: false,
                showCancelButton: false,
                confirmButtonText: Translator.trans('continue', [], 'security'),
                customClass: {
                    htmlContainer: 'h-100',
                    confirmButton: "btn btn-sm btn-success",
                }
            })

            // A NotAllowedError indicates that the user canceled the operation.
        } else if (e.name === 'NotAllowedError') {

            // Show other errors in an alert.
        } else {
            CMSAdmin.createInitCmsMessageBox('error', e.message);
        }
    }
}

function startRegistration() {
    Swal.fire({
        title: Translator.trans('passkey_create.title', [], 'security'),
        html: `<div class="d-flex flex-column align-items-center">
                    <div class="stack w-150px position-relative">
                        <img src="/bundles/networkinginitcms/img/fido/face-id.svg" alt="WebAuthn" class="position-absolute top-50 w-25px">
                        <img src="/bundles/networkinginitcms/img/fido/fingerprint.svg" alt="WebAuthn" class="position-absolute top-25 end-0 w-30px">
                        <img src="/bundles/networkinginitcms/img/fido/passkey-local.svg" alt="WebAuthn" class="w-50px position-absolute top-50 start-50 translate-middle">
                        <img src="/bundles/networkinginitcms/img/fido/phone.svg" alt="WebAuthn" class="w-150px">
                        <img src="/bundles/networkinginitcms/img/fido/password.svg" alt="WebAuthn" class="w-30px position-absolute bottom-25 end-0">
                    </div>
                    <p>${Translator.trans('passkey_create.text', [], 'security')}</p>
                </div>
`,
        buttonsStyling: false,
        showCancelButton: true,
        confirmButtonText: Translator.trans('continue', [], 'security'),
        cancelButtonText: Translator.trans('cancel', [], 'security'),
        customClass: {
            htmlContainer: 'h-100',
            confirmButton: "btn btn-sm btn-primary",
            cancelButton: "btn btn-sm btn-light"
        }
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            register()
        }
    });
}

async function createAuthenticator() {

    try{
        const authenticator = await (0,_client_js__WEBPACK_IMPORTED_MODULE_2__._fetch)(CMSRouting.generate('admin_networking_initcms_user_profile_security_create_authenticator'))

        let qrCodeUrl = authenticator.qrCodeUrl;
        let secret = authenticator.secret;

        Swal.fire({
            title: Translator.trans('authenticator_edit.setup_authenticator_title', [], 'security'),
            html: `<div class="d-flex flex-column align-items-center">
                    ${Translator.trans('authenticator_edit.setup_authenticator_text', [], 'security')}
                    <div class="stack w-150px position-relative">
                        <img src="${qrCodeUrl}"  class="w-150px">
                    </div>
                    
                    <p>${Translator.trans('authenticator_edit.setup_authenticator_text2', [], 'security')}</p>
                    
                    <div class="w-250px">
                        <code>${secret}</code>
                </div>
`,
            buttonsStyling: false,
            showCancelButton: true,
            confirmButtonText: Translator.trans('next', [], 'security'),
            cancelButtonText: Translator.trans('cancel', [], 'security'),
            customClass: {
                htmlContainer: 'h-100',
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-light"
            }
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {

                verifyCode();
            }
        });
    }catch (e) {
        CMSAdmin.createInitCmsMessageBox('error', e.message);
    }
}

async function verifyCode() {
    try{

        const { value: code, isDenied: isDenied } = await Swal.fire({
            title: Translator.trans('authenticator_edit.verify_code_title', [], 'security'),
            inputLabel: Translator.trans('authenticator_edit.verify_code_text', [], 'security'),
            input: 'text',
            inputAttributes: {
                autocomplete: 'one-time-code',
            },
            buttonsStyling: false,
            showCancelButton: true,
            showDenyButton: true,
            denyButtonText: Translator.trans('back', [], 'security'),
            confirmButtonText: Translator.trans('confirm', [], 'security'),
            cancelButtonText: Translator.trans('cancel', [], 'security'),
            reverseButtons: true,
            customClass: {
                denyButton: "btn btn-light-primary",
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-light"
            }
        })

        if(isDenied) {
            return createAuthenticator();
        }

        if (code) {
            let result =  await (0,_client_js__WEBPACK_IMPORTED_MODULE_2__._fetch)(CMSRouting.generate('admin_networking_initcms_user_profile_security_verify_authenticator'), {_code: code})

            if(result && 'ok' === result.status) {

                Swal.fire({
                    text: Translator.trans('test.success_text', [], 'security'),
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: Translator.trans('continue', [], 'security'),
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });

                renderAutheticator();
            }
        }
    }catch (e) {
        CMSAdmin.createInitCmsMessageBox('error', e.message);
    }
}

async function editAuthenticator() {
    try{
        const {isConfirmed: isConfirmed} = await Swal.fire({
            title: Translator.trans('authenticator_edit.change_authenticator_title', [], 'security'),
            text: Translator.trans('authenticator_edit.change_authenticator_text', [], 'security'),
            buttonsStyling: false,
            showCancelButton: true,
            confirmButtonText: Translator.trans('edit', [], 'security'),
            cancelButtonText: Translator.trans('cancel', [], 'security'),
            customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-light"
            }
        })

        if (isConfirmed) {
            createAuthenticator();
        }

    } catch (e) {
        CMSAdmin.createInitCmsMessageBox('error', e.message);
    }
}

async function testSignin() {

    const options = await (0,_client_js__WEBPACK_IMPORTED_MODULE_2__._fetch)('/admin/assertion/options', {
        requireUserVerification: 'preferred',
    });

    let asseResp;
    try {
        // Pass the options to the authenticator and wait for a response
        asseResp = await (0,_simplewebauthn_browser__WEBPACK_IMPORTED_MODULE_1__.startAuthentication)(options);
    } catch (e) {

        CMSAdmin.createInitCmsMessageBox('error', e.message);

        return;
    }

    try{
        const result = await (0,_client_js__WEBPACK_IMPORTED_MODULE_2__._fetch)('/admin/assertion', asseResp);

        if(result && 'ok' === result.status) {

            Swal.fire({
                text: Translator.trans('test.success_text', [], 'security'),
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: Translator.trans('continue', [], 'security'),
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });

            return;

        }

        throw new Error("User not found.");
    }catch(e){

        CMSAdmin.createInitCmsMessageBox('error', e.message);
    }
}

if(webauthnEnabled){

    if ((0,_simplewebauthn_browser__WEBPACK_IMPORTED_MODULE_1__.browserSupportsWebAuthn)() &&
        PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable &&
        PublicKeyCredential.isConditionalMediationAvailable) {
        try {
            const results = await Promise.all([

                // Is platform authenticator available in this browser?
                (0,_simplewebauthn_browser__WEBPACK_IMPORTED_MODULE_1__.platformAuthenticatorIsAvailable)(),

                // Is conditional UI available in this browser?
                (0,_simplewebauthn_browser__WEBPACK_IMPORTED_MODULE_1__.browserSupportsWebAuthnAutofill)()
            ]);


            if (results.every(r => r === true)) {
                renderCredentials();
            } else {

                // If conditional UI isn't available, show a message.
                document.querySelector('#message').innerText = Translator.trans('passkey_create.webauthn_not_available', [], 'security');
            }
        } catch (e) {
            CMSAdmin.createInitCmsMessageBox('error', e);
        }
    } else {

        // If WebAuthn isn't available, show a message.
        document.querySelector('#message').innerText = Translator.trans('passkey_create.webauthn_not_available', [], 'security')
    }
    createPasskeyButtons.forEach((button) => {
        button.addEventListener('click', startRegistration);
    });
    testSigninButton.addEventListener('click', testSignin);
}

if(googleAuthenticatorEnabled){
    renderAutheticator();
    createAuthenticatorButton.addEventListener('click', createAuthenticator);
}
__webpack_async_result__();
} catch(e) { __webpack_async_result__(e); } }, 1);

/***/ },

/***/ "./node_modules/@simplewebauthn/browser/esm/helpers/base64URLStringToBuffer.js"
/*!*************************************************************************************!*\
  !*** ./node_modules/@simplewebauthn/browser/esm/helpers/base64URLStringToBuffer.js ***!
  \*************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   base64URLStringToBuffer: () => (/* binding */ base64URLStringToBuffer)
/* harmony export */ });
/**
 * Convert from a Base64URL-encoded string to an Array Buffer. Best used when converting a
 * credential ID from a JSON string to an ArrayBuffer, like in allowCredentials or
 * excludeCredentials
 *
 * Helper method to compliment `bufferToBase64URLString`
 */
function base64URLStringToBuffer(base64URLString) {
    // Convert from Base64URL to Base64
    const base64 = base64URLString.replace(/-/g, '+').replace(/_/g, '/');
    /**
     * Pad with '=' until it's a multiple of four
     * (4 - (85 % 4 = 1) = 3) % 4 = 3 padding
     * (4 - (86 % 4 = 2) = 2) % 4 = 2 padding
     * (4 - (87 % 4 = 3) = 1) % 4 = 1 padding
     * (4 - (88 % 4 = 0) = 4) % 4 = 0 padding
     */
    const padLength = (4 - (base64.length % 4)) % 4;
    const padded = base64.padEnd(base64.length + padLength, '=');
    // Convert to a binary string
    const binary = atob(padded);
    // Convert binary string to buffer
    const buffer = new ArrayBuffer(binary.length);
    const bytes = new Uint8Array(buffer);
    for (let i = 0; i < binary.length; i++) {
        bytes[i] = binary.charCodeAt(i);
    }
    return buffer;
}


/***/ },

/***/ "./node_modules/@simplewebauthn/browser/esm/helpers/browserSupportsWebAuthn.js"
/*!*************************************************************************************!*\
  !*** ./node_modules/@simplewebauthn/browser/esm/helpers/browserSupportsWebAuthn.js ***!
  \*************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   _browserSupportsWebAuthnInternals: () => (/* binding */ _browserSupportsWebAuthnInternals),
/* harmony export */   browserSupportsWebAuthn: () => (/* binding */ browserSupportsWebAuthn)
/* harmony export */ });
/**
 * Determine if the browser is capable of Webauthn
 */
function browserSupportsWebAuthn() {
    return _browserSupportsWebAuthnInternals.stubThis(globalThis?.PublicKeyCredential !== undefined &&
        typeof globalThis.PublicKeyCredential === 'function');
}
/**
 * Make it possible to stub the return value during testing
 * @ignore Don't include this in docs output
 */
const _browserSupportsWebAuthnInternals = {
    stubThis: (value) => value,
};


/***/ },

/***/ "./node_modules/@simplewebauthn/browser/esm/helpers/browserSupportsWebAuthnAutofill.js"
/*!*********************************************************************************************!*\
  !*** ./node_modules/@simplewebauthn/browser/esm/helpers/browserSupportsWebAuthnAutofill.js ***!
  \*********************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   _browserSupportsWebAuthnAutofillInternals: () => (/* binding */ _browserSupportsWebAuthnAutofillInternals),
/* harmony export */   browserSupportsWebAuthnAutofill: () => (/* binding */ browserSupportsWebAuthnAutofill)
/* harmony export */ });
/* harmony import */ var _browserSupportsWebAuthn_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./browserSupportsWebAuthn.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/browserSupportsWebAuthn.js");

/**
 * Determine if the browser supports conditional UI, so that WebAuthn credentials can
 * be shown to the user in the browser's typical password autofill popup.
 */
function browserSupportsWebAuthnAutofill() {
    if (!(0,_browserSupportsWebAuthn_js__WEBPACK_IMPORTED_MODULE_0__.browserSupportsWebAuthn)()) {
        return _browserSupportsWebAuthnAutofillInternals.stubThis(new Promise((resolve) => resolve(false)));
    }
    /**
     * I don't like the `as unknown` here but there's a `declare var PublicKeyCredential` in
     * TS' DOM lib that's making it difficult for me to just go `as PublicKeyCredentialFuture` as I
     * want. I think I'm fine with this for now since it's _supposed_ to be temporary, until TS types
     * have a chance to catch up.
     */
    const globalPublicKeyCredential = globalThis
        .PublicKeyCredential;
    if (globalPublicKeyCredential?.isConditionalMediationAvailable === undefined) {
        return _browserSupportsWebAuthnAutofillInternals.stubThis(new Promise((resolve) => resolve(false)));
    }
    return _browserSupportsWebAuthnAutofillInternals.stubThis(globalPublicKeyCredential.isConditionalMediationAvailable());
}
// Make it possible to stub the return value during testing
const _browserSupportsWebAuthnAutofillInternals = {
    stubThis: (value) => value,
};


/***/ },

/***/ "./node_modules/@simplewebauthn/browser/esm/helpers/bufferToBase64URLString.js"
/*!*************************************************************************************!*\
  !*** ./node_modules/@simplewebauthn/browser/esm/helpers/bufferToBase64URLString.js ***!
  \*************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   bufferToBase64URLString: () => (/* binding */ bufferToBase64URLString)
/* harmony export */ });
/**
 * Convert the given array buffer into a Base64URL-encoded string. Ideal for converting various
 * credential response ArrayBuffers to string for sending back to the server as JSON.
 *
 * Helper method to compliment `base64URLStringToBuffer`
 */
function bufferToBase64URLString(buffer) {
    const bytes = new Uint8Array(buffer);
    let str = '';
    for (const charCode of bytes) {
        str += String.fromCharCode(charCode);
    }
    const base64String = btoa(str);
    return base64String.replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
}


/***/ },

/***/ "./node_modules/@simplewebauthn/browser/esm/helpers/identifyAuthenticationError.js"
/*!*****************************************************************************************!*\
  !*** ./node_modules/@simplewebauthn/browser/esm/helpers/identifyAuthenticationError.js ***!
  \*****************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   identifyAuthenticationError: () => (/* binding */ identifyAuthenticationError)
/* harmony export */ });
/* harmony import */ var _isValidDomain_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./isValidDomain.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/isValidDomain.js");
/* harmony import */ var _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./webAuthnError.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/webAuthnError.js");


/**
 * Attempt to intuit _why_ an error was raised after calling `navigator.credentials.get()`
 */
function identifyAuthenticationError({ error, options, }) {
    const { publicKey } = options;
    if (!publicKey) {
        throw Error('options was missing required publicKey property');
    }
    if (error.name === 'AbortError') {
        if (options.signal instanceof AbortSignal) {
            // https://www.w3.org/TR/webauthn-2/#sctn-createCredential (Step 16)
            return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
                message: 'Authentication ceremony was sent an abort signal',
                code: 'ERROR_CEREMONY_ABORTED',
                cause: error,
            });
        }
    }
    else if (error.name === 'NotAllowedError') {
        /**
         * Pass the error directly through. Platforms are overloading this error beyond what the spec
         * defines and we don't want to overwrite potentially useful error messages.
         */
        return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
            message: error.message,
            code: 'ERROR_PASSTHROUGH_SEE_CAUSE_PROPERTY',
            cause: error,
        });
    }
    else if (error.name === 'SecurityError') {
        const effectiveDomain = globalThis.location.hostname;
        if (!(0,_isValidDomain_js__WEBPACK_IMPORTED_MODULE_0__.isValidDomain)(effectiveDomain)) {
            // https://www.w3.org/TR/webauthn-2/#sctn-discover-from-external-source (Step 5)
            return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
                message: `${globalThis.location.hostname} is an invalid domain`,
                code: 'ERROR_INVALID_DOMAIN',
                cause: error,
            });
        }
        else if (publicKey.rpId !== effectiveDomain) {
            // https://www.w3.org/TR/webauthn-2/#sctn-discover-from-external-source (Step 6)
            return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
                message: `The RP ID "${publicKey.rpId}" is invalid for this domain`,
                code: 'ERROR_INVALID_RP_ID',
                cause: error,
            });
        }
    }
    else if (error.name === 'UnknownError') {
        // https://www.w3.org/TR/webauthn-2/#sctn-op-get-assertion (Step 1)
        // https://www.w3.org/TR/webauthn-2/#sctn-op-get-assertion (Step 12)
        return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
            message: 'The authenticator was unable to process the specified options, or could not create a new assertion signature',
            code: 'ERROR_AUTHENTICATOR_GENERAL_ERROR',
            cause: error,
        });
    }
    return error;
}


/***/ },

/***/ "./node_modules/@simplewebauthn/browser/esm/helpers/identifyRegistrationError.js"
/*!***************************************************************************************!*\
  !*** ./node_modules/@simplewebauthn/browser/esm/helpers/identifyRegistrationError.js ***!
  \***************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   identifyRegistrationError: () => (/* binding */ identifyRegistrationError)
/* harmony export */ });
/* harmony import */ var _isValidDomain_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./isValidDomain.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/isValidDomain.js");
/* harmony import */ var _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./webAuthnError.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/webAuthnError.js");


/**
 * Attempt to intuit _why_ an error was raised after calling `navigator.credentials.create()`
 */
function identifyRegistrationError({ error, options, }) {
    const { publicKey } = options;
    if (!publicKey) {
        throw Error('options was missing required publicKey property');
    }
    if (error.name === 'AbortError') {
        if (options.signal instanceof AbortSignal) {
            // https://www.w3.org/TR/webauthn-2/#sctn-createCredential (Step 16)
            return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
                message: 'Registration ceremony was sent an abort signal',
                code: 'ERROR_CEREMONY_ABORTED',
                cause: error,
            });
        }
    }
    else if (error.name === 'ConstraintError') {
        if (publicKey.authenticatorSelection?.requireResidentKey === true) {
            // https://www.w3.org/TR/webauthn-2/#sctn-op-make-cred (Step 4)
            return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
                message: 'Discoverable credentials were required but no available authenticator supported it',
                code: 'ERROR_AUTHENTICATOR_MISSING_DISCOVERABLE_CREDENTIAL_SUPPORT',
                cause: error,
            });
        }
        else if (
        // @ts-ignore: `mediation` doesn't yet exist on CredentialCreationOptions but it's possible as of Sept 2024
        options.mediation === 'conditional' &&
            publicKey.authenticatorSelection?.userVerification === 'required') {
            // https://w3c.github.io/webauthn/#sctn-createCredential (Step 22.4)
            return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
                message: 'User verification was required during automatic registration but it could not be performed',
                code: 'ERROR_AUTO_REGISTER_USER_VERIFICATION_FAILURE',
                cause: error,
            });
        }
        else if (publicKey.authenticatorSelection?.userVerification === 'required') {
            // https://www.w3.org/TR/webauthn-2/#sctn-op-make-cred (Step 5)
            return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
                message: 'User verification was required but no available authenticator supported it',
                code: 'ERROR_AUTHENTICATOR_MISSING_USER_VERIFICATION_SUPPORT',
                cause: error,
            });
        }
    }
    else if (error.name === 'InvalidStateError') {
        // https://www.w3.org/TR/webauthn-2/#sctn-createCredential (Step 20)
        // https://www.w3.org/TR/webauthn-2/#sctn-op-make-cred (Step 3)
        return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
            message: 'The authenticator was previously registered',
            code: 'ERROR_AUTHENTICATOR_PREVIOUSLY_REGISTERED',
            cause: error,
        });
    }
    else if (error.name === 'NotAllowedError') {
        /**
         * Pass the error directly through. Platforms are overloading this error beyond what the spec
         * defines and we don't want to overwrite potentially useful error messages.
         */
        return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
            message: error.message,
            code: 'ERROR_PASSTHROUGH_SEE_CAUSE_PROPERTY',
            cause: error,
        });
    }
    else if (error.name === 'NotSupportedError') {
        const validPubKeyCredParams = publicKey.pubKeyCredParams.filter((param) => param.type === 'public-key');
        if (validPubKeyCredParams.length === 0) {
            // https://www.w3.org/TR/webauthn-2/#sctn-createCredential (Step 10)
            return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
                message: 'No entry in pubKeyCredParams was of type "public-key"',
                code: 'ERROR_MALFORMED_PUBKEYCREDPARAMS',
                cause: error,
            });
        }
        // https://www.w3.org/TR/webauthn-2/#sctn-op-make-cred (Step 2)
        return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
            message: 'No available authenticator supported any of the specified pubKeyCredParams algorithms',
            code: 'ERROR_AUTHENTICATOR_NO_SUPPORTED_PUBKEYCREDPARAMS_ALG',
            cause: error,
        });
    }
    else if (error.name === 'SecurityError') {
        const effectiveDomain = globalThis.location.hostname;
        if (!(0,_isValidDomain_js__WEBPACK_IMPORTED_MODULE_0__.isValidDomain)(effectiveDomain)) {
            // https://www.w3.org/TR/webauthn-2/#sctn-createCredential (Step 7)
            return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
                message: `${globalThis.location.hostname} is an invalid domain`,
                code: 'ERROR_INVALID_DOMAIN',
                cause: error,
            });
        }
        else if (publicKey.rp.id !== effectiveDomain) {
            // https://www.w3.org/TR/webauthn-2/#sctn-createCredential (Step 8)
            return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
                message: `The RP ID "${publicKey.rp.id}" is invalid for this domain`,
                code: 'ERROR_INVALID_RP_ID',
                cause: error,
            });
        }
    }
    else if (error.name === 'TypeError') {
        if (publicKey.user.id.byteLength < 1 || publicKey.user.id.byteLength > 64) {
            // https://www.w3.org/TR/webauthn-2/#sctn-createCredential (Step 5)
            return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
                message: 'User ID was not between 1 and 64 characters',
                code: 'ERROR_INVALID_USER_ID_LENGTH',
                cause: error,
            });
        }
    }
    else if (error.name === 'UnknownError') {
        // https://www.w3.org/TR/webauthn-2/#sctn-op-make-cred (Step 1)
        // https://www.w3.org/TR/webauthn-2/#sctn-op-make-cred (Step 8)
        return new _webAuthnError_js__WEBPACK_IMPORTED_MODULE_1__.WebAuthnError({
            message: 'The authenticator was unable to process the specified options, or could not create a new credential',
            code: 'ERROR_AUTHENTICATOR_GENERAL_ERROR',
            cause: error,
        });
    }
    return error;
}


/***/ },

/***/ "./node_modules/@simplewebauthn/browser/esm/helpers/isValidDomain.js"
/*!***************************************************************************!*\
  !*** ./node_modules/@simplewebauthn/browser/esm/helpers/isValidDomain.js ***!
  \***************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   isValidDomain: () => (/* binding */ isValidDomain)
/* harmony export */ });
/**
 * A simple test to determine if a hostname is a properly-formatted domain name
 *
 * A "valid domain" is defined here: https://url.spec.whatwg.org/#valid-domain
 *
 * Regex sourced from here:
 * https://www.oreilly.com/library/view/regular-expressions-cookbook/9781449327453/ch08s15.html
 */
function isValidDomain(hostname) {
    return (
    // Consider localhost valid as well since it's okay wrt Secure Contexts
    hostname === 'localhost' ||
        /^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i.test(hostname));
}


/***/ },

/***/ "./node_modules/@simplewebauthn/browser/esm/helpers/platformAuthenticatorIsAvailable.js"
/*!**********************************************************************************************!*\
  !*** ./node_modules/@simplewebauthn/browser/esm/helpers/platformAuthenticatorIsAvailable.js ***!
  \**********************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   platformAuthenticatorIsAvailable: () => (/* binding */ platformAuthenticatorIsAvailable)
/* harmony export */ });
/* harmony import */ var _browserSupportsWebAuthn_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./browserSupportsWebAuthn.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/browserSupportsWebAuthn.js");

/**
 * Determine whether the browser can communicate with a built-in authenticator, like
 * Touch ID, Android fingerprint scanner, or Windows Hello.
 *
 * This method will _not_ be able to tell you the name of the platform authenticator.
 */
function platformAuthenticatorIsAvailable() {
    if (!(0,_browserSupportsWebAuthn_js__WEBPACK_IMPORTED_MODULE_0__.browserSupportsWebAuthn)()) {
        return new Promise((resolve) => resolve(false));
    }
    return PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
}


/***/ },

/***/ "./node_modules/@simplewebauthn/browser/esm/helpers/toAuthenticatorAttachment.js"
/*!***************************************************************************************!*\
  !*** ./node_modules/@simplewebauthn/browser/esm/helpers/toAuthenticatorAttachment.js ***!
  \***************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   toAuthenticatorAttachment: () => (/* binding */ toAuthenticatorAttachment)
/* harmony export */ });
const attachments = ['cross-platform', 'platform'];
/**
 * If possible coerce a `string` value into a known `AuthenticatorAttachment`
 */
function toAuthenticatorAttachment(attachment) {
    if (!attachment) {
        return;
    }
    if (attachments.indexOf(attachment) < 0) {
        return;
    }
    return attachment;
}


/***/ },

/***/ "./node_modules/@simplewebauthn/browser/esm/helpers/toPublicKeyCredentialDescriptor.js"
/*!*********************************************************************************************!*\
  !*** ./node_modules/@simplewebauthn/browser/esm/helpers/toPublicKeyCredentialDescriptor.js ***!
  \*********************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   toPublicKeyCredentialDescriptor: () => (/* binding */ toPublicKeyCredentialDescriptor)
/* harmony export */ });
/* harmony import */ var _base64URLStringToBuffer_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./base64URLStringToBuffer.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/base64URLStringToBuffer.js");

function toPublicKeyCredentialDescriptor(descriptor) {
    const { id } = descriptor;
    return {
        ...descriptor,
        id: (0,_base64URLStringToBuffer_js__WEBPACK_IMPORTED_MODULE_0__.base64URLStringToBuffer)(id),
        /**
         * `descriptor.transports` is an array of our `AuthenticatorTransportFuture` that includes newer
         * transports that TypeScript's DOM lib is ignorant of. Convince TS that our list of transports
         * are fine to pass to WebAuthn since browsers will recognize the new value.
         */
        transports: descriptor.transports,
    };
}


/***/ },

/***/ "./node_modules/@simplewebauthn/browser/esm/helpers/webAuthnAbortService.js"
/*!**********************************************************************************!*\
  !*** ./node_modules/@simplewebauthn/browser/esm/helpers/webAuthnAbortService.js ***!
  \**********************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   WebAuthnAbortService: () => (/* binding */ WebAuthnAbortService)
/* harmony export */ });
class BaseWebAuthnAbortService {
    constructor() {
        Object.defineProperty(this, "controller", {
            enumerable: true,
            configurable: true,
            writable: true,
            value: void 0
        });
    }
    createNewAbortSignal() {
        // Abort any existing calls to navigator.credentials.create() or navigator.credentials.get()
        if (this.controller) {
            const abortError = new Error('Cancelling existing WebAuthn API call for new one');
            abortError.name = 'AbortError';
            this.controller.abort(abortError);
        }
        const newController = new AbortController();
        this.controller = newController;
        return newController.signal;
    }
    cancelCeremony() {
        if (this.controller) {
            const abortError = new Error('Manually cancelling existing WebAuthn API call');
            abortError.name = 'AbortError';
            this.controller.abort(abortError);
            this.controller = undefined;
        }
    }
}
/**
 * A service singleton to help ensure that only a single WebAuthn ceremony is active at a time.
 *
 * Users of **@simplewebauthn/browser** shouldn't typically need to use this, but it can help e.g.
 * developers building projects that use client-side routing to better control the behavior of
 * their UX in response to router navigation events.
 */
const WebAuthnAbortService = new BaseWebAuthnAbortService();


/***/ },

/***/ "./node_modules/@simplewebauthn/browser/esm/helpers/webAuthnError.js"
/*!***************************************************************************!*\
  !*** ./node_modules/@simplewebauthn/browser/esm/helpers/webAuthnError.js ***!
  \***************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   WebAuthnError: () => (/* binding */ WebAuthnError)
/* harmony export */ });
/**
 * A custom Error used to return a more nuanced error detailing _why_ one of the eight documented
 * errors in the spec was raised after calling `navigator.credentials.create()` or
 * `navigator.credentials.get()`:
 *
 * - `AbortError`
 * - `ConstraintError`
 * - `InvalidStateError`
 * - `NotAllowedError`
 * - `NotSupportedError`
 * - `SecurityError`
 * - `TypeError`
 * - `UnknownError`
 *
 * Error messages were determined through investigation of the spec to determine under which
 * scenarios a given error would be raised.
 */
class WebAuthnError extends Error {
    constructor({ message, code, cause, name, }) {
        // @ts-ignore: help Rollup understand that `cause` is okay to set
        super(message, { cause });
        Object.defineProperty(this, "code", {
            enumerable: true,
            configurable: true,
            writable: true,
            value: void 0
        });
        this.name = name ?? cause.name;
        this.code = code;
    }
}


/***/ },

/***/ "./node_modules/@simplewebauthn/browser/esm/index.js"
/*!***********************************************************!*\
  !*** ./node_modules/@simplewebauthn/browser/esm/index.js ***!
  \***********************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   WebAuthnAbortService: () => (/* reexport safe */ _helpers_webAuthnAbortService_js__WEBPACK_IMPORTED_MODULE_7__.WebAuthnAbortService),
/* harmony export */   WebAuthnError: () => (/* reexport safe */ _helpers_webAuthnError_js__WEBPACK_IMPORTED_MODULE_8__.WebAuthnError),
/* harmony export */   _browserSupportsWebAuthnAutofillInternals: () => (/* reexport safe */ _helpers_browserSupportsWebAuthnAutofill_js__WEBPACK_IMPORTED_MODULE_4__._browserSupportsWebAuthnAutofillInternals),
/* harmony export */   _browserSupportsWebAuthnInternals: () => (/* reexport safe */ _helpers_browserSupportsWebAuthn_js__WEBPACK_IMPORTED_MODULE_2__._browserSupportsWebAuthnInternals),
/* harmony export */   base64URLStringToBuffer: () => (/* reexport safe */ _helpers_base64URLStringToBuffer_js__WEBPACK_IMPORTED_MODULE_5__.base64URLStringToBuffer),
/* harmony export */   browserSupportsWebAuthn: () => (/* reexport safe */ _helpers_browserSupportsWebAuthn_js__WEBPACK_IMPORTED_MODULE_2__.browserSupportsWebAuthn),
/* harmony export */   browserSupportsWebAuthnAutofill: () => (/* reexport safe */ _helpers_browserSupportsWebAuthnAutofill_js__WEBPACK_IMPORTED_MODULE_4__.browserSupportsWebAuthnAutofill),
/* harmony export */   bufferToBase64URLString: () => (/* reexport safe */ _helpers_bufferToBase64URLString_js__WEBPACK_IMPORTED_MODULE_6__.bufferToBase64URLString),
/* harmony export */   platformAuthenticatorIsAvailable: () => (/* reexport safe */ _helpers_platformAuthenticatorIsAvailable_js__WEBPACK_IMPORTED_MODULE_3__.platformAuthenticatorIsAvailable),
/* harmony export */   startAuthentication: () => (/* reexport safe */ _methods_startAuthentication_js__WEBPACK_IMPORTED_MODULE_1__.startAuthentication),
/* harmony export */   startRegistration: () => (/* reexport safe */ _methods_startRegistration_js__WEBPACK_IMPORTED_MODULE_0__.startRegistration)
/* harmony export */ });
/* harmony import */ var _methods_startRegistration_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./methods/startRegistration.js */ "./node_modules/@simplewebauthn/browser/esm/methods/startRegistration.js");
/* harmony import */ var _methods_startAuthentication_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./methods/startAuthentication.js */ "./node_modules/@simplewebauthn/browser/esm/methods/startAuthentication.js");
/* harmony import */ var _helpers_browserSupportsWebAuthn_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./helpers/browserSupportsWebAuthn.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/browserSupportsWebAuthn.js");
/* harmony import */ var _helpers_platformAuthenticatorIsAvailable_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./helpers/platformAuthenticatorIsAvailable.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/platformAuthenticatorIsAvailable.js");
/* harmony import */ var _helpers_browserSupportsWebAuthnAutofill_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./helpers/browserSupportsWebAuthnAutofill.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/browserSupportsWebAuthnAutofill.js");
/* harmony import */ var _helpers_base64URLStringToBuffer_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./helpers/base64URLStringToBuffer.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/base64URLStringToBuffer.js");
/* harmony import */ var _helpers_bufferToBase64URLString_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./helpers/bufferToBase64URLString.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/bufferToBase64URLString.js");
/* harmony import */ var _helpers_webAuthnAbortService_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./helpers/webAuthnAbortService.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/webAuthnAbortService.js");
/* harmony import */ var _helpers_webAuthnError_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./helpers/webAuthnError.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/webAuthnError.js");
/* harmony import */ var _types_index_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./types/index.js */ "./node_modules/@simplewebauthn/browser/esm/types/index.js");












/***/ },

/***/ "./node_modules/@simplewebauthn/browser/esm/methods/startAuthentication.js"
/*!*********************************************************************************!*\
  !*** ./node_modules/@simplewebauthn/browser/esm/methods/startAuthentication.js ***!
  \*********************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   startAuthentication: () => (/* binding */ startAuthentication)
/* harmony export */ });
/* harmony import */ var _helpers_bufferToBase64URLString_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../helpers/bufferToBase64URLString.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/bufferToBase64URLString.js");
/* harmony import */ var _helpers_base64URLStringToBuffer_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helpers/base64URLStringToBuffer.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/base64URLStringToBuffer.js");
/* harmony import */ var _helpers_browserSupportsWebAuthn_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../helpers/browserSupportsWebAuthn.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/browserSupportsWebAuthn.js");
/* harmony import */ var _helpers_browserSupportsWebAuthnAutofill_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../helpers/browserSupportsWebAuthnAutofill.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/browserSupportsWebAuthnAutofill.js");
/* harmony import */ var _helpers_toPublicKeyCredentialDescriptor_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../helpers/toPublicKeyCredentialDescriptor.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/toPublicKeyCredentialDescriptor.js");
/* harmony import */ var _helpers_identifyAuthenticationError_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../helpers/identifyAuthenticationError.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/identifyAuthenticationError.js");
/* harmony import */ var _helpers_webAuthnAbortService_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../helpers/webAuthnAbortService.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/webAuthnAbortService.js");
/* harmony import */ var _helpers_toAuthenticatorAttachment_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../helpers/toAuthenticatorAttachment.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/toAuthenticatorAttachment.js");








/**
 * Begin authenticator "login" via WebAuthn assertion
 *
 * @param optionsJSON Output from **@simplewebauthn/server**'s `generateAuthenticationOptions()`
 * @param useBrowserAutofill (Optional) Initialize conditional UI to enable logging in via browser autofill prompts. Defaults to `false`.
 * @param verifyBrowserAutofillInput (Optional) Ensure a suitable `<input>` element is present when `useBrowserAutofill` is `true`. Defaults to `true`.
 */
async function startAuthentication(options) {
    // @ts-ignore: Intentionally check for old call structure to warn about improper API call
    if (!options.optionsJSON && options.challenge) {
        console.warn('startAuthentication() was not called correctly. It will try to continue with the provided options, but this call should be refactored to use the expected call structure instead. See https://simplewebauthn.dev/docs/packages/browser#typeerror-cannot-read-properties-of-undefined-reading-challenge for more information.');
        // @ts-ignore: Reassign the options, passed in as a positional argument, to the expected variable
        options = { optionsJSON: options };
    }
    const { optionsJSON, useBrowserAutofill = false, verifyBrowserAutofillInput = true, } = options;
    if (!(0,_helpers_browserSupportsWebAuthn_js__WEBPACK_IMPORTED_MODULE_2__.browserSupportsWebAuthn)()) {
        throw new Error('WebAuthn is not supported in this browser');
    }
    // We need to avoid passing empty array to avoid blocking retrieval
    // of public key
    let allowCredentials;
    if (optionsJSON.allowCredentials?.length !== 0) {
        allowCredentials = optionsJSON.allowCredentials?.map(_helpers_toPublicKeyCredentialDescriptor_js__WEBPACK_IMPORTED_MODULE_4__.toPublicKeyCredentialDescriptor);
    }
    // We need to convert some values to Uint8Arrays before passing the credentials to the navigator
    const publicKey = {
        ...optionsJSON,
        challenge: (0,_helpers_base64URLStringToBuffer_js__WEBPACK_IMPORTED_MODULE_1__.base64URLStringToBuffer)(optionsJSON.challenge),
        allowCredentials,
    };
    // Prepare options for `.get()`
    const getOptions = {};
    /**
     * Set up the page to prompt the user to select a credential for authentication via the browser's
     * input autofill mechanism.
     */
    if (useBrowserAutofill) {
        if (!(await (0,_helpers_browserSupportsWebAuthnAutofill_js__WEBPACK_IMPORTED_MODULE_3__.browserSupportsWebAuthnAutofill)())) {
            throw Error('Browser does not support WebAuthn autofill');
        }
        // Check for an <input> with "webauthn" in its `autocomplete` attribute
        const eligibleInputs = document.querySelectorAll("input[autocomplete$='webauthn']");
        // WebAuthn autofill requires at least one valid input
        if (eligibleInputs.length < 1 && verifyBrowserAutofillInput) {
            throw Error('No <input> with "webauthn" as the only or last value in its `autocomplete` attribute was detected');
        }
        // `CredentialMediationRequirement` doesn't know about "conditional" yet as of
        // typescript@4.6.3
        getOptions.mediation = 'conditional';
        // Conditional UI requires an empty allow list
        publicKey.allowCredentials = [];
    }
    // Finalize options
    getOptions.publicKey = publicKey;
    // Set up the ability to cancel this request if the user attempts another
    getOptions.signal = _helpers_webAuthnAbortService_js__WEBPACK_IMPORTED_MODULE_6__.WebAuthnAbortService.createNewAbortSignal();
    // Wait for the user to complete assertion
    let credential;
    try {
        credential = (await navigator.credentials.get(getOptions));
    }
    catch (err) {
        throw (0,_helpers_identifyAuthenticationError_js__WEBPACK_IMPORTED_MODULE_5__.identifyAuthenticationError)({ error: err, options: getOptions });
    }
    if (!credential) {
        throw new Error('Authentication was not completed');
    }
    const { id, rawId, response, type } = credential;
    let userHandle = undefined;
    if (response.userHandle) {
        userHandle = (0,_helpers_bufferToBase64URLString_js__WEBPACK_IMPORTED_MODULE_0__.bufferToBase64URLString)(response.userHandle);
    }
    // Convert values to base64 to make it easier to send back to the server
    return {
        id,
        rawId: (0,_helpers_bufferToBase64URLString_js__WEBPACK_IMPORTED_MODULE_0__.bufferToBase64URLString)(rawId),
        response: {
            authenticatorData: (0,_helpers_bufferToBase64URLString_js__WEBPACK_IMPORTED_MODULE_0__.bufferToBase64URLString)(response.authenticatorData),
            clientDataJSON: (0,_helpers_bufferToBase64URLString_js__WEBPACK_IMPORTED_MODULE_0__.bufferToBase64URLString)(response.clientDataJSON),
            signature: (0,_helpers_bufferToBase64URLString_js__WEBPACK_IMPORTED_MODULE_0__.bufferToBase64URLString)(response.signature),
            userHandle,
        },
        type,
        clientExtensionResults: credential.getClientExtensionResults(),
        authenticatorAttachment: (0,_helpers_toAuthenticatorAttachment_js__WEBPACK_IMPORTED_MODULE_7__.toAuthenticatorAttachment)(credential.authenticatorAttachment),
    };
}


/***/ },

/***/ "./node_modules/@simplewebauthn/browser/esm/methods/startRegistration.js"
/*!*******************************************************************************!*\
  !*** ./node_modules/@simplewebauthn/browser/esm/methods/startRegistration.js ***!
  \*******************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   startRegistration: () => (/* binding */ startRegistration)
/* harmony export */ });
/* harmony import */ var _helpers_bufferToBase64URLString_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../helpers/bufferToBase64URLString.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/bufferToBase64URLString.js");
/* harmony import */ var _helpers_base64URLStringToBuffer_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helpers/base64URLStringToBuffer.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/base64URLStringToBuffer.js");
/* harmony import */ var _helpers_browserSupportsWebAuthn_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../helpers/browserSupportsWebAuthn.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/browserSupportsWebAuthn.js");
/* harmony import */ var _helpers_toPublicKeyCredentialDescriptor_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../helpers/toPublicKeyCredentialDescriptor.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/toPublicKeyCredentialDescriptor.js");
/* harmony import */ var _helpers_identifyRegistrationError_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../helpers/identifyRegistrationError.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/identifyRegistrationError.js");
/* harmony import */ var _helpers_webAuthnAbortService_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../helpers/webAuthnAbortService.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/webAuthnAbortService.js");
/* harmony import */ var _helpers_toAuthenticatorAttachment_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../helpers/toAuthenticatorAttachment.js */ "./node_modules/@simplewebauthn/browser/esm/helpers/toAuthenticatorAttachment.js");







/**
 * Begin authenticator "registration" via WebAuthn attestation
 *
 * @param optionsJSON Output from **@simplewebauthn/server**'s `generateRegistrationOptions()`
 * @param useAutoRegister (Optional) Try to silently create a passkey with the password manager that the user just signed in with. Defaults to `false`.
 */
async function startRegistration(options) {
    // @ts-ignore: Intentionally check for old call structure to warn about improper API call
    if (!options.optionsJSON && options.challenge) {
        console.warn('startRegistration() was not called correctly. It will try to continue with the provided options, but this call should be refactored to use the expected call structure instead. See https://simplewebauthn.dev/docs/packages/browser#typeerror-cannot-read-properties-of-undefined-reading-challenge for more information.');
        // @ts-ignore: Reassign the options, passed in as a positional argument, to the expected variable
        options = { optionsJSON: options };
    }
    const { optionsJSON, useAutoRegister = false } = options;
    if (!(0,_helpers_browserSupportsWebAuthn_js__WEBPACK_IMPORTED_MODULE_2__.browserSupportsWebAuthn)()) {
        throw new Error('WebAuthn is not supported in this browser');
    }
    // We need to convert some values to Uint8Arrays before passing the credentials to the navigator
    const publicKey = {
        ...optionsJSON,
        challenge: (0,_helpers_base64URLStringToBuffer_js__WEBPACK_IMPORTED_MODULE_1__.base64URLStringToBuffer)(optionsJSON.challenge),
        user: {
            ...optionsJSON.user,
            id: (0,_helpers_base64URLStringToBuffer_js__WEBPACK_IMPORTED_MODULE_1__.base64URLStringToBuffer)(optionsJSON.user.id),
        },
        excludeCredentials: optionsJSON.excludeCredentials?.map(_helpers_toPublicKeyCredentialDescriptor_js__WEBPACK_IMPORTED_MODULE_3__.toPublicKeyCredentialDescriptor),
    };
    // Prepare options for `.create()`
    const createOptions = {};
    /**
     * Try to use conditional create to register a passkey for the user with the password manager
     * the user just used to authenticate with. The user won't be shown any prominent UI by the
     * browser.
     */
    if (useAutoRegister) {
        // @ts-ignore: `mediation` doesn't yet exist on CredentialCreationOptions but it's possible as of Sept 2024
        createOptions.mediation = 'conditional';
    }
    // Finalize options
    createOptions.publicKey = publicKey;
    // Set up the ability to cancel this request if the user attempts another
    createOptions.signal = _helpers_webAuthnAbortService_js__WEBPACK_IMPORTED_MODULE_5__.WebAuthnAbortService.createNewAbortSignal();
    // Wait for the user to complete attestation
    let credential;
    try {
        credential = (await navigator.credentials.create(createOptions));
    }
    catch (err) {
        throw (0,_helpers_identifyRegistrationError_js__WEBPACK_IMPORTED_MODULE_4__.identifyRegistrationError)({ error: err, options: createOptions });
    }
    if (!credential) {
        throw new Error('Registration was not completed');
    }
    const { id, rawId, response, type } = credential;
    // Continue to play it safe with `getTransports()` for now, even when L3 types say it's required
    let transports = undefined;
    if (typeof response.getTransports === 'function') {
        transports = response.getTransports();
    }
    // L3 says this is required, but browser and webview support are still not guaranteed.
    let responsePublicKeyAlgorithm = undefined;
    if (typeof response.getPublicKeyAlgorithm === 'function') {
        try {
            responsePublicKeyAlgorithm = response.getPublicKeyAlgorithm();
        }
        catch (error) {
            warnOnBrokenImplementation('getPublicKeyAlgorithm()', error);
        }
    }
    let responsePublicKey = undefined;
    if (typeof response.getPublicKey === 'function') {
        try {
            const _publicKey = response.getPublicKey();
            if (_publicKey !== null) {
                responsePublicKey = (0,_helpers_bufferToBase64URLString_js__WEBPACK_IMPORTED_MODULE_0__.bufferToBase64URLString)(_publicKey);
            }
        }
        catch (error) {
            warnOnBrokenImplementation('getPublicKey()', error);
        }
    }
    // L3 says this is required, but browser and webview support are still not guaranteed.
    let responseAuthenticatorData;
    if (typeof response.getAuthenticatorData === 'function') {
        try {
            responseAuthenticatorData = (0,_helpers_bufferToBase64URLString_js__WEBPACK_IMPORTED_MODULE_0__.bufferToBase64URLString)(response.getAuthenticatorData());
        }
        catch (error) {
            warnOnBrokenImplementation('getAuthenticatorData()', error);
        }
    }
    return {
        id,
        rawId: (0,_helpers_bufferToBase64URLString_js__WEBPACK_IMPORTED_MODULE_0__.bufferToBase64URLString)(rawId),
        response: {
            attestationObject: (0,_helpers_bufferToBase64URLString_js__WEBPACK_IMPORTED_MODULE_0__.bufferToBase64URLString)(response.attestationObject),
            clientDataJSON: (0,_helpers_bufferToBase64URLString_js__WEBPACK_IMPORTED_MODULE_0__.bufferToBase64URLString)(response.clientDataJSON),
            transports,
            publicKeyAlgorithm: responsePublicKeyAlgorithm,
            publicKey: responsePublicKey,
            authenticatorData: responseAuthenticatorData,
        },
        type,
        clientExtensionResults: credential.getClientExtensionResults(),
        authenticatorAttachment: (0,_helpers_toAuthenticatorAttachment_js__WEBPACK_IMPORTED_MODULE_6__.toAuthenticatorAttachment)(credential.authenticatorAttachment),
    };
}
/**
 * Visibly warn when we detect an issue related to a passkey provider intercepting WebAuthn API
 * calls
 */
function warnOnBrokenImplementation(methodName, cause) {
    console.warn(`The browser extension that intercepted this WebAuthn API call incorrectly implemented ${methodName}. You should report this error to them.\n`, cause);
}


/***/ },

/***/ "./node_modules/@simplewebauthn/browser/esm/types/index.js"
/*!*****************************************************************!*\
  !*** ./node_modules/@simplewebauthn/browser/esm/types/index.js ***!
  \*****************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);



/***/ },

/***/ "./node_modules/lit-html/development/lit-html.js"
/*!*******************************************************!*\
  !*** ./node_modules/lit-html/development/lit-html.js ***!
  \*******************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   _$LH: () => (/* binding */ _$LH),
/* harmony export */   html: () => (/* binding */ html),
/* harmony export */   mathml: () => (/* binding */ mathml),
/* harmony export */   noChange: () => (/* binding */ noChange),
/* harmony export */   nothing: () => (/* binding */ nothing),
/* harmony export */   render: () => (/* binding */ render),
/* harmony export */   svg: () => (/* binding */ svg)
/* harmony export */ });
/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const DEV_MODE = true;
const ENABLE_EXTRA_SECURITY_HOOKS = true;
const ENABLE_SHADYDOM_NOPATCH = true;
const NODE_MODE = false;
// Allows minifiers to rename references to globalThis
const global = globalThis;
/**
 * Useful for visualizing and logging insights into what the Lit template system is doing.
 *
 * Compiled out of prod mode builds.
 */
const debugLogEvent = DEV_MODE
    ? (event) => {
        const shouldEmit = global
            .emitLitDebugLogEvents;
        if (!shouldEmit) {
            return;
        }
        global.dispatchEvent(new CustomEvent('lit-debug', {
            detail: event,
        }));
    }
    : undefined;
// Used for connecting beginRender and endRender events when there are nested
// renders when errors are thrown preventing an endRender event from being
// called.
let debugLogRenderId = 0;
let issueWarning;
if (DEV_MODE) {
    global.litIssuedWarnings ??= new Set();
    /**
     * Issue a warning if we haven't already, based either on `code` or `warning`.
     * Warnings are disabled automatically only by `warning`; disabling via `code`
     * can be done by users.
     */
    issueWarning = (code, warning) => {
        warning += code
            ? ` See https://lit.dev/msg/${code} for more information.`
            : '';
        if (!global.litIssuedWarnings.has(warning) &&
            !global.litIssuedWarnings.has(code)) {
            console.warn(warning);
            global.litIssuedWarnings.add(warning);
        }
    };
    queueMicrotask(() => {
        issueWarning('dev-mode', `Lit is in dev mode. Not recommended for production!`);
    });
}
const wrap = ENABLE_SHADYDOM_NOPATCH &&
    global.ShadyDOM?.inUse &&
    global.ShadyDOM?.noPatch === true
    ? global.ShadyDOM.wrap
    : (node) => node;
const trustedTypes = global.trustedTypes;
/**
 * Our TrustedTypePolicy for HTML which is declared using the html template
 * tag function.
 *
 * That HTML is a developer-authored constant, and is parsed with innerHTML
 * before any untrusted expressions have been mixed in. Therefor it is
 * considered safe by construction.
 */
const policy = trustedTypes
    ? trustedTypes.createPolicy('lit-html', {
        createHTML: (s) => s,
    })
    : undefined;
const identityFunction = (value) => value;
const noopSanitizer = (_node, _name, _type) => identityFunction;
/** Sets the global sanitizer factory. */
const setSanitizer = (newSanitizer) => {
    if (!ENABLE_EXTRA_SECURITY_HOOKS) {
        return;
    }
    if (sanitizerFactoryInternal !== noopSanitizer) {
        throw new Error(`Attempted to overwrite existing lit-html security policy.` +
            ` setSanitizeDOMValueFactory should be called at most once.`);
    }
    sanitizerFactoryInternal = newSanitizer;
};
/**
 * Only used in internal tests, not a part of the public API.
 */
const _testOnlyClearSanitizerFactoryDoNotCallOrElse = () => {
    sanitizerFactoryInternal = noopSanitizer;
};
const createSanitizer = (node, name, type) => {
    return sanitizerFactoryInternal(node, name, type);
};
// Added to an attribute name to mark the attribute as bound so we can find
// it easily.
const boundAttributeSuffix = '$lit$';
// This marker is used in many syntactic positions in HTML, so it must be
// a valid element name and attribute name. We don't support dynamic names (yet)
// but this at least ensures that the parse tree is closer to the template
// intention.
const marker = `lit$${Math.random().toFixed(9).slice(2)}$`;
// String used to tell if a comment is a marker comment
const markerMatch = '?' + marker;
// Text used to insert a comment marker node. We use processing instruction
// syntax because it's slightly smaller, but parses as a comment node.
const nodeMarker = `<${markerMatch}>`;
const d = NODE_MODE && global.document === undefined
    ? {
        createTreeWalker() {
            return {};
        },
    }
    : document;
// Creates a dynamic marker. We never have to search for these in the DOM.
const createMarker = () => d.createComment('');
const isPrimitive = (value) => value === null || (typeof value != 'object' && typeof value != 'function');
const isArray = Array.isArray;
const isIterable = (value) => isArray(value) ||
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    typeof value?.[Symbol.iterator] === 'function';
const SPACE_CHAR = `[ \t\n\f\r]`;
const ATTR_VALUE_CHAR = `[^ \t\n\f\r"'\`<>=]`;
const NAME_CHAR = `[^\\s"'>=/]`;
// These regexes represent the five parsing states that we care about in the
// Template's HTML scanner. They match the *end* of the state they're named
// after.
// Depending on the match, we transition to a new state. If there's no match,
// we stay in the same state.
// Note that the regexes are stateful. We utilize lastIndex and sync it
// across the multiple regexes used. In addition to the five regexes below
// we also dynamically create a regex to find the matching end tags for raw
// text elements.
/**
 * End of text is: `<` followed by:
 *   (comment start) or (tag) or (dynamic tag binding)
 */
const textEndRegex = /<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g;
const COMMENT_START = 1;
const TAG_NAME = 2;
const DYNAMIC_TAG_NAME = 3;
const commentEndRegex = /-->/g;
/**
 * Comments not started with <!--, like </{, can be ended by a single `>`
 */
const comment2EndRegex = />/g;
/**
 * The tagEnd regex matches the end of the "inside an opening" tag syntax
 * position. It either matches a `>`, an attribute-like sequence, or the end
 * of the string after a space (attribute-name position ending).
 *
 * See attributes in the HTML spec:
 * https://www.w3.org/TR/html5/syntax.html#elements-attributes
 *
 * " \t\n\f\r" are HTML space characters:
 * https://infra.spec.whatwg.org/#ascii-whitespace
 *
 * So an attribute is:
 *  * The name: any character except a whitespace character, ("), ('), ">",
 *    "=", or "/". Note: this is different from the HTML spec which also excludes control characters.
 *  * Followed by zero or more space characters
 *  * Followed by "="
 *  * Followed by zero or more space characters
 *  * Followed by:
 *    * Any character except space, ('), ("), "<", ">", "=", (`), or
 *    * (") then any non-("), or
 *    * (') then any non-(')
 */
const tagEndRegex = new RegExp(`>|${SPACE_CHAR}(?:(${NAME_CHAR}+)(${SPACE_CHAR}*=${SPACE_CHAR}*(?:${ATTR_VALUE_CHAR}|("|')|))|$)`, 'g');
const ENTIRE_MATCH = 0;
const ATTRIBUTE_NAME = 1;
const SPACES_AND_EQUALS = 2;
const QUOTE_CHAR = 3;
const singleQuoteAttrEndRegex = /'/g;
const doubleQuoteAttrEndRegex = /"/g;
/**
 * Matches the raw text elements.
 *
 * Comments are not parsed within raw text elements, so we need to search their
 * text content for marker strings.
 */
const rawTextElement = /^(?:script|style|textarea|title)$/i;
/** TemplateResult types */
const HTML_RESULT = 1;
const SVG_RESULT = 2;
const MATHML_RESULT = 3;
// TemplatePart types
// IMPORTANT: these must match the values in PartType
const ATTRIBUTE_PART = 1;
const CHILD_PART = 2;
const PROPERTY_PART = 3;
const BOOLEAN_ATTRIBUTE_PART = 4;
const EVENT_PART = 5;
const ELEMENT_PART = 6;
const COMMENT_PART = 7;
/**
 * Generates a template literal tag function that returns a TemplateResult with
 * the given result type.
 */
const tag = (type) => (strings, ...values) => {
    // Warn against templates octal escape sequences
    // We do this here rather than in render so that the warning is closer to the
    // template definition.
    if (DEV_MODE && strings.some((s) => s === undefined)) {
        console.warn('Some template strings are undefined.\n' +
            'This is probably caused by illegal octal escape sequences.');
    }
    if (DEV_MODE) {
        // Import static-html.js results in a circular dependency which g3 doesn't
        // handle. Instead we know that static values must have the field
        // `_$litStatic$`.
        if (values.some((val) => val?.['_$litStatic$'])) {
            issueWarning('', `Static values 'literal' or 'unsafeStatic' cannot be used as values to non-static templates.\n` +
                `Please use the static 'html' tag function. See https://lit.dev/docs/templates/expressions/#static-expressions`);
        }
    }
    return {
        // This property needs to remain unminified.
        ['_$litType$']: type,
        strings,
        values,
    };
};
/**
 * Interprets a template literal as an HTML template that can efficiently
 * render to and update a container.
 *
 * ```ts
 * const header = (title: string) => html`<h1>${title}</h1>`;
 * ```
 *
 * The `html` tag returns a description of the DOM to render as a value. It is
 * lazy, meaning no work is done until the template is rendered. When rendering,
 * if a template comes from the same expression as a previously rendered result,
 * it's efficiently updated instead of replaced.
 */
const html = tag(HTML_RESULT);
/**
 * Interprets a template literal as an SVG fragment that can efficiently render
 * to and update a container.
 *
 * ```ts
 * const rect = svg`<rect width="10" height="10"></rect>`;
 *
 * const myImage = html`
 *   <svg viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
 *     ${rect}
 *   </svg>`;
 * ```
 *
 * The `svg` *tag function* should only be used for SVG fragments, or elements
 * that would be contained **inside** an `<svg>` HTML element. A common error is
 * placing an `<svg>` *element* in a template tagged with the `svg` tag
 * function. The `<svg>` element is an HTML element and should be used within a
 * template tagged with the {@linkcode html} tag function.
 *
 * In LitElement usage, it's invalid to return an SVG fragment from the
 * `render()` method, as the SVG fragment will be contained within the element's
 * shadow root and thus not be properly contained within an `<svg>` HTML
 * element.
 */
const svg = tag(SVG_RESULT);
/**
 * Interprets a template literal as MathML fragment that can efficiently render
 * to and update a container.
 *
 * ```ts
 * const num = mathml`<mn>1</mn>`;
 *
 * const eq = html`
 *   <math>
 *     ${num}
 *   </math>`;
 * ```
 *
 * The `mathml` *tag function* should only be used for MathML fragments, or
 * elements that would be contained **inside** a `<math>` HTML element. A common
 * error is placing a `<math>` *element* in a template tagged with the `mathml`
 * tag function. The `<math>` element is an HTML element and should be used
 * within a template tagged with the {@linkcode html} tag function.
 *
 * In LitElement usage, it's invalid to return an MathML fragment from the
 * `render()` method, as the MathML fragment will be contained within the
 * element's shadow root and thus not be properly contained within a `<math>`
 * HTML element.
 */
const mathml = tag(MATHML_RESULT);
/**
 * A sentinel value that signals that a value was handled by a directive and
 * should not be written to the DOM.
 */
const noChange = Symbol.for('lit-noChange');
/**
 * A sentinel value that signals a ChildPart to fully clear its content.
 *
 * ```ts
 * const button = html`${
 *  user.isAdmin
 *    ? html`<button>DELETE</button>`
 *    : nothing
 * }`;
 * ```
 *
 * Prefer using `nothing` over other falsy values as it provides a consistent
 * behavior between various expression binding contexts.
 *
 * In child expressions, `undefined`, `null`, `''`, and `nothing` all behave the
 * same and render no nodes. In attribute expressions, `nothing` _removes_ the
 * attribute, while `undefined` and `null` will render an empty string. In
 * property expressions `nothing` becomes `undefined`.
 */
const nothing = Symbol.for('lit-nothing');
/**
 * The cache of prepared templates, keyed by the tagged TemplateStringsArray
 * and _not_ accounting for the specific template tag used. This means that
 * template tags cannot be dynamic - they must statically be one of html, svg,
 * or attr. This restriction simplifies the cache lookup, which is on the hot
 * path for rendering.
 */
const templateCache = new WeakMap();
const walker = d.createTreeWalker(d, 129 /* NodeFilter.SHOW_{ELEMENT|COMMENT} */);
let sanitizerFactoryInternal = noopSanitizer;
function trustFromTemplateString(tsa, stringFromTSA) {
    // A security check to prevent spoofing of Lit template results.
    // In the future, we may be able to replace this with Array.isTemplateObject,
    // though we might need to make that check inside of the html and svg
    // functions, because precompiled templates don't come in as
    // TemplateStringArray objects.
    if (!isArray(tsa) || !tsa.hasOwnProperty('raw')) {
        let message = 'invalid template strings array';
        if (DEV_MODE) {
            message = `
          Internal Error: expected template strings to be an array
          with a 'raw' field. Faking a template strings array by
          calling html or svg like an ordinary function is effectively
          the same as calling unsafeHtml and can lead to major security
          issues, e.g. opening your code up to XSS attacks.
          If you're using the html or svg tagged template functions normally
          and still seeing this error, please file a bug at
          https://github.com/lit/lit/issues/new?template=bug_report.md
          and include information about your build tooling, if any.
        `
                .trim()
                .replace(/\n */g, '\n');
        }
        throw new Error(message);
    }
    return policy !== undefined
        ? policy.createHTML(stringFromTSA)
        : stringFromTSA;
}
/**
 * Returns an HTML string for the given TemplateStringsArray and result type
 * (HTML or SVG), along with the case-sensitive bound attribute names in
 * template order. The HTML contains comment markers denoting the `ChildPart`s
 * and suffixes on bound attributes denoting the `AttributeParts`.
 *
 * @param strings template strings array
 * @param type HTML or SVG
 * @return Array containing `[html, attrNames]` (array returned for terseness,
 *     to avoid object fields since this code is shared with non-minified SSR
 *     code)
 */
const getTemplateHtml = (strings, type) => {
    // Insert makers into the template HTML to represent the position of
    // bindings. The following code scans the template strings to determine the
    // syntactic position of the bindings. They can be in text position, where
    // we insert an HTML comment, attribute value position, where we insert a
    // sentinel string and re-write the attribute name, or inside a tag where
    // we insert the sentinel string.
    const l = strings.length - 1;
    // Stores the case-sensitive bound attribute names in the order of their
    // parts. ElementParts are also reflected in this array as undefined
    // rather than a string, to disambiguate from attribute bindings.
    const attrNames = [];
    let html = type === SVG_RESULT ? '<svg>' : type === MATHML_RESULT ? '<math>' : '';
    // When we're inside a raw text tag (not it's text content), the regex
    // will still be tagRegex so we can find attributes, but will switch to
    // this regex when the tag ends.
    let rawTextEndRegex;
    // The current parsing state, represented as a reference to one of the
    // regexes
    let regex = textEndRegex;
    for (let i = 0; i < l; i++) {
        const s = strings[i];
        // The index of the end of the last attribute name. When this is
        // positive at end of a string, it means we're in an attribute value
        // position and need to rewrite the attribute name.
        // We also use a special value of -2 to indicate that we encountered
        // the end of a string in attribute name position.
        let attrNameEndIndex = -1;
        let attrName;
        let lastIndex = 0;
        let match;
        // The conditions in this loop handle the current parse state, and the
        // assignments to the `regex` variable are the state transitions.
        while (lastIndex < s.length) {
            // Make sure we start searching from where we previously left off
            regex.lastIndex = lastIndex;
            match = regex.exec(s);
            if (match === null) {
                break;
            }
            lastIndex = regex.lastIndex;
            if (regex === textEndRegex) {
                if (match[COMMENT_START] === '!--') {
                    regex = commentEndRegex;
                }
                else if (match[COMMENT_START] !== undefined) {
                    // We started a weird comment, like </{
                    regex = comment2EndRegex;
                }
                else if (match[TAG_NAME] !== undefined) {
                    if (rawTextElement.test(match[TAG_NAME])) {
                        // Record if we encounter a raw-text element. We'll switch to
                        // this regex at the end of the tag.
                        rawTextEndRegex = new RegExp(`</${match[TAG_NAME]}`, 'g');
                    }
                    regex = tagEndRegex;
                }
                else if (match[DYNAMIC_TAG_NAME] !== undefined) {
                    if (DEV_MODE) {
                        throw new Error('Bindings in tag names are not supported. Please use static templates instead. ' +
                            'See https://lit.dev/docs/templates/expressions/#static-expressions');
                    }
                    regex = tagEndRegex;
                }
            }
            else if (regex === tagEndRegex) {
                if (match[ENTIRE_MATCH] === '>') {
                    // End of a tag. If we had started a raw-text element, use that
                    // regex
                    regex = rawTextEndRegex ?? textEndRegex;
                    // We may be ending an unquoted attribute value, so make sure we
                    // clear any pending attrNameEndIndex
                    attrNameEndIndex = -1;
                }
                else if (match[ATTRIBUTE_NAME] === undefined) {
                    // Attribute name position
                    attrNameEndIndex = -2;
                }
                else {
                    attrNameEndIndex = regex.lastIndex - match[SPACES_AND_EQUALS].length;
                    attrName = match[ATTRIBUTE_NAME];
                    regex =
                        match[QUOTE_CHAR] === undefined
                            ? tagEndRegex
                            : match[QUOTE_CHAR] === '"'
                                ? doubleQuoteAttrEndRegex
                                : singleQuoteAttrEndRegex;
                }
            }
            else if (regex === doubleQuoteAttrEndRegex ||
                regex === singleQuoteAttrEndRegex) {
                regex = tagEndRegex;
            }
            else if (regex === commentEndRegex || regex === comment2EndRegex) {
                regex = textEndRegex;
            }
            else {
                // Not one of the five state regexes, so it must be the dynamically
                // created raw text regex and we're at the close of that element.
                regex = tagEndRegex;
                rawTextEndRegex = undefined;
            }
        }
        if (DEV_MODE) {
            // If we have a attrNameEndIndex, which indicates that we should
            // rewrite the attribute name, assert that we're in a valid attribute
            // position - either in a tag, or a quoted attribute value.
            console.assert(attrNameEndIndex === -1 ||
                regex === tagEndRegex ||
                regex === singleQuoteAttrEndRegex ||
                regex === doubleQuoteAttrEndRegex, 'unexpected parse state B');
        }
        // We have four cases:
        //  1. We're in text position, and not in a raw text element
        //     (regex === textEndRegex): insert a comment marker.
        //  2. We have a non-negative attrNameEndIndex which means we need to
        //     rewrite the attribute name to add a bound attribute suffix.
        //  3. We're at the non-first binding in a multi-binding attribute, use a
        //     plain marker.
        //  4. We're somewhere else inside the tag. If we're in attribute name
        //     position (attrNameEndIndex === -2), add a sequential suffix to
        //     generate a unique attribute name.
        // Detect a binding next to self-closing tag end and insert a space to
        // separate the marker from the tag end:
        const end = regex === tagEndRegex && strings[i + 1].startsWith('/>') ? ' ' : '';
        html +=
            regex === textEndRegex
                ? s + nodeMarker
                : attrNameEndIndex >= 0
                    ? (attrNames.push(attrName),
                        s.slice(0, attrNameEndIndex) +
                            boundAttributeSuffix +
                            s.slice(attrNameEndIndex)) +
                        marker +
                        end
                    : s + marker + (attrNameEndIndex === -2 ? i : end);
    }
    const htmlResult = html +
        (strings[l] || '<?>') +
        (type === SVG_RESULT ? '</svg>' : type === MATHML_RESULT ? '</math>' : '');
    // Returned as an array for terseness
    return [trustFromTemplateString(strings, htmlResult), attrNames];
};
class Template {
    constructor(
    // This property needs to remain unminified.
    { strings, ['_$litType$']: type }, options) {
        this.parts = [];
        let node;
        let nodeIndex = 0;
        let attrNameIndex = 0;
        const partCount = strings.length - 1;
        const parts = this.parts;
        // Create template element
        const [html, attrNames] = getTemplateHtml(strings, type);
        this.el = Template.createElement(html, options);
        walker.currentNode = this.el.content;
        // Re-parent SVG or MathML nodes into template root
        if (type === SVG_RESULT || type === MATHML_RESULT) {
            const wrapper = this.el.content.firstChild;
            wrapper.replaceWith(...wrapper.childNodes);
        }
        // Walk the template to find binding markers and create TemplateParts
        while ((node = walker.nextNode()) !== null && parts.length < partCount) {
            if (node.nodeType === 1) {
                if (DEV_MODE) {
                    const tag = node.localName;
                    // Warn if `textarea` includes an expression and throw if `template`
                    // does since these are not supported. We do this by checking
                    // innerHTML for anything that looks like a marker. This catches
                    // cases like bindings in textarea there markers turn into text nodes.
                    if (/^(?:textarea|template)$/i.test(tag) &&
                        node.innerHTML.includes(marker)) {
                        const m = `Expressions are not supported inside \`${tag}\` ` +
                            `elements. See https://lit.dev/msg/expression-in-${tag} for more ` +
                            `information.`;
                        if (tag === 'template') {
                            throw new Error(m);
                        }
                        else
                            issueWarning('', m);
                    }
                }
                // TODO (justinfagnani): for attempted dynamic tag names, we don't
                // increment the bindingIndex, and it'll be off by 1 in the element
                // and off by two after it.
                if (node.hasAttributes()) {
                    for (const name of node.getAttributeNames()) {
                        if (name.endsWith(boundAttributeSuffix)) {
                            const realName = attrNames[attrNameIndex++];
                            const value = node.getAttribute(name);
                            const statics = value.split(marker);
                            const m = /([.?@])?(.*)/.exec(realName);
                            parts.push({
                                type: ATTRIBUTE_PART,
                                index: nodeIndex,
                                name: m[2],
                                strings: statics,
                                ctor: m[1] === '.'
                                    ? PropertyPart
                                    : m[1] === '?'
                                        ? BooleanAttributePart
                                        : m[1] === '@'
                                            ? EventPart
                                            : AttributePart,
                            });
                            node.removeAttribute(name);
                        }
                        else if (name.startsWith(marker)) {
                            parts.push({
                                type: ELEMENT_PART,
                                index: nodeIndex,
                            });
                            node.removeAttribute(name);
                        }
                    }
                }
                // TODO (justinfagnani): benchmark the regex against testing for each
                // of the 3 raw text element names.
                if (rawTextElement.test(node.tagName)) {
                    // For raw text elements we need to split the text content on
                    // markers, create a Text node for each segment, and create
                    // a TemplatePart for each marker.
                    const strings = node.textContent.split(marker);
                    const lastIndex = strings.length - 1;
                    if (lastIndex > 0) {
                        node.textContent = trustedTypes
                            ? trustedTypes.emptyScript
                            : '';
                        // Generate a new text node for each literal section
                        // These nodes are also used as the markers for child parts
                        for (let i = 0; i < lastIndex; i++) {
                            node.append(strings[i], createMarker());
                            // Walk past the marker node we just added
                            walker.nextNode();
                            parts.push({ type: CHILD_PART, index: ++nodeIndex });
                        }
                        // Note because this marker is added after the walker's current
                        // node, it will be walked to in the outer loop (and ignored), so
                        // we don't need to adjust nodeIndex here
                        node.append(strings[lastIndex], createMarker());
                    }
                }
            }
            else if (node.nodeType === 8) {
                const data = node.data;
                if (data === markerMatch) {
                    parts.push({ type: CHILD_PART, index: nodeIndex });
                }
                else {
                    let i = -1;
                    while ((i = node.data.indexOf(marker, i + 1)) !== -1) {
                        // Comment node has a binding marker inside, make an inactive part
                        // The binding won't work, but subsequent bindings will
                        parts.push({ type: COMMENT_PART, index: nodeIndex });
                        // Move to the end of the match
                        i += marker.length - 1;
                    }
                }
            }
            nodeIndex++;
        }
        if (DEV_MODE) {
            // If there was a duplicate attribute on a tag, then when the tag is
            // parsed into an element the attribute gets de-duplicated. We can detect
            // this mismatch if we haven't precisely consumed every attribute name
            // when preparing the template. This works because `attrNames` is built
            // from the template string and `attrNameIndex` comes from processing the
            // resulting DOM.
            if (attrNames.length !== attrNameIndex) {
                throw new Error(`Detected duplicate attribute bindings. This occurs if your template ` +
                    `has duplicate attributes on an element tag. For example ` +
                    `"<input ?disabled=\${true} ?disabled=\${false}>" contains a ` +
                    `duplicate "disabled" attribute. The error was detected in ` +
                    `the following template: \n` +
                    '`' +
                    strings.join('${...}') +
                    '`');
            }
        }
        // We could set walker.currentNode to another node here to prevent a memory
        // leak, but every time we prepare a template, we immediately render it
        // and re-use the walker in new TemplateInstance._clone().
        debugLogEvent &&
            debugLogEvent({
                kind: 'template prep',
                template: this,
                clonableTemplate: this.el,
                parts: this.parts,
                strings,
            });
    }
    // Overridden via `litHtmlPolyfillSupport` to provide platform support.
    /** @nocollapse */
    static createElement(html, _options) {
        const el = d.createElement('template');
        el.innerHTML = html;
        return el;
    }
}
function resolveDirective(part, value, parent = part, attributeIndex) {
    // Bail early if the value is explicitly noChange. Note, this means any
    // nested directive is still attached and is not run.
    if (value === noChange) {
        return value;
    }
    let currentDirective = attributeIndex !== undefined
        ? parent.__directives?.[attributeIndex]
        : parent.__directive;
    const nextDirectiveConstructor = isPrimitive(value)
        ? undefined
        : // This property needs to remain unminified.
            value['_$litDirective$'];
    if (currentDirective?.constructor !== nextDirectiveConstructor) {
        // This property needs to remain unminified.
        currentDirective?.['_$notifyDirectiveConnectionChanged']?.(false);
        if (nextDirectiveConstructor === undefined) {
            currentDirective = undefined;
        }
        else {
            currentDirective = new nextDirectiveConstructor(part);
            currentDirective._$initialize(part, parent, attributeIndex);
        }
        if (attributeIndex !== undefined) {
            (parent.__directives ??= [])[attributeIndex] =
                currentDirective;
        }
        else {
            parent.__directive = currentDirective;
        }
    }
    if (currentDirective !== undefined) {
        value = resolveDirective(part, currentDirective._$resolve(part, value.values), currentDirective, attributeIndex);
    }
    return value;
}
/**
 * An updateable instance of a Template. Holds references to the Parts used to
 * update the template instance.
 */
class TemplateInstance {
    constructor(template, parent) {
        this._$parts = [];
        /** @internal */
        this._$disconnectableChildren = undefined;
        this._$template = template;
        this._$parent = parent;
    }
    // Called by ChildPart parentNode getter
    get parentNode() {
        return this._$parent.parentNode;
    }
    // See comment in Disconnectable interface for why this is a getter
    get _$isConnected() {
        return this._$parent._$isConnected;
    }
    // This method is separate from the constructor because we need to return a
    // DocumentFragment and we don't want to hold onto it with an instance field.
    _clone(options) {
        const { el: { content }, parts: parts, } = this._$template;
        const fragment = (options?.creationScope ?? d).importNode(content, true);
        walker.currentNode = fragment;
        let node = walker.nextNode();
        let nodeIndex = 0;
        let partIndex = 0;
        let templatePart = parts[0];
        while (templatePart !== undefined) {
            if (nodeIndex === templatePart.index) {
                let part;
                if (templatePart.type === CHILD_PART) {
                    part = new ChildPart(node, node.nextSibling, this, options);
                }
                else if (templatePart.type === ATTRIBUTE_PART) {
                    part = new templatePart.ctor(node, templatePart.name, templatePart.strings, this, options);
                }
                else if (templatePart.type === ELEMENT_PART) {
                    part = new ElementPart(node, this, options);
                }
                this._$parts.push(part);
                templatePart = parts[++partIndex];
            }
            if (nodeIndex !== templatePart?.index) {
                node = walker.nextNode();
                nodeIndex++;
            }
        }
        // We need to set the currentNode away from the cloned tree so that we
        // don't hold onto the tree even if the tree is detached and should be
        // freed.
        walker.currentNode = d;
        return fragment;
    }
    _update(values) {
        let i = 0;
        for (const part of this._$parts) {
            if (part !== undefined) {
                debugLogEvent &&
                    debugLogEvent({
                        kind: 'set part',
                        part,
                        value: values[i],
                        valueIndex: i,
                        values,
                        templateInstance: this,
                    });
                if (part.strings !== undefined) {
                    part._$setValue(values, part, i);
                    // The number of values the part consumes is part.strings.length - 1
                    // since values are in between template spans. We increment i by 1
                    // later in the loop, so increment it by part.strings.length - 2 here
                    i += part.strings.length - 2;
                }
                else {
                    part._$setValue(values[i]);
                }
            }
            i++;
        }
    }
}
class ChildPart {
    // See comment in Disconnectable interface for why this is a getter
    get _$isConnected() {
        // ChildParts that are not at the root should always be created with a
        // parent; only RootChildNode's won't, so they return the local isConnected
        // state
        return this._$parent?._$isConnected ?? this.__isConnected;
    }
    constructor(startNode, endNode, parent, options) {
        this.type = CHILD_PART;
        this._$committedValue = nothing;
        // The following fields will be patched onto ChildParts when required by
        // AsyncDirective
        /** @internal */
        this._$disconnectableChildren = undefined;
        this._$startNode = startNode;
        this._$endNode = endNode;
        this._$parent = parent;
        this.options = options;
        // Note __isConnected is only ever accessed on RootParts (i.e. when there is
        // no _$parent); the value on a non-root-part is "don't care", but checking
        // for parent would be more code
        this.__isConnected = options?.isConnected ?? true;
        if (ENABLE_EXTRA_SECURITY_HOOKS) {
            // Explicitly initialize for consistent class shape.
            this._textSanitizer = undefined;
        }
    }
    /**
     * The parent node into which the part renders its content.
     *
     * A ChildPart's content consists of a range of adjacent child nodes of
     * `.parentNode`, possibly bordered by 'marker nodes' (`.startNode` and
     * `.endNode`).
     *
     * - If both `.startNode` and `.endNode` are non-null, then the part's content
     * consists of all siblings between `.startNode` and `.endNode`, exclusively.
     *
     * - If `.startNode` is non-null but `.endNode` is null, then the part's
     * content consists of all siblings following `.startNode`, up to and
     * including the last child of `.parentNode`. If `.endNode` is non-null, then
     * `.startNode` will always be non-null.
     *
     * - If both `.endNode` and `.startNode` are null, then the part's content
     * consists of all child nodes of `.parentNode`.
     */
    get parentNode() {
        let parentNode = wrap(this._$startNode).parentNode;
        const parent = this._$parent;
        if (parent !== undefined &&
            parentNode?.nodeType === 11 /* Node.DOCUMENT_FRAGMENT */) {
            // If the parentNode is a DocumentFragment, it may be because the DOM is
            // still in the cloned fragment during initial render; if so, get the real
            // parentNode the part will be committed into by asking the parent.
            parentNode = parent.parentNode;
        }
        return parentNode;
    }
    /**
     * The part's leading marker node, if any. See `.parentNode` for more
     * information.
     */
    get startNode() {
        return this._$startNode;
    }
    /**
     * The part's trailing marker node, if any. See `.parentNode` for more
     * information.
     */
    get endNode() {
        return this._$endNode;
    }
    _$setValue(value, directiveParent = this) {
        if (DEV_MODE && this.parentNode === null) {
            throw new Error(`This \`ChildPart\` has no \`parentNode\` and therefore cannot accept a value. This likely means the element containing the part was manipulated in an unsupported way outside of Lit's control such that the part's marker nodes were ejected from DOM. For example, setting the element's \`innerHTML\` or \`textContent\` can do this.`);
        }
        value = resolveDirective(this, value, directiveParent);
        if (isPrimitive(value)) {
            // Non-rendering child values. It's important that these do not render
            // empty text nodes to avoid issues with preventing default <slot>
            // fallback content.
            if (value === nothing || value == null || value === '') {
                if (this._$committedValue !== nothing) {
                    debugLogEvent &&
                        debugLogEvent({
                            kind: 'commit nothing to child',
                            start: this._$startNode,
                            end: this._$endNode,
                            parent: this._$parent,
                            options: this.options,
                        });
                    this._$clear();
                }
                this._$committedValue = nothing;
            }
            else if (value !== this._$committedValue && value !== noChange) {
                this._commitText(value);
            }
            // This property needs to remain unminified.
        }
        else if (value['_$litType$'] !== undefined) {
            this._commitTemplateResult(value);
        }
        else if (value.nodeType !== undefined) {
            if (DEV_MODE && this.options?.host === value) {
                this._commitText(`[probable mistake: rendered a template's host in itself ` +
                    `(commonly caused by writing \${this} in a template]`);
                console.warn(`Attempted to render the template host`, value, `inside itself. This is almost always a mistake, and in dev mode `, `we render some warning text. In production however, we'll `, `render it, which will usually result in an error, and sometimes `, `in the element disappearing from the DOM.`);
                return;
            }
            this._commitNode(value);
        }
        else if (isIterable(value)) {
            this._commitIterable(value);
        }
        else {
            // Fallback, will render the string representation
            this._commitText(value);
        }
    }
    _insert(node) {
        return wrap(wrap(this._$startNode).parentNode).insertBefore(node, this._$endNode);
    }
    _commitNode(value) {
        if (this._$committedValue !== value) {
            this._$clear();
            if (ENABLE_EXTRA_SECURITY_HOOKS &&
                sanitizerFactoryInternal !== noopSanitizer) {
                const parentNodeName = this._$startNode.parentNode?.nodeName;
                if (parentNodeName === 'STYLE' || parentNodeName === 'SCRIPT') {
                    let message = 'Forbidden';
                    if (DEV_MODE) {
                        if (parentNodeName === 'STYLE') {
                            message =
                                `Lit does not support binding inside style nodes. ` +
                                    `This is a security risk, as style injection attacks can ` +
                                    `exfiltrate data and spoof UIs. ` +
                                    `Consider instead using css\`...\` literals ` +
                                    `to compose styles, and do dynamic styling with ` +
                                    `css custom properties, ::parts, <slot>s, ` +
                                    `and by mutating the DOM rather than stylesheets.`;
                        }
                        else {
                            message =
                                `Lit does not support binding inside script nodes. ` +
                                    `This is a security risk, as it could allow arbitrary ` +
                                    `code execution.`;
                        }
                    }
                    throw new Error(message);
                }
            }
            debugLogEvent &&
                debugLogEvent({
                    kind: 'commit node',
                    start: this._$startNode,
                    parent: this._$parent,
                    value: value,
                    options: this.options,
                });
            this._$committedValue = this._insert(value);
        }
    }
    _commitText(value) {
        // If the committed value is a primitive it means we called _commitText on
        // the previous render, and we know that this._$startNode.nextSibling is a
        // Text node. We can now just replace the text content (.data) of the node.
        if (this._$committedValue !== nothing &&
            isPrimitive(this._$committedValue)) {
            const node = wrap(this._$startNode).nextSibling;
            if (ENABLE_EXTRA_SECURITY_HOOKS) {
                if (this._textSanitizer === undefined) {
                    this._textSanitizer = createSanitizer(node, 'data', 'property');
                }
                value = this._textSanitizer(value);
            }
            debugLogEvent &&
                debugLogEvent({
                    kind: 'commit text',
                    node,
                    value,
                    options: this.options,
                });
            node.data = value;
        }
        else {
            if (ENABLE_EXTRA_SECURITY_HOOKS) {
                const textNode = d.createTextNode('');
                this._commitNode(textNode);
                // When setting text content, for security purposes it matters a lot
                // what the parent is. For example, <style> and <script> need to be
                // handled with care, while <span> does not. So first we need to put a
                // text node into the document, then we can sanitize its content.
                if (this._textSanitizer === undefined) {
                    this._textSanitizer = createSanitizer(textNode, 'data', 'property');
                }
                value = this._textSanitizer(value);
                debugLogEvent &&
                    debugLogEvent({
                        kind: 'commit text',
                        node: textNode,
                        value,
                        options: this.options,
                    });
                textNode.data = value;
            }
            else {
                this._commitNode(d.createTextNode(value));
                debugLogEvent &&
                    debugLogEvent({
                        kind: 'commit text',
                        node: wrap(this._$startNode).nextSibling,
                        value,
                        options: this.options,
                    });
            }
        }
        this._$committedValue = value;
    }
    _commitTemplateResult(result) {
        // This property needs to remain unminified.
        const { values, ['_$litType$']: type } = result;
        // If $litType$ is a number, result is a plain TemplateResult and we get
        // the template from the template cache. If not, result is a
        // CompiledTemplateResult and _$litType$ is a CompiledTemplate and we need
        // to create the <template> element the first time we see it.
        const template = typeof type === 'number'
            ? this._$getTemplate(result)
            : (type.el === undefined &&
                (type.el = Template.createElement(trustFromTemplateString(type.h, type.h[0]), this.options)),
                type);
        if (this._$committedValue?._$template === template) {
            debugLogEvent &&
                debugLogEvent({
                    kind: 'template updating',
                    template,
                    instance: this._$committedValue,
                    parts: this._$committedValue._$parts,
                    options: this.options,
                    values,
                });
            this._$committedValue._update(values);
        }
        else {
            const instance = new TemplateInstance(template, this);
            const fragment = instance._clone(this.options);
            debugLogEvent &&
                debugLogEvent({
                    kind: 'template instantiated',
                    template,
                    instance,
                    parts: instance._$parts,
                    options: this.options,
                    fragment,
                    values,
                });
            instance._update(values);
            debugLogEvent &&
                debugLogEvent({
                    kind: 'template instantiated and updated',
                    template,
                    instance,
                    parts: instance._$parts,
                    options: this.options,
                    fragment,
                    values,
                });
            this._commitNode(fragment);
            this._$committedValue = instance;
        }
    }
    // Overridden via `litHtmlPolyfillSupport` to provide platform support.
    /** @internal */
    _$getTemplate(result) {
        let template = templateCache.get(result.strings);
        if (template === undefined) {
            templateCache.set(result.strings, (template = new Template(result)));
        }
        return template;
    }
    _commitIterable(value) {
        // For an Iterable, we create a new InstancePart per item, then set its
        // value to the item. This is a little bit of overhead for every item in
        // an Iterable, but it lets us recurse easily and efficiently update Arrays
        // of TemplateResults that will be commonly returned from expressions like:
        // array.map((i) => html`${i}`), by reusing existing TemplateInstances.
        // If value is an array, then the previous render was of an
        // iterable and value will contain the ChildParts from the previous
        // render. If value is not an array, clear this part and make a new
        // array for ChildParts.
        if (!isArray(this._$committedValue)) {
            this._$committedValue = [];
            this._$clear();
        }
        // Lets us keep track of how many items we stamped so we can clear leftover
        // items from a previous render
        const itemParts = this._$committedValue;
        let partIndex = 0;
        let itemPart;
        for (const item of value) {
            if (partIndex === itemParts.length) {
                // If no existing part, create a new one
                // TODO (justinfagnani): test perf impact of always creating two parts
                // instead of sharing parts between nodes
                // https://github.com/lit/lit/issues/1266
                itemParts.push((itemPart = new ChildPart(this._insert(createMarker()), this._insert(createMarker()), this, this.options)));
            }
            else {
                // Reuse an existing part
                itemPart = itemParts[partIndex];
            }
            itemPart._$setValue(item);
            partIndex++;
        }
        if (partIndex < itemParts.length) {
            // itemParts always have end nodes
            this._$clear(itemPart && wrap(itemPart._$endNode).nextSibling, partIndex);
            // Truncate the parts array so _value reflects the current state
            itemParts.length = partIndex;
        }
    }
    /**
     * Removes the nodes contained within this Part from the DOM.
     *
     * @param start Start node to clear from, for clearing a subset of the part's
     *     DOM (used when truncating iterables)
     * @param from  When `start` is specified, the index within the iterable from
     *     which ChildParts are being removed, used for disconnecting directives
     *     in those Parts.
     *
     * @internal
     */
    _$clear(start = wrap(this._$startNode).nextSibling, from) {
        this._$notifyConnectionChanged?.(false, true, from);
        while (start !== this._$endNode) {
            // The non-null assertion is safe because if _$startNode.nextSibling is
            // null, then _$endNode is also null, and we would not have entered this
            // loop.
            const n = wrap(start).nextSibling;
            wrap(start).remove();
            start = n;
        }
    }
    /**
     * Implementation of RootPart's `isConnected`. Note that this method
     * should only be called on `RootPart`s (the `ChildPart` returned from a
     * top-level `render()` call). It has no effect on non-root ChildParts.
     * @param isConnected Whether to set
     * @internal
     */
    setConnected(isConnected) {
        if (this._$parent === undefined) {
            this.__isConnected = isConnected;
            this._$notifyConnectionChanged?.(isConnected);
        }
        else if (DEV_MODE) {
            throw new Error('part.setConnected() may only be called on a ' +
                'RootPart returned from render().');
        }
    }
}
class AttributePart {
    get tagName() {
        return this.element.tagName;
    }
    // See comment in Disconnectable interface for why this is a getter
    get _$isConnected() {
        return this._$parent._$isConnected;
    }
    constructor(element, name, strings, parent, options) {
        this.type = ATTRIBUTE_PART;
        /** @internal */
        this._$committedValue = nothing;
        /** @internal */
        this._$disconnectableChildren = undefined;
        this.element = element;
        this.name = name;
        this._$parent = parent;
        this.options = options;
        if (strings.length > 2 || strings[0] !== '' || strings[1] !== '') {
            this._$committedValue = new Array(strings.length - 1).fill(new String());
            this.strings = strings;
        }
        else {
            this._$committedValue = nothing;
        }
        if (ENABLE_EXTRA_SECURITY_HOOKS) {
            this._sanitizer = undefined;
        }
    }
    /**
     * Sets the value of this part by resolving the value from possibly multiple
     * values and static strings and committing it to the DOM.
     * If this part is single-valued, `this._strings` will be undefined, and the
     * method will be called with a single value argument. If this part is
     * multi-value, `this._strings` will be defined, and the method is called
     * with the value array of the part's owning TemplateInstance, and an offset
     * into the value array from which the values should be read.
     * This method is overloaded this way to eliminate short-lived array slices
     * of the template instance values, and allow a fast-path for single-valued
     * parts.
     *
     * @param value The part value, or an array of values for multi-valued parts
     * @param valueIndex the index to start reading values from. `undefined` for
     *   single-valued parts
     * @param noCommit causes the part to not commit its value to the DOM. Used
     *   in hydration to prime attribute parts with their first-rendered value,
     *   but not set the attribute, and in SSR to no-op the DOM operation and
     *   capture the value for serialization.
     *
     * @internal
     */
    _$setValue(value, directiveParent = this, valueIndex, noCommit) {
        const strings = this.strings;
        // Whether any of the values has changed, for dirty-checking
        let change = false;
        if (strings === undefined) {
            // Single-value binding case
            value = resolveDirective(this, value, directiveParent, 0);
            change =
                !isPrimitive(value) ||
                    (value !== this._$committedValue && value !== noChange);
            if (change) {
                this._$committedValue = value;
            }
        }
        else {
            // Interpolation case
            const values = value;
            value = strings[0];
            let i, v;
            for (i = 0; i < strings.length - 1; i++) {
                v = resolveDirective(this, values[valueIndex + i], directiveParent, i);
                if (v === noChange) {
                    // If the user-provided value is `noChange`, use the previous value
                    v = this._$committedValue[i];
                }
                change ||=
                    !isPrimitive(v) || v !== this._$committedValue[i];
                if (v === nothing) {
                    value = nothing;
                }
                else if (value !== nothing) {
                    value += (v ?? '') + strings[i + 1];
                }
                // We always record each value, even if one is `nothing`, for future
                // change detection.
                this._$committedValue[i] = v;
            }
        }
        if (change && !noCommit) {
            this._commitValue(value);
        }
    }
    /** @internal */
    _commitValue(value) {
        if (value === nothing) {
            wrap(this.element).removeAttribute(this.name);
        }
        else {
            if (ENABLE_EXTRA_SECURITY_HOOKS) {
                if (this._sanitizer === undefined) {
                    this._sanitizer = sanitizerFactoryInternal(this.element, this.name, 'attribute');
                }
                value = this._sanitizer(value ?? '');
            }
            debugLogEvent &&
                debugLogEvent({
                    kind: 'commit attribute',
                    element: this.element,
                    name: this.name,
                    value,
                    options: this.options,
                });
            wrap(this.element).setAttribute(this.name, (value ?? ''));
        }
    }
}
class PropertyPart extends AttributePart {
    constructor() {
        super(...arguments);
        this.type = PROPERTY_PART;
    }
    /** @internal */
    _commitValue(value) {
        if (ENABLE_EXTRA_SECURITY_HOOKS) {
            if (this._sanitizer === undefined) {
                this._sanitizer = sanitizerFactoryInternal(this.element, this.name, 'property');
            }
            value = this._sanitizer(value);
        }
        debugLogEvent &&
            debugLogEvent({
                kind: 'commit property',
                element: this.element,
                name: this.name,
                value,
                options: this.options,
            });
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        this.element[this.name] = value === nothing ? undefined : value;
    }
}
class BooleanAttributePart extends AttributePart {
    constructor() {
        super(...arguments);
        this.type = BOOLEAN_ATTRIBUTE_PART;
    }
    /** @internal */
    _commitValue(value) {
        debugLogEvent &&
            debugLogEvent({
                kind: 'commit boolean attribute',
                element: this.element,
                name: this.name,
                value: !!(value && value !== nothing),
                options: this.options,
            });
        wrap(this.element).toggleAttribute(this.name, !!value && value !== nothing);
    }
}
class EventPart extends AttributePart {
    constructor(element, name, strings, parent, options) {
        super(element, name, strings, parent, options);
        this.type = EVENT_PART;
        if (DEV_MODE && this.strings !== undefined) {
            throw new Error(`A \`<${element.localName}>\` has a \`@${name}=...\` listener with ` +
                'invalid content. Event listeners in templates must have exactly ' +
                'one expression and no surrounding text.');
        }
    }
    // EventPart does not use the base _$setValue/_resolveValue implementation
    // since the dirty checking is more complex
    /** @internal */
    _$setValue(newListener, directiveParent = this) {
        newListener =
            resolveDirective(this, newListener, directiveParent, 0) ?? nothing;
        if (newListener === noChange) {
            return;
        }
        const oldListener = this._$committedValue;
        // If the new value is nothing or any options change we have to remove the
        // part as a listener.
        const shouldRemoveListener = (newListener === nothing && oldListener !== nothing) ||
            newListener.capture !==
                oldListener.capture ||
            newListener.once !==
                oldListener.once ||
            newListener.passive !==
                oldListener.passive;
        // If the new value is not nothing and we removed the listener, we have
        // to add the part as a listener.
        const shouldAddListener = newListener !== nothing &&
            (oldListener === nothing || shouldRemoveListener);
        debugLogEvent &&
            debugLogEvent({
                kind: 'commit event listener',
                element: this.element,
                name: this.name,
                value: newListener,
                options: this.options,
                removeListener: shouldRemoveListener,
                addListener: shouldAddListener,
                oldListener,
            });
        if (shouldRemoveListener) {
            this.element.removeEventListener(this.name, this, oldListener);
        }
        if (shouldAddListener) {
            this.element.addEventListener(this.name, this, newListener);
        }
        this._$committedValue = newListener;
    }
    handleEvent(event) {
        if (typeof this._$committedValue === 'function') {
            this._$committedValue.call(this.options?.host ?? this.element, event);
        }
        else {
            this._$committedValue.handleEvent(event);
        }
    }
}
class ElementPart {
    constructor(element, parent, options) {
        this.element = element;
        this.type = ELEMENT_PART;
        /** @internal */
        this._$disconnectableChildren = undefined;
        this._$parent = parent;
        this.options = options;
    }
    // See comment in Disconnectable interface for why this is a getter
    get _$isConnected() {
        return this._$parent._$isConnected;
    }
    _$setValue(value) {
        debugLogEvent &&
            debugLogEvent({
                kind: 'commit to element binding',
                element: this.element,
                value,
                options: this.options,
            });
        resolveDirective(this, value);
    }
}
/**
 * END USERS SHOULD NOT RELY ON THIS OBJECT.
 *
 * Private exports for use by other Lit packages, not intended for use by
 * external users.
 *
 * We currently do not make a mangled rollup build of the lit-ssr code. In order
 * to keep a number of (otherwise private) top-level exports mangled in the
 * client side code, we export a _$LH object containing those members (or
 * helper methods for accessing private fields of those members), and then
 * re-export them for use in lit-ssr. This keeps lit-ssr agnostic to whether the
 * client-side code is being used in `dev` mode or `prod` mode.
 *
 * This has a unique name, to disambiguate it from private exports in
 * lit-element, which re-exports all of lit-html.
 *
 * @private
 */
const _$LH = {
    // Used in lit-ssr
    _boundAttributeSuffix: boundAttributeSuffix,
    _marker: marker,
    _markerMatch: markerMatch,
    _HTML_RESULT: HTML_RESULT,
    _getTemplateHtml: getTemplateHtml,
    // Used in tests and private-ssr-support
    _TemplateInstance: TemplateInstance,
    _isIterable: isIterable,
    _resolveDirective: resolveDirective,
    _ChildPart: ChildPart,
    _AttributePart: AttributePart,
    _BooleanAttributePart: BooleanAttributePart,
    _EventPart: EventPart,
    _PropertyPart: PropertyPart,
    _ElementPart: ElementPart,
};
// Apply polyfills if available
const polyfillSupport = DEV_MODE
    ? global.litHtmlPolyfillSupportDevMode
    : global.litHtmlPolyfillSupport;
polyfillSupport?.(Template, ChildPart);
// IMPORTANT: do not change the property name or the assignment expression.
// This line will be used in regexes to search for lit-html usage.
(global.litHtmlVersions ??= []).push('3.3.2');
if (DEV_MODE && global.litHtmlVersions.length > 1) {
    queueMicrotask(() => {
        issueWarning('multiple-versions', `Multiple versions of Lit loaded. ` +
            `Loading multiple versions is not recommended.`);
    });
}
/**
 * Renders a value, usually a lit-html TemplateResult, to the container.
 *
 * This example renders the text "Hello, Zoe!" inside a paragraph tag, appending
 * it to the container `document.body`.
 *
 * ```js
 * import {html, render} from 'lit';
 *
 * const name = "Zoe";
 * render(html`<p>Hello, ${name}!</p>`, document.body);
 * ```
 *
 * @param value Any [renderable
 *   value](https://lit.dev/docs/templates/expressions/#child-expressions),
 *   typically a {@linkcode TemplateResult} created by evaluating a template tag
 *   like {@linkcode html} or {@linkcode svg}.
 * @param container A DOM container to render to. The first render will append
 *   the rendered value to the container, and subsequent renders will
 *   efficiently update the rendered value if the same result type was
 *   previously rendered there.
 * @param options See {@linkcode RenderOptions} for options documentation.
 * @see
 * {@link https://lit.dev/docs/libraries/standalone-templates/#rendering-lit-html-templates| Rendering Lit HTML Templates}
 */
const render = (value, container, options) => {
    if (DEV_MODE && container == null) {
        // Give a clearer error message than
        //     Uncaught TypeError: Cannot read properties of null (reading
        //     '_$litPart$')
        // which reads like an internal Lit error.
        throw new TypeError(`The container to render into may not be ${container}`);
    }
    const renderId = DEV_MODE ? debugLogRenderId++ : 0;
    const partOwnerNode = options?.renderBefore ?? container;
    // This property needs to remain unminified.
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    let part = partOwnerNode['_$litPart$'];
    debugLogEvent &&
        debugLogEvent({
            kind: 'begin render',
            id: renderId,
            value,
            container,
            options,
            part,
        });
    if (part === undefined) {
        const endNode = options?.renderBefore ?? null;
        // This property needs to remain unminified.
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        partOwnerNode['_$litPart$'] = part = new ChildPart(container.insertBefore(createMarker(), endNode), endNode, undefined, options ?? {});
    }
    part._$setValue(value);
    debugLogEvent &&
        debugLogEvent({
            kind: 'end render',
            id: renderId,
            value,
            container,
            options,
            part,
        });
    return part;
};
if (ENABLE_EXTRA_SECURITY_HOOKS) {
    render.setSanitizer = setSanitizer;
    render.createSanitizer = createSanitizer;
    if (DEV_MODE) {
        render._testOnlyClearSanitizerFactoryDoNotCallOrElse =
            _testOnlyClearSanitizerFactoryDoNotCallOrElse;
    }
}
//# sourceMappingURL=lit-html.js.map

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Check if module exists (development only)
/******/ 		if (__webpack_modules__[moduleId] === undefined) {
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/async module */
/******/ 	(() => {
/******/ 		var hasSymbol = typeof Symbol === "function";
/******/ 		var webpackQueues = hasSymbol ? Symbol("webpack queues") : "__webpack_queues__";
/******/ 		var webpackExports = hasSymbol ? Symbol("webpack exports") : "__webpack_exports__";
/******/ 		var webpackError = hasSymbol ? Symbol("webpack error") : "__webpack_error__";
/******/ 		
/******/ 		var resolveQueue = (queue) => {
/******/ 			if(queue && queue.d < 1) {
/******/ 				queue.d = 1;
/******/ 				queue.forEach((fn) => (fn.r--));
/******/ 				queue.forEach((fn) => (fn.r-- ? fn.r++ : fn()));
/******/ 			}
/******/ 		}
/******/ 		var wrapDeps = (deps) => (deps.map((dep) => {
/******/ 			if(dep !== null && typeof dep === "object") {
/******/ 		
/******/ 				if(dep[webpackQueues]) return dep;
/******/ 				if(dep.then) {
/******/ 					var queue = [];
/******/ 					queue.d = 0;
/******/ 					dep.then((r) => {
/******/ 						obj[webpackExports] = r;
/******/ 						resolveQueue(queue);
/******/ 					}, (e) => {
/******/ 						obj[webpackError] = e;
/******/ 						resolveQueue(queue);
/******/ 					});
/******/ 					var obj = {};
/******/ 		
/******/ 					obj[webpackQueues] = (fn) => (fn(queue));
/******/ 					return obj;
/******/ 				}
/******/ 			}
/******/ 			var ret = {};
/******/ 			ret[webpackQueues] = x => {};
/******/ 			ret[webpackExports] = dep;
/******/ 			return ret;
/******/ 		}));
/******/ 		__webpack_require__.a = (module, body, hasAwait) => {
/******/ 			var queue;
/******/ 			hasAwait && ((queue = []).d = -1);
/******/ 			var depQueues = new Set();
/******/ 			var exports = module.exports;
/******/ 			var currentDeps;
/******/ 			var outerResolve;
/******/ 			var reject;
/******/ 			var promise = new Promise((resolve, rej) => {
/******/ 				reject = rej;
/******/ 				outerResolve = resolve;
/******/ 			});
/******/ 			promise[webpackExports] = exports;
/******/ 			promise[webpackQueues] = (fn) => (queue && fn(queue), depQueues.forEach(fn), promise["catch"](x => {}));
/******/ 			module.exports = promise;
/******/ 			var handle = (deps) => {
/******/ 				currentDeps = wrapDeps(deps);
/******/ 				var fn;
/******/ 				var getResult = () => (currentDeps.map((d) => {
/******/ 		
/******/ 					if(d[webpackError]) throw d[webpackError];
/******/ 					return d[webpackExports];
/******/ 				}))
/******/ 				var promise = new Promise((resolve) => {
/******/ 					fn = () => (resolve(getResult));
/******/ 					fn.r = 0;
/******/ 					var fnQueue = (q) => (q !== queue && !depQueues.has(q) && (depQueues.add(q), q && !q.d && (fn.r++, q.push(fn))));
/******/ 					currentDeps.map((dep) => (dep[webpackQueues](fnQueue)));
/******/ 				});
/******/ 				return fn.r ? promise : getResult();
/******/ 			}
/******/ 			var done = (err) => ((err ? reject(promise[webpackError] = err) : outerResolve(exports)), resolveQueue(queue))
/******/ 			body(handle, done);
/******/ 			queue && queue.d < 0 && (queue.d = 0);
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module used 'module' so it can't be inlined
/******/ 	var __webpack_exports__ = __webpack_require__("./assets/cms/authentication/webauthn/register.js");
/******/ 	
/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoid2ViYXV0aG5SZWdpc3Rlci5qcyIsIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7Ozs7Ozs7Ozs7O0FBQWlGOztBQUUxRTtBQUNQO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLEtBQUs7QUFDTDtBQUNBO0FBQ0E7QUFDQSxNQUFNO0FBQ047QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOzs7QUFHTzs7QUFFUDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNUOztBQUVBOztBQUVBO0FBQ0E7QUFDQTtBQUNBLHdCQUF3QiwwRUFBaUI7QUFDekMsTUFBTTs7QUFFTjtBQUNBOztBQUVBOztBQUVBO0FBQ087QUFDUDtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7OztBQUdBO0FBQ0E7QUFDQTtBQUNBLHlCQUF5Qiw0RUFBbUI7QUFDNUMsTUFBTTtBQUNOO0FBQ0E7O0FBRUE7QUFDQTs7QUFFTztBQUNQLHdHQUF3RywyQkFBMkI7QUFDbkk7O0FBRU87QUFDUCx3R0FBd0csMkJBQTJCLFFBQVEseUJBQXlCO0FBQ3BLOzs7Ozs7Ozs7Ozs7Ozs7O0FDdkZ3QztBQUtQO0FBTVo7QUFDckI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7O0FBR0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFlBQVksZUFBZTtBQUMzQjtBQUNBLGdCQUFnQiwyQ0FBMkM7QUFDM0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGFBQWE7QUFDYjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTOztBQUVUO0FBQ0Esa0JBQWtCLDREQUFnQjtBQUNsQztBQUNBO0FBQ0EsS0FBSztBQUNMO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsS0FBSzs7QUFFTDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0Esa0JBQWtCLGdFQUFvQjtBQUN0QztBQUNBLFVBQVU7QUFDVjtBQUNBO0FBQ0EsS0FBSztBQUNMOztBQUVBO0FBQ0E7QUFDQSx3QkFBd0Isa0RBQU07QUFDOUI7QUFDQTtBQUNBO0FBQ0Esc0JBQXNCLDhDQUFJLEdBQUcsaUJBQWlCLDhDQUFJOztBQUVsRDtBQUNBLHdCQUF3QixnQkFBZ0IsOENBQUk7QUFDNUM7QUFDQTtBQUNBLDZDQUE2QyxVQUFVO0FBQ3ZEO0FBQ0E7QUFDQSx5Q0FBeUMsdUJBQXVCO0FBQ2hFLDREQUE0RCxlQUFlO0FBQzNFO0FBQ0E7O0FBRUEsMERBQTBELFFBQVEsWUFBWSxPQUFPLGVBQWUsVUFBVTtBQUM5RztBQUNBO0FBQ0EsbUZBQW1GLHlEQUF5RDtBQUM1SSxvRUFBb0UseURBQXlEO0FBQzdILG1EQUFtRCx5REFBeUQ7QUFDNUc7QUFDQTs7QUFFQSwwREFBMEQsUUFBUSxZQUFZLE9BQU87QUFDckY7QUFDQTtBQUNBO0FBQ0Esd0RBQXdELDJEQUEyRDtBQUNuSCxvRUFBb0UsMkRBQTJEO0FBQy9ILG1EQUFtRCwyREFBMkQ7QUFDOUc7QUFDQTtBQUNBO0FBQ0E7QUFDQSwyQkFBMkIsOENBQUk7QUFDL0I7QUFDQSxtREFBbUQsd0VBQXdFO0FBQzNILHlCQUF5QjtBQUN6QixRQUFRLGdEQUFNOztBQUVkO0FBQ0E7QUFDQSxTQUFTO0FBQ1QsS0FBSztBQUNMO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQSx3QkFBd0Isa0RBQU07QUFDOUI7QUFDQTtBQUNBLHNCQUFzQiw4Q0FBSSxHQUFHLDhCQUE4Qiw4Q0FBSTtBQUMvRDtBQUNBO0FBQ0E7QUFDQSxvQ0FBb0MsY0FBYztBQUNsRDtBQUNBO0FBQ0EsdUNBQXVDLDZFQUE2RTtBQUNwSCxrREFBa0QsV0FBVztBQUM3RDtBQUNBO0FBQ0EsZ0ZBQWdGLGtCQUFrQjtBQUNsRztBQUNBO0FBQ0E7QUFDQSw4Q0FBOEMseURBQXlEO0FBQ3ZHLDBEQUEwRCx5REFBeUQ7QUFDbkg7QUFDQTtBQUNBO0FBQ0E7QUFDQSx1QkFBdUIsOENBQUk7QUFDM0I7QUFDQSw4Q0FBOEMsOEVBQThFO0FBQzVILHFCQUFxQjtBQUNyQixRQUFRLGdEQUFNO0FBQ2QsS0FBSztBQUNMO0FBQ0E7QUFDQTs7O0FBR0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7O0FBRUE7QUFDQSxjQUFjLDhEQUFrQjs7QUFFaEM7O0FBRUE7QUFDQTtBQUNBLE1BQU07O0FBRU47O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsYUFBYTs7QUFFYjtBQUNBLFVBQVU7O0FBRVY7QUFDQSxVQUFVO0FBQ1Y7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHlCQUF5Qix3REFBd0Q7QUFDakY7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLEtBQUs7QUFDTDtBQUNBO0FBQ0E7QUFDQTtBQUNBLEtBQUs7QUFDTDs7QUFFQTs7QUFFQTtBQUNBLG9DQUFvQyxrREFBTTs7QUFFMUM7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQSxzQkFBc0I7QUFDdEI7QUFDQSxvQ0FBb0MsVUFBVTtBQUM5QztBQUNBO0FBQ0EseUJBQXlCLGlGQUFpRjtBQUMxRztBQUNBO0FBQ0EsZ0NBQWdDLE9BQU87QUFDdkM7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVDtBQUNBOztBQUVBO0FBQ0E7QUFDQSxTQUFTO0FBQ1QsS0FBSztBQUNMO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBLGdCQUFnQixrQ0FBa0M7QUFDbEQ7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGFBQWE7QUFDYjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTOztBQUVUO0FBQ0E7QUFDQTs7QUFFQTtBQUNBLGdDQUFnQyxrREFBTSw4RkFBOEYsWUFBWTs7QUFFaEo7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGlCQUFpQjs7QUFFakI7QUFDQTtBQUNBO0FBQ0EsS0FBSztBQUNMO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0EsZUFBZSwwQkFBMEI7QUFDekM7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTOztBQUVUO0FBQ0E7QUFDQTs7QUFFQSxNQUFNO0FBQ047QUFDQTtBQUNBOztBQUVBOztBQUVBLDBCQUEwQixrREFBTTtBQUNoQztBQUNBLEtBQUs7O0FBRUw7QUFDQTtBQUNBO0FBQ0EseUJBQXlCLDRFQUFtQjtBQUM1QyxNQUFNOztBQUVOOztBQUVBO0FBQ0E7O0FBRUE7QUFDQSw2QkFBNkIsa0RBQU07O0FBRW5DOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFhOztBQUViOztBQUVBOztBQUVBO0FBQ0EsS0FBSzs7QUFFTDtBQUNBO0FBQ0E7O0FBRUE7O0FBRUEsUUFBUSxnRkFBdUI7QUFDL0I7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQSxnQkFBZ0IseUZBQWdDOztBQUVoRDtBQUNBLGdCQUFnQix3RkFBK0I7QUFDL0M7OztBQUdBO0FBQ0E7QUFDQSxjQUFjOztBQUVkO0FBQ0E7QUFDQTtBQUNBLFVBQVU7QUFDVjtBQUNBO0FBQ0EsTUFBTTs7QUFFTjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsS0FBSztBQUNMO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0EsQzs7Ozs7Ozs7Ozs7Ozs7OztBQ2xkQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNPO0FBQ1A7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxvQkFBb0IsbUJBQW1CO0FBQ3ZDO0FBQ0E7QUFDQTtBQUNBOzs7Ozs7Ozs7Ozs7Ozs7O0FDNUJBO0FBQ0E7QUFDQTtBQUNPO0FBQ1A7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDTztBQUNQO0FBQ0E7Ozs7Ozs7Ozs7Ozs7Ozs7O0FDYnVFO0FBQ3ZFO0FBQ0E7QUFDQTtBQUNBO0FBQ087QUFDUCxTQUFTLG9GQUF1QjtBQUNoQztBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNPO0FBQ1A7QUFDQTs7Ozs7Ozs7Ozs7Ozs7O0FDekJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNPO0FBQ1A7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7Ozs7Ozs7Ozs7Ozs7Ozs7QUNkbUQ7QUFDQTtBQUNuRDtBQUNBO0FBQ0E7QUFDTyx1Q0FBdUMsaUJBQWlCO0FBQy9ELFlBQVksWUFBWTtBQUN4QjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSx1QkFBdUIsNERBQWE7QUFDcEM7QUFDQTtBQUNBO0FBQ0EsYUFBYTtBQUNiO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsbUJBQW1CLDREQUFhO0FBQ2hDO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVDtBQUNBO0FBQ0E7QUFDQSxhQUFhLGdFQUFhO0FBQzFCO0FBQ0EsdUJBQXVCLDREQUFhO0FBQ3BDLDRCQUE0Qiw4QkFBOEI7QUFDMUQ7QUFDQTtBQUNBLGFBQWE7QUFDYjtBQUNBO0FBQ0E7QUFDQSx1QkFBdUIsNERBQWE7QUFDcEMsdUNBQXVDLGVBQWU7QUFDdEQ7QUFDQTtBQUNBLGFBQWE7QUFDYjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsbUJBQW1CLDREQUFhO0FBQ2hDO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVDtBQUNBO0FBQ0E7Ozs7Ozs7Ozs7Ozs7Ozs7O0FDNURtRDtBQUNBO0FBQ25EO0FBQ0E7QUFDQTtBQUNPLHFDQUFxQyxpQkFBaUI7QUFDN0QsWUFBWSxZQUFZO0FBQ3hCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHVCQUF1Qiw0REFBYTtBQUNwQztBQUNBO0FBQ0E7QUFDQSxhQUFhO0FBQ2I7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHVCQUF1Qiw0REFBYTtBQUNwQztBQUNBO0FBQ0E7QUFDQSxhQUFhO0FBQ2I7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsdUJBQXVCLDREQUFhO0FBQ3BDO0FBQ0E7QUFDQTtBQUNBLGFBQWE7QUFDYjtBQUNBO0FBQ0E7QUFDQSx1QkFBdUIsNERBQWE7QUFDcEM7QUFDQTtBQUNBO0FBQ0EsYUFBYTtBQUNiO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxtQkFBbUIsNERBQWE7QUFDaEM7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNUO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLG1CQUFtQiw0REFBYTtBQUNoQztBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHVCQUF1Qiw0REFBYTtBQUNwQztBQUNBO0FBQ0E7QUFDQSxhQUFhO0FBQ2I7QUFDQTtBQUNBLG1CQUFtQiw0REFBYTtBQUNoQztBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBO0FBQ0EsYUFBYSxnRUFBYTtBQUMxQjtBQUNBLHVCQUF1Qiw0REFBYTtBQUNwQyw0QkFBNEIsOEJBQThCO0FBQzFEO0FBQ0E7QUFDQSxhQUFhO0FBQ2I7QUFDQTtBQUNBO0FBQ0EsdUJBQXVCLDREQUFhO0FBQ3BDLHVDQUF1QyxnQkFBZ0I7QUFDdkQ7QUFDQTtBQUNBLGFBQWE7QUFDYjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsdUJBQXVCLDREQUFhO0FBQ3BDO0FBQ0E7QUFDQTtBQUNBLGFBQWE7QUFDYjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsbUJBQW1CLDREQUFhO0FBQ2hDO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVDtBQUNBO0FBQ0E7Ozs7Ozs7Ozs7Ozs7OztBQzdIQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ087QUFDUDtBQUNBO0FBQ0E7QUFDQSwyQ0FBMkMsR0FBRztBQUM5Qzs7Ozs7Ozs7Ozs7Ozs7OztBQ2J1RTtBQUN2RTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDTztBQUNQLFNBQVMsb0ZBQXVCO0FBQ2hDO0FBQ0E7QUFDQTtBQUNBOzs7Ozs7Ozs7Ozs7Ozs7QUNaQTtBQUNBO0FBQ0E7QUFDQTtBQUNPO0FBQ1A7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7Ozs7Ozs7Ozs7Ozs7OztBQ1p1RTtBQUNoRTtBQUNQLFlBQVksS0FBSztBQUNqQjtBQUNBO0FBQ0EsWUFBWSxvRkFBdUI7QUFDbkM7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7Ozs7Ozs7Ozs7Ozs7O0FDYkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDTzs7Ozs7Ozs7Ozs7Ozs7O0FDcENQO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDTztBQUNQLGtCQUFrQiw2QkFBNkI7QUFDL0M7QUFDQSx5QkFBeUIsT0FBTztBQUNoQztBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNUO0FBQ0E7QUFDQTtBQUNBOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztBQzlCK0M7QUFDRTtBQUNJO0FBQ1M7QUFDRDtBQUNSO0FBQ0E7QUFDSDtBQUNQO0FBQ1Y7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0FDVCtDO0FBQ0E7QUFDQTtBQUNnQjtBQUNBO0FBQ1I7QUFDZDtBQUNVO0FBQ3BGO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ087QUFDUDtBQUNBO0FBQ0E7QUFDQTtBQUNBLG9CQUFvQjtBQUNwQjtBQUNBLFlBQVksOEVBQThFO0FBQzFGLFNBQVMsNEZBQXVCO0FBQ2hDO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLDZEQUE2RCx3R0FBK0I7QUFDNUY7QUFDQTtBQUNBO0FBQ0E7QUFDQSxtQkFBbUIsNEZBQXVCO0FBQzFDO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLG9CQUFvQiw0R0FBK0I7QUFDbkQ7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHdCQUF3QixrRkFBb0I7QUFDNUM7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsY0FBYyxvR0FBMkIsR0FBRyxpQ0FBaUM7QUFDN0U7QUFDQTtBQUNBO0FBQ0E7QUFDQSxZQUFZLDRCQUE0QjtBQUN4QztBQUNBO0FBQ0EscUJBQXFCLDRGQUF1QjtBQUM1QztBQUNBO0FBQ0E7QUFDQTtBQUNBLGVBQWUsNEZBQXVCO0FBQ3RDO0FBQ0EsK0JBQStCLDRGQUF1QjtBQUN0RCw0QkFBNEIsNEZBQXVCO0FBQ25ELHVCQUF1Qiw0RkFBdUI7QUFDOUM7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBLGlDQUFpQyxnR0FBeUI7QUFDMUQ7QUFDQTs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztBQzlGZ0Y7QUFDQTtBQUNBO0FBQ2dCO0FBQ1o7QUFDVjtBQUNVO0FBQ3BGO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNPO0FBQ1A7QUFDQTtBQUNBO0FBQ0E7QUFDQSxvQkFBb0I7QUFDcEI7QUFDQSxZQUFZLHVDQUF1QztBQUNuRCxTQUFTLDRGQUF1QjtBQUNoQztBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsbUJBQW1CLDRGQUF1QjtBQUMxQztBQUNBO0FBQ0EsZ0JBQWdCLDRGQUF1QjtBQUN2QyxTQUFTO0FBQ1QsZ0VBQWdFLHdHQUErQjtBQUMvRjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSwyQkFBMkIsa0ZBQW9CO0FBQy9DO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGNBQWMsZ0dBQXlCLEdBQUcsb0NBQW9DO0FBQzlFO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsWUFBWSw0QkFBNEI7QUFDeEM7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLG9DQUFvQyw0RkFBdUI7QUFDM0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSx3Q0FBd0MsNEZBQXVCO0FBQy9EO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsZUFBZSw0RkFBdUI7QUFDdEM7QUFDQSwrQkFBK0IsNEZBQXVCO0FBQ3RELDRCQUE0Qiw0RkFBdUI7QUFDbkQ7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBLGlDQUFpQyxnR0FBeUI7QUFDMUQ7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSwwR0FBMEcsV0FBVztBQUNySDs7Ozs7Ozs7Ozs7O0FDeEhVOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7QUNBVjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsOERBQThEO0FBQzlEO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsMENBQTBDLE1BQU07QUFDaEQ7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsS0FBSztBQUNMO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLEtBQUs7QUFDTDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxzQkFBc0Isa0NBQWtDO0FBQ3hEO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsdUJBQXVCLFlBQVk7QUFDbkM7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSwyQ0FBMkM7QUFDM0M7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0Esb0NBQW9DLFdBQVcsTUFBTSxVQUFVLEtBQUssV0FBVyxJQUFJLFdBQVcsTUFBTSxnQkFBZ0I7QUFDcEg7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGdEQUFnRCxNQUFNO0FBQ3REO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ087QUFDUDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSw2QkFBNkIsZ0JBQWdCO0FBQzdDO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNPO0FBQ1A7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNUO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0Esc0NBQXNDLGdCQUFnQjtBQUN0RDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDTztBQUNQO0FBQ0E7QUFDQTtBQUNBO0FBQ087QUFDUDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsSUFBSTtBQUNKO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ087QUFDUDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsNkRBQTZELGlCQUFpQjtBQUM5RTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0Esb0JBQW9CLE9BQU87QUFDM0I7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLDBEQUEwRCxnQkFBZ0I7QUFDMUU7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLE1BQU0sK0JBQStCO0FBQ3JDO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSw0RUFBNEUsSUFBSTtBQUNoRiwrRUFBK0UsS0FBSztBQUNwRjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSw2QkFBNkI7QUFDN0I7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsNkJBQTZCO0FBQzdCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHdDQUF3QyxlQUFlO0FBQ3ZEO0FBQ0E7QUFDQTtBQUNBLHlDQUF5QyxzQ0FBc0M7QUFDL0U7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGlDQUFpQyxvQ0FBb0M7QUFDckU7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EscUNBQXFDLHNDQUFzQztBQUMzRTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsMENBQTBDLE1BQU0sYUFBYSxNQUFNO0FBQ25FO0FBQ0E7QUFDQTtBQUNBLG9DQUFvQyxJQUFJO0FBQ3hDO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsYUFBYTtBQUNiO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxnQkFBZ0IsTUFBTSxTQUFTLGtCQUFrQjtBQUNqRDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EscUJBQXFCO0FBQ3JCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsbUJBQW1CO0FBQ25CO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHlCQUF5QjtBQUN6QjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLG1FQUFtRTtBQUNuRTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLHlCQUF5QjtBQUN6QjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxvREFBb0QsTUFBTTtBQUMxRDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGlCQUFpQjtBQUNqQjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGlCQUFpQjtBQUNqQjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EscUJBQXFCO0FBQ3JCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EscUJBQXFCO0FBQ3JCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGdCQUFnQiwrQkFBK0I7QUFDL0M7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsaUJBQWlCO0FBQ2pCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxpQkFBaUI7QUFDakI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxpQkFBaUI7QUFDakI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsbUNBQW1DLEVBQUU7QUFDckM7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSx3QkFBd0Isd0JBQXdCO0FBQ2hEO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxpQkFBaUI7QUFDakI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsYUFBYTtBQUNiO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGFBQWE7QUFDYjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0Esb0NBQW9DLGtCQUFrQixlQUFlLEtBQUs7QUFDMUU7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFhO0FBQ2I7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFhO0FBQ2I7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ087QUFDUDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsS0FBSztBQUNMO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxXQUFXLGNBQWM7QUFDekI7QUFDQTtBQUNBLDJCQUEyQixLQUFLO0FBQ2hDO0FBQ0E7QUFDQTtBQUNBO0FBQ0Esa0JBQWtCLDBCQUEwQjtBQUM1QyxXQUFXLGdCQUFnQixJQUFJLGNBQWM7QUFDN0M7QUFDQTtBQUNBO0FBQ0E7QUFDQSx1QkFBdUIseUJBQXlCO0FBQ2hEO0FBQ0EsSUFBSTtBQUNKO0FBQ087QUFDUDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsdUVBQXVFLFVBQVU7QUFDakY7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVDtBQUNBO0FBQ0E7QUFDQTtBQUNBLDZJQUE2STtBQUM3STtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLG9DOzs7Ozs7VUN6K0NBO1VBQ0E7O1VBRUE7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7O1VBRUE7VUFDQTs7VUFFQTtVQUNBO1VBQ0E7Ozs7O1dDNUJBO1dBQ0E7V0FDQTtXQUNBOztXQUVBO1dBQ0E7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBO1dBQ0E7V0FDQTs7V0FFQTtXQUNBO1dBQ0E7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBLElBQUk7V0FDSjtXQUNBO1dBQ0EsSUFBSTtXQUNKOztXQUVBO1dBQ0E7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBO1dBQ0E7V0FDQSxDQUFDO1dBQ0Q7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBLEVBQUU7V0FDRjtXQUNBLHNHQUFzRztXQUN0RztXQUNBO1dBQ0E7V0FDQTtXQUNBOztXQUVBO1dBQ0E7V0FDQSxHQUFHO1dBQ0g7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBLEdBQUc7V0FDSDtXQUNBO1dBQ0E7V0FDQTtXQUNBO1dBQ0EsRTs7Ozs7V0N2RUE7V0FDQTtXQUNBO1dBQ0E7V0FDQSx5Q0FBeUMsd0NBQXdDO1dBQ2pGO1dBQ0E7V0FDQSxFOzs7OztXQ1BBLHdGOzs7OztXQ0FBO1dBQ0E7V0FDQTtXQUNBLHVEQUF1RCxpQkFBaUI7V0FDeEU7V0FDQSxnREFBZ0QsYUFBYTtXQUM3RCxFOzs7OztVRU5BO1VBQ0E7VUFDQTtVQUNBIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vYXNzZXRzL2Ntcy9hdXRoZW50aWNhdGlvbi93ZWJhdXRobi9jbGllbnQuanMiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vYXNzZXRzL2Ntcy9hdXRoZW50aWNhdGlvbi93ZWJhdXRobi9yZWdpc3Rlci5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9ub2RlX21vZHVsZXMvQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXIvZXNtL2hlbHBlcnMvYmFzZTY0VVJMU3RyaW5nVG9CdWZmZXIuanMiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vbm9kZV9tb2R1bGVzL0BzaW1wbGV3ZWJhdXRobi9icm93c2VyL2VzbS9oZWxwZXJzL2Jyb3dzZXJTdXBwb3J0c1dlYkF1dGhuLmpzIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS8uL25vZGVfbW9kdWxlcy9Ac2ltcGxld2ViYXV0aG4vYnJvd3Nlci9lc20vaGVscGVycy9icm93c2VyU3VwcG9ydHNXZWJBdXRobkF1dG9maWxsLmpzIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS8uL25vZGVfbW9kdWxlcy9Ac2ltcGxld2ViYXV0aG4vYnJvd3Nlci9lc20vaGVscGVycy9idWZmZXJUb0Jhc2U2NFVSTFN0cmluZy5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9ub2RlX21vZHVsZXMvQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXIvZXNtL2hlbHBlcnMvaWRlbnRpZnlBdXRoZW50aWNhdGlvbkVycm9yLmpzIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS8uL25vZGVfbW9kdWxlcy9Ac2ltcGxld2ViYXV0aG4vYnJvd3Nlci9lc20vaGVscGVycy9pZGVudGlmeVJlZ2lzdHJhdGlvbkVycm9yLmpzIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS8uL25vZGVfbW9kdWxlcy9Ac2ltcGxld2ViYXV0aG4vYnJvd3Nlci9lc20vaGVscGVycy9pc1ZhbGlkRG9tYWluLmpzIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS8uL25vZGVfbW9kdWxlcy9Ac2ltcGxld2ViYXV0aG4vYnJvd3Nlci9lc20vaGVscGVycy9wbGF0Zm9ybUF1dGhlbnRpY2F0b3JJc0F2YWlsYWJsZS5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9ub2RlX21vZHVsZXMvQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXIvZXNtL2hlbHBlcnMvdG9BdXRoZW50aWNhdG9yQXR0YWNobWVudC5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9ub2RlX21vZHVsZXMvQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXIvZXNtL2hlbHBlcnMvdG9QdWJsaWNLZXlDcmVkZW50aWFsRGVzY3JpcHRvci5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9ub2RlX21vZHVsZXMvQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXIvZXNtL2hlbHBlcnMvd2ViQXV0aG5BYm9ydFNlcnZpY2UuanMiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vbm9kZV9tb2R1bGVzL0BzaW1wbGV3ZWJhdXRobi9icm93c2VyL2VzbS9oZWxwZXJzL3dlYkF1dGhuRXJyb3IuanMiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vbm9kZV9tb2R1bGVzL0BzaW1wbGV3ZWJhdXRobi9icm93c2VyL2VzbS9pbmRleC5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9ub2RlX21vZHVsZXMvQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXIvZXNtL21ldGhvZHMvc3RhcnRBdXRoZW50aWNhdGlvbi5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9ub2RlX21vZHVsZXMvQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXIvZXNtL21ldGhvZHMvc3RhcnRSZWdpc3RyYXRpb24uanMiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vbm9kZV9tb2R1bGVzL0BzaW1wbGV3ZWJhdXRobi9icm93c2VyL2VzbS90eXBlcy9pbmRleC5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9ub2RlX21vZHVsZXMvbGl0LWh0bWwvZGV2ZWxvcG1lbnQvbGl0LWh0bWwuanMiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlL3dlYnBhY2svYm9vdHN0cmFwIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS93ZWJwYWNrL3J1bnRpbWUvYXN5bmMgbW9kdWxlIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS93ZWJwYWNrL3J1bnRpbWUvZGVmaW5lIHByb3BlcnR5IGdldHRlcnMiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlL3dlYnBhY2svcnVudGltZS9oYXNPd25Qcm9wZXJ0eSBzaG9ydGhhbmQiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlL3dlYnBhY2svcnVudGltZS9tYWtlIG5hbWVzcGFjZSBvYmplY3QiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlL3dlYnBhY2svYmVmb3JlLXN0YXJ0dXAiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlL3dlYnBhY2svc3RhcnR1cCIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvd2VicGFjay9hZnRlci1zdGFydHVwIl0sInNvdXJjZXNDb250ZW50IjpbImltcG9ydCB7IHN0YXJ0UmVnaXN0cmF0aW9uLCBzdGFydEF1dGhlbnRpY2F0aW9uIH0gZnJvbSAnQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXInO1xuXG5leHBvcnQgYXN5bmMgZnVuY3Rpb24gX2ZldGNoKHBhdGgsIHBheWxvYWQgPSAnJykge1xuICAgIGNvbnN0IGhlYWRlcnMgPSB7XG4gICAgICAgICdYLVJlcXVlc3RlZC1XaXRoJzogJ1hNTEh0dHBSZXF1ZXN0JyxcbiAgICAgICAgJ0NvbnRlbnQtVHlwZSc6ICdhcHBsaWNhdGlvbi9qc29uJ1xuICAgIH07XG4gICAgaWYgKHBheWxvYWQgJiYgIShwYXlsb2FkIGluc3RhbmNlb2YgRm9ybURhdGEpKSB7XG4gICAgICAgIHBheWxvYWQgPSBKU09OLnN0cmluZ2lmeShwYXlsb2FkKTtcbiAgICB9XG4gICAgY29uc3QgcmVzID0gYXdhaXQgZmV0Y2gocGF0aCwge1xuICAgICAgICBtZXRob2Q6ICdQT1NUJyxcbiAgICAgICAgY3JlZGVudGlhbHM6ICdzYW1lLW9yaWdpbicsXG4gICAgICAgIGhlYWRlcnM6IGhlYWRlcnMsXG4gICAgICAgIGJvZHk6IHBheWxvYWQsXG4gICAgfSk7XG4gICAgaWYgKHJlcy5zdGF0dXMgPT09IDIwMCB8fCByZXMuc3RhdHVzID09PSAyMDEpIHtcbiAgICAgICAgLy8gU2VydmVyIGF1dGhlbnRpY2F0aW9uIHN1Y2NlZWRlZFxuICAgICAgICByZXR1cm4gcmVzLmpzb24oKTtcbiAgICB9IGVsc2Uge1xuICAgICAgICAvLyBTZXJ2ZXIgYXV0aGVudGljYXRpb24gZmFpbGVkXG4gICAgICAgIGNvbnN0IHJlc3VsdCA9IGF3YWl0IHJlcy5qc29uKCk7XG5cbiAgICAgICAgaWYocmVzdWx0Lm1lc3NhZ2Upe1xuICAgICAgICAgICAgdGhyb3cgbmV3IEVycm9yKHJlc3VsdC5tZXNzYWdlKTtcbiAgICAgICAgfVxuXG4gICAgICAgIHRocm93IG5ldyBFcnJvcihyZXN1bHQuZXJyb3IpO1xuICAgIH1cbn1cblxuXG5leHBvcnQgYXN5bmMgZnVuY3Rpb24gcmVnaXN0ZXJDcmVkZW50aWFsKHVzZXJuYW1lLCBkaXNwbGF5TmFtZSkge1xuXG4gICAgbGV0IGRhdGEgPSB7XG4gICAgICAgIHVzZXJuYW1lOiB1c2VybmFtZSxcbiAgICAgICAgZGlzcGxheU5hbWU6IGRpc3BsYXlOYW1lLFxuICAgICAgICBhdXRoZW50aWNhdG9yU2VsZWN0aW9uOiB7XG4gICAgICAgICAgICB1c2VyVmVyaWZpY2F0aW9uOiAncHJlZmVycmVkJyxcbiAgICAgICAgfSxcbiAgICB9XG5cbiAgICBjb25zdCBvcHRpb25zID0gYXdhaXQgX2ZldGNoKCcvYWRtaW4vcmVnaXN0ZXIvb3B0aW9ucycsIGRhdGEpO1xuXG4gICAgbGV0IGF0dFJlc3A7XG4gICAgdHJ5IHtcbiAgICAgICAgLy8gUGFzcyB0aGUgb3B0aW9ucyB0byB0aGUgYXV0aGVudGljYXRvciBhbmQgd2FpdCBmb3IgYSByZXNwb25zZVxuICAgICAgICBhdHRSZXNwID0gYXdhaXQgc3RhcnRSZWdpc3RyYXRpb24ob3B0aW9ucyk7XG4gICAgfSBjYXRjaCAoZXJyb3IpIHtcblxuICAgICAgICB0aHJvdyBlcnJvcjtcbiAgICB9XG5cbiAgICByZXR1cm4gYXdhaXQgX2ZldGNoKENNU1JvdXRpbmcuZ2VuZXJhdGUoJ2luaXRjbXNfd2ViYXV0aG5fcmVnaXN0ZXJfcmVzcG9uc2UnKSwgYXR0UmVzcCk7XG5cbn1cbmV4cG9ydCBhc3luYyBmdW5jdGlvbiBhdXRoZW50aWNhdGUodXNlcm5hbWUpIHtcbiAgICBsZXQgcGF5bG9hZCA9IHtcbiAgICAgICAgcmVxdWlyZVVzZXJWZXJpZmljYXRpb246ICdwcmVmZXJyZWQnLFxuICAgIH1cblxuICAgIGlmKHVzZXJuYW1lKXtcbiAgICAgICAgcGF5bG9hZC51c2VybmFtZSA9IHVzZXJuYW1lO1xuICAgIH1cbiAgICBjb25zdCBvcHRpb25zID0gYXdhaXQgX2ZldGNoKCcvYWRtaW4vYXNzZXJ0aW9uL29wdGlvbnMnLCBwYXlsb2FkKTtcbiAgICBpZih1c2VybmFtZSAmJiBvcHRpb25zLmFsbG93Q3JlZGVudGlhbHMgPT09IHVuZGVmaW5lZCl7XG4gICAgICAgIHRocm93IG5ldyBFcnJvcignbm9fY3JlZGVudGlhbHMnKTtcbiAgICB9XG5cblxuICAgIGxldCBhc3NlUmVzcDtcbiAgICB0cnkge1xuICAgICAgICAvLyBQYXNzIHRoZSBvcHRpb25zIHRvIHRoZSBhdXRoZW50aWNhdG9yIGFuZCB3YWl0IGZvciBhIHJlc3BvbnNlXG4gICAgICAgIGFzc2VSZXNwID0gYXdhaXQgc3RhcnRBdXRoZW50aWNhdGlvbihvcHRpb25zKTtcbiAgICB9IGNhdGNoIChlcnJvcikge1xuICAgICAgICB0aHJvdyBlcnJvcjtcbiAgICB9XG5cbiAgICByZXR1cm4gX2ZldGNoKCcvYWRtaW4vYXNzZXJ0aW9uJywgYXNzZVJlc3ApO1xufVxuXG5leHBvcnQgYXN5bmMgZnVuY3Rpb24gdW5yZWdpc3RlckNyZWRlbnRpYWwoY3JlZElkKSB7XG4gICAgcmV0dXJuIF9mZXRjaChDTVNSb3V0aW5nLmdlbmVyYXRlKCdhZG1pbl9uZXR3b3JraW5nX2luaXRjbXNfdXNlcl9yZW1vdmVfd2ViYXV0aG5fa2V5JykgKyBgP2NyZWRJZD0ke2VuY29kZVVSSUNvbXBvbmVudChjcmVkSWQpfWApO1xufVxuXG5leHBvcnQgYXN5bmMgZnVuY3Rpb24gdXBkYXRlQ3JlZGVudGlhbChjcmVkSWQsIG5hbWUpIHtcbiAgICByZXR1cm4gX2ZldGNoKENNU1JvdXRpbmcuZ2VuZXJhdGUoJ2FkbWluX25ldHdvcmtpbmdfaW5pdGNtc191c2VyX3JlbmFtZV93ZWJhdXRobl9rZXknKSArIGA/Y3JlZElkPSR7ZW5jb2RlVVJJQ29tcG9uZW50KGNyZWRJZCl9Jm5hbWU9JHtlbmNvZGVVUklDb21wb25lbnQobmFtZSl9YCk7XG59XG4iLCJpbXBvcnQgeyBodG1sLCByZW5kZXIgfSBmcm9tICdsaXQtaHRtbCc7XG5pbXBvcnQge1xuICAgIGJyb3dzZXJTdXBwb3J0c1dlYkF1dGhuLFxuICAgIGJyb3dzZXJTdXBwb3J0c1dlYkF1dGhuQXV0b2ZpbGwsXG4gICAgcGxhdGZvcm1BdXRoZW50aWNhdG9ySXNBdmFpbGFibGUsIFdlYkF1dGhuQWJvcnRTZXJ2aWNlLCBzdGFydEF1dGhlbnRpY2F0aW9uXG59IGZyb20gJ0BzaW1wbGV3ZWJhdXRobi9icm93c2VyJztcbmltcG9ydCB7XG4gICAgX2ZldGNoLFxuICAgIHVucmVnaXN0ZXJDcmVkZW50aWFsLFxuICAgIHVwZGF0ZUNyZWRlbnRpYWwsXG4gICAgcmVnaXN0ZXJDcmVkZW50aWFsLCBhdXRoZW50aWNhdGUsXG59IGZyb20gJy4vY2xpZW50LmpzJztcbmxldCBUcmFuc2xhdG9yID0gYXdhaXQgQ01TQWRtaW4uZ2V0VHJhbnNsYXRvcigpO1xuY29uc3QgY3JlYXRlUGFzc2tleUJ1dHRvbnMgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcuY3JlYXRlLXBhc3NrZXknKTtcbmNvbnN0IHRlc3RTaWduaW5CdXR0b24gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcjdGVzdC1zaWduaW4nKTtcbmNvbnN0IGRlbGV0ZUF1dGhlbnRpY2F0b3JCdXR0b24gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcjZGVsZXRlLWF1dGhlbnRpY2F0b3InKTtcbmNvbnN0IGNyZWF0ZUF1dGhlbnRpY2F0b3JCdXR0b24gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcjY3JlYXRlLWF1dGhlbnRpY2F0b3InKTtcbmNvbnN0IGN1cnJlbnRBdXRoZW50aWNhdG9yPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcjY3VycmVudC1hdXRoZW50aWNhdG9yJyk7XG5jb25zdCB3ZWJhdXRobkVuYWJsZWQgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKFwibWV0YVtuYW1lPSd3ZWJhdXRobi1lbmFibGVkJ11cIikuZ2V0QXR0cmlidXRlKFwiY29udGVudFwiKTtcbmNvbnN0IGdvb2dsZUF1dGhlbnRpY2F0b3JFbmFibGVkID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcihcIm1ldGFbbmFtZT0nZ29vZ2xlLWF1dGhlbnRpY2F0b3ItZW5hYmxlZCddXCIpLmdldEF0dHJpYnV0ZShcImNvbnRlbnRcIik7XG5cblxuYXN5bmMgZnVuY3Rpb24gcmVuYW1lKGUpIHtcbiAgICBsZXQgZWwgPSBlLnRhcmdldFxuICAgIGlmKCFlbC5jbGFzc0xpc3QuY29udGFpbnMoJ2J0bicpKSB7XG4gICAgICAgIGVsID0gZWwuY2xvc2VzdCgnLmJ0bicpO1xuICAgIH1cbiAgICBjb25zdCB7IGNyZWRJZCwgbmFtZSB9ID0gZWwuZGF0YXNldDtcbiAgICB0cnl7XG4gICAgICAgIGNvbnN0IHsgdmFsdWU6IG5ld05hbWUsIGlzQ29uZmlybWVkOiBpc0NvbmZpcm1lZCB9ID0gYXdhaXQgU3dhbC5maXJlKHtcbiAgICAgICAgICAgIHRpdGxlOiBUcmFuc2xhdG9yLnRyYW5zKCdwYXNza2V5X3JlbmFtZS50aXRsZScsIFtdLCAnc2VjdXJpdHknKSxcbiAgICAgICAgICAgIGlucHV0TGFiZWw6IFRyYW5zbGF0b3IudHJhbnMoJ3Bhc3NrZXlfcmVuYW1lLmlucHV0X2xhYmVsJywgW10sICdzZWN1cml0eScpLFxuICAgICAgICAgICAgaW5wdXQ6ICd0ZXh0JyxcbiAgICAgICAgICAgIGlucHV0VmFsdWU6IG5hbWUsXG4gICAgICAgICAgICBpbnB1dFZhbGlkYXRvcjogKHZhbHVlKSA9PiB7XG4gICAgICAgICAgICAgICAgaWYgKCF2YWx1ZSkge1xuICAgICAgICAgICAgICAgICAgICByZXR1cm4gVHJhbnNsYXRvci50cmFucygncGFzc2tleV9yZW5hbWUuaW5wdXRfaW52YWxpZCcsIFtdLCAnc2VjdXJpdHknKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9LFxuICAgICAgICAgICAgYnV0dG9uc1N0eWxpbmc6IGZhbHNlLFxuICAgICAgICAgICAgc2hvd0NhbmNlbEJ1dHRvbjogdHJ1ZSxcbiAgICAgICAgICAgIGNvbmZpcm1CdXR0b25UZXh0OiBUcmFuc2xhdG9yLnRyYW5zKCdzdWJtaXQnLCBbXSwgJ3NlY3VyaXR5JyksXG4gICAgICAgICAgICBjYW5jZWxCdXR0b25UZXh0OiBUcmFuc2xhdG9yLnRyYW5zKCdjYW5jZWwnLCBbXSwgJ3NlY3VyaXR5JyksXG4gICAgICAgICAgICByZXZlcnNlQnV0dG9uczogdHJ1ZSxcbiAgICAgICAgICAgIGN1c3RvbUNsYXNzOiB7XG4gICAgICAgICAgICAgICAgY29uZmlybUJ1dHRvbjogXCJidG4gYnRuLXNtIGJ0bi1wcmltYXJ5XCIsXG4gICAgICAgICAgICAgICAgY2FuY2VsQnV0dG9uOiBcImJ0biBidG4tc20gYnRuLWxpZ2h0XCJcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSlcblxuICAgICAgICBpZiAoaXNDb25maXJtZWQpIHtcbiAgICAgICAgICAgIGF3YWl0IHVwZGF0ZUNyZWRlbnRpYWwoY3JlZElkLCBuZXdOYW1lKTtcbiAgICAgICAgICAgIHJlbmRlckNyZWRlbnRpYWxzKCk7XG4gICAgICAgIH1cbiAgICB9Y2F0Y2ggKGUpIHtcbiAgICAgICAgQ01TQWRtaW4uY3JlYXRlSW5pdENtc01lc3NhZ2VCb3goJ2Vycm9yJywgZS5tZXNzYWdlKTtcbiAgICB9XG59O1xuXG5hc3luYyBmdW5jdGlvbiByZW1vdmUoZSkge1xuICAgIFN3YWwuZmlyZSh7XG4gICAgICAgIHRpdGxlOiBUcmFuc2xhdG9yLnRyYW5zKCdwYXNza2V5X2RlbGV0ZS50aXRsZScsIFtdLCAnc2VjdXJpdHknKSxcbiAgICAgICAgdGV4dDogVHJhbnNsYXRvci50cmFucygncGFzc2tleV9kZWxldGUudGV4dCcsIFtdLCAnc2VjdXJpdHknKSxcbiAgICAgICAgaWNvbjogJ3dhcm5pbmcnLFxuICAgICAgICBidXR0b25zU3R5bGluZzogZmFsc2UsXG4gICAgICAgIHNob3dDYW5jZWxCdXR0b246IHRydWUsXG4gICAgICAgIGNvbmZpcm1CdXR0b25UZXh0OiBUcmFuc2xhdG9yLnRyYW5zKCdkZWxldGUnLCBbXSwgJ3NlY3VyaXR5JyksXG4gICAgICAgIGNhbmNlbEJ1dHRvblRleHQ6IFRyYW5zbGF0b3IudHJhbnMoJ2NhbmNlbCcsIFtdLCAnc2VjdXJpdHknKSxcbiAgICAgICAgcmV2ZXJzZUJ1dHRvbnM6IHRydWUsXG4gICAgICAgIGN1c3RvbUNsYXNzOiB7XG4gICAgICAgICAgICBjb25maXJtQnV0dG9uOiBcImJ0biBidG4tc20gYnRuLWRhbmdlclwiLFxuICAgICAgICAgICAgY2FuY2VsQnV0dG9uOiBcImJ0biBidG4tc20gYnRuLWxpZ2h0XCJcbiAgICAgICAgfVxuICAgIH0pLnRoZW4oYXN5bmMgKHJlc3VsdCkgPT4ge1xuXG4gICAgICAgIGlmICghcmVzdWx0LmlzQ29uZmlybWVkKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cbiAgICAgICAgbGV0IGVsID0gZS50YXJnZXRcbiAgICAgICAgaWYoIWVsLmNsYXNzTGlzdC5jb250YWlucygnYnRuJykpIHtcbiAgICAgICAgICAgIGVsID0gZWwuY2xvc2VzdCgnLmJ0bicpO1xuICAgICAgICB9XG4gICAgICAgIHRyeSB7XG4gICAgICAgICAgICBhd2FpdCB1bnJlZ2lzdGVyQ3JlZGVudGlhbChlbC5kYXRhc2V0LmNyZWRJZCk7XG4gICAgICAgICAgICByZW5kZXJDcmVkZW50aWFscygpO1xuICAgICAgICB9IGNhdGNoIChlKSB7XG4gICAgICAgICAgICBhbGVydChlLm1lc3NhZ2UpO1xuICAgICAgICB9XG4gICAgfSlcbn1cblxuYXN5bmMgZnVuY3Rpb24gcmVuZGVyQ3JlZGVudGlhbHMoKSB7XG4gICAgdHJ5IHtcbiAgICAgICAgbGV0IHJlcyA9IGF3YWl0IF9mZXRjaChDTVNSb3V0aW5nLmdlbmVyYXRlKCdhZG1pbl9uZXR3b3JraW5nX2luaXRjbXNfdXNlcl9nZXRfd2ViYXV0aG5fa2V5cycpKVxuICAgICAgICBjb25zdCBsaXN0ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI2xpc3QnKTtcbiAgICAgICAgZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI3Bhc3NrZXlzLXNldHVwJykuY2xhc3NMaXN0LnRvZ2dsZSgnZC1ub25lJywgcmVzLmxlbmd0aCA8IDEpO1xuICAgICAgICBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcjcGFzc2tleXMtbm90LXNldHVwJykuY2xhc3NMaXN0LnRvZ2dsZSgnZC1ub25lJywgcmVzLmxlbmd0aCA+IDApO1xuICAgICAgICBjb25zdCBjcmVkcyA9IGh0bWxgJHtyZXMubGVuZ3RoID4gMCA/IGh0bWxgXG5cbiAgICAgICAgICAgICAgICA8dWwgY2xhc3M9XCJsaXN0LWdyb3VwXCI+XG4gICAgICAgICAgICAgICAgICAgICAgJHtyZXMubWFwKGNyZWQgPT4gaHRtbGBcbiAgICAgICAgICAgICAgICAgICAgICAgICAgPGxpIGNsYXNzPVwibGlzdC1ncm91cC1pdGVtIGQtZmxleCBqdXN0aWZ5LWNvbnRlbnQtYmV0d2VlbiBhbGlnbi1pdGVtcy1zdGFydFwiPlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRpdj5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxpbWcgc3JjPVwiJHtjcmVkLmljb259XCIgY2xhc3M9XCJ3LTI1cHhcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cInctNzVcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZGl2PiR7Y3JlZC5uYW1lIHx8ICdVbm5hbWVkJ308L2Rpdj5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzPVwidGV4dC1tdXRlZFwiPiR7Y3JlZC5jcmVhdGVkQXR9PC9kaXY+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJidXR0b25zXCI+XG5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8YnV0dG9uIGRhdGEtY3JlZC1pZD1cIiR7Y3JlZC5pZH1cIiBAY2xpY2s9XCIke3JlbmFtZX1cIiBkYXRhLW5hbWU9XCIke2NyZWQubmFtZX1cIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY2xhc3M9XCIgYnRuIGJ0bi1saWdodCBidG4tc20gXCJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGRhdGEtYnMtdG9nZ2xlPVwidG9vbHRpcFwiXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBkYXRhLWJzLXBsYWNlbWVudD1cImJvdHRvbVwiIGFyaWEtbGFiZWw9XCIke1RyYW5zbGF0b3IudHJhbnMoXCJhY3Rpb25fZWRpdFwiLCBbXSwgXCJTb25hdGFBZG1pbkJ1bmRsZVwiKX1cIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZGF0YS1icy1vcmlnaW5hbC10aXRsZT1cIiR7VHJhbnNsYXRvci50cmFucyhcImFjdGlvbl9lZGl0XCIsIFtdLCBcIlNvbmF0YUFkbWluQnVuZGxlXCIpfVwiXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aXRsZT1cIiR7VHJhbnNsYXRvci50cmFucyhcImFjdGlvbl9lZGl0XCIsIFtdLCBcIlNvbmF0YUFkbWluQnVuZGxlXCIpfVwiPlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8aSBjbGFzcz1cImtpLW91dGxpbmUga2ktcGVuY2lsIGZzLTNcIj48L2k+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9idXR0b24+XG5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8YnV0dG9uIGRhdGEtY3JlZC1pZD1cIiR7Y3JlZC5pZH1cIiBAY2xpY2s9XCIke3JlbW92ZX1cIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY2xhc3M9XCJidG4gYnRuLWxpZ2h0IGJ0bi1zbVwiXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBkYXRhLWJzLXRvZ2dsZT1cInRvb2x0aXBcIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZGF0YS1icy1wbGFjZW1lbnQ9XCJib3R0b21cIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYXJpYS1sYWJlbD1cIiR7VHJhbnNsYXRvci50cmFucyhcImFjdGlvbl9kZWxldGVcIiwgW10sIFwiU29uYXRhQWRtaW5CdW5kbGVcIil9XCIgXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBkYXRhLWJzLW9yaWdpbmFsLXRpdGxlPVwiJHtUcmFuc2xhdG9yLnRyYW5zKFwiYWN0aW9uX2RlbGV0ZVwiLCBbXSwgXCJTb25hdGFBZG1pbkJ1bmRsZVwiKX1cIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGl0bGU9XCIke1RyYW5zbGF0b3IudHJhbnMoXCJhY3Rpb25fZGVsZXRlXCIsIFtdLCBcIlNvbmF0YUFkbWluQnVuZGxlXCIpfVwiPlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8aSBjbGFzcz1cImtpLW91dGxpbmUga2ktdHJhc2ggZnMtMyBcIj48L2k+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9idXR0b24+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgPC9saT5gKX1cbiAgICAgICAgICAgICAgICAgIDwvdWw+YCA6IGh0bWxgXG4gICAgICAgICAgICAgICAgPHVsIGNsYXNzPVwibGlzdC1ncm91cFwiPlxuICAgICAgICAgICAgICAgICAgICA8bGkgIGNsYXNzPVwibGlzdC1ncm91cC1pdGVtXCI+JHtUcmFuc2xhdG9yLnRyYW5zKCdwYXNza2V5X2NyZWF0ZS5ub19jcmVkZW50aWFsc19mb3VuZCcsIFtdLCAnc2VjdXJpdHknKX08L2xpPlxuICAgICAgICAgICAgICAgICAgPC91bD5gfWA7XG4gICAgICAgIHJlbmRlcihjcmVkcywgbGlzdCk7XG5cbiAgICAgICAgY3JlYXRlUGFzc2tleUJ1dHRvbnMuZm9yRWFjaCgoYnV0dG9uKSA9PiB7XG4gICAgICAgICAgICBidXR0b24uY2xhc3NMaXN0LnJlbW92ZSgnZC1ub25lJyk7XG4gICAgICAgIH0pXG4gICAgfWNhdGNoKGUpIHtcbiAgICAgICAgZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI3Nob3ctcGFzc2tleXMnKS5jbGFzc0xpc3QuYWRkKCdkLW5vbmUnKVxuICAgICAgICBDTVNBZG1pbi5jcmVhdGVJbml0Q21zTWVzc2FnZUJveCgnZXJyb3InLCBlLm1lc3NhZ2UpO1xuICAgIH1cbn1cblxuYXN5bmMgZnVuY3Rpb24gcmVuZGVyQXV0aGV0aWNhdG9yKCkge1xuICAgIHRyeSB7XG4gICAgICAgIGxldCByZXMgPSBhd2FpdCBfZmV0Y2goQ01TUm91dGluZy5nZW5lcmF0ZSgnYWRtaW5fbmV0d29ya2luZ19pbml0Y21zX3VzZXJfcHJvZmlsZV9zZWN1cml0eV9nZXRfYXV0aGVudGljYXRvcicpKVxuICAgICAgICBjb25zdCBsaXN0ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI2xpc3QtYXV0aGVudGljYXRvcicpO1xuICAgICAgICBjcmVhdGVBdXRoZW50aWNhdG9yQnV0dG9uLmNsYXNzTGlzdC50b2dnbGUoJ2Qtbm9uZScsIHJlcy5oYXNTdGVwVmVyaWZpY2F0aW9uQ29kZSk7XG4gICAgICAgIGNvbnN0IGNyZWRzID0gaHRtbGAke3Jlcy5oYXNTdGVwVmVyaWZpY2F0aW9uQ29kZSA/IGh0bWxgXG4gICAgICAgICAgICA8dWwgY2xhc3M9XCJsaXN0LWdyb3VwXCI+XG4gICAgICAgICAgICAgICAgPGxpIGNsYXNzPVwibGlzdC1ncm91cC1pdGVtIGQtZmxleCBqdXN0aWZ5LWNvbnRlbnQtYmV0d2VlbiBhbGlnbi1pdGVtcy1zdGFydFwiPlxuICAgICAgICAgICAgICAgICAgICA8ZGl2PlxuICAgICAgICAgICAgICAgICAgICAgICAgPGltZyBzcmM9XCIke3Jlcy5xckNvZGVVcmx9XCIgY2xhc3M9XCJ3LTE1MHB4XCI+XG4gICAgICAgICAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzPVwidy03NVwiPlxuICAgICAgICAgICAgICAgICAgICAgICAgPGRpdj48c3Ryb25nPiR7VHJhbnNsYXRvci50cmFucygnYXV0aGVudGljYXRvcl9lZGl0LmN1cnJlbnRfYXV0aGVudGljYXRvcicsIFtdLCAnc2VjdXJpdHknKX08L3N0cm9uZz48L2Rpdj5cbiAgICAgICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJ0ZXh0LW11dGVkXCI+JHtyZXMuc2VjcmV0fTwvZGl2PlxuICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cbiAgICAgICAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cImJ1dHRvbnNcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgIDxidXR0b24gaWQ9XCJlZGl0LWF1dGhlbnRpY2F0b3JcIiB0eXBlPVwiYnV0dG9uXCIgQGNsaWNrPVwiJHtlZGl0QXV0aGVudGljYXRvcn1cIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBjbGFzcz1cImJ0biBidG4tbGlnaHQgYnRuLXNtXCJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZGF0YS1icy10b2dnbGU9XCJ0b29sdGlwXCJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZGF0YS1icy1wbGFjZW1lbnQ9XCJib3R0b21cIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBhcmlhLWxhYmVsPVwiJHtUcmFuc2xhdG9yLnRyYW5zKCdhY3Rpb25fZWRpdCcsIFtdLCAnU29uYXRhQWRtaW5CdW5kbGUnKX1cIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBkYXRhLWJzLW9yaWdpbmFsLXRpdGxlPVwiJHtUcmFuc2xhdG9yLnRyYW5zKCdhY3Rpb25fZWRpdCcsIFtdLCAnU29uYXRhQWRtaW5CdW5kbGUnKX1cIj5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8aSBjbGFzcz1cImtpLW91dGxpbmUga2ktcGVuY2lsIGZzLTMgXCI+PC9pPlxuICAgICAgICAgICAgICAgICAgICAgICAgPC9idXR0b24+XG4gICAgICAgICAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICAgICAgICAgIDwvbGk+XG4gICAgICAgICAgICAgIDwvdWw+YCA6IGh0bWxgXG4gICAgICAgICAgICA8dWwgY2xhc3M9XCJsaXN0LWdyb3VwXCI+XG4gICAgICAgICAgICAgICAgPGxpIGNsYXNzPVwibGlzdC1ncm91cC1pdGVtXCI+JHtUcmFuc2xhdG9yLnRyYW5zKCdhdXRoZW50aWNhdG9yX2VkaXQubm9fYXV0aGVudGljYXRvcl9mb3VuZCcsIFtdLCAnc2VjdXJpdHknKX08L2xpPlxuICAgICAgICAgICAgICA8L3VsPmB9YDtcbiAgICAgICAgcmVuZGVyKGNyZWRzLCBsaXN0KTtcbiAgICB9Y2F0Y2goZSkge1xuICAgICAgICBDTVNBZG1pbi5jcmVhdGVJbml0Q21zTWVzc2FnZUJveCgnZXJyb3InLCBlLm1lc3NhZ2UpO1xuICAgIH1cbn1cblxuXG5hc3luYyBmdW5jdGlvbiByZWdpc3RlcigpIHtcbiAgICB0cnkge1xuICAgICAgICBsZXQgdXNlcm5hbWUgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCdtZXRhW25hbWU9XCJ1c2VybmFtZVwiXScpLmdldEF0dHJpYnV0ZSgnY29udGVudCcpXG4gICAgICAgIGxldCBkaXNwbGF5TmFtZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJ21ldGFbbmFtZT1cImRpc3BsYXlOYW1lXCJdJykuZ2V0QXR0cmlidXRlKCdjb250ZW50JylcblxuICAgICAgICAvLyBTdGFydCB0aGUgbG9hZGluZyBVSS5cblxuICAgICAgICAvLyBTdGFydCBjcmVhdGluZyBhIHBhc3NrZXkuXG4gICAgICAgIGF3YWl0IHJlZ2lzdGVyQ3JlZGVudGlhbCh1c2VybmFtZSwgZGlzcGxheU5hbWUpO1xuXG4gICAgICAgIC8vIFN0b3AgdGhlIGxvYWRpbmcgVUkuXG5cbiAgICAgICAgLy8gUmVuZGVyIHRoZSB1cGRhdGVkIHBhc3NrZXkgbGlzdC5cbiAgICAgICAgcmVuZGVyQ3JlZGVudGlhbHMoKTtcbiAgICB9IGNhdGNoIChlKSB7XG5cbiAgICAgICAgLy8gU3RvcCB0aGUgbG9hZGluZyBVSS5cblxuICAgICAgICAvLyBBbiBJbnZhbGlkU3RhdGVFcnJvciBpbmRpY2F0ZXMgdGhhdCBhIHBhc3NrZXkgYWxyZWFkeSBleGlzdHMgb24gdGhlIGRldmljZS5cbiAgICAgICAgaWYgKGUubmFtZSA9PT0gJ0ludmFsaWRTdGF0ZUVycm9yJykge1xuICAgICAgICAgICAgU3dhbC5maXJlKHtcbiAgICAgICAgICAgICAgICB0aXRsZTogVHJhbnNsYXRvci50cmFucygncGFzc2tleV9jcmVhdGUucGFzc2tleV9leGlzdHNfZm9yX2RldmljZV9tZXRob2QnLCBbXSwgJ3NlY3VyaXR5JyksXG4gICAgICAgICAgICAgICAgaHRtbDogYDxkaXYgY2xhc3M9XCJkLWZsZXggZmxleC1jb2x1bW4gYWxpZ24taXRlbXMtY2VudGVyXCI+XG4gICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJzdGFjayB3LTE1MHB4IHBvc2l0aW9uLXJlbGF0aXZlXCI+XG4gICAgICAgICAgICAgICAgICAgICAgICA8aW1nIHNyYz1cIi9idW5kbGVzL25ldHdvcmtpbmdpbml0Y21zL2ltZy9maWRvL2ZhY2UtaWQuc3ZnXCIgYWx0PVwiV2ViQXV0aG5cIiBjbGFzcz1cInBvc2l0aW9uLWFic29sdXRlIHRvcC01MCB3LTI1cHhcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgIDxpbWcgc3JjPVwiL2J1bmRsZXMvbmV0d29ya2luZ2luaXRjbXMvaW1nL2ZpZG8vZmluZ2VycHJpbnQuc3ZnXCIgYWx0PVwiV2ViQXV0aG5cIiBjbGFzcz1cInBvc2l0aW9uLWFic29sdXRlIHRvcC0yNSBlbmQtMCB3LTMwcHhcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgIDxpbWcgc3JjPVwiL2J1bmRsZXMvbmV0d29ya2luZ2luaXRjbXMvaW1nL2ZpZG8vcGFzc2tleS1sb2NhbC5zdmdcIiBhbHQ9XCJXZWJBdXRoblwiIGNsYXNzPVwidy01MHB4IHBvc2l0aW9uLWFic29sdXRlIHRvcC01MCBzdGFydC01MCB0cmFuc2xhdGUtbWlkZGxlXCI+XG4gICAgICAgICAgICAgICAgICAgICAgICA8aW1nIHNyYz1cIi9idW5kbGVzL25ldHdvcmtpbmdpbml0Y21zL2ltZy9maWRvL3Bob25lLnN2Z1wiIGFsdD1cIldlYkF1dGhuXCIgY2xhc3M9XCJ3LTE1MHB4XCI+XG4gICAgICAgICAgICAgICAgICAgICAgICA8aW1nIHNyYz1cIi9idW5kbGVzL25ldHdvcmtpbmdpbml0Y21zL2ltZy9maWRvL3Bhc3N3b3JkLnN2Z1wiIGFsdD1cIldlYkF1dGhuXCIgY2xhc3M9XCJ3LTMwcHggcG9zaXRpb24tYWJzb2x1dGUgYm90dG9tLTI1IGVuZC0wXCI+XG4gICAgICAgICAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICAgICAgICAgIDwvZGl2PlxuYCxcbiAgICAgICAgICAgICAgICBidXR0b25zU3R5bGluZzogZmFsc2UsXG4gICAgICAgICAgICAgICAgc2hvd0NhbmNlbEJ1dHRvbjogZmFsc2UsXG4gICAgICAgICAgICAgICAgY29uZmlybUJ1dHRvblRleHQ6IFRyYW5zbGF0b3IudHJhbnMoJ2NvbnRpbnVlJywgW10sICdzZWN1cml0eScpLFxuICAgICAgICAgICAgICAgIGN1c3RvbUNsYXNzOiB7XG4gICAgICAgICAgICAgICAgICAgIGh0bWxDb250YWluZXI6ICdoLTEwMCcsXG4gICAgICAgICAgICAgICAgICAgIGNvbmZpcm1CdXR0b246IFwiYnRuIGJ0bi1zbSBidG4tc3VjY2Vzc1wiLFxuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pXG5cbiAgICAgICAgICAgIC8vIEEgTm90QWxsb3dlZEVycm9yIGluZGljYXRlcyB0aGF0IHRoZSB1c2VyIGNhbmNlbGVkIHRoZSBvcGVyYXRpb24uXG4gICAgICAgIH0gZWxzZSBpZiAoZS5uYW1lID09PSAnTm90QWxsb3dlZEVycm9yJykge1xuXG4gICAgICAgICAgICAvLyBTaG93IG90aGVyIGVycm9ycyBpbiBhbiBhbGVydC5cbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIENNU0FkbWluLmNyZWF0ZUluaXRDbXNNZXNzYWdlQm94KCdlcnJvcicsIGUubWVzc2FnZSk7XG4gICAgICAgIH1cbiAgICB9XG59XG5cbmZ1bmN0aW9uIHN0YXJ0UmVnaXN0cmF0aW9uKCkge1xuICAgIFN3YWwuZmlyZSh7XG4gICAgICAgIHRpdGxlOiBUcmFuc2xhdG9yLnRyYW5zKCdwYXNza2V5X2NyZWF0ZS50aXRsZScsIFtdLCAnc2VjdXJpdHknKSxcbiAgICAgICAgaHRtbDogYDxkaXYgY2xhc3M9XCJkLWZsZXggZmxleC1jb2x1bW4gYWxpZ24taXRlbXMtY2VudGVyXCI+XG4gICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJzdGFjayB3LTE1MHB4IHBvc2l0aW9uLXJlbGF0aXZlXCI+XG4gICAgICAgICAgICAgICAgICAgICAgICA8aW1nIHNyYz1cIi9idW5kbGVzL25ldHdvcmtpbmdpbml0Y21zL2ltZy9maWRvL2ZhY2UtaWQuc3ZnXCIgYWx0PVwiV2ViQXV0aG5cIiBjbGFzcz1cInBvc2l0aW9uLWFic29sdXRlIHRvcC01MCB3LTI1cHhcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgIDxpbWcgc3JjPVwiL2J1bmRsZXMvbmV0d29ya2luZ2luaXRjbXMvaW1nL2ZpZG8vZmluZ2VycHJpbnQuc3ZnXCIgYWx0PVwiV2ViQXV0aG5cIiBjbGFzcz1cInBvc2l0aW9uLWFic29sdXRlIHRvcC0yNSBlbmQtMCB3LTMwcHhcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgIDxpbWcgc3JjPVwiL2J1bmRsZXMvbmV0d29ya2luZ2luaXRjbXMvaW1nL2ZpZG8vcGFzc2tleS1sb2NhbC5zdmdcIiBhbHQ9XCJXZWJBdXRoblwiIGNsYXNzPVwidy01MHB4IHBvc2l0aW9uLWFic29sdXRlIHRvcC01MCBzdGFydC01MCB0cmFuc2xhdGUtbWlkZGxlXCI+XG4gICAgICAgICAgICAgICAgICAgICAgICA8aW1nIHNyYz1cIi9idW5kbGVzL25ldHdvcmtpbmdpbml0Y21zL2ltZy9maWRvL3Bob25lLnN2Z1wiIGFsdD1cIldlYkF1dGhuXCIgY2xhc3M9XCJ3LTE1MHB4XCI+XG4gICAgICAgICAgICAgICAgICAgICAgICA8aW1nIHNyYz1cIi9idW5kbGVzL25ldHdvcmtpbmdpbml0Y21zL2ltZy9maWRvL3Bhc3N3b3JkLnN2Z1wiIGFsdD1cIldlYkF1dGhuXCIgY2xhc3M9XCJ3LTMwcHggcG9zaXRpb24tYWJzb2x1dGUgYm90dG9tLTI1IGVuZC0wXCI+XG4gICAgICAgICAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICAgICAgICAgICAgICA8cD4ke1RyYW5zbGF0b3IudHJhbnMoJ3Bhc3NrZXlfY3JlYXRlLnRleHQnLCBbXSwgJ3NlY3VyaXR5Jyl9PC9wPlxuICAgICAgICAgICAgICAgIDwvZGl2PlxuYCxcbiAgICAgICAgYnV0dG9uc1N0eWxpbmc6IGZhbHNlLFxuICAgICAgICBzaG93Q2FuY2VsQnV0dG9uOiB0cnVlLFxuICAgICAgICBjb25maXJtQnV0dG9uVGV4dDogVHJhbnNsYXRvci50cmFucygnY29udGludWUnLCBbXSwgJ3NlY3VyaXR5JyksXG4gICAgICAgIGNhbmNlbEJ1dHRvblRleHQ6IFRyYW5zbGF0b3IudHJhbnMoJ2NhbmNlbCcsIFtdLCAnc2VjdXJpdHknKSxcbiAgICAgICAgY3VzdG9tQ2xhc3M6IHtcbiAgICAgICAgICAgIGh0bWxDb250YWluZXI6ICdoLTEwMCcsXG4gICAgICAgICAgICBjb25maXJtQnV0dG9uOiBcImJ0biBidG4tc20gYnRuLXByaW1hcnlcIixcbiAgICAgICAgICAgIGNhbmNlbEJ1dHRvbjogXCJidG4gYnRuLXNtIGJ0bi1saWdodFwiXG4gICAgICAgIH1cbiAgICB9KS50aGVuKChyZXN1bHQpID0+IHtcbiAgICAgICAgLyogUmVhZCBtb3JlIGFib3V0IGlzQ29uZmlybWVkLCBpc0RlbmllZCBiZWxvdyAqL1xuICAgICAgICBpZiAocmVzdWx0LmlzQ29uZmlybWVkKSB7XG4gICAgICAgICAgICByZWdpc3RlcigpXG4gICAgICAgIH1cbiAgICB9KTtcbn1cblxuYXN5bmMgZnVuY3Rpb24gY3JlYXRlQXV0aGVudGljYXRvcigpIHtcblxuICAgIHRyeXtcbiAgICAgICAgY29uc3QgYXV0aGVudGljYXRvciA9IGF3YWl0IF9mZXRjaChDTVNSb3V0aW5nLmdlbmVyYXRlKCdhZG1pbl9uZXR3b3JraW5nX2luaXRjbXNfdXNlcl9wcm9maWxlX3NlY3VyaXR5X2NyZWF0ZV9hdXRoZW50aWNhdG9yJykpXG5cbiAgICAgICAgbGV0IHFyQ29kZVVybCA9IGF1dGhlbnRpY2F0b3IucXJDb2RlVXJsO1xuICAgICAgICBsZXQgc2VjcmV0ID0gYXV0aGVudGljYXRvci5zZWNyZXQ7XG5cbiAgICAgICAgU3dhbC5maXJlKHtcbiAgICAgICAgICAgIHRpdGxlOiBUcmFuc2xhdG9yLnRyYW5zKCdhdXRoZW50aWNhdG9yX2VkaXQuc2V0dXBfYXV0aGVudGljYXRvcl90aXRsZScsIFtdLCAnc2VjdXJpdHknKSxcbiAgICAgICAgICAgIGh0bWw6IGA8ZGl2IGNsYXNzPVwiZC1mbGV4IGZsZXgtY29sdW1uIGFsaWduLWl0ZW1zLWNlbnRlclwiPlxuICAgICAgICAgICAgICAgICAgICAke1RyYW5zbGF0b3IudHJhbnMoJ2F1dGhlbnRpY2F0b3JfZWRpdC5zZXR1cF9hdXRoZW50aWNhdG9yX3RleHQnLCBbXSwgJ3NlY3VyaXR5Jyl9XG4gICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJzdGFjayB3LTE1MHB4IHBvc2l0aW9uLXJlbGF0aXZlXCI+XG4gICAgICAgICAgICAgICAgICAgICAgICA8aW1nIHNyYz1cIiR7cXJDb2RlVXJsfVwiICBjbGFzcz1cInctMTUwcHhcIj5cbiAgICAgICAgICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICAgICAgICAgICAgIFxuICAgICAgICAgICAgICAgICAgICA8cD4ke1RyYW5zbGF0b3IudHJhbnMoJ2F1dGhlbnRpY2F0b3JfZWRpdC5zZXR1cF9hdXRoZW50aWNhdG9yX3RleHQyJywgW10sICdzZWN1cml0eScpfTwvcD5cbiAgICAgICAgICAgICAgICAgICAgXG4gICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJ3LTI1MHB4XCI+XG4gICAgICAgICAgICAgICAgICAgICAgICA8Y29kZT4ke3NlY3JldH08L2NvZGU+XG4gICAgICAgICAgICAgICAgPC9kaXY+XG5gLFxuICAgICAgICAgICAgYnV0dG9uc1N0eWxpbmc6IGZhbHNlLFxuICAgICAgICAgICAgc2hvd0NhbmNlbEJ1dHRvbjogdHJ1ZSxcbiAgICAgICAgICAgIGNvbmZpcm1CdXR0b25UZXh0OiBUcmFuc2xhdG9yLnRyYW5zKCduZXh0JywgW10sICdzZWN1cml0eScpLFxuICAgICAgICAgICAgY2FuY2VsQnV0dG9uVGV4dDogVHJhbnNsYXRvci50cmFucygnY2FuY2VsJywgW10sICdzZWN1cml0eScpLFxuICAgICAgICAgICAgY3VzdG9tQ2xhc3M6IHtcbiAgICAgICAgICAgICAgICBodG1sQ29udGFpbmVyOiAnaC0xMDAnLFxuICAgICAgICAgICAgICAgIGNvbmZpcm1CdXR0b246IFwiYnRuIGJ0bi1wcmltYXJ5XCIsXG4gICAgICAgICAgICAgICAgY2FuY2VsQnV0dG9uOiBcImJ0biBidG4tbGlnaHRcIlxuICAgICAgICAgICAgfVxuICAgICAgICB9KS50aGVuKChyZXN1bHQpID0+IHtcbiAgICAgICAgICAgIC8qIFJlYWQgbW9yZSBhYm91dCBpc0NvbmZpcm1lZCwgaXNEZW5pZWQgYmVsb3cgKi9cbiAgICAgICAgICAgIGlmIChyZXN1bHQuaXNDb25maXJtZWQpIHtcblxuICAgICAgICAgICAgICAgIHZlcmlmeUNvZGUoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgfWNhdGNoIChlKSB7XG4gICAgICAgIENNU0FkbWluLmNyZWF0ZUluaXRDbXNNZXNzYWdlQm94KCdlcnJvcicsIGUubWVzc2FnZSk7XG4gICAgfVxufVxuXG5hc3luYyBmdW5jdGlvbiB2ZXJpZnlDb2RlKCkge1xuICAgIHRyeXtcblxuICAgICAgICBjb25zdCB7IHZhbHVlOiBjb2RlLCBpc0RlbmllZDogaXNEZW5pZWQgfSA9IGF3YWl0IFN3YWwuZmlyZSh7XG4gICAgICAgICAgICB0aXRsZTogVHJhbnNsYXRvci50cmFucygnYXV0aGVudGljYXRvcl9lZGl0LnZlcmlmeV9jb2RlX3RpdGxlJywgW10sICdzZWN1cml0eScpLFxuICAgICAgICAgICAgaW5wdXRMYWJlbDogVHJhbnNsYXRvci50cmFucygnYXV0aGVudGljYXRvcl9lZGl0LnZlcmlmeV9jb2RlX3RleHQnLCBbXSwgJ3NlY3VyaXR5JyksXG4gICAgICAgICAgICBpbnB1dDogJ3RleHQnLFxuICAgICAgICAgICAgaW5wdXRBdHRyaWJ1dGVzOiB7XG4gICAgICAgICAgICAgICAgYXV0b2NvbXBsZXRlOiAnb25lLXRpbWUtY29kZScsXG4gICAgICAgICAgICB9LFxuICAgICAgICAgICAgYnV0dG9uc1N0eWxpbmc6IGZhbHNlLFxuICAgICAgICAgICAgc2hvd0NhbmNlbEJ1dHRvbjogdHJ1ZSxcbiAgICAgICAgICAgIHNob3dEZW55QnV0dG9uOiB0cnVlLFxuICAgICAgICAgICAgZGVueUJ1dHRvblRleHQ6IFRyYW5zbGF0b3IudHJhbnMoJ2JhY2snLCBbXSwgJ3NlY3VyaXR5JyksXG4gICAgICAgICAgICBjb25maXJtQnV0dG9uVGV4dDogVHJhbnNsYXRvci50cmFucygnY29uZmlybScsIFtdLCAnc2VjdXJpdHknKSxcbiAgICAgICAgICAgIGNhbmNlbEJ1dHRvblRleHQ6IFRyYW5zbGF0b3IudHJhbnMoJ2NhbmNlbCcsIFtdLCAnc2VjdXJpdHknKSxcbiAgICAgICAgICAgIHJldmVyc2VCdXR0b25zOiB0cnVlLFxuICAgICAgICAgICAgY3VzdG9tQ2xhc3M6IHtcbiAgICAgICAgICAgICAgICBkZW55QnV0dG9uOiBcImJ0biBidG4tbGlnaHQtcHJpbWFyeVwiLFxuICAgICAgICAgICAgICAgIGNvbmZpcm1CdXR0b246IFwiYnRuIGJ0bi1wcmltYXJ5XCIsXG4gICAgICAgICAgICAgICAgY2FuY2VsQnV0dG9uOiBcImJ0biBidG4tbGlnaHRcIlxuICAgICAgICAgICAgfVxuICAgICAgICB9KVxuXG4gICAgICAgIGlmKGlzRGVuaWVkKSB7XG4gICAgICAgICAgICByZXR1cm4gY3JlYXRlQXV0aGVudGljYXRvcigpO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKGNvZGUpIHtcbiAgICAgICAgICAgIGxldCByZXN1bHQgPSAgYXdhaXQgX2ZldGNoKENNU1JvdXRpbmcuZ2VuZXJhdGUoJ2FkbWluX25ldHdvcmtpbmdfaW5pdGNtc191c2VyX3Byb2ZpbGVfc2VjdXJpdHlfdmVyaWZ5X2F1dGhlbnRpY2F0b3InKSwge19jb2RlOiBjb2RlfSlcblxuICAgICAgICAgICAgaWYocmVzdWx0ICYmICdvaycgPT09IHJlc3VsdC5zdGF0dXMpIHtcblxuICAgICAgICAgICAgICAgIFN3YWwuZmlyZSh7XG4gICAgICAgICAgICAgICAgICAgIHRleHQ6IFRyYW5zbGF0b3IudHJhbnMoJ3Rlc3Quc3VjY2Vzc190ZXh0JywgW10sICdzZWN1cml0eScpLFxuICAgICAgICAgICAgICAgICAgICBpY29uOiBcInN1Y2Nlc3NcIixcbiAgICAgICAgICAgICAgICAgICAgYnV0dG9uc1N0eWxpbmc6IGZhbHNlLFxuICAgICAgICAgICAgICAgICAgICBjb25maXJtQnV0dG9uVGV4dDogVHJhbnNsYXRvci50cmFucygnY29udGludWUnLCBbXSwgJ3NlY3VyaXR5JyksXG4gICAgICAgICAgICAgICAgICAgIGN1c3RvbUNsYXNzOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICBjb25maXJtQnV0dG9uOiBcImJ0biBidG4tcHJpbWFyeVwiXG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB9KTtcblxuICAgICAgICAgICAgICAgIHJlbmRlckF1dGhldGljYXRvcigpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfWNhdGNoIChlKSB7XG4gICAgICAgIENNU0FkbWluLmNyZWF0ZUluaXRDbXNNZXNzYWdlQm94KCdlcnJvcicsIGUubWVzc2FnZSk7XG4gICAgfVxufVxuXG5hc3luYyBmdW5jdGlvbiBlZGl0QXV0aGVudGljYXRvcigpIHtcbiAgICB0cnl7XG4gICAgICAgIGNvbnN0IHtpc0NvbmZpcm1lZDogaXNDb25maXJtZWR9ID0gYXdhaXQgU3dhbC5maXJlKHtcbiAgICAgICAgICAgIHRpdGxlOiBUcmFuc2xhdG9yLnRyYW5zKCdhdXRoZW50aWNhdG9yX2VkaXQuY2hhbmdlX2F1dGhlbnRpY2F0b3JfdGl0bGUnLCBbXSwgJ3NlY3VyaXR5JyksXG4gICAgICAgICAgICB0ZXh0OiBUcmFuc2xhdG9yLnRyYW5zKCdhdXRoZW50aWNhdG9yX2VkaXQuY2hhbmdlX2F1dGhlbnRpY2F0b3JfdGV4dCcsIFtdLCAnc2VjdXJpdHknKSxcbiAgICAgICAgICAgIGJ1dHRvbnNTdHlsaW5nOiBmYWxzZSxcbiAgICAgICAgICAgIHNob3dDYW5jZWxCdXR0b246IHRydWUsXG4gICAgICAgICAgICBjb25maXJtQnV0dG9uVGV4dDogVHJhbnNsYXRvci50cmFucygnZWRpdCcsIFtdLCAnc2VjdXJpdHknKSxcbiAgICAgICAgICAgIGNhbmNlbEJ1dHRvblRleHQ6IFRyYW5zbGF0b3IudHJhbnMoJ2NhbmNlbCcsIFtdLCAnc2VjdXJpdHknKSxcbiAgICAgICAgICAgIGN1c3RvbUNsYXNzOiB7XG4gICAgICAgICAgICAgICAgY29uZmlybUJ1dHRvbjogXCJidG4gYnRuLXByaW1hcnlcIixcbiAgICAgICAgICAgICAgICBjYW5jZWxCdXR0b246IFwiYnRuIGJ0bi1saWdodFwiXG4gICAgICAgICAgICB9XG4gICAgICAgIH0pXG5cbiAgICAgICAgaWYgKGlzQ29uZmlybWVkKSB7XG4gICAgICAgICAgICBjcmVhdGVBdXRoZW50aWNhdG9yKCk7XG4gICAgICAgIH1cblxuICAgIH0gY2F0Y2ggKGUpIHtcbiAgICAgICAgQ01TQWRtaW4uY3JlYXRlSW5pdENtc01lc3NhZ2VCb3goJ2Vycm9yJywgZS5tZXNzYWdlKTtcbiAgICB9XG59XG5cbmFzeW5jIGZ1bmN0aW9uIHRlc3RTaWduaW4oKSB7XG5cbiAgICBjb25zdCBvcHRpb25zID0gYXdhaXQgX2ZldGNoKCcvYWRtaW4vYXNzZXJ0aW9uL29wdGlvbnMnLCB7XG4gICAgICAgIHJlcXVpcmVVc2VyVmVyaWZpY2F0aW9uOiAncHJlZmVycmVkJyxcbiAgICB9KTtcblxuICAgIGxldCBhc3NlUmVzcDtcbiAgICB0cnkge1xuICAgICAgICAvLyBQYXNzIHRoZSBvcHRpb25zIHRvIHRoZSBhdXRoZW50aWNhdG9yIGFuZCB3YWl0IGZvciBhIHJlc3BvbnNlXG4gICAgICAgIGFzc2VSZXNwID0gYXdhaXQgc3RhcnRBdXRoZW50aWNhdGlvbihvcHRpb25zKTtcbiAgICB9IGNhdGNoIChlKSB7XG5cbiAgICAgICAgQ01TQWRtaW4uY3JlYXRlSW5pdENtc01lc3NhZ2VCb3goJ2Vycm9yJywgZS5tZXNzYWdlKTtcblxuICAgICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgdHJ5e1xuICAgICAgICBjb25zdCByZXN1bHQgPSBhd2FpdCBfZmV0Y2goJy9hZG1pbi9hc3NlcnRpb24nLCBhc3NlUmVzcCk7XG5cbiAgICAgICAgaWYocmVzdWx0ICYmICdvaycgPT09IHJlc3VsdC5zdGF0dXMpIHtcblxuICAgICAgICAgICAgU3dhbC5maXJlKHtcbiAgICAgICAgICAgICAgICB0ZXh0OiBUcmFuc2xhdG9yLnRyYW5zKCd0ZXN0LnN1Y2Nlc3NfdGV4dCcsIFtdLCAnc2VjdXJpdHknKSxcbiAgICAgICAgICAgICAgICBpY29uOiBcInN1Y2Nlc3NcIixcbiAgICAgICAgICAgICAgICBidXR0b25zU3R5bGluZzogZmFsc2UsXG4gICAgICAgICAgICAgICAgY29uZmlybUJ1dHRvblRleHQ6IFRyYW5zbGF0b3IudHJhbnMoJ2NvbnRpbnVlJywgW10sICdzZWN1cml0eScpLFxuICAgICAgICAgICAgICAgIGN1c3RvbUNsYXNzOiB7XG4gICAgICAgICAgICAgICAgICAgIGNvbmZpcm1CdXR0b246IFwiYnRuIGJ0bi1wcmltYXJ5XCJcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KTtcblxuICAgICAgICAgICAgcmV0dXJuO1xuXG4gICAgICAgIH1cblxuICAgICAgICB0aHJvdyBuZXcgRXJyb3IoXCJVc2VyIG5vdCBmb3VuZC5cIik7XG4gICAgfWNhdGNoKGUpe1xuXG4gICAgICAgIENNU0FkbWluLmNyZWF0ZUluaXRDbXNNZXNzYWdlQm94KCdlcnJvcicsIGUubWVzc2FnZSk7XG4gICAgfVxufVxuXG5pZih3ZWJhdXRobkVuYWJsZWQpe1xuXG4gICAgaWYgKGJyb3dzZXJTdXBwb3J0c1dlYkF1dGhuKCkgJiZcbiAgICAgICAgUHVibGljS2V5Q3JlZGVudGlhbC5pc1VzZXJWZXJpZnlpbmdQbGF0Zm9ybUF1dGhlbnRpY2F0b3JBdmFpbGFibGUgJiZcbiAgICAgICAgUHVibGljS2V5Q3JlZGVudGlhbC5pc0NvbmRpdGlvbmFsTWVkaWF0aW9uQXZhaWxhYmxlKSB7XG4gICAgICAgIHRyeSB7XG4gICAgICAgICAgICBjb25zdCByZXN1bHRzID0gYXdhaXQgUHJvbWlzZS5hbGwoW1xuXG4gICAgICAgICAgICAgICAgLy8gSXMgcGxhdGZvcm0gYXV0aGVudGljYXRvciBhdmFpbGFibGUgaW4gdGhpcyBicm93c2VyP1xuICAgICAgICAgICAgICAgIHBsYXRmb3JtQXV0aGVudGljYXRvcklzQXZhaWxhYmxlKCksXG5cbiAgICAgICAgICAgICAgICAvLyBJcyBjb25kaXRpb25hbCBVSSBhdmFpbGFibGUgaW4gdGhpcyBicm93c2VyP1xuICAgICAgICAgICAgICAgIGJyb3dzZXJTdXBwb3J0c1dlYkF1dGhuQXV0b2ZpbGwoKVxuICAgICAgICAgICAgXSk7XG5cblxuICAgICAgICAgICAgaWYgKHJlc3VsdHMuZXZlcnkociA9PiByID09PSB0cnVlKSkge1xuICAgICAgICAgICAgICAgIHJlbmRlckNyZWRlbnRpYWxzKCk7XG4gICAgICAgICAgICB9IGVsc2Uge1xuXG4gICAgICAgICAgICAgICAgLy8gSWYgY29uZGl0aW9uYWwgVUkgaXNuJ3QgYXZhaWxhYmxlLCBzaG93IGEgbWVzc2FnZS5cbiAgICAgICAgICAgICAgICBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcjbWVzc2FnZScpLmlubmVyVGV4dCA9IFRyYW5zbGF0b3IudHJhbnMoJ3Bhc3NrZXlfY3JlYXRlLndlYmF1dGhuX25vdF9hdmFpbGFibGUnLCBbXSwgJ3NlY3VyaXR5Jyk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0gY2F0Y2ggKGUpIHtcbiAgICAgICAgICAgIENNU0FkbWluLmNyZWF0ZUluaXRDbXNNZXNzYWdlQm94KCdlcnJvcicsIGUpO1xuICAgICAgICB9XG4gICAgfSBlbHNlIHtcblxuICAgICAgICAvLyBJZiBXZWJBdXRobiBpc24ndCBhdmFpbGFibGUsIHNob3cgYSBtZXNzYWdlLlxuICAgICAgICBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcjbWVzc2FnZScpLmlubmVyVGV4dCA9IFRyYW5zbGF0b3IudHJhbnMoJ3Bhc3NrZXlfY3JlYXRlLndlYmF1dGhuX25vdF9hdmFpbGFibGUnLCBbXSwgJ3NlY3VyaXR5JylcbiAgICB9XG4gICAgY3JlYXRlUGFzc2tleUJ1dHRvbnMuZm9yRWFjaCgoYnV0dG9uKSA9PiB7XG4gICAgICAgIGJ1dHRvbi5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIHN0YXJ0UmVnaXN0cmF0aW9uKTtcbiAgICB9KTtcbiAgICB0ZXN0U2lnbmluQnV0dG9uLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgdGVzdFNpZ25pbik7XG59XG5cbmlmKGdvb2dsZUF1dGhlbnRpY2F0b3JFbmFibGVkKXtcbiAgICByZW5kZXJBdXRoZXRpY2F0b3IoKTtcbiAgICBjcmVhdGVBdXRoZW50aWNhdG9yQnV0dG9uLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgY3JlYXRlQXV0aGVudGljYXRvcik7XG59IiwiLyoqXG4gKiBDb252ZXJ0IGZyb20gYSBCYXNlNjRVUkwtZW5jb2RlZCBzdHJpbmcgdG8gYW4gQXJyYXkgQnVmZmVyLiBCZXN0IHVzZWQgd2hlbiBjb252ZXJ0aW5nIGFcbiAqIGNyZWRlbnRpYWwgSUQgZnJvbSBhIEpTT04gc3RyaW5nIHRvIGFuIEFycmF5QnVmZmVyLCBsaWtlIGluIGFsbG93Q3JlZGVudGlhbHMgb3JcbiAqIGV4Y2x1ZGVDcmVkZW50aWFsc1xuICpcbiAqIEhlbHBlciBtZXRob2QgdG8gY29tcGxpbWVudCBgYnVmZmVyVG9CYXNlNjRVUkxTdHJpbmdgXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBiYXNlNjRVUkxTdHJpbmdUb0J1ZmZlcihiYXNlNjRVUkxTdHJpbmcpIHtcbiAgICAvLyBDb252ZXJ0IGZyb20gQmFzZTY0VVJMIHRvIEJhc2U2NFxuICAgIGNvbnN0IGJhc2U2NCA9IGJhc2U2NFVSTFN0cmluZy5yZXBsYWNlKC8tL2csICcrJykucmVwbGFjZSgvXy9nLCAnLycpO1xuICAgIC8qKlxuICAgICAqIFBhZCB3aXRoICc9JyB1bnRpbCBpdCdzIGEgbXVsdGlwbGUgb2YgZm91clxuICAgICAqICg0IC0gKDg1ICUgNCA9IDEpID0gMykgJSA0ID0gMyBwYWRkaW5nXG4gICAgICogKDQgLSAoODYgJSA0ID0gMikgPSAyKSAlIDQgPSAyIHBhZGRpbmdcbiAgICAgKiAoNCAtICg4NyAlIDQgPSAzKSA9IDEpICUgNCA9IDEgcGFkZGluZ1xuICAgICAqICg0IC0gKDg4ICUgNCA9IDApID0gNCkgJSA0ID0gMCBwYWRkaW5nXG4gICAgICovXG4gICAgY29uc3QgcGFkTGVuZ3RoID0gKDQgLSAoYmFzZTY0Lmxlbmd0aCAlIDQpKSAlIDQ7XG4gICAgY29uc3QgcGFkZGVkID0gYmFzZTY0LnBhZEVuZChiYXNlNjQubGVuZ3RoICsgcGFkTGVuZ3RoLCAnPScpO1xuICAgIC8vIENvbnZlcnQgdG8gYSBiaW5hcnkgc3RyaW5nXG4gICAgY29uc3QgYmluYXJ5ID0gYXRvYihwYWRkZWQpO1xuICAgIC8vIENvbnZlcnQgYmluYXJ5IHN0cmluZyB0byBidWZmZXJcbiAgICBjb25zdCBidWZmZXIgPSBuZXcgQXJyYXlCdWZmZXIoYmluYXJ5Lmxlbmd0aCk7XG4gICAgY29uc3QgYnl0ZXMgPSBuZXcgVWludDhBcnJheShidWZmZXIpO1xuICAgIGZvciAobGV0IGkgPSAwOyBpIDwgYmluYXJ5Lmxlbmd0aDsgaSsrKSB7XG4gICAgICAgIGJ5dGVzW2ldID0gYmluYXJ5LmNoYXJDb2RlQXQoaSk7XG4gICAgfVxuICAgIHJldHVybiBidWZmZXI7XG59XG4iLCIvKipcbiAqIERldGVybWluZSBpZiB0aGUgYnJvd3NlciBpcyBjYXBhYmxlIG9mIFdlYmF1dGhuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBicm93c2VyU3VwcG9ydHNXZWJBdXRobigpIHtcbiAgICByZXR1cm4gX2Jyb3dzZXJTdXBwb3J0c1dlYkF1dGhuSW50ZXJuYWxzLnN0dWJUaGlzKGdsb2JhbFRoaXM/LlB1YmxpY0tleUNyZWRlbnRpYWwgIT09IHVuZGVmaW5lZCAmJlxuICAgICAgICB0eXBlb2YgZ2xvYmFsVGhpcy5QdWJsaWNLZXlDcmVkZW50aWFsID09PSAnZnVuY3Rpb24nKTtcbn1cbi8qKlxuICogTWFrZSBpdCBwb3NzaWJsZSB0byBzdHViIHRoZSByZXR1cm4gdmFsdWUgZHVyaW5nIHRlc3RpbmdcbiAqIEBpZ25vcmUgRG9uJ3QgaW5jbHVkZSB0aGlzIGluIGRvY3Mgb3V0cHV0XG4gKi9cbmV4cG9ydCBjb25zdCBfYnJvd3NlclN1cHBvcnRzV2ViQXV0aG5JbnRlcm5hbHMgPSB7XG4gICAgc3R1YlRoaXM6ICh2YWx1ZSkgPT4gdmFsdWUsXG59O1xuIiwiaW1wb3J0IHsgYnJvd3NlclN1cHBvcnRzV2ViQXV0aG4gfSBmcm9tICcuL2Jyb3dzZXJTdXBwb3J0c1dlYkF1dGhuLmpzJztcbi8qKlxuICogRGV0ZXJtaW5lIGlmIHRoZSBicm93c2VyIHN1cHBvcnRzIGNvbmRpdGlvbmFsIFVJLCBzbyB0aGF0IFdlYkF1dGhuIGNyZWRlbnRpYWxzIGNhblxuICogYmUgc2hvd24gdG8gdGhlIHVzZXIgaW4gdGhlIGJyb3dzZXIncyB0eXBpY2FsIHBhc3N3b3JkIGF1dG9maWxsIHBvcHVwLlxuICovXG5leHBvcnQgZnVuY3Rpb24gYnJvd3NlclN1cHBvcnRzV2ViQXV0aG5BdXRvZmlsbCgpIHtcbiAgICBpZiAoIWJyb3dzZXJTdXBwb3J0c1dlYkF1dGhuKCkpIHtcbiAgICAgICAgcmV0dXJuIF9icm93c2VyU3VwcG9ydHNXZWJBdXRobkF1dG9maWxsSW50ZXJuYWxzLnN0dWJUaGlzKG5ldyBQcm9taXNlKChyZXNvbHZlKSA9PiByZXNvbHZlKGZhbHNlKSkpO1xuICAgIH1cbiAgICAvKipcbiAgICAgKiBJIGRvbid0IGxpa2UgdGhlIGBhcyB1bmtub3duYCBoZXJlIGJ1dCB0aGVyZSdzIGEgYGRlY2xhcmUgdmFyIFB1YmxpY0tleUNyZWRlbnRpYWxgIGluXG4gICAgICogVFMnIERPTSBsaWIgdGhhdCdzIG1ha2luZyBpdCBkaWZmaWN1bHQgZm9yIG1lIHRvIGp1c3QgZ28gYGFzIFB1YmxpY0tleUNyZWRlbnRpYWxGdXR1cmVgIGFzIElcbiAgICAgKiB3YW50LiBJIHRoaW5rIEknbSBmaW5lIHdpdGggdGhpcyBmb3Igbm93IHNpbmNlIGl0J3MgX3N1cHBvc2VkXyB0byBiZSB0ZW1wb3JhcnksIHVudGlsIFRTIHR5cGVzXG4gICAgICogaGF2ZSBhIGNoYW5jZSB0byBjYXRjaCB1cC5cbiAgICAgKi9cbiAgICBjb25zdCBnbG9iYWxQdWJsaWNLZXlDcmVkZW50aWFsID0gZ2xvYmFsVGhpc1xuICAgICAgICAuUHVibGljS2V5Q3JlZGVudGlhbDtcbiAgICBpZiAoZ2xvYmFsUHVibGljS2V5Q3JlZGVudGlhbD8uaXNDb25kaXRpb25hbE1lZGlhdGlvbkF2YWlsYWJsZSA9PT0gdW5kZWZpbmVkKSB7XG4gICAgICAgIHJldHVybiBfYnJvd3NlclN1cHBvcnRzV2ViQXV0aG5BdXRvZmlsbEludGVybmFscy5zdHViVGhpcyhuZXcgUHJvbWlzZSgocmVzb2x2ZSkgPT4gcmVzb2x2ZShmYWxzZSkpKTtcbiAgICB9XG4gICAgcmV0dXJuIF9icm93c2VyU3VwcG9ydHNXZWJBdXRobkF1dG9maWxsSW50ZXJuYWxzLnN0dWJUaGlzKGdsb2JhbFB1YmxpY0tleUNyZWRlbnRpYWwuaXNDb25kaXRpb25hbE1lZGlhdGlvbkF2YWlsYWJsZSgpKTtcbn1cbi8vIE1ha2UgaXQgcG9zc2libGUgdG8gc3R1YiB0aGUgcmV0dXJuIHZhbHVlIGR1cmluZyB0ZXN0aW5nXG5leHBvcnQgY29uc3QgX2Jyb3dzZXJTdXBwb3J0c1dlYkF1dGhuQXV0b2ZpbGxJbnRlcm5hbHMgPSB7XG4gICAgc3R1YlRoaXM6ICh2YWx1ZSkgPT4gdmFsdWUsXG59O1xuIiwiLyoqXG4gKiBDb252ZXJ0IHRoZSBnaXZlbiBhcnJheSBidWZmZXIgaW50byBhIEJhc2U2NFVSTC1lbmNvZGVkIHN0cmluZy4gSWRlYWwgZm9yIGNvbnZlcnRpbmcgdmFyaW91c1xuICogY3JlZGVudGlhbCByZXNwb25zZSBBcnJheUJ1ZmZlcnMgdG8gc3RyaW5nIGZvciBzZW5kaW5nIGJhY2sgdG8gdGhlIHNlcnZlciBhcyBKU09OLlxuICpcbiAqIEhlbHBlciBtZXRob2QgdG8gY29tcGxpbWVudCBgYmFzZTY0VVJMU3RyaW5nVG9CdWZmZXJgXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBidWZmZXJUb0Jhc2U2NFVSTFN0cmluZyhidWZmZXIpIHtcbiAgICBjb25zdCBieXRlcyA9IG5ldyBVaW50OEFycmF5KGJ1ZmZlcik7XG4gICAgbGV0IHN0ciA9ICcnO1xuICAgIGZvciAoY29uc3QgY2hhckNvZGUgb2YgYnl0ZXMpIHtcbiAgICAgICAgc3RyICs9IFN0cmluZy5mcm9tQ2hhckNvZGUoY2hhckNvZGUpO1xuICAgIH1cbiAgICBjb25zdCBiYXNlNjRTdHJpbmcgPSBidG9hKHN0cik7XG4gICAgcmV0dXJuIGJhc2U2NFN0cmluZy5yZXBsYWNlKC9cXCsvZywgJy0nKS5yZXBsYWNlKC9cXC8vZywgJ18nKS5yZXBsYWNlKC89L2csICcnKTtcbn1cbiIsImltcG9ydCB7IGlzVmFsaWREb21haW4gfSBmcm9tICcuL2lzVmFsaWREb21haW4uanMnO1xuaW1wb3J0IHsgV2ViQXV0aG5FcnJvciB9IGZyb20gJy4vd2ViQXV0aG5FcnJvci5qcyc7XG4vKipcbiAqIEF0dGVtcHQgdG8gaW50dWl0IF93aHlfIGFuIGVycm9yIHdhcyByYWlzZWQgYWZ0ZXIgY2FsbGluZyBgbmF2aWdhdG9yLmNyZWRlbnRpYWxzLmdldCgpYFxuICovXG5leHBvcnQgZnVuY3Rpb24gaWRlbnRpZnlBdXRoZW50aWNhdGlvbkVycm9yKHsgZXJyb3IsIG9wdGlvbnMsIH0pIHtcbiAgICBjb25zdCB7IHB1YmxpY0tleSB9ID0gb3B0aW9ucztcbiAgICBpZiAoIXB1YmxpY0tleSkge1xuICAgICAgICB0aHJvdyBFcnJvcignb3B0aW9ucyB3YXMgbWlzc2luZyByZXF1aXJlZCBwdWJsaWNLZXkgcHJvcGVydHknKTtcbiAgICB9XG4gICAgaWYgKGVycm9yLm5hbWUgPT09ICdBYm9ydEVycm9yJykge1xuICAgICAgICBpZiAob3B0aW9ucy5zaWduYWwgaW5zdGFuY2VvZiBBYm9ydFNpZ25hbCkge1xuICAgICAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tY3JlYXRlQ3JlZGVudGlhbCAoU3RlcCAxNilcbiAgICAgICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICAgICAgbWVzc2FnZTogJ0F1dGhlbnRpY2F0aW9uIGNlcmVtb255IHdhcyBzZW50IGFuIGFib3J0IHNpZ25hbCcsXG4gICAgICAgICAgICAgICAgY29kZTogJ0VSUk9SX0NFUkVNT05ZX0FCT1JURUQnLFxuICAgICAgICAgICAgICAgIGNhdXNlOiBlcnJvcixcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgfVxuICAgIGVsc2UgaWYgKGVycm9yLm5hbWUgPT09ICdOb3RBbGxvd2VkRXJyb3InKSB7XG4gICAgICAgIC8qKlxuICAgICAgICAgKiBQYXNzIHRoZSBlcnJvciBkaXJlY3RseSB0aHJvdWdoLiBQbGF0Zm9ybXMgYXJlIG92ZXJsb2FkaW5nIHRoaXMgZXJyb3IgYmV5b25kIHdoYXQgdGhlIHNwZWNcbiAgICAgICAgICogZGVmaW5lcyBhbmQgd2UgZG9uJ3Qgd2FudCB0byBvdmVyd3JpdGUgcG90ZW50aWFsbHkgdXNlZnVsIGVycm9yIG1lc3NhZ2VzLlxuICAgICAgICAgKi9cbiAgICAgICAgcmV0dXJuIG5ldyBXZWJBdXRobkVycm9yKHtcbiAgICAgICAgICAgIG1lc3NhZ2U6IGVycm9yLm1lc3NhZ2UsXG4gICAgICAgICAgICBjb2RlOiAnRVJST1JfUEFTU1RIUk9VR0hfU0VFX0NBVVNFX1BST1BFUlRZJyxcbiAgICAgICAgICAgIGNhdXNlOiBlcnJvcixcbiAgICAgICAgfSk7XG4gICAgfVxuICAgIGVsc2UgaWYgKGVycm9yLm5hbWUgPT09ICdTZWN1cml0eUVycm9yJykge1xuICAgICAgICBjb25zdCBlZmZlY3RpdmVEb21haW4gPSBnbG9iYWxUaGlzLmxvY2F0aW9uLmhvc3RuYW1lO1xuICAgICAgICBpZiAoIWlzVmFsaWREb21haW4oZWZmZWN0aXZlRG9tYWluKSkge1xuICAgICAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tZGlzY292ZXItZnJvbS1leHRlcm5hbC1zb3VyY2UgKFN0ZXAgNSlcbiAgICAgICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICAgICAgbWVzc2FnZTogYCR7Z2xvYmFsVGhpcy5sb2NhdGlvbi5ob3N0bmFtZX0gaXMgYW4gaW52YWxpZCBkb21haW5gLFxuICAgICAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9JTlZBTElEX0RPTUFJTicsXG4gICAgICAgICAgICAgICAgY2F1c2U6IGVycm9yLFxuICAgICAgICAgICAgfSk7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSBpZiAocHVibGljS2V5LnJwSWQgIT09IGVmZmVjdGl2ZURvbWFpbikge1xuICAgICAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tZGlzY292ZXItZnJvbS1leHRlcm5hbC1zb3VyY2UgKFN0ZXAgNilcbiAgICAgICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICAgICAgbWVzc2FnZTogYFRoZSBSUCBJRCBcIiR7cHVibGljS2V5LnJwSWR9XCIgaXMgaW52YWxpZCBmb3IgdGhpcyBkb21haW5gLFxuICAgICAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9JTlZBTElEX1JQX0lEJyxcbiAgICAgICAgICAgICAgICBjYXVzZTogZXJyb3IsXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgIH1cbiAgICBlbHNlIGlmIChlcnJvci5uYW1lID09PSAnVW5rbm93bkVycm9yJykge1xuICAgICAgICAvLyBodHRwczovL3d3dy53My5vcmcvVFIvd2ViYXV0aG4tMi8jc2N0bi1vcC1nZXQtYXNzZXJ0aW9uIChTdGVwIDEpXG4gICAgICAgIC8vIGh0dHBzOi8vd3d3LnczLm9yZy9UUi93ZWJhdXRobi0yLyNzY3RuLW9wLWdldC1hc3NlcnRpb24gKFN0ZXAgMTIpXG4gICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICBtZXNzYWdlOiAnVGhlIGF1dGhlbnRpY2F0b3Igd2FzIHVuYWJsZSB0byBwcm9jZXNzIHRoZSBzcGVjaWZpZWQgb3B0aW9ucywgb3IgY291bGQgbm90IGNyZWF0ZSBhIG5ldyBhc3NlcnRpb24gc2lnbmF0dXJlJyxcbiAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9BVVRIRU5USUNBVE9SX0dFTkVSQUxfRVJST1InLFxuICAgICAgICAgICAgY2F1c2U6IGVycm9yLFxuICAgICAgICB9KTtcbiAgICB9XG4gICAgcmV0dXJuIGVycm9yO1xufVxuIiwiaW1wb3J0IHsgaXNWYWxpZERvbWFpbiB9IGZyb20gJy4vaXNWYWxpZERvbWFpbi5qcyc7XG5pbXBvcnQgeyBXZWJBdXRobkVycm9yIH0gZnJvbSAnLi93ZWJBdXRobkVycm9yLmpzJztcbi8qKlxuICogQXR0ZW1wdCB0byBpbnR1aXQgX3doeV8gYW4gZXJyb3Igd2FzIHJhaXNlZCBhZnRlciBjYWxsaW5nIGBuYXZpZ2F0b3IuY3JlZGVudGlhbHMuY3JlYXRlKClgXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBpZGVudGlmeVJlZ2lzdHJhdGlvbkVycm9yKHsgZXJyb3IsIG9wdGlvbnMsIH0pIHtcbiAgICBjb25zdCB7IHB1YmxpY0tleSB9ID0gb3B0aW9ucztcbiAgICBpZiAoIXB1YmxpY0tleSkge1xuICAgICAgICB0aHJvdyBFcnJvcignb3B0aW9ucyB3YXMgbWlzc2luZyByZXF1aXJlZCBwdWJsaWNLZXkgcHJvcGVydHknKTtcbiAgICB9XG4gICAgaWYgKGVycm9yLm5hbWUgPT09ICdBYm9ydEVycm9yJykge1xuICAgICAgICBpZiAob3B0aW9ucy5zaWduYWwgaW5zdGFuY2VvZiBBYm9ydFNpZ25hbCkge1xuICAgICAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tY3JlYXRlQ3JlZGVudGlhbCAoU3RlcCAxNilcbiAgICAgICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICAgICAgbWVzc2FnZTogJ1JlZ2lzdHJhdGlvbiBjZXJlbW9ueSB3YXMgc2VudCBhbiBhYm9ydCBzaWduYWwnLFxuICAgICAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9DRVJFTU9OWV9BQk9SVEVEJyxcbiAgICAgICAgICAgICAgICBjYXVzZTogZXJyb3IsXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgIH1cbiAgICBlbHNlIGlmIChlcnJvci5uYW1lID09PSAnQ29uc3RyYWludEVycm9yJykge1xuICAgICAgICBpZiAocHVibGljS2V5LmF1dGhlbnRpY2F0b3JTZWxlY3Rpb24/LnJlcXVpcmVSZXNpZGVudEtleSA9PT0gdHJ1ZSkge1xuICAgICAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tb3AtbWFrZS1jcmVkIChTdGVwIDQpXG4gICAgICAgICAgICByZXR1cm4gbmV3IFdlYkF1dGhuRXJyb3Ioe1xuICAgICAgICAgICAgICAgIG1lc3NhZ2U6ICdEaXNjb3ZlcmFibGUgY3JlZGVudGlhbHMgd2VyZSByZXF1aXJlZCBidXQgbm8gYXZhaWxhYmxlIGF1dGhlbnRpY2F0b3Igc3VwcG9ydGVkIGl0JyxcbiAgICAgICAgICAgICAgICBjb2RlOiAnRVJST1JfQVVUSEVOVElDQVRPUl9NSVNTSU5HX0RJU0NPVkVSQUJMRV9DUkVERU5USUFMX1NVUFBPUlQnLFxuICAgICAgICAgICAgICAgIGNhdXNlOiBlcnJvcixcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgICAgIGVsc2UgaWYgKFxuICAgICAgICAvLyBAdHMtaWdub3JlOiBgbWVkaWF0aW9uYCBkb2Vzbid0IHlldCBleGlzdCBvbiBDcmVkZW50aWFsQ3JlYXRpb25PcHRpb25zIGJ1dCBpdCdzIHBvc3NpYmxlIGFzIG9mIFNlcHQgMjAyNFxuICAgICAgICBvcHRpb25zLm1lZGlhdGlvbiA9PT0gJ2NvbmRpdGlvbmFsJyAmJlxuICAgICAgICAgICAgcHVibGljS2V5LmF1dGhlbnRpY2F0b3JTZWxlY3Rpb24/LnVzZXJWZXJpZmljYXRpb24gPT09ICdyZXF1aXJlZCcpIHtcbiAgICAgICAgICAgIC8vIGh0dHBzOi8vdzNjLmdpdGh1Yi5pby93ZWJhdXRobi8jc2N0bi1jcmVhdGVDcmVkZW50aWFsIChTdGVwIDIyLjQpXG4gICAgICAgICAgICByZXR1cm4gbmV3IFdlYkF1dGhuRXJyb3Ioe1xuICAgICAgICAgICAgICAgIG1lc3NhZ2U6ICdVc2VyIHZlcmlmaWNhdGlvbiB3YXMgcmVxdWlyZWQgZHVyaW5nIGF1dG9tYXRpYyByZWdpc3RyYXRpb24gYnV0IGl0IGNvdWxkIG5vdCBiZSBwZXJmb3JtZWQnLFxuICAgICAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9BVVRPX1JFR0lTVEVSX1VTRVJfVkVSSUZJQ0FUSU9OX0ZBSUxVUkUnLFxuICAgICAgICAgICAgICAgIGNhdXNlOiBlcnJvcixcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgICAgIGVsc2UgaWYgKHB1YmxpY0tleS5hdXRoZW50aWNhdG9yU2VsZWN0aW9uPy51c2VyVmVyaWZpY2F0aW9uID09PSAncmVxdWlyZWQnKSB7XG4gICAgICAgICAgICAvLyBodHRwczovL3d3dy53My5vcmcvVFIvd2ViYXV0aG4tMi8jc2N0bi1vcC1tYWtlLWNyZWQgKFN0ZXAgNSlcbiAgICAgICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICAgICAgbWVzc2FnZTogJ1VzZXIgdmVyaWZpY2F0aW9uIHdhcyByZXF1aXJlZCBidXQgbm8gYXZhaWxhYmxlIGF1dGhlbnRpY2F0b3Igc3VwcG9ydGVkIGl0JyxcbiAgICAgICAgICAgICAgICBjb2RlOiAnRVJST1JfQVVUSEVOVElDQVRPUl9NSVNTSU5HX1VTRVJfVkVSSUZJQ0FUSU9OX1NVUFBPUlQnLFxuICAgICAgICAgICAgICAgIGNhdXNlOiBlcnJvcixcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgfVxuICAgIGVsc2UgaWYgKGVycm9yLm5hbWUgPT09ICdJbnZhbGlkU3RhdGVFcnJvcicpIHtcbiAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tY3JlYXRlQ3JlZGVudGlhbCAoU3RlcCAyMClcbiAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tb3AtbWFrZS1jcmVkIChTdGVwIDMpXG4gICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICBtZXNzYWdlOiAnVGhlIGF1dGhlbnRpY2F0b3Igd2FzIHByZXZpb3VzbHkgcmVnaXN0ZXJlZCcsXG4gICAgICAgICAgICBjb2RlOiAnRVJST1JfQVVUSEVOVElDQVRPUl9QUkVWSU9VU0xZX1JFR0lTVEVSRUQnLFxuICAgICAgICAgICAgY2F1c2U6IGVycm9yLFxuICAgICAgICB9KTtcbiAgICB9XG4gICAgZWxzZSBpZiAoZXJyb3IubmFtZSA9PT0gJ05vdEFsbG93ZWRFcnJvcicpIHtcbiAgICAgICAgLyoqXG4gICAgICAgICAqIFBhc3MgdGhlIGVycm9yIGRpcmVjdGx5IHRocm91Z2guIFBsYXRmb3JtcyBhcmUgb3ZlcmxvYWRpbmcgdGhpcyBlcnJvciBiZXlvbmQgd2hhdCB0aGUgc3BlY1xuICAgICAgICAgKiBkZWZpbmVzIGFuZCB3ZSBkb24ndCB3YW50IHRvIG92ZXJ3cml0ZSBwb3RlbnRpYWxseSB1c2VmdWwgZXJyb3IgbWVzc2FnZXMuXG4gICAgICAgICAqL1xuICAgICAgICByZXR1cm4gbmV3IFdlYkF1dGhuRXJyb3Ioe1xuICAgICAgICAgICAgbWVzc2FnZTogZXJyb3IubWVzc2FnZSxcbiAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9QQVNTVEhST1VHSF9TRUVfQ0FVU0VfUFJPUEVSVFknLFxuICAgICAgICAgICAgY2F1c2U6IGVycm9yLFxuICAgICAgICB9KTtcbiAgICB9XG4gICAgZWxzZSBpZiAoZXJyb3IubmFtZSA9PT0gJ05vdFN1cHBvcnRlZEVycm9yJykge1xuICAgICAgICBjb25zdCB2YWxpZFB1YktleUNyZWRQYXJhbXMgPSBwdWJsaWNLZXkucHViS2V5Q3JlZFBhcmFtcy5maWx0ZXIoKHBhcmFtKSA9PiBwYXJhbS50eXBlID09PSAncHVibGljLWtleScpO1xuICAgICAgICBpZiAodmFsaWRQdWJLZXlDcmVkUGFyYW1zLmxlbmd0aCA9PT0gMCkge1xuICAgICAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tY3JlYXRlQ3JlZGVudGlhbCAoU3RlcCAxMClcbiAgICAgICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICAgICAgbWVzc2FnZTogJ05vIGVudHJ5IGluIHB1YktleUNyZWRQYXJhbXMgd2FzIG9mIHR5cGUgXCJwdWJsaWMta2V5XCInLFxuICAgICAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9NQUxGT1JNRURfUFVCS0VZQ1JFRFBBUkFNUycsXG4gICAgICAgICAgICAgICAgY2F1c2U6IGVycm9yLFxuICAgICAgICAgICAgfSk7XG4gICAgICAgIH1cbiAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tb3AtbWFrZS1jcmVkIChTdGVwIDIpXG4gICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICBtZXNzYWdlOiAnTm8gYXZhaWxhYmxlIGF1dGhlbnRpY2F0b3Igc3VwcG9ydGVkIGFueSBvZiB0aGUgc3BlY2lmaWVkIHB1YktleUNyZWRQYXJhbXMgYWxnb3JpdGhtcycsXG4gICAgICAgICAgICBjb2RlOiAnRVJST1JfQVVUSEVOVElDQVRPUl9OT19TVVBQT1JURURfUFVCS0VZQ1JFRFBBUkFNU19BTEcnLFxuICAgICAgICAgICAgY2F1c2U6IGVycm9yLFxuICAgICAgICB9KTtcbiAgICB9XG4gICAgZWxzZSBpZiAoZXJyb3IubmFtZSA9PT0gJ1NlY3VyaXR5RXJyb3InKSB7XG4gICAgICAgIGNvbnN0IGVmZmVjdGl2ZURvbWFpbiA9IGdsb2JhbFRoaXMubG9jYXRpb24uaG9zdG5hbWU7XG4gICAgICAgIGlmICghaXNWYWxpZERvbWFpbihlZmZlY3RpdmVEb21haW4pKSB7XG4gICAgICAgICAgICAvLyBodHRwczovL3d3dy53My5vcmcvVFIvd2ViYXV0aG4tMi8jc2N0bi1jcmVhdGVDcmVkZW50aWFsIChTdGVwIDcpXG4gICAgICAgICAgICByZXR1cm4gbmV3IFdlYkF1dGhuRXJyb3Ioe1xuICAgICAgICAgICAgICAgIG1lc3NhZ2U6IGAke2dsb2JhbFRoaXMubG9jYXRpb24uaG9zdG5hbWV9IGlzIGFuIGludmFsaWQgZG9tYWluYCxcbiAgICAgICAgICAgICAgICBjb2RlOiAnRVJST1JfSU5WQUxJRF9ET01BSU4nLFxuICAgICAgICAgICAgICAgIGNhdXNlOiBlcnJvcixcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgICAgIGVsc2UgaWYgKHB1YmxpY0tleS5ycC5pZCAhPT0gZWZmZWN0aXZlRG9tYWluKSB7XG4gICAgICAgICAgICAvLyBodHRwczovL3d3dy53My5vcmcvVFIvd2ViYXV0aG4tMi8jc2N0bi1jcmVhdGVDcmVkZW50aWFsIChTdGVwIDgpXG4gICAgICAgICAgICByZXR1cm4gbmV3IFdlYkF1dGhuRXJyb3Ioe1xuICAgICAgICAgICAgICAgIG1lc3NhZ2U6IGBUaGUgUlAgSUQgXCIke3B1YmxpY0tleS5ycC5pZH1cIiBpcyBpbnZhbGlkIGZvciB0aGlzIGRvbWFpbmAsXG4gICAgICAgICAgICAgICAgY29kZTogJ0VSUk9SX0lOVkFMSURfUlBfSUQnLFxuICAgICAgICAgICAgICAgIGNhdXNlOiBlcnJvcixcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgfVxuICAgIGVsc2UgaWYgKGVycm9yLm5hbWUgPT09ICdUeXBlRXJyb3InKSB7XG4gICAgICAgIGlmIChwdWJsaWNLZXkudXNlci5pZC5ieXRlTGVuZ3RoIDwgMSB8fCBwdWJsaWNLZXkudXNlci5pZC5ieXRlTGVuZ3RoID4gNjQpIHtcbiAgICAgICAgICAgIC8vIGh0dHBzOi8vd3d3LnczLm9yZy9UUi93ZWJhdXRobi0yLyNzY3RuLWNyZWF0ZUNyZWRlbnRpYWwgKFN0ZXAgNSlcbiAgICAgICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICAgICAgbWVzc2FnZTogJ1VzZXIgSUQgd2FzIG5vdCBiZXR3ZWVuIDEgYW5kIDY0IGNoYXJhY3RlcnMnLFxuICAgICAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9JTlZBTElEX1VTRVJfSURfTEVOR1RIJyxcbiAgICAgICAgICAgICAgICBjYXVzZTogZXJyb3IsXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgIH1cbiAgICBlbHNlIGlmIChlcnJvci5uYW1lID09PSAnVW5rbm93bkVycm9yJykge1xuICAgICAgICAvLyBodHRwczovL3d3dy53My5vcmcvVFIvd2ViYXV0aG4tMi8jc2N0bi1vcC1tYWtlLWNyZWQgKFN0ZXAgMSlcbiAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tb3AtbWFrZS1jcmVkIChTdGVwIDgpXG4gICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICBtZXNzYWdlOiAnVGhlIGF1dGhlbnRpY2F0b3Igd2FzIHVuYWJsZSB0byBwcm9jZXNzIHRoZSBzcGVjaWZpZWQgb3B0aW9ucywgb3IgY291bGQgbm90IGNyZWF0ZSBhIG5ldyBjcmVkZW50aWFsJyxcbiAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9BVVRIRU5USUNBVE9SX0dFTkVSQUxfRVJST1InLFxuICAgICAgICAgICAgY2F1c2U6IGVycm9yLFxuICAgICAgICB9KTtcbiAgICB9XG4gICAgcmV0dXJuIGVycm9yO1xufVxuIiwiLyoqXG4gKiBBIHNpbXBsZSB0ZXN0IHRvIGRldGVybWluZSBpZiBhIGhvc3RuYW1lIGlzIGEgcHJvcGVybHktZm9ybWF0dGVkIGRvbWFpbiBuYW1lXG4gKlxuICogQSBcInZhbGlkIGRvbWFpblwiIGlzIGRlZmluZWQgaGVyZTogaHR0cHM6Ly91cmwuc3BlYy53aGF0d2cub3JnLyN2YWxpZC1kb21haW5cbiAqXG4gKiBSZWdleCBzb3VyY2VkIGZyb20gaGVyZTpcbiAqIGh0dHBzOi8vd3d3Lm9yZWlsbHkuY29tL2xpYnJhcnkvdmlldy9yZWd1bGFyLWV4cHJlc3Npb25zLWNvb2tib29rLzk3ODE0NDkzMjc0NTMvY2gwOHMxNS5odG1sXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBpc1ZhbGlkRG9tYWluKGhvc3RuYW1lKSB7XG4gICAgcmV0dXJuIChcbiAgICAvLyBDb25zaWRlciBsb2NhbGhvc3QgdmFsaWQgYXMgd2VsbCBzaW5jZSBpdCdzIG9rYXkgd3J0IFNlY3VyZSBDb250ZXh0c1xuICAgIGhvc3RuYW1lID09PSAnbG9jYWxob3N0JyB8fFxuICAgICAgICAvXihbYS16MC05XSsoLVthLXowLTldKykqXFwuKStbYS16XXsyLH0kL2kudGVzdChob3N0bmFtZSkpO1xufVxuIiwiaW1wb3J0IHsgYnJvd3NlclN1cHBvcnRzV2ViQXV0aG4gfSBmcm9tICcuL2Jyb3dzZXJTdXBwb3J0c1dlYkF1dGhuLmpzJztcbi8qKlxuICogRGV0ZXJtaW5lIHdoZXRoZXIgdGhlIGJyb3dzZXIgY2FuIGNvbW11bmljYXRlIHdpdGggYSBidWlsdC1pbiBhdXRoZW50aWNhdG9yLCBsaWtlXG4gKiBUb3VjaCBJRCwgQW5kcm9pZCBmaW5nZXJwcmludCBzY2FubmVyLCBvciBXaW5kb3dzIEhlbGxvLlxuICpcbiAqIFRoaXMgbWV0aG9kIHdpbGwgX25vdF8gYmUgYWJsZSB0byB0ZWxsIHlvdSB0aGUgbmFtZSBvZiB0aGUgcGxhdGZvcm0gYXV0aGVudGljYXRvci5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHBsYXRmb3JtQXV0aGVudGljYXRvcklzQXZhaWxhYmxlKCkge1xuICAgIGlmICghYnJvd3NlclN1cHBvcnRzV2ViQXV0aG4oKSkge1xuICAgICAgICByZXR1cm4gbmV3IFByb21pc2UoKHJlc29sdmUpID0+IHJlc29sdmUoZmFsc2UpKTtcbiAgICB9XG4gICAgcmV0dXJuIFB1YmxpY0tleUNyZWRlbnRpYWwuaXNVc2VyVmVyaWZ5aW5nUGxhdGZvcm1BdXRoZW50aWNhdG9yQXZhaWxhYmxlKCk7XG59XG4iLCJjb25zdCBhdHRhY2htZW50cyA9IFsnY3Jvc3MtcGxhdGZvcm0nLCAncGxhdGZvcm0nXTtcbi8qKlxuICogSWYgcG9zc2libGUgY29lcmNlIGEgYHN0cmluZ2AgdmFsdWUgaW50byBhIGtub3duIGBBdXRoZW50aWNhdG9yQXR0YWNobWVudGBcbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHRvQXV0aGVudGljYXRvckF0dGFjaG1lbnQoYXR0YWNobWVudCkge1xuICAgIGlmICghYXR0YWNobWVudCkge1xuICAgICAgICByZXR1cm47XG4gICAgfVxuICAgIGlmIChhdHRhY2htZW50cy5pbmRleE9mKGF0dGFjaG1lbnQpIDwgMCkge1xuICAgICAgICByZXR1cm47XG4gICAgfVxuICAgIHJldHVybiBhdHRhY2htZW50O1xufVxuIiwiaW1wb3J0IHsgYmFzZTY0VVJMU3RyaW5nVG9CdWZmZXIgfSBmcm9tICcuL2Jhc2U2NFVSTFN0cmluZ1RvQnVmZmVyLmpzJztcbmV4cG9ydCBmdW5jdGlvbiB0b1B1YmxpY0tleUNyZWRlbnRpYWxEZXNjcmlwdG9yKGRlc2NyaXB0b3IpIHtcbiAgICBjb25zdCB7IGlkIH0gPSBkZXNjcmlwdG9yO1xuICAgIHJldHVybiB7XG4gICAgICAgIC4uLmRlc2NyaXB0b3IsXG4gICAgICAgIGlkOiBiYXNlNjRVUkxTdHJpbmdUb0J1ZmZlcihpZCksXG4gICAgICAgIC8qKlxuICAgICAgICAgKiBgZGVzY3JpcHRvci50cmFuc3BvcnRzYCBpcyBhbiBhcnJheSBvZiBvdXIgYEF1dGhlbnRpY2F0b3JUcmFuc3BvcnRGdXR1cmVgIHRoYXQgaW5jbHVkZXMgbmV3ZXJcbiAgICAgICAgICogdHJhbnNwb3J0cyB0aGF0IFR5cGVTY3JpcHQncyBET00gbGliIGlzIGlnbm9yYW50IG9mLiBDb252aW5jZSBUUyB0aGF0IG91ciBsaXN0IG9mIHRyYW5zcG9ydHNcbiAgICAgICAgICogYXJlIGZpbmUgdG8gcGFzcyB0byBXZWJBdXRobiBzaW5jZSBicm93c2VycyB3aWxsIHJlY29nbml6ZSB0aGUgbmV3IHZhbHVlLlxuICAgICAgICAgKi9cbiAgICAgICAgdHJhbnNwb3J0czogZGVzY3JpcHRvci50cmFuc3BvcnRzLFxuICAgIH07XG59XG4iLCJjbGFzcyBCYXNlV2ViQXV0aG5BYm9ydFNlcnZpY2Uge1xuICAgIGNvbnN0cnVjdG9yKCkge1xuICAgICAgICBPYmplY3QuZGVmaW5lUHJvcGVydHkodGhpcywgXCJjb250cm9sbGVyXCIsIHtcbiAgICAgICAgICAgIGVudW1lcmFibGU6IHRydWUsXG4gICAgICAgICAgICBjb25maWd1cmFibGU6IHRydWUsXG4gICAgICAgICAgICB3cml0YWJsZTogdHJ1ZSxcbiAgICAgICAgICAgIHZhbHVlOiB2b2lkIDBcbiAgICAgICAgfSk7XG4gICAgfVxuICAgIGNyZWF0ZU5ld0Fib3J0U2lnbmFsKCkge1xuICAgICAgICAvLyBBYm9ydCBhbnkgZXhpc3RpbmcgY2FsbHMgdG8gbmF2aWdhdG9yLmNyZWRlbnRpYWxzLmNyZWF0ZSgpIG9yIG5hdmlnYXRvci5jcmVkZW50aWFscy5nZXQoKVxuICAgICAgICBpZiAodGhpcy5jb250cm9sbGVyKSB7XG4gICAgICAgICAgICBjb25zdCBhYm9ydEVycm9yID0gbmV3IEVycm9yKCdDYW5jZWxsaW5nIGV4aXN0aW5nIFdlYkF1dGhuIEFQSSBjYWxsIGZvciBuZXcgb25lJyk7XG4gICAgICAgICAgICBhYm9ydEVycm9yLm5hbWUgPSAnQWJvcnRFcnJvcic7XG4gICAgICAgICAgICB0aGlzLmNvbnRyb2xsZXIuYWJvcnQoYWJvcnRFcnJvcik7XG4gICAgICAgIH1cbiAgICAgICAgY29uc3QgbmV3Q29udHJvbGxlciA9IG5ldyBBYm9ydENvbnRyb2xsZXIoKTtcbiAgICAgICAgdGhpcy5jb250cm9sbGVyID0gbmV3Q29udHJvbGxlcjtcbiAgICAgICAgcmV0dXJuIG5ld0NvbnRyb2xsZXIuc2lnbmFsO1xuICAgIH1cbiAgICBjYW5jZWxDZXJlbW9ueSgpIHtcbiAgICAgICAgaWYgKHRoaXMuY29udHJvbGxlcikge1xuICAgICAgICAgICAgY29uc3QgYWJvcnRFcnJvciA9IG5ldyBFcnJvcignTWFudWFsbHkgY2FuY2VsbGluZyBleGlzdGluZyBXZWJBdXRobiBBUEkgY2FsbCcpO1xuICAgICAgICAgICAgYWJvcnRFcnJvci5uYW1lID0gJ0Fib3J0RXJyb3InO1xuICAgICAgICAgICAgdGhpcy5jb250cm9sbGVyLmFib3J0KGFib3J0RXJyb3IpO1xuICAgICAgICAgICAgdGhpcy5jb250cm9sbGVyID0gdW5kZWZpbmVkO1xuICAgICAgICB9XG4gICAgfVxufVxuLyoqXG4gKiBBIHNlcnZpY2Ugc2luZ2xldG9uIHRvIGhlbHAgZW5zdXJlIHRoYXQgb25seSBhIHNpbmdsZSBXZWJBdXRobiBjZXJlbW9ueSBpcyBhY3RpdmUgYXQgYSB0aW1lLlxuICpcbiAqIFVzZXJzIG9mICoqQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXIqKiBzaG91bGRuJ3QgdHlwaWNhbGx5IG5lZWQgdG8gdXNlIHRoaXMsIGJ1dCBpdCBjYW4gaGVscCBlLmcuXG4gKiBkZXZlbG9wZXJzIGJ1aWxkaW5nIHByb2plY3RzIHRoYXQgdXNlIGNsaWVudC1zaWRlIHJvdXRpbmcgdG8gYmV0dGVyIGNvbnRyb2wgdGhlIGJlaGF2aW9yIG9mXG4gKiB0aGVpciBVWCBpbiByZXNwb25zZSB0byByb3V0ZXIgbmF2aWdhdGlvbiBldmVudHMuXG4gKi9cbmV4cG9ydCBjb25zdCBXZWJBdXRobkFib3J0U2VydmljZSA9IG5ldyBCYXNlV2ViQXV0aG5BYm9ydFNlcnZpY2UoKTtcbiIsIi8qKlxuICogQSBjdXN0b20gRXJyb3IgdXNlZCB0byByZXR1cm4gYSBtb3JlIG51YW5jZWQgZXJyb3IgZGV0YWlsaW5nIF93aHlfIG9uZSBvZiB0aGUgZWlnaHQgZG9jdW1lbnRlZFxuICogZXJyb3JzIGluIHRoZSBzcGVjIHdhcyByYWlzZWQgYWZ0ZXIgY2FsbGluZyBgbmF2aWdhdG9yLmNyZWRlbnRpYWxzLmNyZWF0ZSgpYCBvclxuICogYG5hdmlnYXRvci5jcmVkZW50aWFscy5nZXQoKWA6XG4gKlxuICogLSBgQWJvcnRFcnJvcmBcbiAqIC0gYENvbnN0cmFpbnRFcnJvcmBcbiAqIC0gYEludmFsaWRTdGF0ZUVycm9yYFxuICogLSBgTm90QWxsb3dlZEVycm9yYFxuICogLSBgTm90U3VwcG9ydGVkRXJyb3JgXG4gKiAtIGBTZWN1cml0eUVycm9yYFxuICogLSBgVHlwZUVycm9yYFxuICogLSBgVW5rbm93bkVycm9yYFxuICpcbiAqIEVycm9yIG1lc3NhZ2VzIHdlcmUgZGV0ZXJtaW5lZCB0aHJvdWdoIGludmVzdGlnYXRpb24gb2YgdGhlIHNwZWMgdG8gZGV0ZXJtaW5lIHVuZGVyIHdoaWNoXG4gKiBzY2VuYXJpb3MgYSBnaXZlbiBlcnJvciB3b3VsZCBiZSByYWlzZWQuXG4gKi9cbmV4cG9ydCBjbGFzcyBXZWJBdXRobkVycm9yIGV4dGVuZHMgRXJyb3Ige1xuICAgIGNvbnN0cnVjdG9yKHsgbWVzc2FnZSwgY29kZSwgY2F1c2UsIG5hbWUsIH0pIHtcbiAgICAgICAgLy8gQHRzLWlnbm9yZTogaGVscCBSb2xsdXAgdW5kZXJzdGFuZCB0aGF0IGBjYXVzZWAgaXMgb2theSB0byBzZXRcbiAgICAgICAgc3VwZXIobWVzc2FnZSwgeyBjYXVzZSB9KTtcbiAgICAgICAgT2JqZWN0LmRlZmluZVByb3BlcnR5KHRoaXMsIFwiY29kZVwiLCB7XG4gICAgICAgICAgICBlbnVtZXJhYmxlOiB0cnVlLFxuICAgICAgICAgICAgY29uZmlndXJhYmxlOiB0cnVlLFxuICAgICAgICAgICAgd3JpdGFibGU6IHRydWUsXG4gICAgICAgICAgICB2YWx1ZTogdm9pZCAwXG4gICAgICAgIH0pO1xuICAgICAgICB0aGlzLm5hbWUgPSBuYW1lID8/IGNhdXNlLm5hbWU7XG4gICAgICAgIHRoaXMuY29kZSA9IGNvZGU7XG4gICAgfVxufVxuIiwiZXhwb3J0ICogZnJvbSAnLi9tZXRob2RzL3N0YXJ0UmVnaXN0cmF0aW9uLmpzJztcbmV4cG9ydCAqIGZyb20gJy4vbWV0aG9kcy9zdGFydEF1dGhlbnRpY2F0aW9uLmpzJztcbmV4cG9ydCAqIGZyb20gJy4vaGVscGVycy9icm93c2VyU3VwcG9ydHNXZWJBdXRobi5qcyc7XG5leHBvcnQgKiBmcm9tICcuL2hlbHBlcnMvcGxhdGZvcm1BdXRoZW50aWNhdG9ySXNBdmFpbGFibGUuanMnO1xuZXhwb3J0ICogZnJvbSAnLi9oZWxwZXJzL2Jyb3dzZXJTdXBwb3J0c1dlYkF1dGhuQXV0b2ZpbGwuanMnO1xuZXhwb3J0ICogZnJvbSAnLi9oZWxwZXJzL2Jhc2U2NFVSTFN0cmluZ1RvQnVmZmVyLmpzJztcbmV4cG9ydCAqIGZyb20gJy4vaGVscGVycy9idWZmZXJUb0Jhc2U2NFVSTFN0cmluZy5qcyc7XG5leHBvcnQgKiBmcm9tICcuL2hlbHBlcnMvd2ViQXV0aG5BYm9ydFNlcnZpY2UuanMnO1xuZXhwb3J0ICogZnJvbSAnLi9oZWxwZXJzL3dlYkF1dGhuRXJyb3IuanMnO1xuZXhwb3J0ICogZnJvbSAnLi90eXBlcy9pbmRleC5qcyc7XG4iLCJpbXBvcnQgeyBidWZmZXJUb0Jhc2U2NFVSTFN0cmluZyB9IGZyb20gJy4uL2hlbHBlcnMvYnVmZmVyVG9CYXNlNjRVUkxTdHJpbmcuanMnO1xuaW1wb3J0IHsgYmFzZTY0VVJMU3RyaW5nVG9CdWZmZXIgfSBmcm9tICcuLi9oZWxwZXJzL2Jhc2U2NFVSTFN0cmluZ1RvQnVmZmVyLmpzJztcbmltcG9ydCB7IGJyb3dzZXJTdXBwb3J0c1dlYkF1dGhuIH0gZnJvbSAnLi4vaGVscGVycy9icm93c2VyU3VwcG9ydHNXZWJBdXRobi5qcyc7XG5pbXBvcnQgeyBicm93c2VyU3VwcG9ydHNXZWJBdXRobkF1dG9maWxsIH0gZnJvbSAnLi4vaGVscGVycy9icm93c2VyU3VwcG9ydHNXZWJBdXRobkF1dG9maWxsLmpzJztcbmltcG9ydCB7IHRvUHVibGljS2V5Q3JlZGVudGlhbERlc2NyaXB0b3IgfSBmcm9tICcuLi9oZWxwZXJzL3RvUHVibGljS2V5Q3JlZGVudGlhbERlc2NyaXB0b3IuanMnO1xuaW1wb3J0IHsgaWRlbnRpZnlBdXRoZW50aWNhdGlvbkVycm9yIH0gZnJvbSAnLi4vaGVscGVycy9pZGVudGlmeUF1dGhlbnRpY2F0aW9uRXJyb3IuanMnO1xuaW1wb3J0IHsgV2ViQXV0aG5BYm9ydFNlcnZpY2UgfSBmcm9tICcuLi9oZWxwZXJzL3dlYkF1dGhuQWJvcnRTZXJ2aWNlLmpzJztcbmltcG9ydCB7IHRvQXV0aGVudGljYXRvckF0dGFjaG1lbnQgfSBmcm9tICcuLi9oZWxwZXJzL3RvQXV0aGVudGljYXRvckF0dGFjaG1lbnQuanMnO1xuLyoqXG4gKiBCZWdpbiBhdXRoZW50aWNhdG9yIFwibG9naW5cIiB2aWEgV2ViQXV0aG4gYXNzZXJ0aW9uXG4gKlxuICogQHBhcmFtIG9wdGlvbnNKU09OIE91dHB1dCBmcm9tICoqQHNpbXBsZXdlYmF1dGhuL3NlcnZlcioqJ3MgYGdlbmVyYXRlQXV0aGVudGljYXRpb25PcHRpb25zKClgXG4gKiBAcGFyYW0gdXNlQnJvd3NlckF1dG9maWxsIChPcHRpb25hbCkgSW5pdGlhbGl6ZSBjb25kaXRpb25hbCBVSSB0byBlbmFibGUgbG9nZ2luZyBpbiB2aWEgYnJvd3NlciBhdXRvZmlsbCBwcm9tcHRzLiBEZWZhdWx0cyB0byBgZmFsc2VgLlxuICogQHBhcmFtIHZlcmlmeUJyb3dzZXJBdXRvZmlsbElucHV0IChPcHRpb25hbCkgRW5zdXJlIGEgc3VpdGFibGUgYDxpbnB1dD5gIGVsZW1lbnQgaXMgcHJlc2VudCB3aGVuIGB1c2VCcm93c2VyQXV0b2ZpbGxgIGlzIGB0cnVlYC4gRGVmYXVsdHMgdG8gYHRydWVgLlxuICovXG5leHBvcnQgYXN5bmMgZnVuY3Rpb24gc3RhcnRBdXRoZW50aWNhdGlvbihvcHRpb25zKSB7XG4gICAgLy8gQHRzLWlnbm9yZTogSW50ZW50aW9uYWxseSBjaGVjayBmb3Igb2xkIGNhbGwgc3RydWN0dXJlIHRvIHdhcm4gYWJvdXQgaW1wcm9wZXIgQVBJIGNhbGxcbiAgICBpZiAoIW9wdGlvbnMub3B0aW9uc0pTT04gJiYgb3B0aW9ucy5jaGFsbGVuZ2UpIHtcbiAgICAgICAgY29uc29sZS53YXJuKCdzdGFydEF1dGhlbnRpY2F0aW9uKCkgd2FzIG5vdCBjYWxsZWQgY29ycmVjdGx5LiBJdCB3aWxsIHRyeSB0byBjb250aW51ZSB3aXRoIHRoZSBwcm92aWRlZCBvcHRpb25zLCBidXQgdGhpcyBjYWxsIHNob3VsZCBiZSByZWZhY3RvcmVkIHRvIHVzZSB0aGUgZXhwZWN0ZWQgY2FsbCBzdHJ1Y3R1cmUgaW5zdGVhZC4gU2VlIGh0dHBzOi8vc2ltcGxld2ViYXV0aG4uZGV2L2RvY3MvcGFja2FnZXMvYnJvd3NlciN0eXBlZXJyb3ItY2Fubm90LXJlYWQtcHJvcGVydGllcy1vZi11bmRlZmluZWQtcmVhZGluZy1jaGFsbGVuZ2UgZm9yIG1vcmUgaW5mb3JtYXRpb24uJyk7XG4gICAgICAgIC8vIEB0cy1pZ25vcmU6IFJlYXNzaWduIHRoZSBvcHRpb25zLCBwYXNzZWQgaW4gYXMgYSBwb3NpdGlvbmFsIGFyZ3VtZW50LCB0byB0aGUgZXhwZWN0ZWQgdmFyaWFibGVcbiAgICAgICAgb3B0aW9ucyA9IHsgb3B0aW9uc0pTT046IG9wdGlvbnMgfTtcbiAgICB9XG4gICAgY29uc3QgeyBvcHRpb25zSlNPTiwgdXNlQnJvd3NlckF1dG9maWxsID0gZmFsc2UsIHZlcmlmeUJyb3dzZXJBdXRvZmlsbElucHV0ID0gdHJ1ZSwgfSA9IG9wdGlvbnM7XG4gICAgaWYgKCFicm93c2VyU3VwcG9ydHNXZWJBdXRobigpKSB7XG4gICAgICAgIHRocm93IG5ldyBFcnJvcignV2ViQXV0aG4gaXMgbm90IHN1cHBvcnRlZCBpbiB0aGlzIGJyb3dzZXInKTtcbiAgICB9XG4gICAgLy8gV2UgbmVlZCB0byBhdm9pZCBwYXNzaW5nIGVtcHR5IGFycmF5IHRvIGF2b2lkIGJsb2NraW5nIHJldHJpZXZhbFxuICAgIC8vIG9mIHB1YmxpYyBrZXlcbiAgICBsZXQgYWxsb3dDcmVkZW50aWFscztcbiAgICBpZiAob3B0aW9uc0pTT04uYWxsb3dDcmVkZW50aWFscz8ubGVuZ3RoICE9PSAwKSB7XG4gICAgICAgIGFsbG93Q3JlZGVudGlhbHMgPSBvcHRpb25zSlNPTi5hbGxvd0NyZWRlbnRpYWxzPy5tYXAodG9QdWJsaWNLZXlDcmVkZW50aWFsRGVzY3JpcHRvcik7XG4gICAgfVxuICAgIC8vIFdlIG5lZWQgdG8gY29udmVydCBzb21lIHZhbHVlcyB0byBVaW50OEFycmF5cyBiZWZvcmUgcGFzc2luZyB0aGUgY3JlZGVudGlhbHMgdG8gdGhlIG5hdmlnYXRvclxuICAgIGNvbnN0IHB1YmxpY0tleSA9IHtcbiAgICAgICAgLi4ub3B0aW9uc0pTT04sXG4gICAgICAgIGNoYWxsZW5nZTogYmFzZTY0VVJMU3RyaW5nVG9CdWZmZXIob3B0aW9uc0pTT04uY2hhbGxlbmdlKSxcbiAgICAgICAgYWxsb3dDcmVkZW50aWFscyxcbiAgICB9O1xuICAgIC8vIFByZXBhcmUgb3B0aW9ucyBmb3IgYC5nZXQoKWBcbiAgICBjb25zdCBnZXRPcHRpb25zID0ge307XG4gICAgLyoqXG4gICAgICogU2V0IHVwIHRoZSBwYWdlIHRvIHByb21wdCB0aGUgdXNlciB0byBzZWxlY3QgYSBjcmVkZW50aWFsIGZvciBhdXRoZW50aWNhdGlvbiB2aWEgdGhlIGJyb3dzZXInc1xuICAgICAqIGlucHV0IGF1dG9maWxsIG1lY2hhbmlzbS5cbiAgICAgKi9cbiAgICBpZiAodXNlQnJvd3NlckF1dG9maWxsKSB7XG4gICAgICAgIGlmICghKGF3YWl0IGJyb3dzZXJTdXBwb3J0c1dlYkF1dGhuQXV0b2ZpbGwoKSkpIHtcbiAgICAgICAgICAgIHRocm93IEVycm9yKCdCcm93c2VyIGRvZXMgbm90IHN1cHBvcnQgV2ViQXV0aG4gYXV0b2ZpbGwnKTtcbiAgICAgICAgfVxuICAgICAgICAvLyBDaGVjayBmb3IgYW4gPGlucHV0PiB3aXRoIFwid2ViYXV0aG5cIiBpbiBpdHMgYGF1dG9jb21wbGV0ZWAgYXR0cmlidXRlXG4gICAgICAgIGNvbnN0IGVsaWdpYmxlSW5wdXRzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbChcImlucHV0W2F1dG9jb21wbGV0ZSQ9J3dlYmF1dGhuJ11cIik7XG4gICAgICAgIC8vIFdlYkF1dGhuIGF1dG9maWxsIHJlcXVpcmVzIGF0IGxlYXN0IG9uZSB2YWxpZCBpbnB1dFxuICAgICAgICBpZiAoZWxpZ2libGVJbnB1dHMubGVuZ3RoIDwgMSAmJiB2ZXJpZnlCcm93c2VyQXV0b2ZpbGxJbnB1dCkge1xuICAgICAgICAgICAgdGhyb3cgRXJyb3IoJ05vIDxpbnB1dD4gd2l0aCBcIndlYmF1dGhuXCIgYXMgdGhlIG9ubHkgb3IgbGFzdCB2YWx1ZSBpbiBpdHMgYGF1dG9jb21wbGV0ZWAgYXR0cmlidXRlIHdhcyBkZXRlY3RlZCcpO1xuICAgICAgICB9XG4gICAgICAgIC8vIGBDcmVkZW50aWFsTWVkaWF0aW9uUmVxdWlyZW1lbnRgIGRvZXNuJ3Qga25vdyBhYm91dCBcImNvbmRpdGlvbmFsXCIgeWV0IGFzIG9mXG4gICAgICAgIC8vIHR5cGVzY3JpcHRANC42LjNcbiAgICAgICAgZ2V0T3B0aW9ucy5tZWRpYXRpb24gPSAnY29uZGl0aW9uYWwnO1xuICAgICAgICAvLyBDb25kaXRpb25hbCBVSSByZXF1aXJlcyBhbiBlbXB0eSBhbGxvdyBsaXN0XG4gICAgICAgIHB1YmxpY0tleS5hbGxvd0NyZWRlbnRpYWxzID0gW107XG4gICAgfVxuICAgIC8vIEZpbmFsaXplIG9wdGlvbnNcbiAgICBnZXRPcHRpb25zLnB1YmxpY0tleSA9IHB1YmxpY0tleTtcbiAgICAvLyBTZXQgdXAgdGhlIGFiaWxpdHkgdG8gY2FuY2VsIHRoaXMgcmVxdWVzdCBpZiB0aGUgdXNlciBhdHRlbXB0cyBhbm90aGVyXG4gICAgZ2V0T3B0aW9ucy5zaWduYWwgPSBXZWJBdXRobkFib3J0U2VydmljZS5jcmVhdGVOZXdBYm9ydFNpZ25hbCgpO1xuICAgIC8vIFdhaXQgZm9yIHRoZSB1c2VyIHRvIGNvbXBsZXRlIGFzc2VydGlvblxuICAgIGxldCBjcmVkZW50aWFsO1xuICAgIHRyeSB7XG4gICAgICAgIGNyZWRlbnRpYWwgPSAoYXdhaXQgbmF2aWdhdG9yLmNyZWRlbnRpYWxzLmdldChnZXRPcHRpb25zKSk7XG4gICAgfVxuICAgIGNhdGNoIChlcnIpIHtcbiAgICAgICAgdGhyb3cgaWRlbnRpZnlBdXRoZW50aWNhdGlvbkVycm9yKHsgZXJyb3I6IGVyciwgb3B0aW9uczogZ2V0T3B0aW9ucyB9KTtcbiAgICB9XG4gICAgaWYgKCFjcmVkZW50aWFsKSB7XG4gICAgICAgIHRocm93IG5ldyBFcnJvcignQXV0aGVudGljYXRpb24gd2FzIG5vdCBjb21wbGV0ZWQnKTtcbiAgICB9XG4gICAgY29uc3QgeyBpZCwgcmF3SWQsIHJlc3BvbnNlLCB0eXBlIH0gPSBjcmVkZW50aWFsO1xuICAgIGxldCB1c2VySGFuZGxlID0gdW5kZWZpbmVkO1xuICAgIGlmIChyZXNwb25zZS51c2VySGFuZGxlKSB7XG4gICAgICAgIHVzZXJIYW5kbGUgPSBidWZmZXJUb0Jhc2U2NFVSTFN0cmluZyhyZXNwb25zZS51c2VySGFuZGxlKTtcbiAgICB9XG4gICAgLy8gQ29udmVydCB2YWx1ZXMgdG8gYmFzZTY0IHRvIG1ha2UgaXQgZWFzaWVyIHRvIHNlbmQgYmFjayB0byB0aGUgc2VydmVyXG4gICAgcmV0dXJuIHtcbiAgICAgICAgaWQsXG4gICAgICAgIHJhd0lkOiBidWZmZXJUb0Jhc2U2NFVSTFN0cmluZyhyYXdJZCksXG4gICAgICAgIHJlc3BvbnNlOiB7XG4gICAgICAgICAgICBhdXRoZW50aWNhdG9yRGF0YTogYnVmZmVyVG9CYXNlNjRVUkxTdHJpbmcocmVzcG9uc2UuYXV0aGVudGljYXRvckRhdGEpLFxuICAgICAgICAgICAgY2xpZW50RGF0YUpTT046IGJ1ZmZlclRvQmFzZTY0VVJMU3RyaW5nKHJlc3BvbnNlLmNsaWVudERhdGFKU09OKSxcbiAgICAgICAgICAgIHNpZ25hdHVyZTogYnVmZmVyVG9CYXNlNjRVUkxTdHJpbmcocmVzcG9uc2Uuc2lnbmF0dXJlKSxcbiAgICAgICAgICAgIHVzZXJIYW5kbGUsXG4gICAgICAgIH0sXG4gICAgICAgIHR5cGUsXG4gICAgICAgIGNsaWVudEV4dGVuc2lvblJlc3VsdHM6IGNyZWRlbnRpYWwuZ2V0Q2xpZW50RXh0ZW5zaW9uUmVzdWx0cygpLFxuICAgICAgICBhdXRoZW50aWNhdG9yQXR0YWNobWVudDogdG9BdXRoZW50aWNhdG9yQXR0YWNobWVudChjcmVkZW50aWFsLmF1dGhlbnRpY2F0b3JBdHRhY2htZW50KSxcbiAgICB9O1xufVxuIiwiaW1wb3J0IHsgYnVmZmVyVG9CYXNlNjRVUkxTdHJpbmcgfSBmcm9tICcuLi9oZWxwZXJzL2J1ZmZlclRvQmFzZTY0VVJMU3RyaW5nLmpzJztcbmltcG9ydCB7IGJhc2U2NFVSTFN0cmluZ1RvQnVmZmVyIH0gZnJvbSAnLi4vaGVscGVycy9iYXNlNjRVUkxTdHJpbmdUb0J1ZmZlci5qcyc7XG5pbXBvcnQgeyBicm93c2VyU3VwcG9ydHNXZWJBdXRobiB9IGZyb20gJy4uL2hlbHBlcnMvYnJvd3NlclN1cHBvcnRzV2ViQXV0aG4uanMnO1xuaW1wb3J0IHsgdG9QdWJsaWNLZXlDcmVkZW50aWFsRGVzY3JpcHRvciB9IGZyb20gJy4uL2hlbHBlcnMvdG9QdWJsaWNLZXlDcmVkZW50aWFsRGVzY3JpcHRvci5qcyc7XG5pbXBvcnQgeyBpZGVudGlmeVJlZ2lzdHJhdGlvbkVycm9yIH0gZnJvbSAnLi4vaGVscGVycy9pZGVudGlmeVJlZ2lzdHJhdGlvbkVycm9yLmpzJztcbmltcG9ydCB7IFdlYkF1dGhuQWJvcnRTZXJ2aWNlIH0gZnJvbSAnLi4vaGVscGVycy93ZWJBdXRobkFib3J0U2VydmljZS5qcyc7XG5pbXBvcnQgeyB0b0F1dGhlbnRpY2F0b3JBdHRhY2htZW50IH0gZnJvbSAnLi4vaGVscGVycy90b0F1dGhlbnRpY2F0b3JBdHRhY2htZW50LmpzJztcbi8qKlxuICogQmVnaW4gYXV0aGVudGljYXRvciBcInJlZ2lzdHJhdGlvblwiIHZpYSBXZWJBdXRobiBhdHRlc3RhdGlvblxuICpcbiAqIEBwYXJhbSBvcHRpb25zSlNPTiBPdXRwdXQgZnJvbSAqKkBzaW1wbGV3ZWJhdXRobi9zZXJ2ZXIqKidzIGBnZW5lcmF0ZVJlZ2lzdHJhdGlvbk9wdGlvbnMoKWBcbiAqIEBwYXJhbSB1c2VBdXRvUmVnaXN0ZXIgKE9wdGlvbmFsKSBUcnkgdG8gc2lsZW50bHkgY3JlYXRlIGEgcGFzc2tleSB3aXRoIHRoZSBwYXNzd29yZCBtYW5hZ2VyIHRoYXQgdGhlIHVzZXIganVzdCBzaWduZWQgaW4gd2l0aC4gRGVmYXVsdHMgdG8gYGZhbHNlYC5cbiAqL1xuZXhwb3J0IGFzeW5jIGZ1bmN0aW9uIHN0YXJ0UmVnaXN0cmF0aW9uKG9wdGlvbnMpIHtcbiAgICAvLyBAdHMtaWdub3JlOiBJbnRlbnRpb25hbGx5IGNoZWNrIGZvciBvbGQgY2FsbCBzdHJ1Y3R1cmUgdG8gd2FybiBhYm91dCBpbXByb3BlciBBUEkgY2FsbFxuICAgIGlmICghb3B0aW9ucy5vcHRpb25zSlNPTiAmJiBvcHRpb25zLmNoYWxsZW5nZSkge1xuICAgICAgICBjb25zb2xlLndhcm4oJ3N0YXJ0UmVnaXN0cmF0aW9uKCkgd2FzIG5vdCBjYWxsZWQgY29ycmVjdGx5LiBJdCB3aWxsIHRyeSB0byBjb250aW51ZSB3aXRoIHRoZSBwcm92aWRlZCBvcHRpb25zLCBidXQgdGhpcyBjYWxsIHNob3VsZCBiZSByZWZhY3RvcmVkIHRvIHVzZSB0aGUgZXhwZWN0ZWQgY2FsbCBzdHJ1Y3R1cmUgaW5zdGVhZC4gU2VlIGh0dHBzOi8vc2ltcGxld2ViYXV0aG4uZGV2L2RvY3MvcGFja2FnZXMvYnJvd3NlciN0eXBlZXJyb3ItY2Fubm90LXJlYWQtcHJvcGVydGllcy1vZi11bmRlZmluZWQtcmVhZGluZy1jaGFsbGVuZ2UgZm9yIG1vcmUgaW5mb3JtYXRpb24uJyk7XG4gICAgICAgIC8vIEB0cy1pZ25vcmU6IFJlYXNzaWduIHRoZSBvcHRpb25zLCBwYXNzZWQgaW4gYXMgYSBwb3NpdGlvbmFsIGFyZ3VtZW50LCB0byB0aGUgZXhwZWN0ZWQgdmFyaWFibGVcbiAgICAgICAgb3B0aW9ucyA9IHsgb3B0aW9uc0pTT046IG9wdGlvbnMgfTtcbiAgICB9XG4gICAgY29uc3QgeyBvcHRpb25zSlNPTiwgdXNlQXV0b1JlZ2lzdGVyID0gZmFsc2UgfSA9IG9wdGlvbnM7XG4gICAgaWYgKCFicm93c2VyU3VwcG9ydHNXZWJBdXRobigpKSB7XG4gICAgICAgIHRocm93IG5ldyBFcnJvcignV2ViQXV0aG4gaXMgbm90IHN1cHBvcnRlZCBpbiB0aGlzIGJyb3dzZXInKTtcbiAgICB9XG4gICAgLy8gV2UgbmVlZCB0byBjb252ZXJ0IHNvbWUgdmFsdWVzIHRvIFVpbnQ4QXJyYXlzIGJlZm9yZSBwYXNzaW5nIHRoZSBjcmVkZW50aWFscyB0byB0aGUgbmF2aWdhdG9yXG4gICAgY29uc3QgcHVibGljS2V5ID0ge1xuICAgICAgICAuLi5vcHRpb25zSlNPTixcbiAgICAgICAgY2hhbGxlbmdlOiBiYXNlNjRVUkxTdHJpbmdUb0J1ZmZlcihvcHRpb25zSlNPTi5jaGFsbGVuZ2UpLFxuICAgICAgICB1c2VyOiB7XG4gICAgICAgICAgICAuLi5vcHRpb25zSlNPTi51c2VyLFxuICAgICAgICAgICAgaWQ6IGJhc2U2NFVSTFN0cmluZ1RvQnVmZmVyKG9wdGlvbnNKU09OLnVzZXIuaWQpLFxuICAgICAgICB9LFxuICAgICAgICBleGNsdWRlQ3JlZGVudGlhbHM6IG9wdGlvbnNKU09OLmV4Y2x1ZGVDcmVkZW50aWFscz8ubWFwKHRvUHVibGljS2V5Q3JlZGVudGlhbERlc2NyaXB0b3IpLFxuICAgIH07XG4gICAgLy8gUHJlcGFyZSBvcHRpb25zIGZvciBgLmNyZWF0ZSgpYFxuICAgIGNvbnN0IGNyZWF0ZU9wdGlvbnMgPSB7fTtcbiAgICAvKipcbiAgICAgKiBUcnkgdG8gdXNlIGNvbmRpdGlvbmFsIGNyZWF0ZSB0byByZWdpc3RlciBhIHBhc3NrZXkgZm9yIHRoZSB1c2VyIHdpdGggdGhlIHBhc3N3b3JkIG1hbmFnZXJcbiAgICAgKiB0aGUgdXNlciBqdXN0IHVzZWQgdG8gYXV0aGVudGljYXRlIHdpdGguIFRoZSB1c2VyIHdvbid0IGJlIHNob3duIGFueSBwcm9taW5lbnQgVUkgYnkgdGhlXG4gICAgICogYnJvd3Nlci5cbiAgICAgKi9cbiAgICBpZiAodXNlQXV0b1JlZ2lzdGVyKSB7XG4gICAgICAgIC8vIEB0cy1pZ25vcmU6IGBtZWRpYXRpb25gIGRvZXNuJ3QgeWV0IGV4aXN0IG9uIENyZWRlbnRpYWxDcmVhdGlvbk9wdGlvbnMgYnV0IGl0J3MgcG9zc2libGUgYXMgb2YgU2VwdCAyMDI0XG4gICAgICAgIGNyZWF0ZU9wdGlvbnMubWVkaWF0aW9uID0gJ2NvbmRpdGlvbmFsJztcbiAgICB9XG4gICAgLy8gRmluYWxpemUgb3B0aW9uc1xuICAgIGNyZWF0ZU9wdGlvbnMucHVibGljS2V5ID0gcHVibGljS2V5O1xuICAgIC8vIFNldCB1cCB0aGUgYWJpbGl0eSB0byBjYW5jZWwgdGhpcyByZXF1ZXN0IGlmIHRoZSB1c2VyIGF0dGVtcHRzIGFub3RoZXJcbiAgICBjcmVhdGVPcHRpb25zLnNpZ25hbCA9IFdlYkF1dGhuQWJvcnRTZXJ2aWNlLmNyZWF0ZU5ld0Fib3J0U2lnbmFsKCk7XG4gICAgLy8gV2FpdCBmb3IgdGhlIHVzZXIgdG8gY29tcGxldGUgYXR0ZXN0YXRpb25cbiAgICBsZXQgY3JlZGVudGlhbDtcbiAgICB0cnkge1xuICAgICAgICBjcmVkZW50aWFsID0gKGF3YWl0IG5hdmlnYXRvci5jcmVkZW50aWFscy5jcmVhdGUoY3JlYXRlT3B0aW9ucykpO1xuICAgIH1cbiAgICBjYXRjaCAoZXJyKSB7XG4gICAgICAgIHRocm93IGlkZW50aWZ5UmVnaXN0cmF0aW9uRXJyb3IoeyBlcnJvcjogZXJyLCBvcHRpb25zOiBjcmVhdGVPcHRpb25zIH0pO1xuICAgIH1cbiAgICBpZiAoIWNyZWRlbnRpYWwpIHtcbiAgICAgICAgdGhyb3cgbmV3IEVycm9yKCdSZWdpc3RyYXRpb24gd2FzIG5vdCBjb21wbGV0ZWQnKTtcbiAgICB9XG4gICAgY29uc3QgeyBpZCwgcmF3SWQsIHJlc3BvbnNlLCB0eXBlIH0gPSBjcmVkZW50aWFsO1xuICAgIC8vIENvbnRpbnVlIHRvIHBsYXkgaXQgc2FmZSB3aXRoIGBnZXRUcmFuc3BvcnRzKClgIGZvciBub3csIGV2ZW4gd2hlbiBMMyB0eXBlcyBzYXkgaXQncyByZXF1aXJlZFxuICAgIGxldCB0cmFuc3BvcnRzID0gdW5kZWZpbmVkO1xuICAgIGlmICh0eXBlb2YgcmVzcG9uc2UuZ2V0VHJhbnNwb3J0cyA9PT0gJ2Z1bmN0aW9uJykge1xuICAgICAgICB0cmFuc3BvcnRzID0gcmVzcG9uc2UuZ2V0VHJhbnNwb3J0cygpO1xuICAgIH1cbiAgICAvLyBMMyBzYXlzIHRoaXMgaXMgcmVxdWlyZWQsIGJ1dCBicm93c2VyIGFuZCB3ZWJ2aWV3IHN1cHBvcnQgYXJlIHN0aWxsIG5vdCBndWFyYW50ZWVkLlxuICAgIGxldCByZXNwb25zZVB1YmxpY0tleUFsZ29yaXRobSA9IHVuZGVmaW5lZDtcbiAgICBpZiAodHlwZW9mIHJlc3BvbnNlLmdldFB1YmxpY0tleUFsZ29yaXRobSA9PT0gJ2Z1bmN0aW9uJykge1xuICAgICAgICB0cnkge1xuICAgICAgICAgICAgcmVzcG9uc2VQdWJsaWNLZXlBbGdvcml0aG0gPSByZXNwb25zZS5nZXRQdWJsaWNLZXlBbGdvcml0aG0oKTtcbiAgICAgICAgfVxuICAgICAgICBjYXRjaCAoZXJyb3IpIHtcbiAgICAgICAgICAgIHdhcm5PbkJyb2tlbkltcGxlbWVudGF0aW9uKCdnZXRQdWJsaWNLZXlBbGdvcml0aG0oKScsIGVycm9yKTtcbiAgICAgICAgfVxuICAgIH1cbiAgICBsZXQgcmVzcG9uc2VQdWJsaWNLZXkgPSB1bmRlZmluZWQ7XG4gICAgaWYgKHR5cGVvZiByZXNwb25zZS5nZXRQdWJsaWNLZXkgPT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgdHJ5IHtcbiAgICAgICAgICAgIGNvbnN0IF9wdWJsaWNLZXkgPSByZXNwb25zZS5nZXRQdWJsaWNLZXkoKTtcbiAgICAgICAgICAgIGlmIChfcHVibGljS2V5ICE9PSBudWxsKSB7XG4gICAgICAgICAgICAgICAgcmVzcG9uc2VQdWJsaWNLZXkgPSBidWZmZXJUb0Jhc2U2NFVSTFN0cmluZyhfcHVibGljS2V5KTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICBjYXRjaCAoZXJyb3IpIHtcbiAgICAgICAgICAgIHdhcm5PbkJyb2tlbkltcGxlbWVudGF0aW9uKCdnZXRQdWJsaWNLZXkoKScsIGVycm9yKTtcbiAgICAgICAgfVxuICAgIH1cbiAgICAvLyBMMyBzYXlzIHRoaXMgaXMgcmVxdWlyZWQsIGJ1dCBicm93c2VyIGFuZCB3ZWJ2aWV3IHN1cHBvcnQgYXJlIHN0aWxsIG5vdCBndWFyYW50ZWVkLlxuICAgIGxldCByZXNwb25zZUF1dGhlbnRpY2F0b3JEYXRhO1xuICAgIGlmICh0eXBlb2YgcmVzcG9uc2UuZ2V0QXV0aGVudGljYXRvckRhdGEgPT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgdHJ5IHtcbiAgICAgICAgICAgIHJlc3BvbnNlQXV0aGVudGljYXRvckRhdGEgPSBidWZmZXJUb0Jhc2U2NFVSTFN0cmluZyhyZXNwb25zZS5nZXRBdXRoZW50aWNhdG9yRGF0YSgpKTtcbiAgICAgICAgfVxuICAgICAgICBjYXRjaCAoZXJyb3IpIHtcbiAgICAgICAgICAgIHdhcm5PbkJyb2tlbkltcGxlbWVudGF0aW9uKCdnZXRBdXRoZW50aWNhdG9yRGF0YSgpJywgZXJyb3IpO1xuICAgICAgICB9XG4gICAgfVxuICAgIHJldHVybiB7XG4gICAgICAgIGlkLFxuICAgICAgICByYXdJZDogYnVmZmVyVG9CYXNlNjRVUkxTdHJpbmcocmF3SWQpLFxuICAgICAgICByZXNwb25zZToge1xuICAgICAgICAgICAgYXR0ZXN0YXRpb25PYmplY3Q6IGJ1ZmZlclRvQmFzZTY0VVJMU3RyaW5nKHJlc3BvbnNlLmF0dGVzdGF0aW9uT2JqZWN0KSxcbiAgICAgICAgICAgIGNsaWVudERhdGFKU09OOiBidWZmZXJUb0Jhc2U2NFVSTFN0cmluZyhyZXNwb25zZS5jbGllbnREYXRhSlNPTiksXG4gICAgICAgICAgICB0cmFuc3BvcnRzLFxuICAgICAgICAgICAgcHVibGljS2V5QWxnb3JpdGhtOiByZXNwb25zZVB1YmxpY0tleUFsZ29yaXRobSxcbiAgICAgICAgICAgIHB1YmxpY0tleTogcmVzcG9uc2VQdWJsaWNLZXksXG4gICAgICAgICAgICBhdXRoZW50aWNhdG9yRGF0YTogcmVzcG9uc2VBdXRoZW50aWNhdG9yRGF0YSxcbiAgICAgICAgfSxcbiAgICAgICAgdHlwZSxcbiAgICAgICAgY2xpZW50RXh0ZW5zaW9uUmVzdWx0czogY3JlZGVudGlhbC5nZXRDbGllbnRFeHRlbnNpb25SZXN1bHRzKCksXG4gICAgICAgIGF1dGhlbnRpY2F0b3JBdHRhY2htZW50OiB0b0F1dGhlbnRpY2F0b3JBdHRhY2htZW50KGNyZWRlbnRpYWwuYXV0aGVudGljYXRvckF0dGFjaG1lbnQpLFxuICAgIH07XG59XG4vKipcbiAqIFZpc2libHkgd2FybiB3aGVuIHdlIGRldGVjdCBhbiBpc3N1ZSByZWxhdGVkIHRvIGEgcGFzc2tleSBwcm92aWRlciBpbnRlcmNlcHRpbmcgV2ViQXV0aG4gQVBJXG4gKiBjYWxsc1xuICovXG5mdW5jdGlvbiB3YXJuT25Ccm9rZW5JbXBsZW1lbnRhdGlvbihtZXRob2ROYW1lLCBjYXVzZSkge1xuICAgIGNvbnNvbGUud2FybihgVGhlIGJyb3dzZXIgZXh0ZW5zaW9uIHRoYXQgaW50ZXJjZXB0ZWQgdGhpcyBXZWJBdXRobiBBUEkgY2FsbCBpbmNvcnJlY3RseSBpbXBsZW1lbnRlZCAke21ldGhvZE5hbWV9LiBZb3Ugc2hvdWxkIHJlcG9ydCB0aGlzIGVycm9yIHRvIHRoZW0uXFxuYCwgY2F1c2UpO1xufVxuIiwiZXhwb3J0IHt9O1xuIiwiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IDIwMTcgR29vZ2xlIExMQ1xuICogU1BEWC1MaWNlbnNlLUlkZW50aWZpZXI6IEJTRC0zLUNsYXVzZVxuICovXG5jb25zdCBERVZfTU9ERSA9IHRydWU7XG5jb25zdCBFTkFCTEVfRVhUUkFfU0VDVVJJVFlfSE9PS1MgPSB0cnVlO1xuY29uc3QgRU5BQkxFX1NIQURZRE9NX05PUEFUQ0ggPSB0cnVlO1xuY29uc3QgTk9ERV9NT0RFID0gZmFsc2U7XG4vLyBBbGxvd3MgbWluaWZpZXJzIHRvIHJlbmFtZSByZWZlcmVuY2VzIHRvIGdsb2JhbFRoaXNcbmNvbnN0IGdsb2JhbCA9IGdsb2JhbFRoaXM7XG4vKipcbiAqIFVzZWZ1bCBmb3IgdmlzdWFsaXppbmcgYW5kIGxvZ2dpbmcgaW5zaWdodHMgaW50byB3aGF0IHRoZSBMaXQgdGVtcGxhdGUgc3lzdGVtIGlzIGRvaW5nLlxuICpcbiAqIENvbXBpbGVkIG91dCBvZiBwcm9kIG1vZGUgYnVpbGRzLlxuICovXG5jb25zdCBkZWJ1Z0xvZ0V2ZW50ID0gREVWX01PREVcbiAgICA/IChldmVudCkgPT4ge1xuICAgICAgICBjb25zdCBzaG91bGRFbWl0ID0gZ2xvYmFsXG4gICAgICAgICAgICAuZW1pdExpdERlYnVnTG9nRXZlbnRzO1xuICAgICAgICBpZiAoIXNob3VsZEVtaXQpIHtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuICAgICAgICBnbG9iYWwuZGlzcGF0Y2hFdmVudChuZXcgQ3VzdG9tRXZlbnQoJ2xpdC1kZWJ1ZycsIHtcbiAgICAgICAgICAgIGRldGFpbDogZXZlbnQsXG4gICAgICAgIH0pKTtcbiAgICB9XG4gICAgOiB1bmRlZmluZWQ7XG4vLyBVc2VkIGZvciBjb25uZWN0aW5nIGJlZ2luUmVuZGVyIGFuZCBlbmRSZW5kZXIgZXZlbnRzIHdoZW4gdGhlcmUgYXJlIG5lc3RlZFxuLy8gcmVuZGVycyB3aGVuIGVycm9ycyBhcmUgdGhyb3duIHByZXZlbnRpbmcgYW4gZW5kUmVuZGVyIGV2ZW50IGZyb20gYmVpbmdcbi8vIGNhbGxlZC5cbmxldCBkZWJ1Z0xvZ1JlbmRlcklkID0gMDtcbmxldCBpc3N1ZVdhcm5pbmc7XG5pZiAoREVWX01PREUpIHtcbiAgICBnbG9iYWwubGl0SXNzdWVkV2FybmluZ3MgPz89IG5ldyBTZXQoKTtcbiAgICAvKipcbiAgICAgKiBJc3N1ZSBhIHdhcm5pbmcgaWYgd2UgaGF2ZW4ndCBhbHJlYWR5LCBiYXNlZCBlaXRoZXIgb24gYGNvZGVgIG9yIGB3YXJuaW5nYC5cbiAgICAgKiBXYXJuaW5ncyBhcmUgZGlzYWJsZWQgYXV0b21hdGljYWxseSBvbmx5IGJ5IGB3YXJuaW5nYDsgZGlzYWJsaW5nIHZpYSBgY29kZWBcbiAgICAgKiBjYW4gYmUgZG9uZSBieSB1c2Vycy5cbiAgICAgKi9cbiAgICBpc3N1ZVdhcm5pbmcgPSAoY29kZSwgd2FybmluZykgPT4ge1xuICAgICAgICB3YXJuaW5nICs9IGNvZGVcbiAgICAgICAgICAgID8gYCBTZWUgaHR0cHM6Ly9saXQuZGV2L21zZy8ke2NvZGV9IGZvciBtb3JlIGluZm9ybWF0aW9uLmBcbiAgICAgICAgICAgIDogJyc7XG4gICAgICAgIGlmICghZ2xvYmFsLmxpdElzc3VlZFdhcm5pbmdzLmhhcyh3YXJuaW5nKSAmJlxuICAgICAgICAgICAgIWdsb2JhbC5saXRJc3N1ZWRXYXJuaW5ncy5oYXMoY29kZSkpIHtcbiAgICAgICAgICAgIGNvbnNvbGUud2Fybih3YXJuaW5nKTtcbiAgICAgICAgICAgIGdsb2JhbC5saXRJc3N1ZWRXYXJuaW5ncy5hZGQod2FybmluZyk7XG4gICAgICAgIH1cbiAgICB9O1xuICAgIHF1ZXVlTWljcm90YXNrKCgpID0+IHtcbiAgICAgICAgaXNzdWVXYXJuaW5nKCdkZXYtbW9kZScsIGBMaXQgaXMgaW4gZGV2IG1vZGUuIE5vdCByZWNvbW1lbmRlZCBmb3IgcHJvZHVjdGlvbiFgKTtcbiAgICB9KTtcbn1cbmNvbnN0IHdyYXAgPSBFTkFCTEVfU0hBRFlET01fTk9QQVRDSCAmJlxuICAgIGdsb2JhbC5TaGFkeURPTT8uaW5Vc2UgJiZcbiAgICBnbG9iYWwuU2hhZHlET00/Lm5vUGF0Y2ggPT09IHRydWVcbiAgICA/IGdsb2JhbC5TaGFkeURPTS53cmFwXG4gICAgOiAobm9kZSkgPT4gbm9kZTtcbmNvbnN0IHRydXN0ZWRUeXBlcyA9IGdsb2JhbC50cnVzdGVkVHlwZXM7XG4vKipcbiAqIE91ciBUcnVzdGVkVHlwZVBvbGljeSBmb3IgSFRNTCB3aGljaCBpcyBkZWNsYXJlZCB1c2luZyB0aGUgaHRtbCB0ZW1wbGF0ZVxuICogdGFnIGZ1bmN0aW9uLlxuICpcbiAqIFRoYXQgSFRNTCBpcyBhIGRldmVsb3Blci1hdXRob3JlZCBjb25zdGFudCwgYW5kIGlzIHBhcnNlZCB3aXRoIGlubmVySFRNTFxuICogYmVmb3JlIGFueSB1bnRydXN0ZWQgZXhwcmVzc2lvbnMgaGF2ZSBiZWVuIG1peGVkIGluLiBUaGVyZWZvciBpdCBpc1xuICogY29uc2lkZXJlZCBzYWZlIGJ5IGNvbnN0cnVjdGlvbi5cbiAqL1xuY29uc3QgcG9saWN5ID0gdHJ1c3RlZFR5cGVzXG4gICAgPyB0cnVzdGVkVHlwZXMuY3JlYXRlUG9saWN5KCdsaXQtaHRtbCcsIHtcbiAgICAgICAgY3JlYXRlSFRNTDogKHMpID0+IHMsXG4gICAgfSlcbiAgICA6IHVuZGVmaW5lZDtcbmNvbnN0IGlkZW50aXR5RnVuY3Rpb24gPSAodmFsdWUpID0+IHZhbHVlO1xuY29uc3Qgbm9vcFNhbml0aXplciA9IChfbm9kZSwgX25hbWUsIF90eXBlKSA9PiBpZGVudGl0eUZ1bmN0aW9uO1xuLyoqIFNldHMgdGhlIGdsb2JhbCBzYW5pdGl6ZXIgZmFjdG9yeS4gKi9cbmNvbnN0IHNldFNhbml0aXplciA9IChuZXdTYW5pdGl6ZXIpID0+IHtcbiAgICBpZiAoIUVOQUJMRV9FWFRSQV9TRUNVUklUWV9IT09LUykge1xuICAgICAgICByZXR1cm47XG4gICAgfVxuICAgIGlmIChzYW5pdGl6ZXJGYWN0b3J5SW50ZXJuYWwgIT09IG5vb3BTYW5pdGl6ZXIpIHtcbiAgICAgICAgdGhyb3cgbmV3IEVycm9yKGBBdHRlbXB0ZWQgdG8gb3ZlcndyaXRlIGV4aXN0aW5nIGxpdC1odG1sIHNlY3VyaXR5IHBvbGljeS5gICtcbiAgICAgICAgICAgIGAgc2V0U2FuaXRpemVET01WYWx1ZUZhY3Rvcnkgc2hvdWxkIGJlIGNhbGxlZCBhdCBtb3N0IG9uY2UuYCk7XG4gICAgfVxuICAgIHNhbml0aXplckZhY3RvcnlJbnRlcm5hbCA9IG5ld1Nhbml0aXplcjtcbn07XG4vKipcbiAqIE9ubHkgdXNlZCBpbiBpbnRlcm5hbCB0ZXN0cywgbm90IGEgcGFydCBvZiB0aGUgcHVibGljIEFQSS5cbiAqL1xuY29uc3QgX3Rlc3RPbmx5Q2xlYXJTYW5pdGl6ZXJGYWN0b3J5RG9Ob3RDYWxsT3JFbHNlID0gKCkgPT4ge1xuICAgIHNhbml0aXplckZhY3RvcnlJbnRlcm5hbCA9IG5vb3BTYW5pdGl6ZXI7XG59O1xuY29uc3QgY3JlYXRlU2FuaXRpemVyID0gKG5vZGUsIG5hbWUsIHR5cGUpID0+IHtcbiAgICByZXR1cm4gc2FuaXRpemVyRmFjdG9yeUludGVybmFsKG5vZGUsIG5hbWUsIHR5cGUpO1xufTtcbi8vIEFkZGVkIHRvIGFuIGF0dHJpYnV0ZSBuYW1lIHRvIG1hcmsgdGhlIGF0dHJpYnV0ZSBhcyBib3VuZCBzbyB3ZSBjYW4gZmluZFxuLy8gaXQgZWFzaWx5LlxuY29uc3QgYm91bmRBdHRyaWJ1dGVTdWZmaXggPSAnJGxpdCQnO1xuLy8gVGhpcyBtYXJrZXIgaXMgdXNlZCBpbiBtYW55IHN5bnRhY3RpYyBwb3NpdGlvbnMgaW4gSFRNTCwgc28gaXQgbXVzdCBiZVxuLy8gYSB2YWxpZCBlbGVtZW50IG5hbWUgYW5kIGF0dHJpYnV0ZSBuYW1lLiBXZSBkb24ndCBzdXBwb3J0IGR5bmFtaWMgbmFtZXMgKHlldClcbi8vIGJ1dCB0aGlzIGF0IGxlYXN0IGVuc3VyZXMgdGhhdCB0aGUgcGFyc2UgdHJlZSBpcyBjbG9zZXIgdG8gdGhlIHRlbXBsYXRlXG4vLyBpbnRlbnRpb24uXG5jb25zdCBtYXJrZXIgPSBgbGl0JCR7TWF0aC5yYW5kb20oKS50b0ZpeGVkKDkpLnNsaWNlKDIpfSRgO1xuLy8gU3RyaW5nIHVzZWQgdG8gdGVsbCBpZiBhIGNvbW1lbnQgaXMgYSBtYXJrZXIgY29tbWVudFxuY29uc3QgbWFya2VyTWF0Y2ggPSAnPycgKyBtYXJrZXI7XG4vLyBUZXh0IHVzZWQgdG8gaW5zZXJ0IGEgY29tbWVudCBtYXJrZXIgbm9kZS4gV2UgdXNlIHByb2Nlc3NpbmcgaW5zdHJ1Y3Rpb25cbi8vIHN5bnRheCBiZWNhdXNlIGl0J3Mgc2xpZ2h0bHkgc21hbGxlciwgYnV0IHBhcnNlcyBhcyBhIGNvbW1lbnQgbm9kZS5cbmNvbnN0IG5vZGVNYXJrZXIgPSBgPCR7bWFya2VyTWF0Y2h9PmA7XG5jb25zdCBkID0gTk9ERV9NT0RFICYmIGdsb2JhbC5kb2N1bWVudCA9PT0gdW5kZWZpbmVkXG4gICAgPyB7XG4gICAgICAgIGNyZWF0ZVRyZWVXYWxrZXIoKSB7XG4gICAgICAgICAgICByZXR1cm4ge307XG4gICAgICAgIH0sXG4gICAgfVxuICAgIDogZG9jdW1lbnQ7XG4vLyBDcmVhdGVzIGEgZHluYW1pYyBtYXJrZXIuIFdlIG5ldmVyIGhhdmUgdG8gc2VhcmNoIGZvciB0aGVzZSBpbiB0aGUgRE9NLlxuY29uc3QgY3JlYXRlTWFya2VyID0gKCkgPT4gZC5jcmVhdGVDb21tZW50KCcnKTtcbmNvbnN0IGlzUHJpbWl0aXZlID0gKHZhbHVlKSA9PiB2YWx1ZSA9PT0gbnVsbCB8fCAodHlwZW9mIHZhbHVlICE9ICdvYmplY3QnICYmIHR5cGVvZiB2YWx1ZSAhPSAnZnVuY3Rpb24nKTtcbmNvbnN0IGlzQXJyYXkgPSBBcnJheS5pc0FycmF5O1xuY29uc3QgaXNJdGVyYWJsZSA9ICh2YWx1ZSkgPT4gaXNBcnJheSh2YWx1ZSkgfHxcbiAgICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgQHR5cGVzY3JpcHQtZXNsaW50L25vLWV4cGxpY2l0LWFueVxuICAgIHR5cGVvZiB2YWx1ZT8uW1N5bWJvbC5pdGVyYXRvcl0gPT09ICdmdW5jdGlvbic7XG5jb25zdCBTUEFDRV9DSEFSID0gYFsgXFx0XFxuXFxmXFxyXWA7XG5jb25zdCBBVFRSX1ZBTFVFX0NIQVIgPSBgW14gXFx0XFxuXFxmXFxyXCInXFxgPD49XWA7XG5jb25zdCBOQU1FX0NIQVIgPSBgW15cXFxcc1wiJz49L11gO1xuLy8gVGhlc2UgcmVnZXhlcyByZXByZXNlbnQgdGhlIGZpdmUgcGFyc2luZyBzdGF0ZXMgdGhhdCB3ZSBjYXJlIGFib3V0IGluIHRoZVxuLy8gVGVtcGxhdGUncyBIVE1MIHNjYW5uZXIuIFRoZXkgbWF0Y2ggdGhlICplbmQqIG9mIHRoZSBzdGF0ZSB0aGV5J3JlIG5hbWVkXG4vLyBhZnRlci5cbi8vIERlcGVuZGluZyBvbiB0aGUgbWF0Y2gsIHdlIHRyYW5zaXRpb24gdG8gYSBuZXcgc3RhdGUuIElmIHRoZXJlJ3Mgbm8gbWF0Y2gsXG4vLyB3ZSBzdGF5IGluIHRoZSBzYW1lIHN0YXRlLlxuLy8gTm90ZSB0aGF0IHRoZSByZWdleGVzIGFyZSBzdGF0ZWZ1bC4gV2UgdXRpbGl6ZSBsYXN0SW5kZXggYW5kIHN5bmMgaXRcbi8vIGFjcm9zcyB0aGUgbXVsdGlwbGUgcmVnZXhlcyB1c2VkLiBJbiBhZGRpdGlvbiB0byB0aGUgZml2ZSByZWdleGVzIGJlbG93XG4vLyB3ZSBhbHNvIGR5bmFtaWNhbGx5IGNyZWF0ZSBhIHJlZ2V4IHRvIGZpbmQgdGhlIG1hdGNoaW5nIGVuZCB0YWdzIGZvciByYXdcbi8vIHRleHQgZWxlbWVudHMuXG4vKipcbiAqIEVuZCBvZiB0ZXh0IGlzOiBgPGAgZm9sbG93ZWQgYnk6XG4gKiAgIChjb21tZW50IHN0YXJ0KSBvciAodGFnKSBvciAoZHluYW1pYyB0YWcgYmluZGluZylcbiAqL1xuY29uc3QgdGV4dEVuZFJlZ2V4ID0gLzwoPzooIS0tfFxcL1teYS16QS1aXSl8KFxcLz9bYS16QS1aXVtePlxcc10qKXwoXFwvPyQpKS9nO1xuY29uc3QgQ09NTUVOVF9TVEFSVCA9IDE7XG5jb25zdCBUQUdfTkFNRSA9IDI7XG5jb25zdCBEWU5BTUlDX1RBR19OQU1FID0gMztcbmNvbnN0IGNvbW1lbnRFbmRSZWdleCA9IC8tLT4vZztcbi8qKlxuICogQ29tbWVudHMgbm90IHN0YXJ0ZWQgd2l0aCA8IS0tLCBsaWtlIDwveywgY2FuIGJlIGVuZGVkIGJ5IGEgc2luZ2xlIGA+YFxuICovXG5jb25zdCBjb21tZW50MkVuZFJlZ2V4ID0gLz4vZztcbi8qKlxuICogVGhlIHRhZ0VuZCByZWdleCBtYXRjaGVzIHRoZSBlbmQgb2YgdGhlIFwiaW5zaWRlIGFuIG9wZW5pbmdcIiB0YWcgc3ludGF4XG4gKiBwb3NpdGlvbi4gSXQgZWl0aGVyIG1hdGNoZXMgYSBgPmAsIGFuIGF0dHJpYnV0ZS1saWtlIHNlcXVlbmNlLCBvciB0aGUgZW5kXG4gKiBvZiB0aGUgc3RyaW5nIGFmdGVyIGEgc3BhY2UgKGF0dHJpYnV0ZS1uYW1lIHBvc2l0aW9uIGVuZGluZykuXG4gKlxuICogU2VlIGF0dHJpYnV0ZXMgaW4gdGhlIEhUTUwgc3BlYzpcbiAqIGh0dHBzOi8vd3d3LnczLm9yZy9UUi9odG1sNS9zeW50YXguaHRtbCNlbGVtZW50cy1hdHRyaWJ1dGVzXG4gKlxuICogXCIgXFx0XFxuXFxmXFxyXCIgYXJlIEhUTUwgc3BhY2UgY2hhcmFjdGVyczpcbiAqIGh0dHBzOi8vaW5mcmEuc3BlYy53aGF0d2cub3JnLyNhc2NpaS13aGl0ZXNwYWNlXG4gKlxuICogU28gYW4gYXR0cmlidXRlIGlzOlxuICogICogVGhlIG5hbWU6IGFueSBjaGFyYWN0ZXIgZXhjZXB0IGEgd2hpdGVzcGFjZSBjaGFyYWN0ZXIsIChcIiksICgnKSwgXCI+XCIsXG4gKiAgICBcIj1cIiwgb3IgXCIvXCIuIE5vdGU6IHRoaXMgaXMgZGlmZmVyZW50IGZyb20gdGhlIEhUTUwgc3BlYyB3aGljaCBhbHNvIGV4Y2x1ZGVzIGNvbnRyb2wgY2hhcmFjdGVycy5cbiAqICAqIEZvbGxvd2VkIGJ5IHplcm8gb3IgbW9yZSBzcGFjZSBjaGFyYWN0ZXJzXG4gKiAgKiBGb2xsb3dlZCBieSBcIj1cIlxuICogICogRm9sbG93ZWQgYnkgemVybyBvciBtb3JlIHNwYWNlIGNoYXJhY3RlcnNcbiAqICAqIEZvbGxvd2VkIGJ5OlxuICogICAgKiBBbnkgY2hhcmFjdGVyIGV4Y2VwdCBzcGFjZSwgKCcpLCAoXCIpLCBcIjxcIiwgXCI+XCIsIFwiPVwiLCAoYCksIG9yXG4gKiAgICAqIChcIikgdGhlbiBhbnkgbm9uLShcIiksIG9yXG4gKiAgICAqICgnKSB0aGVuIGFueSBub24tKCcpXG4gKi9cbmNvbnN0IHRhZ0VuZFJlZ2V4ID0gbmV3IFJlZ0V4cChgPnwke1NQQUNFX0NIQVJ9KD86KCR7TkFNRV9DSEFSfSspKCR7U1BBQ0VfQ0hBUn0qPSR7U1BBQ0VfQ0hBUn0qKD86JHtBVFRSX1ZBTFVFX0NIQVJ9fChcInwnKXwpKXwkKWAsICdnJyk7XG5jb25zdCBFTlRJUkVfTUFUQ0ggPSAwO1xuY29uc3QgQVRUUklCVVRFX05BTUUgPSAxO1xuY29uc3QgU1BBQ0VTX0FORF9FUVVBTFMgPSAyO1xuY29uc3QgUVVPVEVfQ0hBUiA9IDM7XG5jb25zdCBzaW5nbGVRdW90ZUF0dHJFbmRSZWdleCA9IC8nL2c7XG5jb25zdCBkb3VibGVRdW90ZUF0dHJFbmRSZWdleCA9IC9cIi9nO1xuLyoqXG4gKiBNYXRjaGVzIHRoZSByYXcgdGV4dCBlbGVtZW50cy5cbiAqXG4gKiBDb21tZW50cyBhcmUgbm90IHBhcnNlZCB3aXRoaW4gcmF3IHRleHQgZWxlbWVudHMsIHNvIHdlIG5lZWQgdG8gc2VhcmNoIHRoZWlyXG4gKiB0ZXh0IGNvbnRlbnQgZm9yIG1hcmtlciBzdHJpbmdzLlxuICovXG5jb25zdCByYXdUZXh0RWxlbWVudCA9IC9eKD86c2NyaXB0fHN0eWxlfHRleHRhcmVhfHRpdGxlKSQvaTtcbi8qKiBUZW1wbGF0ZVJlc3VsdCB0eXBlcyAqL1xuY29uc3QgSFRNTF9SRVNVTFQgPSAxO1xuY29uc3QgU1ZHX1JFU1VMVCA9IDI7XG5jb25zdCBNQVRITUxfUkVTVUxUID0gMztcbi8vIFRlbXBsYXRlUGFydCB0eXBlc1xuLy8gSU1QT1JUQU5UOiB0aGVzZSBtdXN0IG1hdGNoIHRoZSB2YWx1ZXMgaW4gUGFydFR5cGVcbmNvbnN0IEFUVFJJQlVURV9QQVJUID0gMTtcbmNvbnN0IENISUxEX1BBUlQgPSAyO1xuY29uc3QgUFJPUEVSVFlfUEFSVCA9IDM7XG5jb25zdCBCT09MRUFOX0FUVFJJQlVURV9QQVJUID0gNDtcbmNvbnN0IEVWRU5UX1BBUlQgPSA1O1xuY29uc3QgRUxFTUVOVF9QQVJUID0gNjtcbmNvbnN0IENPTU1FTlRfUEFSVCA9IDc7XG4vKipcbiAqIEdlbmVyYXRlcyBhIHRlbXBsYXRlIGxpdGVyYWwgdGFnIGZ1bmN0aW9uIHRoYXQgcmV0dXJucyBhIFRlbXBsYXRlUmVzdWx0IHdpdGhcbiAqIHRoZSBnaXZlbiByZXN1bHQgdHlwZS5cbiAqL1xuY29uc3QgdGFnID0gKHR5cGUpID0+IChzdHJpbmdzLCAuLi52YWx1ZXMpID0+IHtcbiAgICAvLyBXYXJuIGFnYWluc3QgdGVtcGxhdGVzIG9jdGFsIGVzY2FwZSBzZXF1ZW5jZXNcbiAgICAvLyBXZSBkbyB0aGlzIGhlcmUgcmF0aGVyIHRoYW4gaW4gcmVuZGVyIHNvIHRoYXQgdGhlIHdhcm5pbmcgaXMgY2xvc2VyIHRvIHRoZVxuICAgIC8vIHRlbXBsYXRlIGRlZmluaXRpb24uXG4gICAgaWYgKERFVl9NT0RFICYmIHN0cmluZ3Muc29tZSgocykgPT4gcyA9PT0gdW5kZWZpbmVkKSkge1xuICAgICAgICBjb25zb2xlLndhcm4oJ1NvbWUgdGVtcGxhdGUgc3RyaW5ncyBhcmUgdW5kZWZpbmVkLlxcbicgK1xuICAgICAgICAgICAgJ1RoaXMgaXMgcHJvYmFibHkgY2F1c2VkIGJ5IGlsbGVnYWwgb2N0YWwgZXNjYXBlIHNlcXVlbmNlcy4nKTtcbiAgICB9XG4gICAgaWYgKERFVl9NT0RFKSB7XG4gICAgICAgIC8vIEltcG9ydCBzdGF0aWMtaHRtbC5qcyByZXN1bHRzIGluIGEgY2lyY3VsYXIgZGVwZW5kZW5jeSB3aGljaCBnMyBkb2Vzbid0XG4gICAgICAgIC8vIGhhbmRsZS4gSW5zdGVhZCB3ZSBrbm93IHRoYXQgc3RhdGljIHZhbHVlcyBtdXN0IGhhdmUgdGhlIGZpZWxkXG4gICAgICAgIC8vIGBfJGxpdFN0YXRpYyRgLlxuICAgICAgICBpZiAodmFsdWVzLnNvbWUoKHZhbCkgPT4gdmFsPy5bJ18kbGl0U3RhdGljJCddKSkge1xuICAgICAgICAgICAgaXNzdWVXYXJuaW5nKCcnLCBgU3RhdGljIHZhbHVlcyAnbGl0ZXJhbCcgb3IgJ3Vuc2FmZVN0YXRpYycgY2Fubm90IGJlIHVzZWQgYXMgdmFsdWVzIHRvIG5vbi1zdGF0aWMgdGVtcGxhdGVzLlxcbmAgK1xuICAgICAgICAgICAgICAgIGBQbGVhc2UgdXNlIHRoZSBzdGF0aWMgJ2h0bWwnIHRhZyBmdW5jdGlvbi4gU2VlIGh0dHBzOi8vbGl0LmRldi9kb2NzL3RlbXBsYXRlcy9leHByZXNzaW9ucy8jc3RhdGljLWV4cHJlc3Npb25zYCk7XG4gICAgICAgIH1cbiAgICB9XG4gICAgcmV0dXJuIHtcbiAgICAgICAgLy8gVGhpcyBwcm9wZXJ0eSBuZWVkcyB0byByZW1haW4gdW5taW5pZmllZC5cbiAgICAgICAgWydfJGxpdFR5cGUkJ106IHR5cGUsXG4gICAgICAgIHN0cmluZ3MsXG4gICAgICAgIHZhbHVlcyxcbiAgICB9O1xufTtcbi8qKlxuICogSW50ZXJwcmV0cyBhIHRlbXBsYXRlIGxpdGVyYWwgYXMgYW4gSFRNTCB0ZW1wbGF0ZSB0aGF0IGNhbiBlZmZpY2llbnRseVxuICogcmVuZGVyIHRvIGFuZCB1cGRhdGUgYSBjb250YWluZXIuXG4gKlxuICogYGBgdHNcbiAqIGNvbnN0IGhlYWRlciA9ICh0aXRsZTogc3RyaW5nKSA9PiBodG1sYDxoMT4ke3RpdGxlfTwvaDE+YDtcbiAqIGBgYFxuICpcbiAqIFRoZSBgaHRtbGAgdGFnIHJldHVybnMgYSBkZXNjcmlwdGlvbiBvZiB0aGUgRE9NIHRvIHJlbmRlciBhcyBhIHZhbHVlLiBJdCBpc1xuICogbGF6eSwgbWVhbmluZyBubyB3b3JrIGlzIGRvbmUgdW50aWwgdGhlIHRlbXBsYXRlIGlzIHJlbmRlcmVkLiBXaGVuIHJlbmRlcmluZyxcbiAqIGlmIGEgdGVtcGxhdGUgY29tZXMgZnJvbSB0aGUgc2FtZSBleHByZXNzaW9uIGFzIGEgcHJldmlvdXNseSByZW5kZXJlZCByZXN1bHQsXG4gKiBpdCdzIGVmZmljaWVudGx5IHVwZGF0ZWQgaW5zdGVhZCBvZiByZXBsYWNlZC5cbiAqL1xuZXhwb3J0IGNvbnN0IGh0bWwgPSB0YWcoSFRNTF9SRVNVTFQpO1xuLyoqXG4gKiBJbnRlcnByZXRzIGEgdGVtcGxhdGUgbGl0ZXJhbCBhcyBhbiBTVkcgZnJhZ21lbnQgdGhhdCBjYW4gZWZmaWNpZW50bHkgcmVuZGVyXG4gKiB0byBhbmQgdXBkYXRlIGEgY29udGFpbmVyLlxuICpcbiAqIGBgYHRzXG4gKiBjb25zdCByZWN0ID0gc3ZnYDxyZWN0IHdpZHRoPVwiMTBcIiBoZWlnaHQ9XCIxMFwiPjwvcmVjdD5gO1xuICpcbiAqIGNvbnN0IG15SW1hZ2UgPSBodG1sYFxuICogICA8c3ZnIHZpZXdCb3g9XCIwIDAgMTAgMTBcIiB4bWxucz1cImh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnXCI+XG4gKiAgICAgJHtyZWN0fVxuICogICA8L3N2Zz5gO1xuICogYGBgXG4gKlxuICogVGhlIGBzdmdgICp0YWcgZnVuY3Rpb24qIHNob3VsZCBvbmx5IGJlIHVzZWQgZm9yIFNWRyBmcmFnbWVudHMsIG9yIGVsZW1lbnRzXG4gKiB0aGF0IHdvdWxkIGJlIGNvbnRhaW5lZCAqKmluc2lkZSoqIGFuIGA8c3ZnPmAgSFRNTCBlbGVtZW50LiBBIGNvbW1vbiBlcnJvciBpc1xuICogcGxhY2luZyBhbiBgPHN2Zz5gICplbGVtZW50KiBpbiBhIHRlbXBsYXRlIHRhZ2dlZCB3aXRoIHRoZSBgc3ZnYCB0YWdcbiAqIGZ1bmN0aW9uLiBUaGUgYDxzdmc+YCBlbGVtZW50IGlzIGFuIEhUTUwgZWxlbWVudCBhbmQgc2hvdWxkIGJlIHVzZWQgd2l0aGluIGFcbiAqIHRlbXBsYXRlIHRhZ2dlZCB3aXRoIHRoZSB7QGxpbmtjb2RlIGh0bWx9IHRhZyBmdW5jdGlvbi5cbiAqXG4gKiBJbiBMaXRFbGVtZW50IHVzYWdlLCBpdCdzIGludmFsaWQgdG8gcmV0dXJuIGFuIFNWRyBmcmFnbWVudCBmcm9tIHRoZVxuICogYHJlbmRlcigpYCBtZXRob2QsIGFzIHRoZSBTVkcgZnJhZ21lbnQgd2lsbCBiZSBjb250YWluZWQgd2l0aGluIHRoZSBlbGVtZW50J3NcbiAqIHNoYWRvdyByb290IGFuZCB0aHVzIG5vdCBiZSBwcm9wZXJseSBjb250YWluZWQgd2l0aGluIGFuIGA8c3ZnPmAgSFRNTFxuICogZWxlbWVudC5cbiAqL1xuZXhwb3J0IGNvbnN0IHN2ZyA9IHRhZyhTVkdfUkVTVUxUKTtcbi8qKlxuICogSW50ZXJwcmV0cyBhIHRlbXBsYXRlIGxpdGVyYWwgYXMgTWF0aE1MIGZyYWdtZW50IHRoYXQgY2FuIGVmZmljaWVudGx5IHJlbmRlclxuICogdG8gYW5kIHVwZGF0ZSBhIGNvbnRhaW5lci5cbiAqXG4gKiBgYGB0c1xuICogY29uc3QgbnVtID0gbWF0aG1sYDxtbj4xPC9tbj5gO1xuICpcbiAqIGNvbnN0IGVxID0gaHRtbGBcbiAqICAgPG1hdGg+XG4gKiAgICAgJHtudW19XG4gKiAgIDwvbWF0aD5gO1xuICogYGBgXG4gKlxuICogVGhlIGBtYXRobWxgICp0YWcgZnVuY3Rpb24qIHNob3VsZCBvbmx5IGJlIHVzZWQgZm9yIE1hdGhNTCBmcmFnbWVudHMsIG9yXG4gKiBlbGVtZW50cyB0aGF0IHdvdWxkIGJlIGNvbnRhaW5lZCAqKmluc2lkZSoqIGEgYDxtYXRoPmAgSFRNTCBlbGVtZW50LiBBIGNvbW1vblxuICogZXJyb3IgaXMgcGxhY2luZyBhIGA8bWF0aD5gICplbGVtZW50KiBpbiBhIHRlbXBsYXRlIHRhZ2dlZCB3aXRoIHRoZSBgbWF0aG1sYFxuICogdGFnIGZ1bmN0aW9uLiBUaGUgYDxtYXRoPmAgZWxlbWVudCBpcyBhbiBIVE1MIGVsZW1lbnQgYW5kIHNob3VsZCBiZSB1c2VkXG4gKiB3aXRoaW4gYSB0ZW1wbGF0ZSB0YWdnZWQgd2l0aCB0aGUge0BsaW5rY29kZSBodG1sfSB0YWcgZnVuY3Rpb24uXG4gKlxuICogSW4gTGl0RWxlbWVudCB1c2FnZSwgaXQncyBpbnZhbGlkIHRvIHJldHVybiBhbiBNYXRoTUwgZnJhZ21lbnQgZnJvbSB0aGVcbiAqIGByZW5kZXIoKWAgbWV0aG9kLCBhcyB0aGUgTWF0aE1MIGZyYWdtZW50IHdpbGwgYmUgY29udGFpbmVkIHdpdGhpbiB0aGVcbiAqIGVsZW1lbnQncyBzaGFkb3cgcm9vdCBhbmQgdGh1cyBub3QgYmUgcHJvcGVybHkgY29udGFpbmVkIHdpdGhpbiBhIGA8bWF0aD5gXG4gKiBIVE1MIGVsZW1lbnQuXG4gKi9cbmV4cG9ydCBjb25zdCBtYXRobWwgPSB0YWcoTUFUSE1MX1JFU1VMVCk7XG4vKipcbiAqIEEgc2VudGluZWwgdmFsdWUgdGhhdCBzaWduYWxzIHRoYXQgYSB2YWx1ZSB3YXMgaGFuZGxlZCBieSBhIGRpcmVjdGl2ZSBhbmRcbiAqIHNob3VsZCBub3QgYmUgd3JpdHRlbiB0byB0aGUgRE9NLlxuICovXG5leHBvcnQgY29uc3Qgbm9DaGFuZ2UgPSBTeW1ib2wuZm9yKCdsaXQtbm9DaGFuZ2UnKTtcbi8qKlxuICogQSBzZW50aW5lbCB2YWx1ZSB0aGF0IHNpZ25hbHMgYSBDaGlsZFBhcnQgdG8gZnVsbHkgY2xlYXIgaXRzIGNvbnRlbnQuXG4gKlxuICogYGBgdHNcbiAqIGNvbnN0IGJ1dHRvbiA9IGh0bWxgJHtcbiAqICB1c2VyLmlzQWRtaW5cbiAqICAgID8gaHRtbGA8YnV0dG9uPkRFTEVURTwvYnV0dG9uPmBcbiAqICAgIDogbm90aGluZ1xuICogfWA7XG4gKiBgYGBcbiAqXG4gKiBQcmVmZXIgdXNpbmcgYG5vdGhpbmdgIG92ZXIgb3RoZXIgZmFsc3kgdmFsdWVzIGFzIGl0IHByb3ZpZGVzIGEgY29uc2lzdGVudFxuICogYmVoYXZpb3IgYmV0d2VlbiB2YXJpb3VzIGV4cHJlc3Npb24gYmluZGluZyBjb250ZXh0cy5cbiAqXG4gKiBJbiBjaGlsZCBleHByZXNzaW9ucywgYHVuZGVmaW5lZGAsIGBudWxsYCwgYCcnYCwgYW5kIGBub3RoaW5nYCBhbGwgYmVoYXZlIHRoZVxuICogc2FtZSBhbmQgcmVuZGVyIG5vIG5vZGVzLiBJbiBhdHRyaWJ1dGUgZXhwcmVzc2lvbnMsIGBub3RoaW5nYCBfcmVtb3Zlc18gdGhlXG4gKiBhdHRyaWJ1dGUsIHdoaWxlIGB1bmRlZmluZWRgIGFuZCBgbnVsbGAgd2lsbCByZW5kZXIgYW4gZW1wdHkgc3RyaW5nLiBJblxuICogcHJvcGVydHkgZXhwcmVzc2lvbnMgYG5vdGhpbmdgIGJlY29tZXMgYHVuZGVmaW5lZGAuXG4gKi9cbmV4cG9ydCBjb25zdCBub3RoaW5nID0gU3ltYm9sLmZvcignbGl0LW5vdGhpbmcnKTtcbi8qKlxuICogVGhlIGNhY2hlIG9mIHByZXBhcmVkIHRlbXBsYXRlcywga2V5ZWQgYnkgdGhlIHRhZ2dlZCBUZW1wbGF0ZVN0cmluZ3NBcnJheVxuICogYW5kIF9ub3RfIGFjY291bnRpbmcgZm9yIHRoZSBzcGVjaWZpYyB0ZW1wbGF0ZSB0YWcgdXNlZC4gVGhpcyBtZWFucyB0aGF0XG4gKiB0ZW1wbGF0ZSB0YWdzIGNhbm5vdCBiZSBkeW5hbWljIC0gdGhleSBtdXN0IHN0YXRpY2FsbHkgYmUgb25lIG9mIGh0bWwsIHN2ZyxcbiAqIG9yIGF0dHIuIFRoaXMgcmVzdHJpY3Rpb24gc2ltcGxpZmllcyB0aGUgY2FjaGUgbG9va3VwLCB3aGljaCBpcyBvbiB0aGUgaG90XG4gKiBwYXRoIGZvciByZW5kZXJpbmcuXG4gKi9cbmNvbnN0IHRlbXBsYXRlQ2FjaGUgPSBuZXcgV2Vha01hcCgpO1xuY29uc3Qgd2Fsa2VyID0gZC5jcmVhdGVUcmVlV2Fsa2VyKGQsIDEyOSAvKiBOb2RlRmlsdGVyLlNIT1dfe0VMRU1FTlR8Q09NTUVOVH0gKi8pO1xubGV0IHNhbml0aXplckZhY3RvcnlJbnRlcm5hbCA9IG5vb3BTYW5pdGl6ZXI7XG5mdW5jdGlvbiB0cnVzdEZyb21UZW1wbGF0ZVN0cmluZyh0c2EsIHN0cmluZ0Zyb21UU0EpIHtcbiAgICAvLyBBIHNlY3VyaXR5IGNoZWNrIHRvIHByZXZlbnQgc3Bvb2Zpbmcgb2YgTGl0IHRlbXBsYXRlIHJlc3VsdHMuXG4gICAgLy8gSW4gdGhlIGZ1dHVyZSwgd2UgbWF5IGJlIGFibGUgdG8gcmVwbGFjZSB0aGlzIHdpdGggQXJyYXkuaXNUZW1wbGF0ZU9iamVjdCxcbiAgICAvLyB0aG91Z2ggd2UgbWlnaHQgbmVlZCB0byBtYWtlIHRoYXQgY2hlY2sgaW5zaWRlIG9mIHRoZSBodG1sIGFuZCBzdmdcbiAgICAvLyBmdW5jdGlvbnMsIGJlY2F1c2UgcHJlY29tcGlsZWQgdGVtcGxhdGVzIGRvbid0IGNvbWUgaW4gYXNcbiAgICAvLyBUZW1wbGF0ZVN0cmluZ0FycmF5IG9iamVjdHMuXG4gICAgaWYgKCFpc0FycmF5KHRzYSkgfHwgIXRzYS5oYXNPd25Qcm9wZXJ0eSgncmF3JykpIHtcbiAgICAgICAgbGV0IG1lc3NhZ2UgPSAnaW52YWxpZCB0ZW1wbGF0ZSBzdHJpbmdzIGFycmF5JztcbiAgICAgICAgaWYgKERFVl9NT0RFKSB7XG4gICAgICAgICAgICBtZXNzYWdlID0gYFxuICAgICAgICAgIEludGVybmFsIEVycm9yOiBleHBlY3RlZCB0ZW1wbGF0ZSBzdHJpbmdzIHRvIGJlIGFuIGFycmF5XG4gICAgICAgICAgd2l0aCBhICdyYXcnIGZpZWxkLiBGYWtpbmcgYSB0ZW1wbGF0ZSBzdHJpbmdzIGFycmF5IGJ5XG4gICAgICAgICAgY2FsbGluZyBodG1sIG9yIHN2ZyBsaWtlIGFuIG9yZGluYXJ5IGZ1bmN0aW9uIGlzIGVmZmVjdGl2ZWx5XG4gICAgICAgICAgdGhlIHNhbWUgYXMgY2FsbGluZyB1bnNhZmVIdG1sIGFuZCBjYW4gbGVhZCB0byBtYWpvciBzZWN1cml0eVxuICAgICAgICAgIGlzc3VlcywgZS5nLiBvcGVuaW5nIHlvdXIgY29kZSB1cCB0byBYU1MgYXR0YWNrcy5cbiAgICAgICAgICBJZiB5b3UncmUgdXNpbmcgdGhlIGh0bWwgb3Igc3ZnIHRhZ2dlZCB0ZW1wbGF0ZSBmdW5jdGlvbnMgbm9ybWFsbHlcbiAgICAgICAgICBhbmQgc3RpbGwgc2VlaW5nIHRoaXMgZXJyb3IsIHBsZWFzZSBmaWxlIGEgYnVnIGF0XG4gICAgICAgICAgaHR0cHM6Ly9naXRodWIuY29tL2xpdC9saXQvaXNzdWVzL25ldz90ZW1wbGF0ZT1idWdfcmVwb3J0Lm1kXG4gICAgICAgICAgYW5kIGluY2x1ZGUgaW5mb3JtYXRpb24gYWJvdXQgeW91ciBidWlsZCB0b29saW5nLCBpZiBhbnkuXG4gICAgICAgIGBcbiAgICAgICAgICAgICAgICAudHJpbSgpXG4gICAgICAgICAgICAgICAgLnJlcGxhY2UoL1xcbiAqL2csICdcXG4nKTtcbiAgICAgICAgfVxuICAgICAgICB0aHJvdyBuZXcgRXJyb3IobWVzc2FnZSk7XG4gICAgfVxuICAgIHJldHVybiBwb2xpY3kgIT09IHVuZGVmaW5lZFxuICAgICAgICA/IHBvbGljeS5jcmVhdGVIVE1MKHN0cmluZ0Zyb21UU0EpXG4gICAgICAgIDogc3RyaW5nRnJvbVRTQTtcbn1cbi8qKlxuICogUmV0dXJucyBhbiBIVE1MIHN0cmluZyBmb3IgdGhlIGdpdmVuIFRlbXBsYXRlU3RyaW5nc0FycmF5IGFuZCByZXN1bHQgdHlwZVxuICogKEhUTUwgb3IgU1ZHKSwgYWxvbmcgd2l0aCB0aGUgY2FzZS1zZW5zaXRpdmUgYm91bmQgYXR0cmlidXRlIG5hbWVzIGluXG4gKiB0ZW1wbGF0ZSBvcmRlci4gVGhlIEhUTUwgY29udGFpbnMgY29tbWVudCBtYXJrZXJzIGRlbm90aW5nIHRoZSBgQ2hpbGRQYXJ0YHNcbiAqIGFuZCBzdWZmaXhlcyBvbiBib3VuZCBhdHRyaWJ1dGVzIGRlbm90aW5nIHRoZSBgQXR0cmlidXRlUGFydHNgLlxuICpcbiAqIEBwYXJhbSBzdHJpbmdzIHRlbXBsYXRlIHN0cmluZ3MgYXJyYXlcbiAqIEBwYXJhbSB0eXBlIEhUTUwgb3IgU1ZHXG4gKiBAcmV0dXJuIEFycmF5IGNvbnRhaW5pbmcgYFtodG1sLCBhdHRyTmFtZXNdYCAoYXJyYXkgcmV0dXJuZWQgZm9yIHRlcnNlbmVzcyxcbiAqICAgICB0byBhdm9pZCBvYmplY3QgZmllbGRzIHNpbmNlIHRoaXMgY29kZSBpcyBzaGFyZWQgd2l0aCBub24tbWluaWZpZWQgU1NSXG4gKiAgICAgY29kZSlcbiAqL1xuY29uc3QgZ2V0VGVtcGxhdGVIdG1sID0gKHN0cmluZ3MsIHR5cGUpID0+IHtcbiAgICAvLyBJbnNlcnQgbWFrZXJzIGludG8gdGhlIHRlbXBsYXRlIEhUTUwgdG8gcmVwcmVzZW50IHRoZSBwb3NpdGlvbiBvZlxuICAgIC8vIGJpbmRpbmdzLiBUaGUgZm9sbG93aW5nIGNvZGUgc2NhbnMgdGhlIHRlbXBsYXRlIHN0cmluZ3MgdG8gZGV0ZXJtaW5lIHRoZVxuICAgIC8vIHN5bnRhY3RpYyBwb3NpdGlvbiBvZiB0aGUgYmluZGluZ3MuIFRoZXkgY2FuIGJlIGluIHRleHQgcG9zaXRpb24sIHdoZXJlXG4gICAgLy8gd2UgaW5zZXJ0IGFuIEhUTUwgY29tbWVudCwgYXR0cmlidXRlIHZhbHVlIHBvc2l0aW9uLCB3aGVyZSB3ZSBpbnNlcnQgYVxuICAgIC8vIHNlbnRpbmVsIHN0cmluZyBhbmQgcmUtd3JpdGUgdGhlIGF0dHJpYnV0ZSBuYW1lLCBvciBpbnNpZGUgYSB0YWcgd2hlcmVcbiAgICAvLyB3ZSBpbnNlcnQgdGhlIHNlbnRpbmVsIHN0cmluZy5cbiAgICBjb25zdCBsID0gc3RyaW5ncy5sZW5ndGggLSAxO1xuICAgIC8vIFN0b3JlcyB0aGUgY2FzZS1zZW5zaXRpdmUgYm91bmQgYXR0cmlidXRlIG5hbWVzIGluIHRoZSBvcmRlciBvZiB0aGVpclxuICAgIC8vIHBhcnRzLiBFbGVtZW50UGFydHMgYXJlIGFsc28gcmVmbGVjdGVkIGluIHRoaXMgYXJyYXkgYXMgdW5kZWZpbmVkXG4gICAgLy8gcmF0aGVyIHRoYW4gYSBzdHJpbmcsIHRvIGRpc2FtYmlndWF0ZSBmcm9tIGF0dHJpYnV0ZSBiaW5kaW5ncy5cbiAgICBjb25zdCBhdHRyTmFtZXMgPSBbXTtcbiAgICBsZXQgaHRtbCA9IHR5cGUgPT09IFNWR19SRVNVTFQgPyAnPHN2Zz4nIDogdHlwZSA9PT0gTUFUSE1MX1JFU1VMVCA/ICc8bWF0aD4nIDogJyc7XG4gICAgLy8gV2hlbiB3ZSdyZSBpbnNpZGUgYSByYXcgdGV4dCB0YWcgKG5vdCBpdCdzIHRleHQgY29udGVudCksIHRoZSByZWdleFxuICAgIC8vIHdpbGwgc3RpbGwgYmUgdGFnUmVnZXggc28gd2UgY2FuIGZpbmQgYXR0cmlidXRlcywgYnV0IHdpbGwgc3dpdGNoIHRvXG4gICAgLy8gdGhpcyByZWdleCB3aGVuIHRoZSB0YWcgZW5kcy5cbiAgICBsZXQgcmF3VGV4dEVuZFJlZ2V4O1xuICAgIC8vIFRoZSBjdXJyZW50IHBhcnNpbmcgc3RhdGUsIHJlcHJlc2VudGVkIGFzIGEgcmVmZXJlbmNlIHRvIG9uZSBvZiB0aGVcbiAgICAvLyByZWdleGVzXG4gICAgbGV0IHJlZ2V4ID0gdGV4dEVuZFJlZ2V4O1xuICAgIGZvciAobGV0IGkgPSAwOyBpIDwgbDsgaSsrKSB7XG4gICAgICAgIGNvbnN0IHMgPSBzdHJpbmdzW2ldO1xuICAgICAgICAvLyBUaGUgaW5kZXggb2YgdGhlIGVuZCBvZiB0aGUgbGFzdCBhdHRyaWJ1dGUgbmFtZS4gV2hlbiB0aGlzIGlzXG4gICAgICAgIC8vIHBvc2l0aXZlIGF0IGVuZCBvZiBhIHN0cmluZywgaXQgbWVhbnMgd2UncmUgaW4gYW4gYXR0cmlidXRlIHZhbHVlXG4gICAgICAgIC8vIHBvc2l0aW9uIGFuZCBuZWVkIHRvIHJld3JpdGUgdGhlIGF0dHJpYnV0ZSBuYW1lLlxuICAgICAgICAvLyBXZSBhbHNvIHVzZSBhIHNwZWNpYWwgdmFsdWUgb2YgLTIgdG8gaW5kaWNhdGUgdGhhdCB3ZSBlbmNvdW50ZXJlZFxuICAgICAgICAvLyB0aGUgZW5kIG9mIGEgc3RyaW5nIGluIGF0dHJpYnV0ZSBuYW1lIHBvc2l0aW9uLlxuICAgICAgICBsZXQgYXR0ck5hbWVFbmRJbmRleCA9IC0xO1xuICAgICAgICBsZXQgYXR0ck5hbWU7XG4gICAgICAgIGxldCBsYXN0SW5kZXggPSAwO1xuICAgICAgICBsZXQgbWF0Y2g7XG4gICAgICAgIC8vIFRoZSBjb25kaXRpb25zIGluIHRoaXMgbG9vcCBoYW5kbGUgdGhlIGN1cnJlbnQgcGFyc2Ugc3RhdGUsIGFuZCB0aGVcbiAgICAgICAgLy8gYXNzaWdubWVudHMgdG8gdGhlIGByZWdleGAgdmFyaWFibGUgYXJlIHRoZSBzdGF0ZSB0cmFuc2l0aW9ucy5cbiAgICAgICAgd2hpbGUgKGxhc3RJbmRleCA8IHMubGVuZ3RoKSB7XG4gICAgICAgICAgICAvLyBNYWtlIHN1cmUgd2Ugc3RhcnQgc2VhcmNoaW5nIGZyb20gd2hlcmUgd2UgcHJldmlvdXNseSBsZWZ0IG9mZlxuICAgICAgICAgICAgcmVnZXgubGFzdEluZGV4ID0gbGFzdEluZGV4O1xuICAgICAgICAgICAgbWF0Y2ggPSByZWdleC5leGVjKHMpO1xuICAgICAgICAgICAgaWYgKG1hdGNoID09PSBudWxsKSB7XG4gICAgICAgICAgICAgICAgYnJlYWs7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBsYXN0SW5kZXggPSByZWdleC5sYXN0SW5kZXg7XG4gICAgICAgICAgICBpZiAocmVnZXggPT09IHRleHRFbmRSZWdleCkge1xuICAgICAgICAgICAgICAgIGlmIChtYXRjaFtDT01NRU5UX1NUQVJUXSA9PT0gJyEtLScpIHtcbiAgICAgICAgICAgICAgICAgICAgcmVnZXggPSBjb21tZW50RW5kUmVnZXg7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIGVsc2UgaWYgKG1hdGNoW0NPTU1FTlRfU1RBUlRdICE9PSB1bmRlZmluZWQpIHtcbiAgICAgICAgICAgICAgICAgICAgLy8gV2Ugc3RhcnRlZCBhIHdlaXJkIGNvbW1lbnQsIGxpa2UgPC97XG4gICAgICAgICAgICAgICAgICAgIHJlZ2V4ID0gY29tbWVudDJFbmRSZWdleDtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgZWxzZSBpZiAobWF0Y2hbVEFHX05BTUVdICE9PSB1bmRlZmluZWQpIHtcbiAgICAgICAgICAgICAgICAgICAgaWYgKHJhd1RleHRFbGVtZW50LnRlc3QobWF0Y2hbVEFHX05BTUVdKSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgLy8gUmVjb3JkIGlmIHdlIGVuY291bnRlciBhIHJhdy10ZXh0IGVsZW1lbnQuIFdlJ2xsIHN3aXRjaCB0b1xuICAgICAgICAgICAgICAgICAgICAgICAgLy8gdGhpcyByZWdleCBhdCB0aGUgZW5kIG9mIHRoZSB0YWcuXG4gICAgICAgICAgICAgICAgICAgICAgICByYXdUZXh0RW5kUmVnZXggPSBuZXcgUmVnRXhwKGA8LyR7bWF0Y2hbVEFHX05BTUVdfWAsICdnJyk7XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgcmVnZXggPSB0YWdFbmRSZWdleDtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgZWxzZSBpZiAobWF0Y2hbRFlOQU1JQ19UQUdfTkFNRV0gIT09IHVuZGVmaW5lZCkge1xuICAgICAgICAgICAgICAgICAgICBpZiAoREVWX01PREUpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcignQmluZGluZ3MgaW4gdGFnIG5hbWVzIGFyZSBub3Qgc3VwcG9ydGVkLiBQbGVhc2UgdXNlIHN0YXRpYyB0ZW1wbGF0ZXMgaW5zdGVhZC4gJyArXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgJ1NlZSBodHRwczovL2xpdC5kZXYvZG9jcy90ZW1wbGF0ZXMvZXhwcmVzc2lvbnMvI3N0YXRpYy1leHByZXNzaW9ucycpO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIHJlZ2V4ID0gdGFnRW5kUmVnZXg7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICAgICAgZWxzZSBpZiAocmVnZXggPT09IHRhZ0VuZFJlZ2V4KSB7XG4gICAgICAgICAgICAgICAgaWYgKG1hdGNoW0VOVElSRV9NQVRDSF0gPT09ICc+Jykge1xuICAgICAgICAgICAgICAgICAgICAvLyBFbmQgb2YgYSB0YWcuIElmIHdlIGhhZCBzdGFydGVkIGEgcmF3LXRleHQgZWxlbWVudCwgdXNlIHRoYXRcbiAgICAgICAgICAgICAgICAgICAgLy8gcmVnZXhcbiAgICAgICAgICAgICAgICAgICAgcmVnZXggPSByYXdUZXh0RW5kUmVnZXggPz8gdGV4dEVuZFJlZ2V4O1xuICAgICAgICAgICAgICAgICAgICAvLyBXZSBtYXkgYmUgZW5kaW5nIGFuIHVucXVvdGVkIGF0dHJpYnV0ZSB2YWx1ZSwgc28gbWFrZSBzdXJlIHdlXG4gICAgICAgICAgICAgICAgICAgIC8vIGNsZWFyIGFueSBwZW5kaW5nIGF0dHJOYW1lRW5kSW5kZXhcbiAgICAgICAgICAgICAgICAgICAgYXR0ck5hbWVFbmRJbmRleCA9IC0xO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICBlbHNlIGlmIChtYXRjaFtBVFRSSUJVVEVfTkFNRV0gPT09IHVuZGVmaW5lZCkge1xuICAgICAgICAgICAgICAgICAgICAvLyBBdHRyaWJ1dGUgbmFtZSBwb3NpdGlvblxuICAgICAgICAgICAgICAgICAgICBhdHRyTmFtZUVuZEluZGV4ID0gLTI7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgICAgICAgICBhdHRyTmFtZUVuZEluZGV4ID0gcmVnZXgubGFzdEluZGV4IC0gbWF0Y2hbU1BBQ0VTX0FORF9FUVVBTFNdLmxlbmd0aDtcbiAgICAgICAgICAgICAgICAgICAgYXR0ck5hbWUgPSBtYXRjaFtBVFRSSUJVVEVfTkFNRV07XG4gICAgICAgICAgICAgICAgICAgIHJlZ2V4ID1cbiAgICAgICAgICAgICAgICAgICAgICAgIG1hdGNoW1FVT1RFX0NIQVJdID09PSB1bmRlZmluZWRcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA/IHRhZ0VuZFJlZ2V4XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgOiBtYXRjaFtRVU9URV9DSEFSXSA9PT0gJ1wiJ1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA/IGRvdWJsZVF1b3RlQXR0ckVuZFJlZ2V4XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDogc2luZ2xlUXVvdGVBdHRyRW5kUmVnZXg7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICAgICAgZWxzZSBpZiAocmVnZXggPT09IGRvdWJsZVF1b3RlQXR0ckVuZFJlZ2V4IHx8XG4gICAgICAgICAgICAgICAgcmVnZXggPT09IHNpbmdsZVF1b3RlQXR0ckVuZFJlZ2V4KSB7XG4gICAgICAgICAgICAgICAgcmVnZXggPSB0YWdFbmRSZWdleDtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGVsc2UgaWYgKHJlZ2V4ID09PSBjb21tZW50RW5kUmVnZXggfHwgcmVnZXggPT09IGNvbW1lbnQyRW5kUmVnZXgpIHtcbiAgICAgICAgICAgICAgICByZWdleCA9IHRleHRFbmRSZWdleDtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgICAgIC8vIE5vdCBvbmUgb2YgdGhlIGZpdmUgc3RhdGUgcmVnZXhlcywgc28gaXQgbXVzdCBiZSB0aGUgZHluYW1pY2FsbHlcbiAgICAgICAgICAgICAgICAvLyBjcmVhdGVkIHJhdyB0ZXh0IHJlZ2V4IGFuZCB3ZSdyZSBhdCB0aGUgY2xvc2Ugb2YgdGhhdCBlbGVtZW50LlxuICAgICAgICAgICAgICAgIHJlZ2V4ID0gdGFnRW5kUmVnZXg7XG4gICAgICAgICAgICAgICAgcmF3VGV4dEVuZFJlZ2V4ID0gdW5kZWZpbmVkO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICAgIGlmIChERVZfTU9ERSkge1xuICAgICAgICAgICAgLy8gSWYgd2UgaGF2ZSBhIGF0dHJOYW1lRW5kSW5kZXgsIHdoaWNoIGluZGljYXRlcyB0aGF0IHdlIHNob3VsZFxuICAgICAgICAgICAgLy8gcmV3cml0ZSB0aGUgYXR0cmlidXRlIG5hbWUsIGFzc2VydCB0aGF0IHdlJ3JlIGluIGEgdmFsaWQgYXR0cmlidXRlXG4gICAgICAgICAgICAvLyBwb3NpdGlvbiAtIGVpdGhlciBpbiBhIHRhZywgb3IgYSBxdW90ZWQgYXR0cmlidXRlIHZhbHVlLlxuICAgICAgICAgICAgY29uc29sZS5hc3NlcnQoYXR0ck5hbWVFbmRJbmRleCA9PT0gLTEgfHxcbiAgICAgICAgICAgICAgICByZWdleCA9PT0gdGFnRW5kUmVnZXggfHxcbiAgICAgICAgICAgICAgICByZWdleCA9PT0gc2luZ2xlUXVvdGVBdHRyRW5kUmVnZXggfHxcbiAgICAgICAgICAgICAgICByZWdleCA9PT0gZG91YmxlUXVvdGVBdHRyRW5kUmVnZXgsICd1bmV4cGVjdGVkIHBhcnNlIHN0YXRlIEInKTtcbiAgICAgICAgfVxuICAgICAgICAvLyBXZSBoYXZlIGZvdXIgY2FzZXM6XG4gICAgICAgIC8vICAxLiBXZSdyZSBpbiB0ZXh0IHBvc2l0aW9uLCBhbmQgbm90IGluIGEgcmF3IHRleHQgZWxlbWVudFxuICAgICAgICAvLyAgICAgKHJlZ2V4ID09PSB0ZXh0RW5kUmVnZXgpOiBpbnNlcnQgYSBjb21tZW50IG1hcmtlci5cbiAgICAgICAgLy8gIDIuIFdlIGhhdmUgYSBub24tbmVnYXRpdmUgYXR0ck5hbWVFbmRJbmRleCB3aGljaCBtZWFucyB3ZSBuZWVkIHRvXG4gICAgICAgIC8vICAgICByZXdyaXRlIHRoZSBhdHRyaWJ1dGUgbmFtZSB0byBhZGQgYSBib3VuZCBhdHRyaWJ1dGUgc3VmZml4LlxuICAgICAgICAvLyAgMy4gV2UncmUgYXQgdGhlIG5vbi1maXJzdCBiaW5kaW5nIGluIGEgbXVsdGktYmluZGluZyBhdHRyaWJ1dGUsIHVzZSBhXG4gICAgICAgIC8vICAgICBwbGFpbiBtYXJrZXIuXG4gICAgICAgIC8vICA0LiBXZSdyZSBzb21ld2hlcmUgZWxzZSBpbnNpZGUgdGhlIHRhZy4gSWYgd2UncmUgaW4gYXR0cmlidXRlIG5hbWVcbiAgICAgICAgLy8gICAgIHBvc2l0aW9uIChhdHRyTmFtZUVuZEluZGV4ID09PSAtMiksIGFkZCBhIHNlcXVlbnRpYWwgc3VmZml4IHRvXG4gICAgICAgIC8vICAgICBnZW5lcmF0ZSBhIHVuaXF1ZSBhdHRyaWJ1dGUgbmFtZS5cbiAgICAgICAgLy8gRGV0ZWN0IGEgYmluZGluZyBuZXh0IHRvIHNlbGYtY2xvc2luZyB0YWcgZW5kIGFuZCBpbnNlcnQgYSBzcGFjZSB0b1xuICAgICAgICAvLyBzZXBhcmF0ZSB0aGUgbWFya2VyIGZyb20gdGhlIHRhZyBlbmQ6XG4gICAgICAgIGNvbnN0IGVuZCA9IHJlZ2V4ID09PSB0YWdFbmRSZWdleCAmJiBzdHJpbmdzW2kgKyAxXS5zdGFydHNXaXRoKCcvPicpID8gJyAnIDogJyc7XG4gICAgICAgIGh0bWwgKz1cbiAgICAgICAgICAgIHJlZ2V4ID09PSB0ZXh0RW5kUmVnZXhcbiAgICAgICAgICAgICAgICA/IHMgKyBub2RlTWFya2VyXG4gICAgICAgICAgICAgICAgOiBhdHRyTmFtZUVuZEluZGV4ID49IDBcbiAgICAgICAgICAgICAgICAgICAgPyAoYXR0ck5hbWVzLnB1c2goYXR0ck5hbWUpLFxuICAgICAgICAgICAgICAgICAgICAgICAgcy5zbGljZSgwLCBhdHRyTmFtZUVuZEluZGV4KSArXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgYm91bmRBdHRyaWJ1dGVTdWZmaXggK1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHMuc2xpY2UoYXR0ck5hbWVFbmRJbmRleCkpICtcbiAgICAgICAgICAgICAgICAgICAgICAgIG1hcmtlciArXG4gICAgICAgICAgICAgICAgICAgICAgICBlbmRcbiAgICAgICAgICAgICAgICAgICAgOiBzICsgbWFya2VyICsgKGF0dHJOYW1lRW5kSW5kZXggPT09IC0yID8gaSA6IGVuZCk7XG4gICAgfVxuICAgIGNvbnN0IGh0bWxSZXN1bHQgPSBodG1sICtcbiAgICAgICAgKHN0cmluZ3NbbF0gfHwgJzw/PicpICtcbiAgICAgICAgKHR5cGUgPT09IFNWR19SRVNVTFQgPyAnPC9zdmc+JyA6IHR5cGUgPT09IE1BVEhNTF9SRVNVTFQgPyAnPC9tYXRoPicgOiAnJyk7XG4gICAgLy8gUmV0dXJuZWQgYXMgYW4gYXJyYXkgZm9yIHRlcnNlbmVzc1xuICAgIHJldHVybiBbdHJ1c3RGcm9tVGVtcGxhdGVTdHJpbmcoc3RyaW5ncywgaHRtbFJlc3VsdCksIGF0dHJOYW1lc107XG59O1xuY2xhc3MgVGVtcGxhdGUge1xuICAgIGNvbnN0cnVjdG9yKFxuICAgIC8vIFRoaXMgcHJvcGVydHkgbmVlZHMgdG8gcmVtYWluIHVubWluaWZpZWQuXG4gICAgeyBzdHJpbmdzLCBbJ18kbGl0VHlwZSQnXTogdHlwZSB9LCBvcHRpb25zKSB7XG4gICAgICAgIHRoaXMucGFydHMgPSBbXTtcbiAgICAgICAgbGV0IG5vZGU7XG4gICAgICAgIGxldCBub2RlSW5kZXggPSAwO1xuICAgICAgICBsZXQgYXR0ck5hbWVJbmRleCA9IDA7XG4gICAgICAgIGNvbnN0IHBhcnRDb3VudCA9IHN0cmluZ3MubGVuZ3RoIC0gMTtcbiAgICAgICAgY29uc3QgcGFydHMgPSB0aGlzLnBhcnRzO1xuICAgICAgICAvLyBDcmVhdGUgdGVtcGxhdGUgZWxlbWVudFxuICAgICAgICBjb25zdCBbaHRtbCwgYXR0ck5hbWVzXSA9IGdldFRlbXBsYXRlSHRtbChzdHJpbmdzLCB0eXBlKTtcbiAgICAgICAgdGhpcy5lbCA9IFRlbXBsYXRlLmNyZWF0ZUVsZW1lbnQoaHRtbCwgb3B0aW9ucyk7XG4gICAgICAgIHdhbGtlci5jdXJyZW50Tm9kZSA9IHRoaXMuZWwuY29udGVudDtcbiAgICAgICAgLy8gUmUtcGFyZW50IFNWRyBvciBNYXRoTUwgbm9kZXMgaW50byB0ZW1wbGF0ZSByb290XG4gICAgICAgIGlmICh0eXBlID09PSBTVkdfUkVTVUxUIHx8IHR5cGUgPT09IE1BVEhNTF9SRVNVTFQpIHtcbiAgICAgICAgICAgIGNvbnN0IHdyYXBwZXIgPSB0aGlzLmVsLmNvbnRlbnQuZmlyc3RDaGlsZDtcbiAgICAgICAgICAgIHdyYXBwZXIucmVwbGFjZVdpdGgoLi4ud3JhcHBlci5jaGlsZE5vZGVzKTtcbiAgICAgICAgfVxuICAgICAgICAvLyBXYWxrIHRoZSB0ZW1wbGF0ZSB0byBmaW5kIGJpbmRpbmcgbWFya2VycyBhbmQgY3JlYXRlIFRlbXBsYXRlUGFydHNcbiAgICAgICAgd2hpbGUgKChub2RlID0gd2Fsa2VyLm5leHROb2RlKCkpICE9PSBudWxsICYmIHBhcnRzLmxlbmd0aCA8IHBhcnRDb3VudCkge1xuICAgICAgICAgICAgaWYgKG5vZGUubm9kZVR5cGUgPT09IDEpIHtcbiAgICAgICAgICAgICAgICBpZiAoREVWX01PREUpIHtcbiAgICAgICAgICAgICAgICAgICAgY29uc3QgdGFnID0gbm9kZS5sb2NhbE5hbWU7XG4gICAgICAgICAgICAgICAgICAgIC8vIFdhcm4gaWYgYHRleHRhcmVhYCBpbmNsdWRlcyBhbiBleHByZXNzaW9uIGFuZCB0aHJvdyBpZiBgdGVtcGxhdGVgXG4gICAgICAgICAgICAgICAgICAgIC8vIGRvZXMgc2luY2UgdGhlc2UgYXJlIG5vdCBzdXBwb3J0ZWQuIFdlIGRvIHRoaXMgYnkgY2hlY2tpbmdcbiAgICAgICAgICAgICAgICAgICAgLy8gaW5uZXJIVE1MIGZvciBhbnl0aGluZyB0aGF0IGxvb2tzIGxpa2UgYSBtYXJrZXIuIFRoaXMgY2F0Y2hlc1xuICAgICAgICAgICAgICAgICAgICAvLyBjYXNlcyBsaWtlIGJpbmRpbmdzIGluIHRleHRhcmVhIHRoZXJlIG1hcmtlcnMgdHVybiBpbnRvIHRleHQgbm9kZXMuXG4gICAgICAgICAgICAgICAgICAgIGlmICgvXig/OnRleHRhcmVhfHRlbXBsYXRlKSQvaS50ZXN0KHRhZykgJiZcbiAgICAgICAgICAgICAgICAgICAgICAgIG5vZGUuaW5uZXJIVE1MLmluY2x1ZGVzKG1hcmtlcikpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGNvbnN0IG0gPSBgRXhwcmVzc2lvbnMgYXJlIG5vdCBzdXBwb3J0ZWQgaW5zaWRlIFxcYCR7dGFnfVxcYCBgICtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBgZWxlbWVudHMuIFNlZSBodHRwczovL2xpdC5kZXYvbXNnL2V4cHJlc3Npb24taW4tJHt0YWd9IGZvciBtb3JlIGAgK1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGBpbmZvcm1hdGlvbi5gO1xuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHRhZyA9PT0gJ3RlbXBsYXRlJykge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcihtKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgIGVsc2VcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBpc3N1ZVdhcm5pbmcoJycsIG0pO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIC8vIFRPRE8gKGp1c3RpbmZhZ25hbmkpOiBmb3IgYXR0ZW1wdGVkIGR5bmFtaWMgdGFnIG5hbWVzLCB3ZSBkb24ndFxuICAgICAgICAgICAgICAgIC8vIGluY3JlbWVudCB0aGUgYmluZGluZ0luZGV4LCBhbmQgaXQnbGwgYmUgb2ZmIGJ5IDEgaW4gdGhlIGVsZW1lbnRcbiAgICAgICAgICAgICAgICAvLyBhbmQgb2ZmIGJ5IHR3byBhZnRlciBpdC5cbiAgICAgICAgICAgICAgICBpZiAobm9kZS5oYXNBdHRyaWJ1dGVzKCkpIHtcbiAgICAgICAgICAgICAgICAgICAgZm9yIChjb25zdCBuYW1lIG9mIG5vZGUuZ2V0QXR0cmlidXRlTmFtZXMoKSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKG5hbWUuZW5kc1dpdGgoYm91bmRBdHRyaWJ1dGVTdWZmaXgpKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgY29uc3QgcmVhbE5hbWUgPSBhdHRyTmFtZXNbYXR0ck5hbWVJbmRleCsrXTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBjb25zdCB2YWx1ZSA9IG5vZGUuZ2V0QXR0cmlidXRlKG5hbWUpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNvbnN0IHN0YXRpY3MgPSB2YWx1ZS5zcGxpdChtYXJrZXIpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNvbnN0IG0gPSAvKFsuP0BdKT8oLiopLy5leGVjKHJlYWxOYW1lKTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBwYXJ0cy5wdXNoKHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdHlwZTogQVRUUklCVVRFX1BBUlQsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGluZGV4OiBub2RlSW5kZXgsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG5hbWU6IG1bMl0sXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHN0cmluZ3M6IHN0YXRpY3MsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGN0b3I6IG1bMV0gPT09ICcuJ1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPyBQcm9wZXJ0eVBhcnRcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDogbVsxXSA9PT0gJz8nXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPyBCb29sZWFuQXR0cmlidXRlUGFydFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDogbVsxXSA9PT0gJ0AnXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgID8gRXZlbnRQYXJ0XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDogQXR0cmlidXRlUGFydCxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBub2RlLnJlbW92ZUF0dHJpYnV0ZShuYW1lKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgIGVsc2UgaWYgKG5hbWUuc3RhcnRzV2l0aChtYXJrZXIpKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgcGFydHMucHVzaCh7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHR5cGU6IEVMRU1FTlRfUEFSVCxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaW5kZXg6IG5vZGVJbmRleCxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBub2RlLnJlbW92ZUF0dHJpYnV0ZShuYW1lKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAvLyBUT0RPIChqdXN0aW5mYWduYW5pKTogYmVuY2htYXJrIHRoZSByZWdleCBhZ2FpbnN0IHRlc3RpbmcgZm9yIGVhY2hcbiAgICAgICAgICAgICAgICAvLyBvZiB0aGUgMyByYXcgdGV4dCBlbGVtZW50IG5hbWVzLlxuICAgICAgICAgICAgICAgIGlmIChyYXdUZXh0RWxlbWVudC50ZXN0KG5vZGUudGFnTmFtZSkpIHtcbiAgICAgICAgICAgICAgICAgICAgLy8gRm9yIHJhdyB0ZXh0IGVsZW1lbnRzIHdlIG5lZWQgdG8gc3BsaXQgdGhlIHRleHQgY29udGVudCBvblxuICAgICAgICAgICAgICAgICAgICAvLyBtYXJrZXJzLCBjcmVhdGUgYSBUZXh0IG5vZGUgZm9yIGVhY2ggc2VnbWVudCwgYW5kIGNyZWF0ZVxuICAgICAgICAgICAgICAgICAgICAvLyBhIFRlbXBsYXRlUGFydCBmb3IgZWFjaCBtYXJrZXIuXG4gICAgICAgICAgICAgICAgICAgIGNvbnN0IHN0cmluZ3MgPSBub2RlLnRleHRDb250ZW50LnNwbGl0KG1hcmtlcik7XG4gICAgICAgICAgICAgICAgICAgIGNvbnN0IGxhc3RJbmRleCA9IHN0cmluZ3MubGVuZ3RoIC0gMTtcbiAgICAgICAgICAgICAgICAgICAgaWYgKGxhc3RJbmRleCA+IDApIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIG5vZGUudGV4dENvbnRlbnQgPSB0cnVzdGVkVHlwZXNcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA/IHRydXN0ZWRUeXBlcy5lbXB0eVNjcmlwdFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDogJyc7XG4gICAgICAgICAgICAgICAgICAgICAgICAvLyBHZW5lcmF0ZSBhIG5ldyB0ZXh0IG5vZGUgZm9yIGVhY2ggbGl0ZXJhbCBzZWN0aW9uXG4gICAgICAgICAgICAgICAgICAgICAgICAvLyBUaGVzZSBub2RlcyBhcmUgYWxzbyB1c2VkIGFzIHRoZSBtYXJrZXJzIGZvciBjaGlsZCBwYXJ0c1xuICAgICAgICAgICAgICAgICAgICAgICAgZm9yIChsZXQgaSA9IDA7IGkgPCBsYXN0SW5kZXg7IGkrKykge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIG5vZGUuYXBwZW5kKHN0cmluZ3NbaV0sIGNyZWF0ZU1hcmtlcigpKTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAvLyBXYWxrIHBhc3QgdGhlIG1hcmtlciBub2RlIHdlIGp1c3QgYWRkZWRcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB3YWxrZXIubmV4dE5vZGUoKTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBwYXJ0cy5wdXNoKHsgdHlwZTogQ0hJTERfUEFSVCwgaW5kZXg6ICsrbm9kZUluZGV4IH0pO1xuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgLy8gTm90ZSBiZWNhdXNlIHRoaXMgbWFya2VyIGlzIGFkZGVkIGFmdGVyIHRoZSB3YWxrZXIncyBjdXJyZW50XG4gICAgICAgICAgICAgICAgICAgICAgICAvLyBub2RlLCBpdCB3aWxsIGJlIHdhbGtlZCB0byBpbiB0aGUgb3V0ZXIgbG9vcCAoYW5kIGlnbm9yZWQpLCBzb1xuICAgICAgICAgICAgICAgICAgICAgICAgLy8gd2UgZG9uJ3QgbmVlZCB0byBhZGp1c3Qgbm9kZUluZGV4IGhlcmVcbiAgICAgICAgICAgICAgICAgICAgICAgIG5vZGUuYXBwZW5kKHN0cmluZ3NbbGFzdEluZGV4XSwgY3JlYXRlTWFya2VyKCkpO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICAgICAgZWxzZSBpZiAobm9kZS5ub2RlVHlwZSA9PT0gOCkge1xuICAgICAgICAgICAgICAgIGNvbnN0IGRhdGEgPSBub2RlLmRhdGE7XG4gICAgICAgICAgICAgICAgaWYgKGRhdGEgPT09IG1hcmtlck1hdGNoKSB7XG4gICAgICAgICAgICAgICAgICAgIHBhcnRzLnB1c2goeyB0eXBlOiBDSElMRF9QQVJULCBpbmRleDogbm9kZUluZGV4IH0pO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgbGV0IGkgPSAtMTtcbiAgICAgICAgICAgICAgICAgICAgd2hpbGUgKChpID0gbm9kZS5kYXRhLmluZGV4T2YobWFya2VyLCBpICsgMSkpICE9PSAtMSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgLy8gQ29tbWVudCBub2RlIGhhcyBhIGJpbmRpbmcgbWFya2VyIGluc2lkZSwgbWFrZSBhbiBpbmFjdGl2ZSBwYXJ0XG4gICAgICAgICAgICAgICAgICAgICAgICAvLyBUaGUgYmluZGluZyB3b24ndCB3b3JrLCBidXQgc3Vic2VxdWVudCBiaW5kaW5ncyB3aWxsXG4gICAgICAgICAgICAgICAgICAgICAgICBwYXJ0cy5wdXNoKHsgdHlwZTogQ09NTUVOVF9QQVJULCBpbmRleDogbm9kZUluZGV4IH0pO1xuICAgICAgICAgICAgICAgICAgICAgICAgLy8gTW92ZSB0byB0aGUgZW5kIG9mIHRoZSBtYXRjaFxuICAgICAgICAgICAgICAgICAgICAgICAgaSArPSBtYXJrZXIubGVuZ3RoIC0gMTtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIG5vZGVJbmRleCsrO1xuICAgICAgICB9XG4gICAgICAgIGlmIChERVZfTU9ERSkge1xuICAgICAgICAgICAgLy8gSWYgdGhlcmUgd2FzIGEgZHVwbGljYXRlIGF0dHJpYnV0ZSBvbiBhIHRhZywgdGhlbiB3aGVuIHRoZSB0YWcgaXNcbiAgICAgICAgICAgIC8vIHBhcnNlZCBpbnRvIGFuIGVsZW1lbnQgdGhlIGF0dHJpYnV0ZSBnZXRzIGRlLWR1cGxpY2F0ZWQuIFdlIGNhbiBkZXRlY3RcbiAgICAgICAgICAgIC8vIHRoaXMgbWlzbWF0Y2ggaWYgd2UgaGF2ZW4ndCBwcmVjaXNlbHkgY29uc3VtZWQgZXZlcnkgYXR0cmlidXRlIG5hbWVcbiAgICAgICAgICAgIC8vIHdoZW4gcHJlcGFyaW5nIHRoZSB0ZW1wbGF0ZS4gVGhpcyB3b3JrcyBiZWNhdXNlIGBhdHRyTmFtZXNgIGlzIGJ1aWx0XG4gICAgICAgICAgICAvLyBmcm9tIHRoZSB0ZW1wbGF0ZSBzdHJpbmcgYW5kIGBhdHRyTmFtZUluZGV4YCBjb21lcyBmcm9tIHByb2Nlc3NpbmcgdGhlXG4gICAgICAgICAgICAvLyByZXN1bHRpbmcgRE9NLlxuICAgICAgICAgICAgaWYgKGF0dHJOYW1lcy5sZW5ndGggIT09IGF0dHJOYW1lSW5kZXgpIHtcbiAgICAgICAgICAgICAgICB0aHJvdyBuZXcgRXJyb3IoYERldGVjdGVkIGR1cGxpY2F0ZSBhdHRyaWJ1dGUgYmluZGluZ3MuIFRoaXMgb2NjdXJzIGlmIHlvdXIgdGVtcGxhdGUgYCArXG4gICAgICAgICAgICAgICAgICAgIGBoYXMgZHVwbGljYXRlIGF0dHJpYnV0ZXMgb24gYW4gZWxlbWVudCB0YWcuIEZvciBleGFtcGxlIGAgK1xuICAgICAgICAgICAgICAgICAgICBgXCI8aW5wdXQgP2Rpc2FibGVkPVxcJHt0cnVlfSA/ZGlzYWJsZWQ9XFwke2ZhbHNlfT5cIiBjb250YWlucyBhIGAgK1xuICAgICAgICAgICAgICAgICAgICBgZHVwbGljYXRlIFwiZGlzYWJsZWRcIiBhdHRyaWJ1dGUuIFRoZSBlcnJvciB3YXMgZGV0ZWN0ZWQgaW4gYCArXG4gICAgICAgICAgICAgICAgICAgIGB0aGUgZm9sbG93aW5nIHRlbXBsYXRlOiBcXG5gICtcbiAgICAgICAgICAgICAgICAgICAgJ2AnICtcbiAgICAgICAgICAgICAgICAgICAgc3RyaW5ncy5qb2luKCckey4uLn0nKSArXG4gICAgICAgICAgICAgICAgICAgICdgJyk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICAgICAgLy8gV2UgY291bGQgc2V0IHdhbGtlci5jdXJyZW50Tm9kZSB0byBhbm90aGVyIG5vZGUgaGVyZSB0byBwcmV2ZW50IGEgbWVtb3J5XG4gICAgICAgIC8vIGxlYWssIGJ1dCBldmVyeSB0aW1lIHdlIHByZXBhcmUgYSB0ZW1wbGF0ZSwgd2UgaW1tZWRpYXRlbHkgcmVuZGVyIGl0XG4gICAgICAgIC8vIGFuZCByZS11c2UgdGhlIHdhbGtlciBpbiBuZXcgVGVtcGxhdGVJbnN0YW5jZS5fY2xvbmUoKS5cbiAgICAgICAgZGVidWdMb2dFdmVudCAmJlxuICAgICAgICAgICAgZGVidWdMb2dFdmVudCh7XG4gICAgICAgICAgICAgICAga2luZDogJ3RlbXBsYXRlIHByZXAnLFxuICAgICAgICAgICAgICAgIHRlbXBsYXRlOiB0aGlzLFxuICAgICAgICAgICAgICAgIGNsb25hYmxlVGVtcGxhdGU6IHRoaXMuZWwsXG4gICAgICAgICAgICAgICAgcGFydHM6IHRoaXMucGFydHMsXG4gICAgICAgICAgICAgICAgc3RyaW5ncyxcbiAgICAgICAgICAgIH0pO1xuICAgIH1cbiAgICAvLyBPdmVycmlkZGVuIHZpYSBgbGl0SHRtbFBvbHlmaWxsU3VwcG9ydGAgdG8gcHJvdmlkZSBwbGF0Zm9ybSBzdXBwb3J0LlxuICAgIC8qKiBAbm9jb2xsYXBzZSAqL1xuICAgIHN0YXRpYyBjcmVhdGVFbGVtZW50KGh0bWwsIF9vcHRpb25zKSB7XG4gICAgICAgIGNvbnN0IGVsID0gZC5jcmVhdGVFbGVtZW50KCd0ZW1wbGF0ZScpO1xuICAgICAgICBlbC5pbm5lckhUTUwgPSBodG1sO1xuICAgICAgICByZXR1cm4gZWw7XG4gICAgfVxufVxuZnVuY3Rpb24gcmVzb2x2ZURpcmVjdGl2ZShwYXJ0LCB2YWx1ZSwgcGFyZW50ID0gcGFydCwgYXR0cmlidXRlSW5kZXgpIHtcbiAgICAvLyBCYWlsIGVhcmx5IGlmIHRoZSB2YWx1ZSBpcyBleHBsaWNpdGx5IG5vQ2hhbmdlLiBOb3RlLCB0aGlzIG1lYW5zIGFueVxuICAgIC8vIG5lc3RlZCBkaXJlY3RpdmUgaXMgc3RpbGwgYXR0YWNoZWQgYW5kIGlzIG5vdCBydW4uXG4gICAgaWYgKHZhbHVlID09PSBub0NoYW5nZSkge1xuICAgICAgICByZXR1cm4gdmFsdWU7XG4gICAgfVxuICAgIGxldCBjdXJyZW50RGlyZWN0aXZlID0gYXR0cmlidXRlSW5kZXggIT09IHVuZGVmaW5lZFxuICAgICAgICA/IHBhcmVudC5fX2RpcmVjdGl2ZXM/LlthdHRyaWJ1dGVJbmRleF1cbiAgICAgICAgOiBwYXJlbnQuX19kaXJlY3RpdmU7XG4gICAgY29uc3QgbmV4dERpcmVjdGl2ZUNvbnN0cnVjdG9yID0gaXNQcmltaXRpdmUodmFsdWUpXG4gICAgICAgID8gdW5kZWZpbmVkXG4gICAgICAgIDogLy8gVGhpcyBwcm9wZXJ0eSBuZWVkcyB0byByZW1haW4gdW5taW5pZmllZC5cbiAgICAgICAgICAgIHZhbHVlWydfJGxpdERpcmVjdGl2ZSQnXTtcbiAgICBpZiAoY3VycmVudERpcmVjdGl2ZT8uY29uc3RydWN0b3IgIT09IG5leHREaXJlY3RpdmVDb25zdHJ1Y3Rvcikge1xuICAgICAgICAvLyBUaGlzIHByb3BlcnR5IG5lZWRzIHRvIHJlbWFpbiB1bm1pbmlmaWVkLlxuICAgICAgICBjdXJyZW50RGlyZWN0aXZlPy5bJ18kbm90aWZ5RGlyZWN0aXZlQ29ubmVjdGlvbkNoYW5nZWQnXT8uKGZhbHNlKTtcbiAgICAgICAgaWYgKG5leHREaXJlY3RpdmVDb25zdHJ1Y3RvciA9PT0gdW5kZWZpbmVkKSB7XG4gICAgICAgICAgICBjdXJyZW50RGlyZWN0aXZlID0gdW5kZWZpbmVkO1xuICAgICAgICB9XG4gICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgY3VycmVudERpcmVjdGl2ZSA9IG5ldyBuZXh0RGlyZWN0aXZlQ29uc3RydWN0b3IocGFydCk7XG4gICAgICAgICAgICBjdXJyZW50RGlyZWN0aXZlLl8kaW5pdGlhbGl6ZShwYXJ0LCBwYXJlbnQsIGF0dHJpYnV0ZUluZGV4KTtcbiAgICAgICAgfVxuICAgICAgICBpZiAoYXR0cmlidXRlSW5kZXggIT09IHVuZGVmaW5lZCkge1xuICAgICAgICAgICAgKHBhcmVudC5fX2RpcmVjdGl2ZXMgPz89IFtdKVthdHRyaWJ1dGVJbmRleF0gPVxuICAgICAgICAgICAgICAgIGN1cnJlbnREaXJlY3RpdmU7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICBwYXJlbnQuX19kaXJlY3RpdmUgPSBjdXJyZW50RGlyZWN0aXZlO1xuICAgICAgICB9XG4gICAgfVxuICAgIGlmIChjdXJyZW50RGlyZWN0aXZlICE9PSB1bmRlZmluZWQpIHtcbiAgICAgICAgdmFsdWUgPSByZXNvbHZlRGlyZWN0aXZlKHBhcnQsIGN1cnJlbnREaXJlY3RpdmUuXyRyZXNvbHZlKHBhcnQsIHZhbHVlLnZhbHVlcyksIGN1cnJlbnREaXJlY3RpdmUsIGF0dHJpYnV0ZUluZGV4KTtcbiAgICB9XG4gICAgcmV0dXJuIHZhbHVlO1xufVxuLyoqXG4gKiBBbiB1cGRhdGVhYmxlIGluc3RhbmNlIG9mIGEgVGVtcGxhdGUuIEhvbGRzIHJlZmVyZW5jZXMgdG8gdGhlIFBhcnRzIHVzZWQgdG9cbiAqIHVwZGF0ZSB0aGUgdGVtcGxhdGUgaW5zdGFuY2UuXG4gKi9cbmNsYXNzIFRlbXBsYXRlSW5zdGFuY2Uge1xuICAgIGNvbnN0cnVjdG9yKHRlbXBsYXRlLCBwYXJlbnQpIHtcbiAgICAgICAgdGhpcy5fJHBhcnRzID0gW107XG4gICAgICAgIC8qKiBAaW50ZXJuYWwgKi9cbiAgICAgICAgdGhpcy5fJGRpc2Nvbm5lY3RhYmxlQ2hpbGRyZW4gPSB1bmRlZmluZWQ7XG4gICAgICAgIHRoaXMuXyR0ZW1wbGF0ZSA9IHRlbXBsYXRlO1xuICAgICAgICB0aGlzLl8kcGFyZW50ID0gcGFyZW50O1xuICAgIH1cbiAgICAvLyBDYWxsZWQgYnkgQ2hpbGRQYXJ0IHBhcmVudE5vZGUgZ2V0dGVyXG4gICAgZ2V0IHBhcmVudE5vZGUoKSB7XG4gICAgICAgIHJldHVybiB0aGlzLl8kcGFyZW50LnBhcmVudE5vZGU7XG4gICAgfVxuICAgIC8vIFNlZSBjb21tZW50IGluIERpc2Nvbm5lY3RhYmxlIGludGVyZmFjZSBmb3Igd2h5IHRoaXMgaXMgYSBnZXR0ZXJcbiAgICBnZXQgXyRpc0Nvbm5lY3RlZCgpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuXyRwYXJlbnQuXyRpc0Nvbm5lY3RlZDtcbiAgICB9XG4gICAgLy8gVGhpcyBtZXRob2QgaXMgc2VwYXJhdGUgZnJvbSB0aGUgY29uc3RydWN0b3IgYmVjYXVzZSB3ZSBuZWVkIHRvIHJldHVybiBhXG4gICAgLy8gRG9jdW1lbnRGcmFnbWVudCBhbmQgd2UgZG9uJ3Qgd2FudCB0byBob2xkIG9udG8gaXQgd2l0aCBhbiBpbnN0YW5jZSBmaWVsZC5cbiAgICBfY2xvbmUob3B0aW9ucykge1xuICAgICAgICBjb25zdCB7IGVsOiB7IGNvbnRlbnQgfSwgcGFydHM6IHBhcnRzLCB9ID0gdGhpcy5fJHRlbXBsYXRlO1xuICAgICAgICBjb25zdCBmcmFnbWVudCA9IChvcHRpb25zPy5jcmVhdGlvblNjb3BlID8/IGQpLmltcG9ydE5vZGUoY29udGVudCwgdHJ1ZSk7XG4gICAgICAgIHdhbGtlci5jdXJyZW50Tm9kZSA9IGZyYWdtZW50O1xuICAgICAgICBsZXQgbm9kZSA9IHdhbGtlci5uZXh0Tm9kZSgpO1xuICAgICAgICBsZXQgbm9kZUluZGV4ID0gMDtcbiAgICAgICAgbGV0IHBhcnRJbmRleCA9IDA7XG4gICAgICAgIGxldCB0ZW1wbGF0ZVBhcnQgPSBwYXJ0c1swXTtcbiAgICAgICAgd2hpbGUgKHRlbXBsYXRlUGFydCAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICAgICAgICBpZiAobm9kZUluZGV4ID09PSB0ZW1wbGF0ZVBhcnQuaW5kZXgpIHtcbiAgICAgICAgICAgICAgICBsZXQgcGFydDtcbiAgICAgICAgICAgICAgICBpZiAodGVtcGxhdGVQYXJ0LnR5cGUgPT09IENISUxEX1BBUlQpIHtcbiAgICAgICAgICAgICAgICAgICAgcGFydCA9IG5ldyBDaGlsZFBhcnQobm9kZSwgbm9kZS5uZXh0U2libGluZywgdGhpcywgb3B0aW9ucyk7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIGVsc2UgaWYgKHRlbXBsYXRlUGFydC50eXBlID09PSBBVFRSSUJVVEVfUEFSVCkge1xuICAgICAgICAgICAgICAgICAgICBwYXJ0ID0gbmV3IHRlbXBsYXRlUGFydC5jdG9yKG5vZGUsIHRlbXBsYXRlUGFydC5uYW1lLCB0ZW1wbGF0ZVBhcnQuc3RyaW5ncywgdGhpcywgb3B0aW9ucyk7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIGVsc2UgaWYgKHRlbXBsYXRlUGFydC50eXBlID09PSBFTEVNRU5UX1BBUlQpIHtcbiAgICAgICAgICAgICAgICAgICAgcGFydCA9IG5ldyBFbGVtZW50UGFydChub2RlLCB0aGlzLCBvcHRpb25zKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgdGhpcy5fJHBhcnRzLnB1c2gocGFydCk7XG4gICAgICAgICAgICAgICAgdGVtcGxhdGVQYXJ0ID0gcGFydHNbKytwYXJ0SW5kZXhdO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgaWYgKG5vZGVJbmRleCAhPT0gdGVtcGxhdGVQYXJ0Py5pbmRleCkge1xuICAgICAgICAgICAgICAgIG5vZGUgPSB3YWxrZXIubmV4dE5vZGUoKTtcbiAgICAgICAgICAgICAgICBub2RlSW5kZXgrKztcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICAvLyBXZSBuZWVkIHRvIHNldCB0aGUgY3VycmVudE5vZGUgYXdheSBmcm9tIHRoZSBjbG9uZWQgdHJlZSBzbyB0aGF0IHdlXG4gICAgICAgIC8vIGRvbid0IGhvbGQgb250byB0aGUgdHJlZSBldmVuIGlmIHRoZSB0cmVlIGlzIGRldGFjaGVkIGFuZCBzaG91bGQgYmVcbiAgICAgICAgLy8gZnJlZWQuXG4gICAgICAgIHdhbGtlci5jdXJyZW50Tm9kZSA9IGQ7XG4gICAgICAgIHJldHVybiBmcmFnbWVudDtcbiAgICB9XG4gICAgX3VwZGF0ZSh2YWx1ZXMpIHtcbiAgICAgICAgbGV0IGkgPSAwO1xuICAgICAgICBmb3IgKGNvbnN0IHBhcnQgb2YgdGhpcy5fJHBhcnRzKSB7XG4gICAgICAgICAgICBpZiAocGFydCAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICAgICAgICAgICAgZGVidWdMb2dFdmVudCAmJlxuICAgICAgICAgICAgICAgICAgICBkZWJ1Z0xvZ0V2ZW50KHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGtpbmQ6ICdzZXQgcGFydCcsXG4gICAgICAgICAgICAgICAgICAgICAgICBwYXJ0LFxuICAgICAgICAgICAgICAgICAgICAgICAgdmFsdWU6IHZhbHVlc1tpXSxcbiAgICAgICAgICAgICAgICAgICAgICAgIHZhbHVlSW5kZXg6IGksXG4gICAgICAgICAgICAgICAgICAgICAgICB2YWx1ZXMsXG4gICAgICAgICAgICAgICAgICAgICAgICB0ZW1wbGF0ZUluc3RhbmNlOiB0aGlzLFxuICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICBpZiAocGFydC5zdHJpbmdzICE9PSB1bmRlZmluZWQpIHtcbiAgICAgICAgICAgICAgICAgICAgcGFydC5fJHNldFZhbHVlKHZhbHVlcywgcGFydCwgaSk7XG4gICAgICAgICAgICAgICAgICAgIC8vIFRoZSBudW1iZXIgb2YgdmFsdWVzIHRoZSBwYXJ0IGNvbnN1bWVzIGlzIHBhcnQuc3RyaW5ncy5sZW5ndGggLSAxXG4gICAgICAgICAgICAgICAgICAgIC8vIHNpbmNlIHZhbHVlcyBhcmUgaW4gYmV0d2VlbiB0ZW1wbGF0ZSBzcGFucy4gV2UgaW5jcmVtZW50IGkgYnkgMVxuICAgICAgICAgICAgICAgICAgICAvLyBsYXRlciBpbiB0aGUgbG9vcCwgc28gaW5jcmVtZW50IGl0IGJ5IHBhcnQuc3RyaW5ncy5sZW5ndGggLSAyIGhlcmVcbiAgICAgICAgICAgICAgICAgICAgaSArPSBwYXJ0LnN0cmluZ3MubGVuZ3RoIC0gMjtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgIHBhcnQuXyRzZXRWYWx1ZSh2YWx1ZXNbaV0pO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGkrKztcbiAgICAgICAgfVxuICAgIH1cbn1cbmNsYXNzIENoaWxkUGFydCB7XG4gICAgLy8gU2VlIGNvbW1lbnQgaW4gRGlzY29ubmVjdGFibGUgaW50ZXJmYWNlIGZvciB3aHkgdGhpcyBpcyBhIGdldHRlclxuICAgIGdldCBfJGlzQ29ubmVjdGVkKCkge1xuICAgICAgICAvLyBDaGlsZFBhcnRzIHRoYXQgYXJlIG5vdCBhdCB0aGUgcm9vdCBzaG91bGQgYWx3YXlzIGJlIGNyZWF0ZWQgd2l0aCBhXG4gICAgICAgIC8vIHBhcmVudDsgb25seSBSb290Q2hpbGROb2RlJ3Mgd29uJ3QsIHNvIHRoZXkgcmV0dXJuIHRoZSBsb2NhbCBpc0Nvbm5lY3RlZFxuICAgICAgICAvLyBzdGF0ZVxuICAgICAgICByZXR1cm4gdGhpcy5fJHBhcmVudD8uXyRpc0Nvbm5lY3RlZCA/PyB0aGlzLl9faXNDb25uZWN0ZWQ7XG4gICAgfVxuICAgIGNvbnN0cnVjdG9yKHN0YXJ0Tm9kZSwgZW5kTm9kZSwgcGFyZW50LCBvcHRpb25zKSB7XG4gICAgICAgIHRoaXMudHlwZSA9IENISUxEX1BBUlQ7XG4gICAgICAgIHRoaXMuXyRjb21taXR0ZWRWYWx1ZSA9IG5vdGhpbmc7XG4gICAgICAgIC8vIFRoZSBmb2xsb3dpbmcgZmllbGRzIHdpbGwgYmUgcGF0Y2hlZCBvbnRvIENoaWxkUGFydHMgd2hlbiByZXF1aXJlZCBieVxuICAgICAgICAvLyBBc3luY0RpcmVjdGl2ZVxuICAgICAgICAvKiogQGludGVybmFsICovXG4gICAgICAgIHRoaXMuXyRkaXNjb25uZWN0YWJsZUNoaWxkcmVuID0gdW5kZWZpbmVkO1xuICAgICAgICB0aGlzLl8kc3RhcnROb2RlID0gc3RhcnROb2RlO1xuICAgICAgICB0aGlzLl8kZW5kTm9kZSA9IGVuZE5vZGU7XG4gICAgICAgIHRoaXMuXyRwYXJlbnQgPSBwYXJlbnQ7XG4gICAgICAgIHRoaXMub3B0aW9ucyA9IG9wdGlvbnM7XG4gICAgICAgIC8vIE5vdGUgX19pc0Nvbm5lY3RlZCBpcyBvbmx5IGV2ZXIgYWNjZXNzZWQgb24gUm9vdFBhcnRzIChpLmUuIHdoZW4gdGhlcmUgaXNcbiAgICAgICAgLy8gbm8gXyRwYXJlbnQpOyB0aGUgdmFsdWUgb24gYSBub24tcm9vdC1wYXJ0IGlzIFwiZG9uJ3QgY2FyZVwiLCBidXQgY2hlY2tpbmdcbiAgICAgICAgLy8gZm9yIHBhcmVudCB3b3VsZCBiZSBtb3JlIGNvZGVcbiAgICAgICAgdGhpcy5fX2lzQ29ubmVjdGVkID0gb3B0aW9ucz8uaXNDb25uZWN0ZWQgPz8gdHJ1ZTtcbiAgICAgICAgaWYgKEVOQUJMRV9FWFRSQV9TRUNVUklUWV9IT09LUykge1xuICAgICAgICAgICAgLy8gRXhwbGljaXRseSBpbml0aWFsaXplIGZvciBjb25zaXN0ZW50IGNsYXNzIHNoYXBlLlxuICAgICAgICAgICAgdGhpcy5fdGV4dFNhbml0aXplciA9IHVuZGVmaW5lZDtcbiAgICAgICAgfVxuICAgIH1cbiAgICAvKipcbiAgICAgKiBUaGUgcGFyZW50IG5vZGUgaW50byB3aGljaCB0aGUgcGFydCByZW5kZXJzIGl0cyBjb250ZW50LlxuICAgICAqXG4gICAgICogQSBDaGlsZFBhcnQncyBjb250ZW50IGNvbnNpc3RzIG9mIGEgcmFuZ2Ugb2YgYWRqYWNlbnQgY2hpbGQgbm9kZXMgb2ZcbiAgICAgKiBgLnBhcmVudE5vZGVgLCBwb3NzaWJseSBib3JkZXJlZCBieSAnbWFya2VyIG5vZGVzJyAoYC5zdGFydE5vZGVgIGFuZFxuICAgICAqIGAuZW5kTm9kZWApLlxuICAgICAqXG4gICAgICogLSBJZiBib3RoIGAuc3RhcnROb2RlYCBhbmQgYC5lbmROb2RlYCBhcmUgbm9uLW51bGwsIHRoZW4gdGhlIHBhcnQncyBjb250ZW50XG4gICAgICogY29uc2lzdHMgb2YgYWxsIHNpYmxpbmdzIGJldHdlZW4gYC5zdGFydE5vZGVgIGFuZCBgLmVuZE5vZGVgLCBleGNsdXNpdmVseS5cbiAgICAgKlxuICAgICAqIC0gSWYgYC5zdGFydE5vZGVgIGlzIG5vbi1udWxsIGJ1dCBgLmVuZE5vZGVgIGlzIG51bGwsIHRoZW4gdGhlIHBhcnQnc1xuICAgICAqIGNvbnRlbnQgY29uc2lzdHMgb2YgYWxsIHNpYmxpbmdzIGZvbGxvd2luZyBgLnN0YXJ0Tm9kZWAsIHVwIHRvIGFuZFxuICAgICAqIGluY2x1ZGluZyB0aGUgbGFzdCBjaGlsZCBvZiBgLnBhcmVudE5vZGVgLiBJZiBgLmVuZE5vZGVgIGlzIG5vbi1udWxsLCB0aGVuXG4gICAgICogYC5zdGFydE5vZGVgIHdpbGwgYWx3YXlzIGJlIG5vbi1udWxsLlxuICAgICAqXG4gICAgICogLSBJZiBib3RoIGAuZW5kTm9kZWAgYW5kIGAuc3RhcnROb2RlYCBhcmUgbnVsbCwgdGhlbiB0aGUgcGFydCdzIGNvbnRlbnRcbiAgICAgKiBjb25zaXN0cyBvZiBhbGwgY2hpbGQgbm9kZXMgb2YgYC5wYXJlbnROb2RlYC5cbiAgICAgKi9cbiAgICBnZXQgcGFyZW50Tm9kZSgpIHtcbiAgICAgICAgbGV0IHBhcmVudE5vZGUgPSB3cmFwKHRoaXMuXyRzdGFydE5vZGUpLnBhcmVudE5vZGU7XG4gICAgICAgIGNvbnN0IHBhcmVudCA9IHRoaXMuXyRwYXJlbnQ7XG4gICAgICAgIGlmIChwYXJlbnQgIT09IHVuZGVmaW5lZCAmJlxuICAgICAgICAgICAgcGFyZW50Tm9kZT8ubm9kZVR5cGUgPT09IDExIC8qIE5vZGUuRE9DVU1FTlRfRlJBR01FTlQgKi8pIHtcbiAgICAgICAgICAgIC8vIElmIHRoZSBwYXJlbnROb2RlIGlzIGEgRG9jdW1lbnRGcmFnbWVudCwgaXQgbWF5IGJlIGJlY2F1c2UgdGhlIERPTSBpc1xuICAgICAgICAgICAgLy8gc3RpbGwgaW4gdGhlIGNsb25lZCBmcmFnbWVudCBkdXJpbmcgaW5pdGlhbCByZW5kZXI7IGlmIHNvLCBnZXQgdGhlIHJlYWxcbiAgICAgICAgICAgIC8vIHBhcmVudE5vZGUgdGhlIHBhcnQgd2lsbCBiZSBjb21taXR0ZWQgaW50byBieSBhc2tpbmcgdGhlIHBhcmVudC5cbiAgICAgICAgICAgIHBhcmVudE5vZGUgPSBwYXJlbnQucGFyZW50Tm9kZTtcbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gcGFyZW50Tm9kZTtcbiAgICB9XG4gICAgLyoqXG4gICAgICogVGhlIHBhcnQncyBsZWFkaW5nIG1hcmtlciBub2RlLCBpZiBhbnkuIFNlZSBgLnBhcmVudE5vZGVgIGZvciBtb3JlXG4gICAgICogaW5mb3JtYXRpb24uXG4gICAgICovXG4gICAgZ2V0IHN0YXJ0Tm9kZSgpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuXyRzdGFydE5vZGU7XG4gICAgfVxuICAgIC8qKlxuICAgICAqIFRoZSBwYXJ0J3MgdHJhaWxpbmcgbWFya2VyIG5vZGUsIGlmIGFueS4gU2VlIGAucGFyZW50Tm9kZWAgZm9yIG1vcmVcbiAgICAgKiBpbmZvcm1hdGlvbi5cbiAgICAgKi9cbiAgICBnZXQgZW5kTm9kZSgpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuXyRlbmROb2RlO1xuICAgIH1cbiAgICBfJHNldFZhbHVlKHZhbHVlLCBkaXJlY3RpdmVQYXJlbnQgPSB0aGlzKSB7XG4gICAgICAgIGlmIChERVZfTU9ERSAmJiB0aGlzLnBhcmVudE5vZGUgPT09IG51bGwpIHtcbiAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcihgVGhpcyBcXGBDaGlsZFBhcnRcXGAgaGFzIG5vIFxcYHBhcmVudE5vZGVcXGAgYW5kIHRoZXJlZm9yZSBjYW5ub3QgYWNjZXB0IGEgdmFsdWUuIFRoaXMgbGlrZWx5IG1lYW5zIHRoZSBlbGVtZW50IGNvbnRhaW5pbmcgdGhlIHBhcnQgd2FzIG1hbmlwdWxhdGVkIGluIGFuIHVuc3VwcG9ydGVkIHdheSBvdXRzaWRlIG9mIExpdCdzIGNvbnRyb2wgc3VjaCB0aGF0IHRoZSBwYXJ0J3MgbWFya2VyIG5vZGVzIHdlcmUgZWplY3RlZCBmcm9tIERPTS4gRm9yIGV4YW1wbGUsIHNldHRpbmcgdGhlIGVsZW1lbnQncyBcXGBpbm5lckhUTUxcXGAgb3IgXFxgdGV4dENvbnRlbnRcXGAgY2FuIGRvIHRoaXMuYCk7XG4gICAgICAgIH1cbiAgICAgICAgdmFsdWUgPSByZXNvbHZlRGlyZWN0aXZlKHRoaXMsIHZhbHVlLCBkaXJlY3RpdmVQYXJlbnQpO1xuICAgICAgICBpZiAoaXNQcmltaXRpdmUodmFsdWUpKSB7XG4gICAgICAgICAgICAvLyBOb24tcmVuZGVyaW5nIGNoaWxkIHZhbHVlcy4gSXQncyBpbXBvcnRhbnQgdGhhdCB0aGVzZSBkbyBub3QgcmVuZGVyXG4gICAgICAgICAgICAvLyBlbXB0eSB0ZXh0IG5vZGVzIHRvIGF2b2lkIGlzc3VlcyB3aXRoIHByZXZlbnRpbmcgZGVmYXVsdCA8c2xvdD5cbiAgICAgICAgICAgIC8vIGZhbGxiYWNrIGNvbnRlbnQuXG4gICAgICAgICAgICBpZiAodmFsdWUgPT09IG5vdGhpbmcgfHwgdmFsdWUgPT0gbnVsbCB8fCB2YWx1ZSA9PT0gJycpIHtcbiAgICAgICAgICAgICAgICBpZiAodGhpcy5fJGNvbW1pdHRlZFZhbHVlICE9PSBub3RoaW5nKSB7XG4gICAgICAgICAgICAgICAgICAgIGRlYnVnTG9nRXZlbnQgJiZcbiAgICAgICAgICAgICAgICAgICAgICAgIGRlYnVnTG9nRXZlbnQoe1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGtpbmQ6ICdjb21taXQgbm90aGluZyB0byBjaGlsZCcsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgc3RhcnQ6IHRoaXMuXyRzdGFydE5vZGUsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgZW5kOiB0aGlzLl8kZW5kTm9kZSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBwYXJlbnQ6IHRoaXMuXyRwYXJlbnQsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgb3B0aW9uczogdGhpcy5vcHRpb25zLFxuICAgICAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuXyRjbGVhcigpO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB0aGlzLl8kY29tbWl0dGVkVmFsdWUgPSBub3RoaW5nO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgZWxzZSBpZiAodmFsdWUgIT09IHRoaXMuXyRjb21taXR0ZWRWYWx1ZSAmJiB2YWx1ZSAhPT0gbm9DaGFuZ2UpIHtcbiAgICAgICAgICAgICAgICB0aGlzLl9jb21taXRUZXh0KHZhbHVlKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIC8vIFRoaXMgcHJvcGVydHkgbmVlZHMgdG8gcmVtYWluIHVubWluaWZpZWQuXG4gICAgICAgIH1cbiAgICAgICAgZWxzZSBpZiAodmFsdWVbJ18kbGl0VHlwZSQnXSAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICAgICAgICB0aGlzLl9jb21taXRUZW1wbGF0ZVJlc3VsdCh2YWx1ZSk7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSBpZiAodmFsdWUubm9kZVR5cGUgIT09IHVuZGVmaW5lZCkge1xuICAgICAgICAgICAgaWYgKERFVl9NT0RFICYmIHRoaXMub3B0aW9ucz8uaG9zdCA9PT0gdmFsdWUpIHtcbiAgICAgICAgICAgICAgICB0aGlzLl9jb21taXRUZXh0KGBbcHJvYmFibGUgbWlzdGFrZTogcmVuZGVyZWQgYSB0ZW1wbGF0ZSdzIGhvc3QgaW4gaXRzZWxmIGAgK1xuICAgICAgICAgICAgICAgICAgICBgKGNvbW1vbmx5IGNhdXNlZCBieSB3cml0aW5nIFxcJHt0aGlzfSBpbiBhIHRlbXBsYXRlXWApO1xuICAgICAgICAgICAgICAgIGNvbnNvbGUud2FybihgQXR0ZW1wdGVkIHRvIHJlbmRlciB0aGUgdGVtcGxhdGUgaG9zdGAsIHZhbHVlLCBgaW5zaWRlIGl0c2VsZi4gVGhpcyBpcyBhbG1vc3QgYWx3YXlzIGEgbWlzdGFrZSwgYW5kIGluIGRldiBtb2RlIGAsIGB3ZSByZW5kZXIgc29tZSB3YXJuaW5nIHRleHQuIEluIHByb2R1Y3Rpb24gaG93ZXZlciwgd2UnbGwgYCwgYHJlbmRlciBpdCwgd2hpY2ggd2lsbCB1c3VhbGx5IHJlc3VsdCBpbiBhbiBlcnJvciwgYW5kIHNvbWV0aW1lcyBgLCBgaW4gdGhlIGVsZW1lbnQgZGlzYXBwZWFyaW5nIGZyb20gdGhlIERPTS5gKTtcbiAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICB0aGlzLl9jb21taXROb2RlKHZhbHVlKTtcbiAgICAgICAgfVxuICAgICAgICBlbHNlIGlmIChpc0l0ZXJhYmxlKHZhbHVlKSkge1xuICAgICAgICAgICAgdGhpcy5fY29tbWl0SXRlcmFibGUodmFsdWUpO1xuICAgICAgICB9XG4gICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgLy8gRmFsbGJhY2ssIHdpbGwgcmVuZGVyIHRoZSBzdHJpbmcgcmVwcmVzZW50YXRpb25cbiAgICAgICAgICAgIHRoaXMuX2NvbW1pdFRleHQodmFsdWUpO1xuICAgICAgICB9XG4gICAgfVxuICAgIF9pbnNlcnQobm9kZSkge1xuICAgICAgICByZXR1cm4gd3JhcCh3cmFwKHRoaXMuXyRzdGFydE5vZGUpLnBhcmVudE5vZGUpLmluc2VydEJlZm9yZShub2RlLCB0aGlzLl8kZW5kTm9kZSk7XG4gICAgfVxuICAgIF9jb21taXROb2RlKHZhbHVlKSB7XG4gICAgICAgIGlmICh0aGlzLl8kY29tbWl0dGVkVmFsdWUgIT09IHZhbHVlKSB7XG4gICAgICAgICAgICB0aGlzLl8kY2xlYXIoKTtcbiAgICAgICAgICAgIGlmIChFTkFCTEVfRVhUUkFfU0VDVVJJVFlfSE9PS1MgJiZcbiAgICAgICAgICAgICAgICBzYW5pdGl6ZXJGYWN0b3J5SW50ZXJuYWwgIT09IG5vb3BTYW5pdGl6ZXIpIHtcbiAgICAgICAgICAgICAgICBjb25zdCBwYXJlbnROb2RlTmFtZSA9IHRoaXMuXyRzdGFydE5vZGUucGFyZW50Tm9kZT8ubm9kZU5hbWU7XG4gICAgICAgICAgICAgICAgaWYgKHBhcmVudE5vZGVOYW1lID09PSAnU1RZTEUnIHx8IHBhcmVudE5vZGVOYW1lID09PSAnU0NSSVBUJykge1xuICAgICAgICAgICAgICAgICAgICBsZXQgbWVzc2FnZSA9ICdGb3JiaWRkZW4nO1xuICAgICAgICAgICAgICAgICAgICBpZiAoREVWX01PREUpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChwYXJlbnROb2RlTmFtZSA9PT0gJ1NUWUxFJykge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIG1lc3NhZ2UgPVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBgTGl0IGRvZXMgbm90IHN1cHBvcnQgYmluZGluZyBpbnNpZGUgc3R5bGUgbm9kZXMuIGAgK1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYFRoaXMgaXMgYSBzZWN1cml0eSByaXNrLCBhcyBzdHlsZSBpbmplY3Rpb24gYXR0YWNrcyBjYW4gYCArXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBgZXhmaWx0cmF0ZSBkYXRhIGFuZCBzcG9vZiBVSXMuIGAgK1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYENvbnNpZGVyIGluc3RlYWQgdXNpbmcgY3NzXFxgLi4uXFxgIGxpdGVyYWxzIGAgK1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYHRvIGNvbXBvc2Ugc3R5bGVzLCBhbmQgZG8gZHluYW1pYyBzdHlsaW5nIHdpdGggYCArXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBgY3NzIGN1c3RvbSBwcm9wZXJ0aWVzLCA6OnBhcnRzLCA8c2xvdD5zLCBgICtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGBhbmQgYnkgbXV0YXRpbmcgdGhlIERPTSByYXRoZXIgdGhhbiBzdHlsZXNoZWV0cy5gO1xuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgbWVzc2FnZSA9XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGBMaXQgZG9lcyBub3Qgc3VwcG9ydCBiaW5kaW5nIGluc2lkZSBzY3JpcHQgbm9kZXMuIGAgK1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYFRoaXMgaXMgYSBzZWN1cml0eSByaXNrLCBhcyBpdCBjb3VsZCBhbGxvdyBhcmJpdHJhcnkgYCArXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBgY29kZSBleGVjdXRpb24uYDtcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICB0aHJvdyBuZXcgRXJyb3IobWVzc2FnZSk7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICAgICAgZGVidWdMb2dFdmVudCAmJlxuICAgICAgICAgICAgICAgIGRlYnVnTG9nRXZlbnQoe1xuICAgICAgICAgICAgICAgICAgICBraW5kOiAnY29tbWl0IG5vZGUnLFxuICAgICAgICAgICAgICAgICAgICBzdGFydDogdGhpcy5fJHN0YXJ0Tm9kZSxcbiAgICAgICAgICAgICAgICAgICAgcGFyZW50OiB0aGlzLl8kcGFyZW50LFxuICAgICAgICAgICAgICAgICAgICB2YWx1ZTogdmFsdWUsXG4gICAgICAgICAgICAgICAgICAgIG9wdGlvbnM6IHRoaXMub3B0aW9ucyxcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIHRoaXMuXyRjb21taXR0ZWRWYWx1ZSA9IHRoaXMuX2luc2VydCh2YWx1ZSk7XG4gICAgICAgIH1cbiAgICB9XG4gICAgX2NvbW1pdFRleHQodmFsdWUpIHtcbiAgICAgICAgLy8gSWYgdGhlIGNvbW1pdHRlZCB2YWx1ZSBpcyBhIHByaW1pdGl2ZSBpdCBtZWFucyB3ZSBjYWxsZWQgX2NvbW1pdFRleHQgb25cbiAgICAgICAgLy8gdGhlIHByZXZpb3VzIHJlbmRlciwgYW5kIHdlIGtub3cgdGhhdCB0aGlzLl8kc3RhcnROb2RlLm5leHRTaWJsaW5nIGlzIGFcbiAgICAgICAgLy8gVGV4dCBub2RlLiBXZSBjYW4gbm93IGp1c3QgcmVwbGFjZSB0aGUgdGV4dCBjb250ZW50ICguZGF0YSkgb2YgdGhlIG5vZGUuXG4gICAgICAgIGlmICh0aGlzLl8kY29tbWl0dGVkVmFsdWUgIT09IG5vdGhpbmcgJiZcbiAgICAgICAgICAgIGlzUHJpbWl0aXZlKHRoaXMuXyRjb21taXR0ZWRWYWx1ZSkpIHtcbiAgICAgICAgICAgIGNvbnN0IG5vZGUgPSB3cmFwKHRoaXMuXyRzdGFydE5vZGUpLm5leHRTaWJsaW5nO1xuICAgICAgICAgICAgaWYgKEVOQUJMRV9FWFRSQV9TRUNVUklUWV9IT09LUykge1xuICAgICAgICAgICAgICAgIGlmICh0aGlzLl90ZXh0U2FuaXRpemVyID09PSB1bmRlZmluZWQpIHtcbiAgICAgICAgICAgICAgICAgICAgdGhpcy5fdGV4dFNhbml0aXplciA9IGNyZWF0ZVNhbml0aXplcihub2RlLCAnZGF0YScsICdwcm9wZXJ0eScpO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB2YWx1ZSA9IHRoaXMuX3RleHRTYW5pdGl6ZXIodmFsdWUpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgZGVidWdMb2dFdmVudCAmJlxuICAgICAgICAgICAgICAgIGRlYnVnTG9nRXZlbnQoe1xuICAgICAgICAgICAgICAgICAgICBraW5kOiAnY29tbWl0IHRleHQnLFxuICAgICAgICAgICAgICAgICAgICBub2RlLFxuICAgICAgICAgICAgICAgICAgICB2YWx1ZSxcbiAgICAgICAgICAgICAgICAgICAgb3B0aW9uczogdGhpcy5vcHRpb25zLFxuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgbm9kZS5kYXRhID0gdmFsdWU7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICBpZiAoRU5BQkxFX0VYVFJBX1NFQ1VSSVRZX0hPT0tTKSB7XG4gICAgICAgICAgICAgICAgY29uc3QgdGV4dE5vZGUgPSBkLmNyZWF0ZVRleHROb2RlKCcnKTtcbiAgICAgICAgICAgICAgICB0aGlzLl9jb21taXROb2RlKHRleHROb2RlKTtcbiAgICAgICAgICAgICAgICAvLyBXaGVuIHNldHRpbmcgdGV4dCBjb250ZW50LCBmb3Igc2VjdXJpdHkgcHVycG9zZXMgaXQgbWF0dGVycyBhIGxvdFxuICAgICAgICAgICAgICAgIC8vIHdoYXQgdGhlIHBhcmVudCBpcy4gRm9yIGV4YW1wbGUsIDxzdHlsZT4gYW5kIDxzY3JpcHQ+IG5lZWQgdG8gYmVcbiAgICAgICAgICAgICAgICAvLyBoYW5kbGVkIHdpdGggY2FyZSwgd2hpbGUgPHNwYW4+IGRvZXMgbm90LiBTbyBmaXJzdCB3ZSBuZWVkIHRvIHB1dCBhXG4gICAgICAgICAgICAgICAgLy8gdGV4dCBub2RlIGludG8gdGhlIGRvY3VtZW50LCB0aGVuIHdlIGNhbiBzYW5pdGl6ZSBpdHMgY29udGVudC5cbiAgICAgICAgICAgICAgICBpZiAodGhpcy5fdGV4dFNhbml0aXplciA9PT0gdW5kZWZpbmVkKSB7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuX3RleHRTYW5pdGl6ZXIgPSBjcmVhdGVTYW5pdGl6ZXIodGV4dE5vZGUsICdkYXRhJywgJ3Byb3BlcnR5Jyk7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIHZhbHVlID0gdGhpcy5fdGV4dFNhbml0aXplcih2YWx1ZSk7XG4gICAgICAgICAgICAgICAgZGVidWdMb2dFdmVudCAmJlxuICAgICAgICAgICAgICAgICAgICBkZWJ1Z0xvZ0V2ZW50KHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGtpbmQ6ICdjb21taXQgdGV4dCcsXG4gICAgICAgICAgICAgICAgICAgICAgICBub2RlOiB0ZXh0Tm9kZSxcbiAgICAgICAgICAgICAgICAgICAgICAgIHZhbHVlLFxuICAgICAgICAgICAgICAgICAgICAgICAgb3B0aW9uczogdGhpcy5vcHRpb25zLFxuICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICB0ZXh0Tm9kZS5kYXRhID0gdmFsdWU7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgICAgICB0aGlzLl9jb21taXROb2RlKGQuY3JlYXRlVGV4dE5vZGUodmFsdWUpKTtcbiAgICAgICAgICAgICAgICBkZWJ1Z0xvZ0V2ZW50ICYmXG4gICAgICAgICAgICAgICAgICAgIGRlYnVnTG9nRXZlbnQoe1xuICAgICAgICAgICAgICAgICAgICAgICAga2luZDogJ2NvbW1pdCB0ZXh0JyxcbiAgICAgICAgICAgICAgICAgICAgICAgIG5vZGU6IHdyYXAodGhpcy5fJHN0YXJ0Tm9kZSkubmV4dFNpYmxpbmcsXG4gICAgICAgICAgICAgICAgICAgICAgICB2YWx1ZSxcbiAgICAgICAgICAgICAgICAgICAgICAgIG9wdGlvbnM6IHRoaXMub3B0aW9ucyxcbiAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICAgICAgdGhpcy5fJGNvbW1pdHRlZFZhbHVlID0gdmFsdWU7XG4gICAgfVxuICAgIF9jb21taXRUZW1wbGF0ZVJlc3VsdChyZXN1bHQpIHtcbiAgICAgICAgLy8gVGhpcyBwcm9wZXJ0eSBuZWVkcyB0byByZW1haW4gdW5taW5pZmllZC5cbiAgICAgICAgY29uc3QgeyB2YWx1ZXMsIFsnXyRsaXRUeXBlJCddOiB0eXBlIH0gPSByZXN1bHQ7XG4gICAgICAgIC8vIElmICRsaXRUeXBlJCBpcyBhIG51bWJlciwgcmVzdWx0IGlzIGEgcGxhaW4gVGVtcGxhdGVSZXN1bHQgYW5kIHdlIGdldFxuICAgICAgICAvLyB0aGUgdGVtcGxhdGUgZnJvbSB0aGUgdGVtcGxhdGUgY2FjaGUuIElmIG5vdCwgcmVzdWx0IGlzIGFcbiAgICAgICAgLy8gQ29tcGlsZWRUZW1wbGF0ZVJlc3VsdCBhbmQgXyRsaXRUeXBlJCBpcyBhIENvbXBpbGVkVGVtcGxhdGUgYW5kIHdlIG5lZWRcbiAgICAgICAgLy8gdG8gY3JlYXRlIHRoZSA8dGVtcGxhdGU+IGVsZW1lbnQgdGhlIGZpcnN0IHRpbWUgd2Ugc2VlIGl0LlxuICAgICAgICBjb25zdCB0ZW1wbGF0ZSA9IHR5cGVvZiB0eXBlID09PSAnbnVtYmVyJ1xuICAgICAgICAgICAgPyB0aGlzLl8kZ2V0VGVtcGxhdGUocmVzdWx0KVxuICAgICAgICAgICAgOiAodHlwZS5lbCA9PT0gdW5kZWZpbmVkICYmXG4gICAgICAgICAgICAgICAgKHR5cGUuZWwgPSBUZW1wbGF0ZS5jcmVhdGVFbGVtZW50KHRydXN0RnJvbVRlbXBsYXRlU3RyaW5nKHR5cGUuaCwgdHlwZS5oWzBdKSwgdGhpcy5vcHRpb25zKSksXG4gICAgICAgICAgICAgICAgdHlwZSk7XG4gICAgICAgIGlmICh0aGlzLl8kY29tbWl0dGVkVmFsdWU/Ll8kdGVtcGxhdGUgPT09IHRlbXBsYXRlKSB7XG4gICAgICAgICAgICBkZWJ1Z0xvZ0V2ZW50ICYmXG4gICAgICAgICAgICAgICAgZGVidWdMb2dFdmVudCh7XG4gICAgICAgICAgICAgICAgICAgIGtpbmQ6ICd0ZW1wbGF0ZSB1cGRhdGluZycsXG4gICAgICAgICAgICAgICAgICAgIHRlbXBsYXRlLFxuICAgICAgICAgICAgICAgICAgICBpbnN0YW5jZTogdGhpcy5fJGNvbW1pdHRlZFZhbHVlLFxuICAgICAgICAgICAgICAgICAgICBwYXJ0czogdGhpcy5fJGNvbW1pdHRlZFZhbHVlLl8kcGFydHMsXG4gICAgICAgICAgICAgICAgICAgIG9wdGlvbnM6IHRoaXMub3B0aW9ucyxcbiAgICAgICAgICAgICAgICAgICAgdmFsdWVzLFxuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgdGhpcy5fJGNvbW1pdHRlZFZhbHVlLl91cGRhdGUodmFsdWVzKTtcbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgIGNvbnN0IGluc3RhbmNlID0gbmV3IFRlbXBsYXRlSW5zdGFuY2UodGVtcGxhdGUsIHRoaXMpO1xuICAgICAgICAgICAgY29uc3QgZnJhZ21lbnQgPSBpbnN0YW5jZS5fY2xvbmUodGhpcy5vcHRpb25zKTtcbiAgICAgICAgICAgIGRlYnVnTG9nRXZlbnQgJiZcbiAgICAgICAgICAgICAgICBkZWJ1Z0xvZ0V2ZW50KHtcbiAgICAgICAgICAgICAgICAgICAga2luZDogJ3RlbXBsYXRlIGluc3RhbnRpYXRlZCcsXG4gICAgICAgICAgICAgICAgICAgIHRlbXBsYXRlLFxuICAgICAgICAgICAgICAgICAgICBpbnN0YW5jZSxcbiAgICAgICAgICAgICAgICAgICAgcGFydHM6IGluc3RhbmNlLl8kcGFydHMsXG4gICAgICAgICAgICAgICAgICAgIG9wdGlvbnM6IHRoaXMub3B0aW9ucyxcbiAgICAgICAgICAgICAgICAgICAgZnJhZ21lbnQsXG4gICAgICAgICAgICAgICAgICAgIHZhbHVlcyxcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIGluc3RhbmNlLl91cGRhdGUodmFsdWVzKTtcbiAgICAgICAgICAgIGRlYnVnTG9nRXZlbnQgJiZcbiAgICAgICAgICAgICAgICBkZWJ1Z0xvZ0V2ZW50KHtcbiAgICAgICAgICAgICAgICAgICAga2luZDogJ3RlbXBsYXRlIGluc3RhbnRpYXRlZCBhbmQgdXBkYXRlZCcsXG4gICAgICAgICAgICAgICAgICAgIHRlbXBsYXRlLFxuICAgICAgICAgICAgICAgICAgICBpbnN0YW5jZSxcbiAgICAgICAgICAgICAgICAgICAgcGFydHM6IGluc3RhbmNlLl8kcGFydHMsXG4gICAgICAgICAgICAgICAgICAgIG9wdGlvbnM6IHRoaXMub3B0aW9ucyxcbiAgICAgICAgICAgICAgICAgICAgZnJhZ21lbnQsXG4gICAgICAgICAgICAgICAgICAgIHZhbHVlcyxcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIHRoaXMuX2NvbW1pdE5vZGUoZnJhZ21lbnQpO1xuICAgICAgICAgICAgdGhpcy5fJGNvbW1pdHRlZFZhbHVlID0gaW5zdGFuY2U7XG4gICAgICAgIH1cbiAgICB9XG4gICAgLy8gT3ZlcnJpZGRlbiB2aWEgYGxpdEh0bWxQb2x5ZmlsbFN1cHBvcnRgIHRvIHByb3ZpZGUgcGxhdGZvcm0gc3VwcG9ydC5cbiAgICAvKiogQGludGVybmFsICovXG4gICAgXyRnZXRUZW1wbGF0ZShyZXN1bHQpIHtcbiAgICAgICAgbGV0IHRlbXBsYXRlID0gdGVtcGxhdGVDYWNoZS5nZXQocmVzdWx0LnN0cmluZ3MpO1xuICAgICAgICBpZiAodGVtcGxhdGUgPT09IHVuZGVmaW5lZCkge1xuICAgICAgICAgICAgdGVtcGxhdGVDYWNoZS5zZXQocmVzdWx0LnN0cmluZ3MsICh0ZW1wbGF0ZSA9IG5ldyBUZW1wbGF0ZShyZXN1bHQpKSk7XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuIHRlbXBsYXRlO1xuICAgIH1cbiAgICBfY29tbWl0SXRlcmFibGUodmFsdWUpIHtcbiAgICAgICAgLy8gRm9yIGFuIEl0ZXJhYmxlLCB3ZSBjcmVhdGUgYSBuZXcgSW5zdGFuY2VQYXJ0IHBlciBpdGVtLCB0aGVuIHNldCBpdHNcbiAgICAgICAgLy8gdmFsdWUgdG8gdGhlIGl0ZW0uIFRoaXMgaXMgYSBsaXR0bGUgYml0IG9mIG92ZXJoZWFkIGZvciBldmVyeSBpdGVtIGluXG4gICAgICAgIC8vIGFuIEl0ZXJhYmxlLCBidXQgaXQgbGV0cyB1cyByZWN1cnNlIGVhc2lseSBhbmQgZWZmaWNpZW50bHkgdXBkYXRlIEFycmF5c1xuICAgICAgICAvLyBvZiBUZW1wbGF0ZVJlc3VsdHMgdGhhdCB3aWxsIGJlIGNvbW1vbmx5IHJldHVybmVkIGZyb20gZXhwcmVzc2lvbnMgbGlrZTpcbiAgICAgICAgLy8gYXJyYXkubWFwKChpKSA9PiBodG1sYCR7aX1gKSwgYnkgcmV1c2luZyBleGlzdGluZyBUZW1wbGF0ZUluc3RhbmNlcy5cbiAgICAgICAgLy8gSWYgdmFsdWUgaXMgYW4gYXJyYXksIHRoZW4gdGhlIHByZXZpb3VzIHJlbmRlciB3YXMgb2YgYW5cbiAgICAgICAgLy8gaXRlcmFibGUgYW5kIHZhbHVlIHdpbGwgY29udGFpbiB0aGUgQ2hpbGRQYXJ0cyBmcm9tIHRoZSBwcmV2aW91c1xuICAgICAgICAvLyByZW5kZXIuIElmIHZhbHVlIGlzIG5vdCBhbiBhcnJheSwgY2xlYXIgdGhpcyBwYXJ0IGFuZCBtYWtlIGEgbmV3XG4gICAgICAgIC8vIGFycmF5IGZvciBDaGlsZFBhcnRzLlxuICAgICAgICBpZiAoIWlzQXJyYXkodGhpcy5fJGNvbW1pdHRlZFZhbHVlKSkge1xuICAgICAgICAgICAgdGhpcy5fJGNvbW1pdHRlZFZhbHVlID0gW107XG4gICAgICAgICAgICB0aGlzLl8kY2xlYXIoKTtcbiAgICAgICAgfVxuICAgICAgICAvLyBMZXRzIHVzIGtlZXAgdHJhY2sgb2YgaG93IG1hbnkgaXRlbXMgd2Ugc3RhbXBlZCBzbyB3ZSBjYW4gY2xlYXIgbGVmdG92ZXJcbiAgICAgICAgLy8gaXRlbXMgZnJvbSBhIHByZXZpb3VzIHJlbmRlclxuICAgICAgICBjb25zdCBpdGVtUGFydHMgPSB0aGlzLl8kY29tbWl0dGVkVmFsdWU7XG4gICAgICAgIGxldCBwYXJ0SW5kZXggPSAwO1xuICAgICAgICBsZXQgaXRlbVBhcnQ7XG4gICAgICAgIGZvciAoY29uc3QgaXRlbSBvZiB2YWx1ZSkge1xuICAgICAgICAgICAgaWYgKHBhcnRJbmRleCA9PT0gaXRlbVBhcnRzLmxlbmd0aCkge1xuICAgICAgICAgICAgICAgIC8vIElmIG5vIGV4aXN0aW5nIHBhcnQsIGNyZWF0ZSBhIG5ldyBvbmVcbiAgICAgICAgICAgICAgICAvLyBUT0RPIChqdXN0aW5mYWduYW5pKTogdGVzdCBwZXJmIGltcGFjdCBvZiBhbHdheXMgY3JlYXRpbmcgdHdvIHBhcnRzXG4gICAgICAgICAgICAgICAgLy8gaW5zdGVhZCBvZiBzaGFyaW5nIHBhcnRzIGJldHdlZW4gbm9kZXNcbiAgICAgICAgICAgICAgICAvLyBodHRwczovL2dpdGh1Yi5jb20vbGl0L2xpdC9pc3N1ZXMvMTI2NlxuICAgICAgICAgICAgICAgIGl0ZW1QYXJ0cy5wdXNoKChpdGVtUGFydCA9IG5ldyBDaGlsZFBhcnQodGhpcy5faW5zZXJ0KGNyZWF0ZU1hcmtlcigpKSwgdGhpcy5faW5zZXJ0KGNyZWF0ZU1hcmtlcigpKSwgdGhpcywgdGhpcy5vcHRpb25zKSkpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICAgICAgLy8gUmV1c2UgYW4gZXhpc3RpbmcgcGFydFxuICAgICAgICAgICAgICAgIGl0ZW1QYXJ0ID0gaXRlbVBhcnRzW3BhcnRJbmRleF07XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBpdGVtUGFydC5fJHNldFZhbHVlKGl0ZW0pO1xuICAgICAgICAgICAgcGFydEluZGV4Kys7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKHBhcnRJbmRleCA8IGl0ZW1QYXJ0cy5sZW5ndGgpIHtcbiAgICAgICAgICAgIC8vIGl0ZW1QYXJ0cyBhbHdheXMgaGF2ZSBlbmQgbm9kZXNcbiAgICAgICAgICAgIHRoaXMuXyRjbGVhcihpdGVtUGFydCAmJiB3cmFwKGl0ZW1QYXJ0Ll8kZW5kTm9kZSkubmV4dFNpYmxpbmcsIHBhcnRJbmRleCk7XG4gICAgICAgICAgICAvLyBUcnVuY2F0ZSB0aGUgcGFydHMgYXJyYXkgc28gX3ZhbHVlIHJlZmxlY3RzIHRoZSBjdXJyZW50IHN0YXRlXG4gICAgICAgICAgICBpdGVtUGFydHMubGVuZ3RoID0gcGFydEluZGV4O1xuICAgICAgICB9XG4gICAgfVxuICAgIC8qKlxuICAgICAqIFJlbW92ZXMgdGhlIG5vZGVzIGNvbnRhaW5lZCB3aXRoaW4gdGhpcyBQYXJ0IGZyb20gdGhlIERPTS5cbiAgICAgKlxuICAgICAqIEBwYXJhbSBzdGFydCBTdGFydCBub2RlIHRvIGNsZWFyIGZyb20sIGZvciBjbGVhcmluZyBhIHN1YnNldCBvZiB0aGUgcGFydCdzXG4gICAgICogICAgIERPTSAodXNlZCB3aGVuIHRydW5jYXRpbmcgaXRlcmFibGVzKVxuICAgICAqIEBwYXJhbSBmcm9tICBXaGVuIGBzdGFydGAgaXMgc3BlY2lmaWVkLCB0aGUgaW5kZXggd2l0aGluIHRoZSBpdGVyYWJsZSBmcm9tXG4gICAgICogICAgIHdoaWNoIENoaWxkUGFydHMgYXJlIGJlaW5nIHJlbW92ZWQsIHVzZWQgZm9yIGRpc2Nvbm5lY3RpbmcgZGlyZWN0aXZlc1xuICAgICAqICAgICBpbiB0aG9zZSBQYXJ0cy5cbiAgICAgKlxuICAgICAqIEBpbnRlcm5hbFxuICAgICAqL1xuICAgIF8kY2xlYXIoc3RhcnQgPSB3cmFwKHRoaXMuXyRzdGFydE5vZGUpLm5leHRTaWJsaW5nLCBmcm9tKSB7XG4gICAgICAgIHRoaXMuXyRub3RpZnlDb25uZWN0aW9uQ2hhbmdlZD8uKGZhbHNlLCB0cnVlLCBmcm9tKTtcbiAgICAgICAgd2hpbGUgKHN0YXJ0ICE9PSB0aGlzLl8kZW5kTm9kZSkge1xuICAgICAgICAgICAgLy8gVGhlIG5vbi1udWxsIGFzc2VydGlvbiBpcyBzYWZlIGJlY2F1c2UgaWYgXyRzdGFydE5vZGUubmV4dFNpYmxpbmcgaXNcbiAgICAgICAgICAgIC8vIG51bGwsIHRoZW4gXyRlbmROb2RlIGlzIGFsc28gbnVsbCwgYW5kIHdlIHdvdWxkIG5vdCBoYXZlIGVudGVyZWQgdGhpc1xuICAgICAgICAgICAgLy8gbG9vcC5cbiAgICAgICAgICAgIGNvbnN0IG4gPSB3cmFwKHN0YXJ0KS5uZXh0U2libGluZztcbiAgICAgICAgICAgIHdyYXAoc3RhcnQpLnJlbW92ZSgpO1xuICAgICAgICAgICAgc3RhcnQgPSBuO1xuICAgICAgICB9XG4gICAgfVxuICAgIC8qKlxuICAgICAqIEltcGxlbWVudGF0aW9uIG9mIFJvb3RQYXJ0J3MgYGlzQ29ubmVjdGVkYC4gTm90ZSB0aGF0IHRoaXMgbWV0aG9kXG4gICAgICogc2hvdWxkIG9ubHkgYmUgY2FsbGVkIG9uIGBSb290UGFydGBzICh0aGUgYENoaWxkUGFydGAgcmV0dXJuZWQgZnJvbSBhXG4gICAgICogdG9wLWxldmVsIGByZW5kZXIoKWAgY2FsbCkuIEl0IGhhcyBubyBlZmZlY3Qgb24gbm9uLXJvb3QgQ2hpbGRQYXJ0cy5cbiAgICAgKiBAcGFyYW0gaXNDb25uZWN0ZWQgV2hldGhlciB0byBzZXRcbiAgICAgKiBAaW50ZXJuYWxcbiAgICAgKi9cbiAgICBzZXRDb25uZWN0ZWQoaXNDb25uZWN0ZWQpIHtcbiAgICAgICAgaWYgKHRoaXMuXyRwYXJlbnQgPT09IHVuZGVmaW5lZCkge1xuICAgICAgICAgICAgdGhpcy5fX2lzQ29ubmVjdGVkID0gaXNDb25uZWN0ZWQ7XG4gICAgICAgICAgICB0aGlzLl8kbm90aWZ5Q29ubmVjdGlvbkNoYW5nZWQ/Lihpc0Nvbm5lY3RlZCk7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSBpZiAoREVWX01PREUpIHtcbiAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcigncGFydC5zZXRDb25uZWN0ZWQoKSBtYXkgb25seSBiZSBjYWxsZWQgb24gYSAnICtcbiAgICAgICAgICAgICAgICAnUm9vdFBhcnQgcmV0dXJuZWQgZnJvbSByZW5kZXIoKS4nKTtcbiAgICAgICAgfVxuICAgIH1cbn1cbmNsYXNzIEF0dHJpYnV0ZVBhcnQge1xuICAgIGdldCB0YWdOYW1lKCkge1xuICAgICAgICByZXR1cm4gdGhpcy5lbGVtZW50LnRhZ05hbWU7XG4gICAgfVxuICAgIC8vIFNlZSBjb21tZW50IGluIERpc2Nvbm5lY3RhYmxlIGludGVyZmFjZSBmb3Igd2h5IHRoaXMgaXMgYSBnZXR0ZXJcbiAgICBnZXQgXyRpc0Nvbm5lY3RlZCgpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuXyRwYXJlbnQuXyRpc0Nvbm5lY3RlZDtcbiAgICB9XG4gICAgY29uc3RydWN0b3IoZWxlbWVudCwgbmFtZSwgc3RyaW5ncywgcGFyZW50LCBvcHRpb25zKSB7XG4gICAgICAgIHRoaXMudHlwZSA9IEFUVFJJQlVURV9QQVJUO1xuICAgICAgICAvKiogQGludGVybmFsICovXG4gICAgICAgIHRoaXMuXyRjb21taXR0ZWRWYWx1ZSA9IG5vdGhpbmc7XG4gICAgICAgIC8qKiBAaW50ZXJuYWwgKi9cbiAgICAgICAgdGhpcy5fJGRpc2Nvbm5lY3RhYmxlQ2hpbGRyZW4gPSB1bmRlZmluZWQ7XG4gICAgICAgIHRoaXMuZWxlbWVudCA9IGVsZW1lbnQ7XG4gICAgICAgIHRoaXMubmFtZSA9IG5hbWU7XG4gICAgICAgIHRoaXMuXyRwYXJlbnQgPSBwYXJlbnQ7XG4gICAgICAgIHRoaXMub3B0aW9ucyA9IG9wdGlvbnM7XG4gICAgICAgIGlmIChzdHJpbmdzLmxlbmd0aCA+IDIgfHwgc3RyaW5nc1swXSAhPT0gJycgfHwgc3RyaW5nc1sxXSAhPT0gJycpIHtcbiAgICAgICAgICAgIHRoaXMuXyRjb21taXR0ZWRWYWx1ZSA9IG5ldyBBcnJheShzdHJpbmdzLmxlbmd0aCAtIDEpLmZpbGwobmV3IFN0cmluZygpKTtcbiAgICAgICAgICAgIHRoaXMuc3RyaW5ncyA9IHN0cmluZ3M7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICB0aGlzLl8kY29tbWl0dGVkVmFsdWUgPSBub3RoaW5nO1xuICAgICAgICB9XG4gICAgICAgIGlmIChFTkFCTEVfRVhUUkFfU0VDVVJJVFlfSE9PS1MpIHtcbiAgICAgICAgICAgIHRoaXMuX3Nhbml0aXplciA9IHVuZGVmaW5lZDtcbiAgICAgICAgfVxuICAgIH1cbiAgICAvKipcbiAgICAgKiBTZXRzIHRoZSB2YWx1ZSBvZiB0aGlzIHBhcnQgYnkgcmVzb2x2aW5nIHRoZSB2YWx1ZSBmcm9tIHBvc3NpYmx5IG11bHRpcGxlXG4gICAgICogdmFsdWVzIGFuZCBzdGF0aWMgc3RyaW5ncyBhbmQgY29tbWl0dGluZyBpdCB0byB0aGUgRE9NLlxuICAgICAqIElmIHRoaXMgcGFydCBpcyBzaW5nbGUtdmFsdWVkLCBgdGhpcy5fc3RyaW5nc2Agd2lsbCBiZSB1bmRlZmluZWQsIGFuZCB0aGVcbiAgICAgKiBtZXRob2Qgd2lsbCBiZSBjYWxsZWQgd2l0aCBhIHNpbmdsZSB2YWx1ZSBhcmd1bWVudC4gSWYgdGhpcyBwYXJ0IGlzXG4gICAgICogbXVsdGktdmFsdWUsIGB0aGlzLl9zdHJpbmdzYCB3aWxsIGJlIGRlZmluZWQsIGFuZCB0aGUgbWV0aG9kIGlzIGNhbGxlZFxuICAgICAqIHdpdGggdGhlIHZhbHVlIGFycmF5IG9mIHRoZSBwYXJ0J3Mgb3duaW5nIFRlbXBsYXRlSW5zdGFuY2UsIGFuZCBhbiBvZmZzZXRcbiAgICAgKiBpbnRvIHRoZSB2YWx1ZSBhcnJheSBmcm9tIHdoaWNoIHRoZSB2YWx1ZXMgc2hvdWxkIGJlIHJlYWQuXG4gICAgICogVGhpcyBtZXRob2QgaXMgb3ZlcmxvYWRlZCB0aGlzIHdheSB0byBlbGltaW5hdGUgc2hvcnQtbGl2ZWQgYXJyYXkgc2xpY2VzXG4gICAgICogb2YgdGhlIHRlbXBsYXRlIGluc3RhbmNlIHZhbHVlcywgYW5kIGFsbG93IGEgZmFzdC1wYXRoIGZvciBzaW5nbGUtdmFsdWVkXG4gICAgICogcGFydHMuXG4gICAgICpcbiAgICAgKiBAcGFyYW0gdmFsdWUgVGhlIHBhcnQgdmFsdWUsIG9yIGFuIGFycmF5IG9mIHZhbHVlcyBmb3IgbXVsdGktdmFsdWVkIHBhcnRzXG4gICAgICogQHBhcmFtIHZhbHVlSW5kZXggdGhlIGluZGV4IHRvIHN0YXJ0IHJlYWRpbmcgdmFsdWVzIGZyb20uIGB1bmRlZmluZWRgIGZvclxuICAgICAqICAgc2luZ2xlLXZhbHVlZCBwYXJ0c1xuICAgICAqIEBwYXJhbSBub0NvbW1pdCBjYXVzZXMgdGhlIHBhcnQgdG8gbm90IGNvbW1pdCBpdHMgdmFsdWUgdG8gdGhlIERPTS4gVXNlZFxuICAgICAqICAgaW4gaHlkcmF0aW9uIHRvIHByaW1lIGF0dHJpYnV0ZSBwYXJ0cyB3aXRoIHRoZWlyIGZpcnN0LXJlbmRlcmVkIHZhbHVlLFxuICAgICAqICAgYnV0IG5vdCBzZXQgdGhlIGF0dHJpYnV0ZSwgYW5kIGluIFNTUiB0byBuby1vcCB0aGUgRE9NIG9wZXJhdGlvbiBhbmRcbiAgICAgKiAgIGNhcHR1cmUgdGhlIHZhbHVlIGZvciBzZXJpYWxpemF0aW9uLlxuICAgICAqXG4gICAgICogQGludGVybmFsXG4gICAgICovXG4gICAgXyRzZXRWYWx1ZSh2YWx1ZSwgZGlyZWN0aXZlUGFyZW50ID0gdGhpcywgdmFsdWVJbmRleCwgbm9Db21taXQpIHtcbiAgICAgICAgY29uc3Qgc3RyaW5ncyA9IHRoaXMuc3RyaW5ncztcbiAgICAgICAgLy8gV2hldGhlciBhbnkgb2YgdGhlIHZhbHVlcyBoYXMgY2hhbmdlZCwgZm9yIGRpcnR5LWNoZWNraW5nXG4gICAgICAgIGxldCBjaGFuZ2UgPSBmYWxzZTtcbiAgICAgICAgaWYgKHN0cmluZ3MgPT09IHVuZGVmaW5lZCkge1xuICAgICAgICAgICAgLy8gU2luZ2xlLXZhbHVlIGJpbmRpbmcgY2FzZVxuICAgICAgICAgICAgdmFsdWUgPSByZXNvbHZlRGlyZWN0aXZlKHRoaXMsIHZhbHVlLCBkaXJlY3RpdmVQYXJlbnQsIDApO1xuICAgICAgICAgICAgY2hhbmdlID1cbiAgICAgICAgICAgICAgICAhaXNQcmltaXRpdmUodmFsdWUpIHx8XG4gICAgICAgICAgICAgICAgICAgICh2YWx1ZSAhPT0gdGhpcy5fJGNvbW1pdHRlZFZhbHVlICYmIHZhbHVlICE9PSBub0NoYW5nZSk7XG4gICAgICAgICAgICBpZiAoY2hhbmdlKSB7XG4gICAgICAgICAgICAgICAgdGhpcy5fJGNvbW1pdHRlZFZhbHVlID0gdmFsdWU7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICAvLyBJbnRlcnBvbGF0aW9uIGNhc2VcbiAgICAgICAgICAgIGNvbnN0IHZhbHVlcyA9IHZhbHVlO1xuICAgICAgICAgICAgdmFsdWUgPSBzdHJpbmdzWzBdO1xuICAgICAgICAgICAgbGV0IGksIHY7XG4gICAgICAgICAgICBmb3IgKGkgPSAwOyBpIDwgc3RyaW5ncy5sZW5ndGggLSAxOyBpKyspIHtcbiAgICAgICAgICAgICAgICB2ID0gcmVzb2x2ZURpcmVjdGl2ZSh0aGlzLCB2YWx1ZXNbdmFsdWVJbmRleCArIGldLCBkaXJlY3RpdmVQYXJlbnQsIGkpO1xuICAgICAgICAgICAgICAgIGlmICh2ID09PSBub0NoYW5nZSkge1xuICAgICAgICAgICAgICAgICAgICAvLyBJZiB0aGUgdXNlci1wcm92aWRlZCB2YWx1ZSBpcyBgbm9DaGFuZ2VgLCB1c2UgdGhlIHByZXZpb3VzIHZhbHVlXG4gICAgICAgICAgICAgICAgICAgIHYgPSB0aGlzLl8kY29tbWl0dGVkVmFsdWVbaV07XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIGNoYW5nZSB8fD1cbiAgICAgICAgICAgICAgICAgICAgIWlzUHJpbWl0aXZlKHYpIHx8IHYgIT09IHRoaXMuXyRjb21taXR0ZWRWYWx1ZVtpXTtcbiAgICAgICAgICAgICAgICBpZiAodiA9PT0gbm90aGluZykge1xuICAgICAgICAgICAgICAgICAgICB2YWx1ZSA9IG5vdGhpbmc7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIGVsc2UgaWYgKHZhbHVlICE9PSBub3RoaW5nKSB7XG4gICAgICAgICAgICAgICAgICAgIHZhbHVlICs9ICh2ID8/ICcnKSArIHN0cmluZ3NbaSArIDFdO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAvLyBXZSBhbHdheXMgcmVjb3JkIGVhY2ggdmFsdWUsIGV2ZW4gaWYgb25lIGlzIGBub3RoaW5nYCwgZm9yIGZ1dHVyZVxuICAgICAgICAgICAgICAgIC8vIGNoYW5nZSBkZXRlY3Rpb24uXG4gICAgICAgICAgICAgICAgdGhpcy5fJGNvbW1pdHRlZFZhbHVlW2ldID0gdjtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICBpZiAoY2hhbmdlICYmICFub0NvbW1pdCkge1xuICAgICAgICAgICAgdGhpcy5fY29tbWl0VmFsdWUodmFsdWUpO1xuICAgICAgICB9XG4gICAgfVxuICAgIC8qKiBAaW50ZXJuYWwgKi9cbiAgICBfY29tbWl0VmFsdWUodmFsdWUpIHtcbiAgICAgICAgaWYgKHZhbHVlID09PSBub3RoaW5nKSB7XG4gICAgICAgICAgICB3cmFwKHRoaXMuZWxlbWVudCkucmVtb3ZlQXR0cmlidXRlKHRoaXMubmFtZSk7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICBpZiAoRU5BQkxFX0VYVFJBX1NFQ1VSSVRZX0hPT0tTKSB7XG4gICAgICAgICAgICAgICAgaWYgKHRoaXMuX3Nhbml0aXplciA9PT0gdW5kZWZpbmVkKSB7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuX3Nhbml0aXplciA9IHNhbml0aXplckZhY3RvcnlJbnRlcm5hbCh0aGlzLmVsZW1lbnQsIHRoaXMubmFtZSwgJ2F0dHJpYnV0ZScpO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB2YWx1ZSA9IHRoaXMuX3Nhbml0aXplcih2YWx1ZSA/PyAnJyk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBkZWJ1Z0xvZ0V2ZW50ICYmXG4gICAgICAgICAgICAgICAgZGVidWdMb2dFdmVudCh7XG4gICAgICAgICAgICAgICAgICAgIGtpbmQ6ICdjb21taXQgYXR0cmlidXRlJyxcbiAgICAgICAgICAgICAgICAgICAgZWxlbWVudDogdGhpcy5lbGVtZW50LFxuICAgICAgICAgICAgICAgICAgICBuYW1lOiB0aGlzLm5hbWUsXG4gICAgICAgICAgICAgICAgICAgIHZhbHVlLFxuICAgICAgICAgICAgICAgICAgICBvcHRpb25zOiB0aGlzLm9wdGlvbnMsXG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB3cmFwKHRoaXMuZWxlbWVudCkuc2V0QXR0cmlidXRlKHRoaXMubmFtZSwgKHZhbHVlID8/ICcnKSk7XG4gICAgICAgIH1cbiAgICB9XG59XG5jbGFzcyBQcm9wZXJ0eVBhcnQgZXh0ZW5kcyBBdHRyaWJ1dGVQYXJ0IHtcbiAgICBjb25zdHJ1Y3RvcigpIHtcbiAgICAgICAgc3VwZXIoLi4uYXJndW1lbnRzKTtcbiAgICAgICAgdGhpcy50eXBlID0gUFJPUEVSVFlfUEFSVDtcbiAgICB9XG4gICAgLyoqIEBpbnRlcm5hbCAqL1xuICAgIF9jb21taXRWYWx1ZSh2YWx1ZSkge1xuICAgICAgICBpZiAoRU5BQkxFX0VYVFJBX1NFQ1VSSVRZX0hPT0tTKSB7XG4gICAgICAgICAgICBpZiAodGhpcy5fc2FuaXRpemVyID09PSB1bmRlZmluZWQpIHtcbiAgICAgICAgICAgICAgICB0aGlzLl9zYW5pdGl6ZXIgPSBzYW5pdGl6ZXJGYWN0b3J5SW50ZXJuYWwodGhpcy5lbGVtZW50LCB0aGlzLm5hbWUsICdwcm9wZXJ0eScpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgdmFsdWUgPSB0aGlzLl9zYW5pdGl6ZXIodmFsdWUpO1xuICAgICAgICB9XG4gICAgICAgIGRlYnVnTG9nRXZlbnQgJiZcbiAgICAgICAgICAgIGRlYnVnTG9nRXZlbnQoe1xuICAgICAgICAgICAgICAgIGtpbmQ6ICdjb21taXQgcHJvcGVydHknLFxuICAgICAgICAgICAgICAgIGVsZW1lbnQ6IHRoaXMuZWxlbWVudCxcbiAgICAgICAgICAgICAgICBuYW1lOiB0aGlzLm5hbWUsXG4gICAgICAgICAgICAgICAgdmFsdWUsXG4gICAgICAgICAgICAgICAgb3B0aW9uczogdGhpcy5vcHRpb25zLFxuICAgICAgICAgICAgfSk7XG4gICAgICAgIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBAdHlwZXNjcmlwdC1lc2xpbnQvbm8tZXhwbGljaXQtYW55XG4gICAgICAgIHRoaXMuZWxlbWVudFt0aGlzLm5hbWVdID0gdmFsdWUgPT09IG5vdGhpbmcgPyB1bmRlZmluZWQgOiB2YWx1ZTtcbiAgICB9XG59XG5jbGFzcyBCb29sZWFuQXR0cmlidXRlUGFydCBleHRlbmRzIEF0dHJpYnV0ZVBhcnQge1xuICAgIGNvbnN0cnVjdG9yKCkge1xuICAgICAgICBzdXBlciguLi5hcmd1bWVudHMpO1xuICAgICAgICB0aGlzLnR5cGUgPSBCT09MRUFOX0FUVFJJQlVURV9QQVJUO1xuICAgIH1cbiAgICAvKiogQGludGVybmFsICovXG4gICAgX2NvbW1pdFZhbHVlKHZhbHVlKSB7XG4gICAgICAgIGRlYnVnTG9nRXZlbnQgJiZcbiAgICAgICAgICAgIGRlYnVnTG9nRXZlbnQoe1xuICAgICAgICAgICAgICAgIGtpbmQ6ICdjb21taXQgYm9vbGVhbiBhdHRyaWJ1dGUnLFxuICAgICAgICAgICAgICAgIGVsZW1lbnQ6IHRoaXMuZWxlbWVudCxcbiAgICAgICAgICAgICAgICBuYW1lOiB0aGlzLm5hbWUsXG4gICAgICAgICAgICAgICAgdmFsdWU6ICEhKHZhbHVlICYmIHZhbHVlICE9PSBub3RoaW5nKSxcbiAgICAgICAgICAgICAgICBvcHRpb25zOiB0aGlzLm9wdGlvbnMsXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgd3JhcCh0aGlzLmVsZW1lbnQpLnRvZ2dsZUF0dHJpYnV0ZSh0aGlzLm5hbWUsICEhdmFsdWUgJiYgdmFsdWUgIT09IG5vdGhpbmcpO1xuICAgIH1cbn1cbmNsYXNzIEV2ZW50UGFydCBleHRlbmRzIEF0dHJpYnV0ZVBhcnQge1xuICAgIGNvbnN0cnVjdG9yKGVsZW1lbnQsIG5hbWUsIHN0cmluZ3MsIHBhcmVudCwgb3B0aW9ucykge1xuICAgICAgICBzdXBlcihlbGVtZW50LCBuYW1lLCBzdHJpbmdzLCBwYXJlbnQsIG9wdGlvbnMpO1xuICAgICAgICB0aGlzLnR5cGUgPSBFVkVOVF9QQVJUO1xuICAgICAgICBpZiAoREVWX01PREUgJiYgdGhpcy5zdHJpbmdzICE9PSB1bmRlZmluZWQpIHtcbiAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcihgQSBcXGA8JHtlbGVtZW50LmxvY2FsTmFtZX0+XFxgIGhhcyBhIFxcYEAke25hbWV9PS4uLlxcYCBsaXN0ZW5lciB3aXRoIGAgK1xuICAgICAgICAgICAgICAgICdpbnZhbGlkIGNvbnRlbnQuIEV2ZW50IGxpc3RlbmVycyBpbiB0ZW1wbGF0ZXMgbXVzdCBoYXZlIGV4YWN0bHkgJyArXG4gICAgICAgICAgICAgICAgJ29uZSBleHByZXNzaW9uIGFuZCBubyBzdXJyb3VuZGluZyB0ZXh0LicpO1xuICAgICAgICB9XG4gICAgfVxuICAgIC8vIEV2ZW50UGFydCBkb2VzIG5vdCB1c2UgdGhlIGJhc2UgXyRzZXRWYWx1ZS9fcmVzb2x2ZVZhbHVlIGltcGxlbWVudGF0aW9uXG4gICAgLy8gc2luY2UgdGhlIGRpcnR5IGNoZWNraW5nIGlzIG1vcmUgY29tcGxleFxuICAgIC8qKiBAaW50ZXJuYWwgKi9cbiAgICBfJHNldFZhbHVlKG5ld0xpc3RlbmVyLCBkaXJlY3RpdmVQYXJlbnQgPSB0aGlzKSB7XG4gICAgICAgIG5ld0xpc3RlbmVyID1cbiAgICAgICAgICAgIHJlc29sdmVEaXJlY3RpdmUodGhpcywgbmV3TGlzdGVuZXIsIGRpcmVjdGl2ZVBhcmVudCwgMCkgPz8gbm90aGluZztcbiAgICAgICAgaWYgKG5ld0xpc3RlbmVyID09PSBub0NoYW5nZSkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG4gICAgICAgIGNvbnN0IG9sZExpc3RlbmVyID0gdGhpcy5fJGNvbW1pdHRlZFZhbHVlO1xuICAgICAgICAvLyBJZiB0aGUgbmV3IHZhbHVlIGlzIG5vdGhpbmcgb3IgYW55IG9wdGlvbnMgY2hhbmdlIHdlIGhhdmUgdG8gcmVtb3ZlIHRoZVxuICAgICAgICAvLyBwYXJ0IGFzIGEgbGlzdGVuZXIuXG4gICAgICAgIGNvbnN0IHNob3VsZFJlbW92ZUxpc3RlbmVyID0gKG5ld0xpc3RlbmVyID09PSBub3RoaW5nICYmIG9sZExpc3RlbmVyICE9PSBub3RoaW5nKSB8fFxuICAgICAgICAgICAgbmV3TGlzdGVuZXIuY2FwdHVyZSAhPT1cbiAgICAgICAgICAgICAgICBvbGRMaXN0ZW5lci5jYXB0dXJlIHx8XG4gICAgICAgICAgICBuZXdMaXN0ZW5lci5vbmNlICE9PVxuICAgICAgICAgICAgICAgIG9sZExpc3RlbmVyLm9uY2UgfHxcbiAgICAgICAgICAgIG5ld0xpc3RlbmVyLnBhc3NpdmUgIT09XG4gICAgICAgICAgICAgICAgb2xkTGlzdGVuZXIucGFzc2l2ZTtcbiAgICAgICAgLy8gSWYgdGhlIG5ldyB2YWx1ZSBpcyBub3Qgbm90aGluZyBhbmQgd2UgcmVtb3ZlZCB0aGUgbGlzdGVuZXIsIHdlIGhhdmVcbiAgICAgICAgLy8gdG8gYWRkIHRoZSBwYXJ0IGFzIGEgbGlzdGVuZXIuXG4gICAgICAgIGNvbnN0IHNob3VsZEFkZExpc3RlbmVyID0gbmV3TGlzdGVuZXIgIT09IG5vdGhpbmcgJiZcbiAgICAgICAgICAgIChvbGRMaXN0ZW5lciA9PT0gbm90aGluZyB8fCBzaG91bGRSZW1vdmVMaXN0ZW5lcik7XG4gICAgICAgIGRlYnVnTG9nRXZlbnQgJiZcbiAgICAgICAgICAgIGRlYnVnTG9nRXZlbnQoe1xuICAgICAgICAgICAgICAgIGtpbmQ6ICdjb21taXQgZXZlbnQgbGlzdGVuZXInLFxuICAgICAgICAgICAgICAgIGVsZW1lbnQ6IHRoaXMuZWxlbWVudCxcbiAgICAgICAgICAgICAgICBuYW1lOiB0aGlzLm5hbWUsXG4gICAgICAgICAgICAgICAgdmFsdWU6IG5ld0xpc3RlbmVyLFxuICAgICAgICAgICAgICAgIG9wdGlvbnM6IHRoaXMub3B0aW9ucyxcbiAgICAgICAgICAgICAgICByZW1vdmVMaXN0ZW5lcjogc2hvdWxkUmVtb3ZlTGlzdGVuZXIsXG4gICAgICAgICAgICAgICAgYWRkTGlzdGVuZXI6IHNob3VsZEFkZExpc3RlbmVyLFxuICAgICAgICAgICAgICAgIG9sZExpc3RlbmVyLFxuICAgICAgICAgICAgfSk7XG4gICAgICAgIGlmIChzaG91bGRSZW1vdmVMaXN0ZW5lcikge1xuICAgICAgICAgICAgdGhpcy5lbGVtZW50LnJlbW92ZUV2ZW50TGlzdGVuZXIodGhpcy5uYW1lLCB0aGlzLCBvbGRMaXN0ZW5lcik7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKHNob3VsZEFkZExpc3RlbmVyKSB7XG4gICAgICAgICAgICB0aGlzLmVsZW1lbnQuYWRkRXZlbnRMaXN0ZW5lcih0aGlzLm5hbWUsIHRoaXMsIG5ld0xpc3RlbmVyKTtcbiAgICAgICAgfVxuICAgICAgICB0aGlzLl8kY29tbWl0dGVkVmFsdWUgPSBuZXdMaXN0ZW5lcjtcbiAgICB9XG4gICAgaGFuZGxlRXZlbnQoZXZlbnQpIHtcbiAgICAgICAgaWYgKHR5cGVvZiB0aGlzLl8kY29tbWl0dGVkVmFsdWUgPT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgICAgIHRoaXMuXyRjb21taXR0ZWRWYWx1ZS5jYWxsKHRoaXMub3B0aW9ucz8uaG9zdCA/PyB0aGlzLmVsZW1lbnQsIGV2ZW50KTtcbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgIHRoaXMuXyRjb21taXR0ZWRWYWx1ZS5oYW5kbGVFdmVudChldmVudCk7XG4gICAgICAgIH1cbiAgICB9XG59XG5jbGFzcyBFbGVtZW50UGFydCB7XG4gICAgY29uc3RydWN0b3IoZWxlbWVudCwgcGFyZW50LCBvcHRpb25zKSB7XG4gICAgICAgIHRoaXMuZWxlbWVudCA9IGVsZW1lbnQ7XG4gICAgICAgIHRoaXMudHlwZSA9IEVMRU1FTlRfUEFSVDtcbiAgICAgICAgLyoqIEBpbnRlcm5hbCAqL1xuICAgICAgICB0aGlzLl8kZGlzY29ubmVjdGFibGVDaGlsZHJlbiA9IHVuZGVmaW5lZDtcbiAgICAgICAgdGhpcy5fJHBhcmVudCA9IHBhcmVudDtcbiAgICAgICAgdGhpcy5vcHRpb25zID0gb3B0aW9ucztcbiAgICB9XG4gICAgLy8gU2VlIGNvbW1lbnQgaW4gRGlzY29ubmVjdGFibGUgaW50ZXJmYWNlIGZvciB3aHkgdGhpcyBpcyBhIGdldHRlclxuICAgIGdldCBfJGlzQ29ubmVjdGVkKCkge1xuICAgICAgICByZXR1cm4gdGhpcy5fJHBhcmVudC5fJGlzQ29ubmVjdGVkO1xuICAgIH1cbiAgICBfJHNldFZhbHVlKHZhbHVlKSB7XG4gICAgICAgIGRlYnVnTG9nRXZlbnQgJiZcbiAgICAgICAgICAgIGRlYnVnTG9nRXZlbnQoe1xuICAgICAgICAgICAgICAgIGtpbmQ6ICdjb21taXQgdG8gZWxlbWVudCBiaW5kaW5nJyxcbiAgICAgICAgICAgICAgICBlbGVtZW50OiB0aGlzLmVsZW1lbnQsXG4gICAgICAgICAgICAgICAgdmFsdWUsXG4gICAgICAgICAgICAgICAgb3B0aW9uczogdGhpcy5vcHRpb25zLFxuICAgICAgICAgICAgfSk7XG4gICAgICAgIHJlc29sdmVEaXJlY3RpdmUodGhpcywgdmFsdWUpO1xuICAgIH1cbn1cbi8qKlxuICogRU5EIFVTRVJTIFNIT1VMRCBOT1QgUkVMWSBPTiBUSElTIE9CSkVDVC5cbiAqXG4gKiBQcml2YXRlIGV4cG9ydHMgZm9yIHVzZSBieSBvdGhlciBMaXQgcGFja2FnZXMsIG5vdCBpbnRlbmRlZCBmb3IgdXNlIGJ5XG4gKiBleHRlcm5hbCB1c2Vycy5cbiAqXG4gKiBXZSBjdXJyZW50bHkgZG8gbm90IG1ha2UgYSBtYW5nbGVkIHJvbGx1cCBidWlsZCBvZiB0aGUgbGl0LXNzciBjb2RlLiBJbiBvcmRlclxuICogdG8ga2VlcCBhIG51bWJlciBvZiAob3RoZXJ3aXNlIHByaXZhdGUpIHRvcC1sZXZlbCBleHBvcnRzIG1hbmdsZWQgaW4gdGhlXG4gKiBjbGllbnQgc2lkZSBjb2RlLCB3ZSBleHBvcnQgYSBfJExIIG9iamVjdCBjb250YWluaW5nIHRob3NlIG1lbWJlcnMgKG9yXG4gKiBoZWxwZXIgbWV0aG9kcyBmb3IgYWNjZXNzaW5nIHByaXZhdGUgZmllbGRzIG9mIHRob3NlIG1lbWJlcnMpLCBhbmQgdGhlblxuICogcmUtZXhwb3J0IHRoZW0gZm9yIHVzZSBpbiBsaXQtc3NyLiBUaGlzIGtlZXBzIGxpdC1zc3IgYWdub3N0aWMgdG8gd2hldGhlciB0aGVcbiAqIGNsaWVudC1zaWRlIGNvZGUgaXMgYmVpbmcgdXNlZCBpbiBgZGV2YCBtb2RlIG9yIGBwcm9kYCBtb2RlLlxuICpcbiAqIFRoaXMgaGFzIGEgdW5pcXVlIG5hbWUsIHRvIGRpc2FtYmlndWF0ZSBpdCBmcm9tIHByaXZhdGUgZXhwb3J0cyBpblxuICogbGl0LWVsZW1lbnQsIHdoaWNoIHJlLWV4cG9ydHMgYWxsIG9mIGxpdC1odG1sLlxuICpcbiAqIEBwcml2YXRlXG4gKi9cbmV4cG9ydCBjb25zdCBfJExIID0ge1xuICAgIC8vIFVzZWQgaW4gbGl0LXNzclxuICAgIF9ib3VuZEF0dHJpYnV0ZVN1ZmZpeDogYm91bmRBdHRyaWJ1dGVTdWZmaXgsXG4gICAgX21hcmtlcjogbWFya2VyLFxuICAgIF9tYXJrZXJNYXRjaDogbWFya2VyTWF0Y2gsXG4gICAgX0hUTUxfUkVTVUxUOiBIVE1MX1JFU1VMVCxcbiAgICBfZ2V0VGVtcGxhdGVIdG1sOiBnZXRUZW1wbGF0ZUh0bWwsXG4gICAgLy8gVXNlZCBpbiB0ZXN0cyBhbmQgcHJpdmF0ZS1zc3Itc3VwcG9ydFxuICAgIF9UZW1wbGF0ZUluc3RhbmNlOiBUZW1wbGF0ZUluc3RhbmNlLFxuICAgIF9pc0l0ZXJhYmxlOiBpc0l0ZXJhYmxlLFxuICAgIF9yZXNvbHZlRGlyZWN0aXZlOiByZXNvbHZlRGlyZWN0aXZlLFxuICAgIF9DaGlsZFBhcnQ6IENoaWxkUGFydCxcbiAgICBfQXR0cmlidXRlUGFydDogQXR0cmlidXRlUGFydCxcbiAgICBfQm9vbGVhbkF0dHJpYnV0ZVBhcnQ6IEJvb2xlYW5BdHRyaWJ1dGVQYXJ0LFxuICAgIF9FdmVudFBhcnQ6IEV2ZW50UGFydCxcbiAgICBfUHJvcGVydHlQYXJ0OiBQcm9wZXJ0eVBhcnQsXG4gICAgX0VsZW1lbnRQYXJ0OiBFbGVtZW50UGFydCxcbn07XG4vLyBBcHBseSBwb2x5ZmlsbHMgaWYgYXZhaWxhYmxlXG5jb25zdCBwb2x5ZmlsbFN1cHBvcnQgPSBERVZfTU9ERVxuICAgID8gZ2xvYmFsLmxpdEh0bWxQb2x5ZmlsbFN1cHBvcnREZXZNb2RlXG4gICAgOiBnbG9iYWwubGl0SHRtbFBvbHlmaWxsU3VwcG9ydDtcbnBvbHlmaWxsU3VwcG9ydD8uKFRlbXBsYXRlLCBDaGlsZFBhcnQpO1xuLy8gSU1QT1JUQU5UOiBkbyBub3QgY2hhbmdlIHRoZSBwcm9wZXJ0eSBuYW1lIG9yIHRoZSBhc3NpZ25tZW50IGV4cHJlc3Npb24uXG4vLyBUaGlzIGxpbmUgd2lsbCBiZSB1c2VkIGluIHJlZ2V4ZXMgdG8gc2VhcmNoIGZvciBsaXQtaHRtbCB1c2FnZS5cbihnbG9iYWwubGl0SHRtbFZlcnNpb25zID8/PSBbXSkucHVzaCgnMy4zLjInKTtcbmlmIChERVZfTU9ERSAmJiBnbG9iYWwubGl0SHRtbFZlcnNpb25zLmxlbmd0aCA+IDEpIHtcbiAgICBxdWV1ZU1pY3JvdGFzaygoKSA9PiB7XG4gICAgICAgIGlzc3VlV2FybmluZygnbXVsdGlwbGUtdmVyc2lvbnMnLCBgTXVsdGlwbGUgdmVyc2lvbnMgb2YgTGl0IGxvYWRlZC4gYCArXG4gICAgICAgICAgICBgTG9hZGluZyBtdWx0aXBsZSB2ZXJzaW9ucyBpcyBub3QgcmVjb21tZW5kZWQuYCk7XG4gICAgfSk7XG59XG4vKipcbiAqIFJlbmRlcnMgYSB2YWx1ZSwgdXN1YWxseSBhIGxpdC1odG1sIFRlbXBsYXRlUmVzdWx0LCB0byB0aGUgY29udGFpbmVyLlxuICpcbiAqIFRoaXMgZXhhbXBsZSByZW5kZXJzIHRoZSB0ZXh0IFwiSGVsbG8sIFpvZSFcIiBpbnNpZGUgYSBwYXJhZ3JhcGggdGFnLCBhcHBlbmRpbmdcbiAqIGl0IHRvIHRoZSBjb250YWluZXIgYGRvY3VtZW50LmJvZHlgLlxuICpcbiAqIGBgYGpzXG4gKiBpbXBvcnQge2h0bWwsIHJlbmRlcn0gZnJvbSAnbGl0JztcbiAqXG4gKiBjb25zdCBuYW1lID0gXCJab2VcIjtcbiAqIHJlbmRlcihodG1sYDxwPkhlbGxvLCAke25hbWV9ITwvcD5gLCBkb2N1bWVudC5ib2R5KTtcbiAqIGBgYFxuICpcbiAqIEBwYXJhbSB2YWx1ZSBBbnkgW3JlbmRlcmFibGVcbiAqICAgdmFsdWVdKGh0dHBzOi8vbGl0LmRldi9kb2NzL3RlbXBsYXRlcy9leHByZXNzaW9ucy8jY2hpbGQtZXhwcmVzc2lvbnMpLFxuICogICB0eXBpY2FsbHkgYSB7QGxpbmtjb2RlIFRlbXBsYXRlUmVzdWx0fSBjcmVhdGVkIGJ5IGV2YWx1YXRpbmcgYSB0ZW1wbGF0ZSB0YWdcbiAqICAgbGlrZSB7QGxpbmtjb2RlIGh0bWx9IG9yIHtAbGlua2NvZGUgc3ZnfS5cbiAqIEBwYXJhbSBjb250YWluZXIgQSBET00gY29udGFpbmVyIHRvIHJlbmRlciB0by4gVGhlIGZpcnN0IHJlbmRlciB3aWxsIGFwcGVuZFxuICogICB0aGUgcmVuZGVyZWQgdmFsdWUgdG8gdGhlIGNvbnRhaW5lciwgYW5kIHN1YnNlcXVlbnQgcmVuZGVycyB3aWxsXG4gKiAgIGVmZmljaWVudGx5IHVwZGF0ZSB0aGUgcmVuZGVyZWQgdmFsdWUgaWYgdGhlIHNhbWUgcmVzdWx0IHR5cGUgd2FzXG4gKiAgIHByZXZpb3VzbHkgcmVuZGVyZWQgdGhlcmUuXG4gKiBAcGFyYW0gb3B0aW9ucyBTZWUge0BsaW5rY29kZSBSZW5kZXJPcHRpb25zfSBmb3Igb3B0aW9ucyBkb2N1bWVudGF0aW9uLlxuICogQHNlZVxuICoge0BsaW5rIGh0dHBzOi8vbGl0LmRldi9kb2NzL2xpYnJhcmllcy9zdGFuZGFsb25lLXRlbXBsYXRlcy8jcmVuZGVyaW5nLWxpdC1odG1sLXRlbXBsYXRlc3wgUmVuZGVyaW5nIExpdCBIVE1MIFRlbXBsYXRlc31cbiAqL1xuZXhwb3J0IGNvbnN0IHJlbmRlciA9ICh2YWx1ZSwgY29udGFpbmVyLCBvcHRpb25zKSA9PiB7XG4gICAgaWYgKERFVl9NT0RFICYmIGNvbnRhaW5lciA9PSBudWxsKSB7XG4gICAgICAgIC8vIEdpdmUgYSBjbGVhcmVyIGVycm9yIG1lc3NhZ2UgdGhhblxuICAgICAgICAvLyAgICAgVW5jYXVnaHQgVHlwZUVycm9yOiBDYW5ub3QgcmVhZCBwcm9wZXJ0aWVzIG9mIG51bGwgKHJlYWRpbmdcbiAgICAgICAgLy8gICAgICdfJGxpdFBhcnQkJylcbiAgICAgICAgLy8gd2hpY2ggcmVhZHMgbGlrZSBhbiBpbnRlcm5hbCBMaXQgZXJyb3IuXG4gICAgICAgIHRocm93IG5ldyBUeXBlRXJyb3IoYFRoZSBjb250YWluZXIgdG8gcmVuZGVyIGludG8gbWF5IG5vdCBiZSAke2NvbnRhaW5lcn1gKTtcbiAgICB9XG4gICAgY29uc3QgcmVuZGVySWQgPSBERVZfTU9ERSA/IGRlYnVnTG9nUmVuZGVySWQrKyA6IDA7XG4gICAgY29uc3QgcGFydE93bmVyTm9kZSA9IG9wdGlvbnM/LnJlbmRlckJlZm9yZSA/PyBjb250YWluZXI7XG4gICAgLy8gVGhpcyBwcm9wZXJ0eSBuZWVkcyB0byByZW1haW4gdW5taW5pZmllZC5cbiAgICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgQHR5cGVzY3JpcHQtZXNsaW50L25vLWV4cGxpY2l0LWFueVxuICAgIGxldCBwYXJ0ID0gcGFydE93bmVyTm9kZVsnXyRsaXRQYXJ0JCddO1xuICAgIGRlYnVnTG9nRXZlbnQgJiZcbiAgICAgICAgZGVidWdMb2dFdmVudCh7XG4gICAgICAgICAgICBraW5kOiAnYmVnaW4gcmVuZGVyJyxcbiAgICAgICAgICAgIGlkOiByZW5kZXJJZCxcbiAgICAgICAgICAgIHZhbHVlLFxuICAgICAgICAgICAgY29udGFpbmVyLFxuICAgICAgICAgICAgb3B0aW9ucyxcbiAgICAgICAgICAgIHBhcnQsXG4gICAgICAgIH0pO1xuICAgIGlmIChwYXJ0ID09PSB1bmRlZmluZWQpIHtcbiAgICAgICAgY29uc3QgZW5kTm9kZSA9IG9wdGlvbnM/LnJlbmRlckJlZm9yZSA/PyBudWxsO1xuICAgICAgICAvLyBUaGlzIHByb3BlcnR5IG5lZWRzIHRvIHJlbWFpbiB1bm1pbmlmaWVkLlxuICAgICAgICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgQHR5cGVzY3JpcHQtZXNsaW50L25vLWV4cGxpY2l0LWFueVxuICAgICAgICBwYXJ0T3duZXJOb2RlWydfJGxpdFBhcnQkJ10gPSBwYXJ0ID0gbmV3IENoaWxkUGFydChjb250YWluZXIuaW5zZXJ0QmVmb3JlKGNyZWF0ZU1hcmtlcigpLCBlbmROb2RlKSwgZW5kTm9kZSwgdW5kZWZpbmVkLCBvcHRpb25zID8/IHt9KTtcbiAgICB9XG4gICAgcGFydC5fJHNldFZhbHVlKHZhbHVlKTtcbiAgICBkZWJ1Z0xvZ0V2ZW50ICYmXG4gICAgICAgIGRlYnVnTG9nRXZlbnQoe1xuICAgICAgICAgICAga2luZDogJ2VuZCByZW5kZXInLFxuICAgICAgICAgICAgaWQ6IHJlbmRlcklkLFxuICAgICAgICAgICAgdmFsdWUsXG4gICAgICAgICAgICBjb250YWluZXIsXG4gICAgICAgICAgICBvcHRpb25zLFxuICAgICAgICAgICAgcGFydCxcbiAgICAgICAgfSk7XG4gICAgcmV0dXJuIHBhcnQ7XG59O1xuaWYgKEVOQUJMRV9FWFRSQV9TRUNVUklUWV9IT09LUykge1xuICAgIHJlbmRlci5zZXRTYW5pdGl6ZXIgPSBzZXRTYW5pdGl6ZXI7XG4gICAgcmVuZGVyLmNyZWF0ZVNhbml0aXplciA9IGNyZWF0ZVNhbml0aXplcjtcbiAgICBpZiAoREVWX01PREUpIHtcbiAgICAgICAgcmVuZGVyLl90ZXN0T25seUNsZWFyU2FuaXRpemVyRmFjdG9yeURvTm90Q2FsbE9yRWxzZSA9XG4gICAgICAgICAgICBfdGVzdE9ubHlDbGVhclNhbml0aXplckZhY3RvcnlEb05vdENhbGxPckVsc2U7XG4gICAgfVxufVxuLy8jIHNvdXJjZU1hcHBpbmdVUkw9bGl0LWh0bWwuanMubWFwIiwiLy8gVGhlIG1vZHVsZSBjYWNoZVxudmFyIF9fd2VicGFja19tb2R1bGVfY2FjaGVfXyA9IHt9O1xuXG4vLyBUaGUgcmVxdWlyZSBmdW5jdGlvblxuZnVuY3Rpb24gX193ZWJwYWNrX3JlcXVpcmVfXyhtb2R1bGVJZCkge1xuXHQvLyBDaGVjayBpZiBtb2R1bGUgaXMgaW4gY2FjaGVcblx0dmFyIGNhY2hlZE1vZHVsZSA9IF9fd2VicGFja19tb2R1bGVfY2FjaGVfX1ttb2R1bGVJZF07XG5cdGlmIChjYWNoZWRNb2R1bGUgIT09IHVuZGVmaW5lZCkge1xuXHRcdHJldHVybiBjYWNoZWRNb2R1bGUuZXhwb3J0cztcblx0fVxuXHQvLyBDaGVjayBpZiBtb2R1bGUgZXhpc3RzIChkZXZlbG9wbWVudCBvbmx5KVxuXHRpZiAoX193ZWJwYWNrX21vZHVsZXNfX1ttb2R1bGVJZF0gPT09IHVuZGVmaW5lZCkge1xuXHRcdHZhciBlID0gbmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIiArIG1vZHVsZUlkICsgXCInXCIpO1xuXHRcdGUuY29kZSA9ICdNT0RVTEVfTk9UX0ZPVU5EJztcblx0XHR0aHJvdyBlO1xuXHR9XG5cdC8vIENyZWF0ZSBhIG5ldyBtb2R1bGUgKGFuZCBwdXQgaXQgaW50byB0aGUgY2FjaGUpXG5cdHZhciBtb2R1bGUgPSBfX3dlYnBhY2tfbW9kdWxlX2NhY2hlX19bbW9kdWxlSWRdID0ge1xuXHRcdC8vIG5vIG1vZHVsZS5pZCBuZWVkZWRcblx0XHQvLyBubyBtb2R1bGUubG9hZGVkIG5lZWRlZFxuXHRcdGV4cG9ydHM6IHt9XG5cdH07XG5cblx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG5cdF9fd2VicGFja19tb2R1bGVzX19bbW9kdWxlSWRdKG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG5cdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG5cdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbn1cblxuIiwidmFyIGhhc1N5bWJvbCA9IHR5cGVvZiBTeW1ib2wgPT09IFwiZnVuY3Rpb25cIjtcbnZhciB3ZWJwYWNrUXVldWVzID0gaGFzU3ltYm9sID8gU3ltYm9sKFwid2VicGFjayBxdWV1ZXNcIikgOiBcIl9fd2VicGFja19xdWV1ZXNfX1wiO1xudmFyIHdlYnBhY2tFeHBvcnRzID0gaGFzU3ltYm9sID8gU3ltYm9sKFwid2VicGFjayBleHBvcnRzXCIpIDogXCJfX3dlYnBhY2tfZXhwb3J0c19fXCI7XG52YXIgd2VicGFja0Vycm9yID0gaGFzU3ltYm9sID8gU3ltYm9sKFwid2VicGFjayBlcnJvclwiKSA6IFwiX193ZWJwYWNrX2Vycm9yX19cIjtcblxudmFyIHJlc29sdmVRdWV1ZSA9IChxdWV1ZSkgPT4ge1xuXHRpZihxdWV1ZSAmJiBxdWV1ZS5kIDwgMSkge1xuXHRcdHF1ZXVlLmQgPSAxO1xuXHRcdHF1ZXVlLmZvckVhY2goKGZuKSA9PiAoZm4uci0tKSk7XG5cdFx0cXVldWUuZm9yRWFjaCgoZm4pID0+IChmbi5yLS0gPyBmbi5yKysgOiBmbigpKSk7XG5cdH1cbn1cbnZhciB3cmFwRGVwcyA9IChkZXBzKSA9PiAoZGVwcy5tYXAoKGRlcCkgPT4ge1xuXHRpZihkZXAgIT09IG51bGwgJiYgdHlwZW9mIGRlcCA9PT0gXCJvYmplY3RcIikge1xuXG5cdFx0aWYoZGVwW3dlYnBhY2tRdWV1ZXNdKSByZXR1cm4gZGVwO1xuXHRcdGlmKGRlcC50aGVuKSB7XG5cdFx0XHR2YXIgcXVldWUgPSBbXTtcblx0XHRcdHF1ZXVlLmQgPSAwO1xuXHRcdFx0ZGVwLnRoZW4oKHIpID0+IHtcblx0XHRcdFx0b2JqW3dlYnBhY2tFeHBvcnRzXSA9IHI7XG5cdFx0XHRcdHJlc29sdmVRdWV1ZShxdWV1ZSk7XG5cdFx0XHR9LCAoZSkgPT4ge1xuXHRcdFx0XHRvYmpbd2VicGFja0Vycm9yXSA9IGU7XG5cdFx0XHRcdHJlc29sdmVRdWV1ZShxdWV1ZSk7XG5cdFx0XHR9KTtcblx0XHRcdHZhciBvYmogPSB7fTtcblxuXHRcdFx0b2JqW3dlYnBhY2tRdWV1ZXNdID0gKGZuKSA9PiAoZm4ocXVldWUpKTtcblx0XHRcdHJldHVybiBvYmo7XG5cdFx0fVxuXHR9XG5cdHZhciByZXQgPSB7fTtcblx0cmV0W3dlYnBhY2tRdWV1ZXNdID0geCA9PiB7fTtcblx0cmV0W3dlYnBhY2tFeHBvcnRzXSA9IGRlcDtcblx0cmV0dXJuIHJldDtcbn0pKTtcbl9fd2VicGFja19yZXF1aXJlX18uYSA9IChtb2R1bGUsIGJvZHksIGhhc0F3YWl0KSA9PiB7XG5cdHZhciBxdWV1ZTtcblx0aGFzQXdhaXQgJiYgKChxdWV1ZSA9IFtdKS5kID0gLTEpO1xuXHR2YXIgZGVwUXVldWVzID0gbmV3IFNldCgpO1xuXHR2YXIgZXhwb3J0cyA9IG1vZHVsZS5leHBvcnRzO1xuXHR2YXIgY3VycmVudERlcHM7XG5cdHZhciBvdXRlclJlc29sdmU7XG5cdHZhciByZWplY3Q7XG5cdHZhciBwcm9taXNlID0gbmV3IFByb21pc2UoKHJlc29sdmUsIHJlaikgPT4ge1xuXHRcdHJlamVjdCA9IHJlajtcblx0XHRvdXRlclJlc29sdmUgPSByZXNvbHZlO1xuXHR9KTtcblx0cHJvbWlzZVt3ZWJwYWNrRXhwb3J0c10gPSBleHBvcnRzO1xuXHRwcm9taXNlW3dlYnBhY2tRdWV1ZXNdID0gKGZuKSA9PiAocXVldWUgJiYgZm4ocXVldWUpLCBkZXBRdWV1ZXMuZm9yRWFjaChmbiksIHByb21pc2VbXCJjYXRjaFwiXSh4ID0+IHt9KSk7XG5cdG1vZHVsZS5leHBvcnRzID0gcHJvbWlzZTtcblx0dmFyIGhhbmRsZSA9IChkZXBzKSA9PiB7XG5cdFx0Y3VycmVudERlcHMgPSB3cmFwRGVwcyhkZXBzKTtcblx0XHR2YXIgZm47XG5cdFx0dmFyIGdldFJlc3VsdCA9ICgpID0+IChjdXJyZW50RGVwcy5tYXAoKGQpID0+IHtcblxuXHRcdFx0aWYoZFt3ZWJwYWNrRXJyb3JdKSB0aHJvdyBkW3dlYnBhY2tFcnJvcl07XG5cdFx0XHRyZXR1cm4gZFt3ZWJwYWNrRXhwb3J0c107XG5cdFx0fSkpXG5cdFx0dmFyIHByb21pc2UgPSBuZXcgUHJvbWlzZSgocmVzb2x2ZSkgPT4ge1xuXHRcdFx0Zm4gPSAoKSA9PiAocmVzb2x2ZShnZXRSZXN1bHQpKTtcblx0XHRcdGZuLnIgPSAwO1xuXHRcdFx0dmFyIGZuUXVldWUgPSAocSkgPT4gKHEgIT09IHF1ZXVlICYmICFkZXBRdWV1ZXMuaGFzKHEpICYmIChkZXBRdWV1ZXMuYWRkKHEpLCBxICYmICFxLmQgJiYgKGZuLnIrKywgcS5wdXNoKGZuKSkpKTtcblx0XHRcdGN1cnJlbnREZXBzLm1hcCgoZGVwKSA9PiAoZGVwW3dlYnBhY2tRdWV1ZXNdKGZuUXVldWUpKSk7XG5cdFx0fSk7XG5cdFx0cmV0dXJuIGZuLnIgPyBwcm9taXNlIDogZ2V0UmVzdWx0KCk7XG5cdH1cblx0dmFyIGRvbmUgPSAoZXJyKSA9PiAoKGVyciA/IHJlamVjdChwcm9taXNlW3dlYnBhY2tFcnJvcl0gPSBlcnIpIDogb3V0ZXJSZXNvbHZlKGV4cG9ydHMpKSwgcmVzb2x2ZVF1ZXVlKHF1ZXVlKSlcblx0Ym9keShoYW5kbGUsIGRvbmUpO1xuXHRxdWV1ZSAmJiBxdWV1ZS5kIDwgMCAmJiAocXVldWUuZCA9IDApO1xufTsiLCIvLyBkZWZpbmUgZ2V0dGVyIGZ1bmN0aW9ucyBmb3IgaGFybW9ueSBleHBvcnRzXG5fX3dlYnBhY2tfcmVxdWlyZV9fLmQgPSAoZXhwb3J0cywgZGVmaW5pdGlvbikgPT4ge1xuXHRmb3IodmFyIGtleSBpbiBkZWZpbml0aW9uKSB7XG5cdFx0aWYoX193ZWJwYWNrX3JlcXVpcmVfXy5vKGRlZmluaXRpb24sIGtleSkgJiYgIV9fd2VicGFja19yZXF1aXJlX18ubyhleHBvcnRzLCBrZXkpKSB7XG5cdFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywga2V5LCB7IGVudW1lcmFibGU6IHRydWUsIGdldDogZGVmaW5pdGlvbltrZXldIH0pO1xuXHRcdH1cblx0fVxufTsiLCJfX3dlYnBhY2tfcmVxdWlyZV9fLm8gPSAob2JqLCBwcm9wKSA9PiAoT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsKG9iaiwgcHJvcCkpIiwiLy8gZGVmaW5lIF9fZXNNb2R1bGUgb24gZXhwb3J0c1xuX193ZWJwYWNrX3JlcXVpcmVfXy5yID0gKGV4cG9ydHMpID0+IHtcblx0aWYodHlwZW9mIFN5bWJvbCAhPT0gJ3VuZGVmaW5lZCcgJiYgU3ltYm9sLnRvU3RyaW5nVGFnKSB7XG5cdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIFN5bWJvbC50b1N0cmluZ1RhZywgeyB2YWx1ZTogJ01vZHVsZScgfSk7XG5cdH1cblx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsICdfX2VzTW9kdWxlJywgeyB2YWx1ZTogdHJ1ZSB9KTtcbn07IiwiIiwiLy8gc3RhcnR1cFxuLy8gTG9hZCBlbnRyeSBtb2R1bGUgYW5kIHJldHVybiBleHBvcnRzXG4vLyBUaGlzIGVudHJ5IG1vZHVsZSB1c2VkICdtb2R1bGUnIHNvIGl0IGNhbid0IGJlIGlubGluZWRcbnZhciBfX3dlYnBhY2tfZXhwb3J0c19fID0gX193ZWJwYWNrX3JlcXVpcmVfXyhcIi4vYXNzZXRzL2Ntcy9hdXRoZW50aWNhdGlvbi93ZWJhdXRobi9yZWdpc3Rlci5qc1wiKTtcbiIsIiJdLCJuYW1lcyI6W10sInNvdXJjZVJvb3QiOiIifQ==