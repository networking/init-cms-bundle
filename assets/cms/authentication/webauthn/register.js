import { html, render } from 'lit-html';
import {
    browserSupportsWebAuthn,
    browserSupportsWebAuthnAutofill,
    platformAuthenticatorIsAvailable, WebAuthnAbortService, startAuthentication
} from '@simplewebauthn/browser';
import {
    _fetch,
    unregisterCredential,
    updateCredential,
    registerCredential, authenticate,
} from './client.js';
let Translator = await CMSAdmin.getTranslations();
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
            await updateCredential(credId, newName);
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
            await unregisterCredential(el.dataset.credId);
            renderCredentials();
        } catch (e) {
            alert(e.message);
        }
    })
}

async function renderCredentials() {
    try {
        let res = await _fetch(CMSRouting.generate('admin_networking_initcms_user_get_webauthn_keys'))
        const list = document.querySelector('#list');
        document.querySelector('#passkeys-setup').classList.toggle('d-none', res.length < 1);
        document.querySelector('#passkeys-not-setup').classList.toggle('d-none', res.length > 0);
        const creds = html`${res.length > 0 ? html`

                <ul class="list-group">
                      ${res.map(cred => html`
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
                  </ul>` : html`
                <ul class="list-group">
                    <li  class="list-group-item">${Translator.trans('passkey_create.no_credentials_found', [], 'security')}</li>
                  </ul>`}`;
        render(creds, list);

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
        let res = await _fetch(CMSRouting.generate('admin_networking_initcms_user_profile_security_get_authenticator'))
        const list = document.querySelector('#list-authenticator');
        createAuthenticatorButton.classList.toggle('d-none', res.hasStepVerificationCode);
        const creds = html`${res.hasStepVerificationCode ? html`
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
              </ul>` : html`
            <ul class="list-group">
                <li class="list-group-item">${Translator.trans('authenticator_edit.no_authenticator_found', [], 'security')}</li>
              </ul>`}`;
        render(creds, list);
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
        await registerCredential(username, displayName);

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
        const authenticator = await _fetch(CMSRouting.generate('admin_networking_initcms_user_profile_security_create_authenticator'))

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
            let result =  await _fetch(CMSRouting.generate('admin_networking_initcms_user_profile_security_verify_authenticator'), {_code: code})

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

    const options = await _fetch('/admin/assertion/options', {
        requireUserVerification: 'preferred',
    });

    let asseResp;
    try {
        // Pass the options to the authenticator and wait for a response
        asseResp = await startAuthentication(options);
    } catch (e) {

        CMSAdmin.createInitCmsMessageBox('error', e.message);
    }

    try{
        const result = await _fetch('/admin/assertion', asseResp);

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

    if (browserSupportsWebAuthn() &&
        PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable &&
        PublicKeyCredential.isConditionalMediationAvailable) {
        try {
            const results = await Promise.all([

                // Is platform authenticator available in this browser?
                platformAuthenticatorIsAvailable(),

                // Is conditional UI available in this browser?
                browserSupportsWebAuthnAutofill()
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