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
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!******************************************************!*\
  !*** ./assets/cms/authentication/sign-in/general.js ***!
  \******************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _webauthn_client__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../webauthn/client */ "./assets/cms/authentication/webauthn/client.js");
/* harmony import */ var _simplewebauthn_browser__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @simplewebauthn/browser */ "./node_modules/@simplewebauthn/browser/esm/index.js");





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
                const result = await (0,_webauthn_client__WEBPACK_IMPORTED_MODULE_0__.authenticate)(username);
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

})();

/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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QUFDQSxxQ0FBcUMsOERBQVk7QUFDakQ7QUFDQTtBQUNBOzs7QUFHQTtBQUNBO0FBQ0EsU0FBUztBQUNUOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFhO0FBQ2I7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7OztBQUdBLGdEQUFnRDtBQUNoRCxnREFBZ0Q7QUFDaEQ7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7O0FBRUE7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0Esc0JBQXNCOztBQUV0QjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsaUJBQWlCOztBQUVqQjtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBLGlCQUFpQjtBQUNqQjs7QUFFQTs7QUFFQTtBQUNBLG9DQUFvQztBQUNwQztBQUNBO0FBQ0E7QUFDQSxDQUFDOztBQUVEO0FBQ0E7QUFDQTtBQUNBLENBQUMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9hc3NldHMvY21zL2F1dGhlbnRpY2F0aW9uL3dlYmF1dGhuL2NsaWVudC5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9ub2RlX21vZHVsZXMvQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXIvZXNtL2hlbHBlcnMvYmFzZTY0VVJMU3RyaW5nVG9CdWZmZXIuanMiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vbm9kZV9tb2R1bGVzL0BzaW1wbGV3ZWJhdXRobi9icm93c2VyL2VzbS9oZWxwZXJzL2Jyb3dzZXJTdXBwb3J0c1dlYkF1dGhuLmpzIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS8uL25vZGVfbW9kdWxlcy9Ac2ltcGxld2ViYXV0aG4vYnJvd3Nlci9lc20vaGVscGVycy9icm93c2VyU3VwcG9ydHNXZWJBdXRobkF1dG9maWxsLmpzIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS8uL25vZGVfbW9kdWxlcy9Ac2ltcGxld2ViYXV0aG4vYnJvd3Nlci9lc20vaGVscGVycy9idWZmZXJUb0Jhc2U2NFVSTFN0cmluZy5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9ub2RlX21vZHVsZXMvQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXIvZXNtL2hlbHBlcnMvaWRlbnRpZnlBdXRoZW50aWNhdGlvbkVycm9yLmpzIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS8uL25vZGVfbW9kdWxlcy9Ac2ltcGxld2ViYXV0aG4vYnJvd3Nlci9lc20vaGVscGVycy9pZGVudGlmeVJlZ2lzdHJhdGlvbkVycm9yLmpzIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS8uL25vZGVfbW9kdWxlcy9Ac2ltcGxld2ViYXV0aG4vYnJvd3Nlci9lc20vaGVscGVycy9pc1ZhbGlkRG9tYWluLmpzIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS8uL25vZGVfbW9kdWxlcy9Ac2ltcGxld2ViYXV0aG4vYnJvd3Nlci9lc20vaGVscGVycy9wbGF0Zm9ybUF1dGhlbnRpY2F0b3JJc0F2YWlsYWJsZS5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9ub2RlX21vZHVsZXMvQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXIvZXNtL2hlbHBlcnMvdG9BdXRoZW50aWNhdG9yQXR0YWNobWVudC5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9ub2RlX21vZHVsZXMvQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXIvZXNtL2hlbHBlcnMvdG9QdWJsaWNLZXlDcmVkZW50aWFsRGVzY3JpcHRvci5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9ub2RlX21vZHVsZXMvQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXIvZXNtL2hlbHBlcnMvd2ViQXV0aG5BYm9ydFNlcnZpY2UuanMiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vbm9kZV9tb2R1bGVzL0BzaW1wbGV3ZWJhdXRobi9icm93c2VyL2VzbS9oZWxwZXJzL3dlYkF1dGhuRXJyb3IuanMiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vbm9kZV9tb2R1bGVzL0BzaW1wbGV3ZWJhdXRobi9icm93c2VyL2VzbS9pbmRleC5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9ub2RlX21vZHVsZXMvQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXIvZXNtL21ldGhvZHMvc3RhcnRBdXRoZW50aWNhdGlvbi5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9ub2RlX21vZHVsZXMvQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXIvZXNtL21ldGhvZHMvc3RhcnRSZWdpc3RyYXRpb24uanMiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vbm9kZV9tb2R1bGVzL0BzaW1wbGV3ZWJhdXRobi9icm93c2VyL2VzbS90eXBlcy9pbmRleC5qcyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlL3dlYnBhY2svcnVudGltZS9kZWZpbmUgcHJvcGVydHkgZ2V0dGVycyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvd2VicGFjay9ydW50aW1lL2hhc093blByb3BlcnR5IHNob3J0aGFuZCIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvd2VicGFjay9ydW50aW1lL21ha2UgbmFtZXNwYWNlIG9iamVjdCIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9hc3NldHMvY21zL2F1dGhlbnRpY2F0aW9uL3NpZ24taW4vZ2VuZXJhbC5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyJpbXBvcnQgeyBzdGFydFJlZ2lzdHJhdGlvbiwgc3RhcnRBdXRoZW50aWNhdGlvbiB9IGZyb20gJ0BzaW1wbGV3ZWJhdXRobi9icm93c2VyJztcblxuZXhwb3J0IGFzeW5jIGZ1bmN0aW9uIF9mZXRjaChwYXRoLCBwYXlsb2FkID0gJycpIHtcbiAgICBjb25zdCBoZWFkZXJzID0ge1xuICAgICAgICAnWC1SZXF1ZXN0ZWQtV2l0aCc6ICdYTUxIdHRwUmVxdWVzdCcsXG4gICAgICAgICdDb250ZW50LVR5cGUnOiAnYXBwbGljYXRpb24vanNvbidcbiAgICB9O1xuICAgIGlmIChwYXlsb2FkICYmICEocGF5bG9hZCBpbnN0YW5jZW9mIEZvcm1EYXRhKSkge1xuICAgICAgICBwYXlsb2FkID0gSlNPTi5zdHJpbmdpZnkocGF5bG9hZCk7XG4gICAgfVxuICAgIGNvbnN0IHJlcyA9IGF3YWl0IGZldGNoKHBhdGgsIHtcbiAgICAgICAgbWV0aG9kOiAnUE9TVCcsXG4gICAgICAgIGNyZWRlbnRpYWxzOiAnc2FtZS1vcmlnaW4nLFxuICAgICAgICBoZWFkZXJzOiBoZWFkZXJzLFxuICAgICAgICBib2R5OiBwYXlsb2FkLFxuICAgIH0pO1xuICAgIGlmIChyZXMuc3RhdHVzID09PSAyMDAgfHwgcmVzLnN0YXR1cyA9PT0gMjAxKSB7XG4gICAgICAgIC8vIFNlcnZlciBhdXRoZW50aWNhdGlvbiBzdWNjZWVkZWRcbiAgICAgICAgcmV0dXJuIHJlcy5qc29uKCk7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgLy8gU2VydmVyIGF1dGhlbnRpY2F0aW9uIGZhaWxlZFxuICAgICAgICBjb25zdCByZXN1bHQgPSBhd2FpdCByZXMuanNvbigpO1xuXG4gICAgICAgIGlmKHJlc3VsdC5tZXNzYWdlKXtcbiAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcihyZXN1bHQubWVzc2FnZSk7XG4gICAgICAgIH1cblxuICAgICAgICB0aHJvdyBuZXcgRXJyb3IocmVzdWx0LmVycm9yKTtcbiAgICB9XG59XG5cblxuZXhwb3J0IGFzeW5jIGZ1bmN0aW9uIHJlZ2lzdGVyQ3JlZGVudGlhbCh1c2VybmFtZSwgZGlzcGxheU5hbWUpIHtcblxuICAgIGxldCBkYXRhID0ge1xuICAgICAgICB1c2VybmFtZTogdXNlcm5hbWUsXG4gICAgICAgIGRpc3BsYXlOYW1lOiBkaXNwbGF5TmFtZSxcbiAgICAgICAgYXV0aGVudGljYXRvclNlbGVjdGlvbjoge1xuICAgICAgICAgICAgdXNlclZlcmlmaWNhdGlvbjogJ3ByZWZlcnJlZCcsXG4gICAgICAgIH0sXG4gICAgfVxuXG4gICAgY29uc3Qgb3B0aW9ucyA9IGF3YWl0IF9mZXRjaCgnL2FkbWluL3JlZ2lzdGVyL29wdGlvbnMnLCBkYXRhKTtcblxuICAgIGxldCBhdHRSZXNwO1xuICAgIHRyeSB7XG4gICAgICAgIC8vIFBhc3MgdGhlIG9wdGlvbnMgdG8gdGhlIGF1dGhlbnRpY2F0b3IgYW5kIHdhaXQgZm9yIGEgcmVzcG9uc2VcbiAgICAgICAgYXR0UmVzcCA9IGF3YWl0IHN0YXJ0UmVnaXN0cmF0aW9uKG9wdGlvbnMpO1xuICAgIH0gY2F0Y2ggKGVycm9yKSB7XG5cbiAgICAgICAgdGhyb3cgZXJyb3I7XG4gICAgfVxuXG4gICAgcmV0dXJuIGF3YWl0IF9mZXRjaChDTVNSb3V0aW5nLmdlbmVyYXRlKCdpbml0Y21zX3dlYmF1dGhuX3JlZ2lzdGVyX3Jlc3BvbnNlJyksIGF0dFJlc3ApO1xuXG59XG5leHBvcnQgYXN5bmMgZnVuY3Rpb24gYXV0aGVudGljYXRlKHVzZXJuYW1lKSB7XG4gICAgbGV0IHBheWxvYWQgPSB7XG4gICAgICAgIHJlcXVpcmVVc2VyVmVyaWZpY2F0aW9uOiAncHJlZmVycmVkJyxcbiAgICB9XG5cbiAgICBpZih1c2VybmFtZSl7XG4gICAgICAgIHBheWxvYWQudXNlcm5hbWUgPSB1c2VybmFtZTtcbiAgICB9XG4gICAgY29uc3Qgb3B0aW9ucyA9IGF3YWl0IF9mZXRjaCgnL2FkbWluL2Fzc2VydGlvbi9vcHRpb25zJywgcGF5bG9hZCk7XG4gICAgaWYodXNlcm5hbWUgJiYgb3B0aW9ucy5hbGxvd0NyZWRlbnRpYWxzID09PSB1bmRlZmluZWQpe1xuICAgICAgICB0aHJvdyBuZXcgRXJyb3IoJ25vX2NyZWRlbnRpYWxzJyk7XG4gICAgfVxuXG5cbiAgICBsZXQgYXNzZVJlc3A7XG4gICAgdHJ5IHtcbiAgICAgICAgLy8gUGFzcyB0aGUgb3B0aW9ucyB0byB0aGUgYXV0aGVudGljYXRvciBhbmQgd2FpdCBmb3IgYSByZXNwb25zZVxuICAgICAgICBhc3NlUmVzcCA9IGF3YWl0IHN0YXJ0QXV0aGVudGljYXRpb24ob3B0aW9ucyk7XG4gICAgfSBjYXRjaCAoZXJyb3IpIHtcbiAgICAgICAgdGhyb3cgZXJyb3I7XG4gICAgfVxuXG4gICAgcmV0dXJuIF9mZXRjaCgnL2FkbWluL2Fzc2VydGlvbicsIGFzc2VSZXNwKTtcbn1cblxuZXhwb3J0IGFzeW5jIGZ1bmN0aW9uIHVucmVnaXN0ZXJDcmVkZW50aWFsKGNyZWRJZCkge1xuICAgIHJldHVybiBfZmV0Y2goQ01TUm91dGluZy5nZW5lcmF0ZSgnYWRtaW5fbmV0d29ya2luZ19pbml0Y21zX3VzZXJfcmVtb3ZlX3dlYmF1dGhuX2tleScpICsgYD9jcmVkSWQ9JHtlbmNvZGVVUklDb21wb25lbnQoY3JlZElkKX1gKTtcbn1cblxuZXhwb3J0IGFzeW5jIGZ1bmN0aW9uIHVwZGF0ZUNyZWRlbnRpYWwoY3JlZElkLCBuYW1lKSB7XG4gICAgcmV0dXJuIF9mZXRjaChDTVNSb3V0aW5nLmdlbmVyYXRlKCdhZG1pbl9uZXR3b3JraW5nX2luaXRjbXNfdXNlcl9yZW5hbWVfd2ViYXV0aG5fa2V5JykgKyBgP2NyZWRJZD0ke2VuY29kZVVSSUNvbXBvbmVudChjcmVkSWQpfSZuYW1lPSR7ZW5jb2RlVVJJQ29tcG9uZW50KG5hbWUpfWApO1xufVxuIiwiLyoqXG4gKiBDb252ZXJ0IGZyb20gYSBCYXNlNjRVUkwtZW5jb2RlZCBzdHJpbmcgdG8gYW4gQXJyYXkgQnVmZmVyLiBCZXN0IHVzZWQgd2hlbiBjb252ZXJ0aW5nIGFcbiAqIGNyZWRlbnRpYWwgSUQgZnJvbSBhIEpTT04gc3RyaW5nIHRvIGFuIEFycmF5QnVmZmVyLCBsaWtlIGluIGFsbG93Q3JlZGVudGlhbHMgb3JcbiAqIGV4Y2x1ZGVDcmVkZW50aWFsc1xuICpcbiAqIEhlbHBlciBtZXRob2QgdG8gY29tcGxpbWVudCBgYnVmZmVyVG9CYXNlNjRVUkxTdHJpbmdgXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBiYXNlNjRVUkxTdHJpbmdUb0J1ZmZlcihiYXNlNjRVUkxTdHJpbmcpIHtcbiAgICAvLyBDb252ZXJ0IGZyb20gQmFzZTY0VVJMIHRvIEJhc2U2NFxuICAgIGNvbnN0IGJhc2U2NCA9IGJhc2U2NFVSTFN0cmluZy5yZXBsYWNlKC8tL2csICcrJykucmVwbGFjZSgvXy9nLCAnLycpO1xuICAgIC8qKlxuICAgICAqIFBhZCB3aXRoICc9JyB1bnRpbCBpdCdzIGEgbXVsdGlwbGUgb2YgZm91clxuICAgICAqICg0IC0gKDg1ICUgNCA9IDEpID0gMykgJSA0ID0gMyBwYWRkaW5nXG4gICAgICogKDQgLSAoODYgJSA0ID0gMikgPSAyKSAlIDQgPSAyIHBhZGRpbmdcbiAgICAgKiAoNCAtICg4NyAlIDQgPSAzKSA9IDEpICUgNCA9IDEgcGFkZGluZ1xuICAgICAqICg0IC0gKDg4ICUgNCA9IDApID0gNCkgJSA0ID0gMCBwYWRkaW5nXG4gICAgICovXG4gICAgY29uc3QgcGFkTGVuZ3RoID0gKDQgLSAoYmFzZTY0Lmxlbmd0aCAlIDQpKSAlIDQ7XG4gICAgY29uc3QgcGFkZGVkID0gYmFzZTY0LnBhZEVuZChiYXNlNjQubGVuZ3RoICsgcGFkTGVuZ3RoLCAnPScpO1xuICAgIC8vIENvbnZlcnQgdG8gYSBiaW5hcnkgc3RyaW5nXG4gICAgY29uc3QgYmluYXJ5ID0gYXRvYihwYWRkZWQpO1xuICAgIC8vIENvbnZlcnQgYmluYXJ5IHN0cmluZyB0byBidWZmZXJcbiAgICBjb25zdCBidWZmZXIgPSBuZXcgQXJyYXlCdWZmZXIoYmluYXJ5Lmxlbmd0aCk7XG4gICAgY29uc3QgYnl0ZXMgPSBuZXcgVWludDhBcnJheShidWZmZXIpO1xuICAgIGZvciAobGV0IGkgPSAwOyBpIDwgYmluYXJ5Lmxlbmd0aDsgaSsrKSB7XG4gICAgICAgIGJ5dGVzW2ldID0gYmluYXJ5LmNoYXJDb2RlQXQoaSk7XG4gICAgfVxuICAgIHJldHVybiBidWZmZXI7XG59XG4iLCIvKipcbiAqIERldGVybWluZSBpZiB0aGUgYnJvd3NlciBpcyBjYXBhYmxlIG9mIFdlYmF1dGhuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBicm93c2VyU3VwcG9ydHNXZWJBdXRobigpIHtcbiAgICByZXR1cm4gX2Jyb3dzZXJTdXBwb3J0c1dlYkF1dGhuSW50ZXJuYWxzLnN0dWJUaGlzKGdsb2JhbFRoaXM/LlB1YmxpY0tleUNyZWRlbnRpYWwgIT09IHVuZGVmaW5lZCAmJlxuICAgICAgICB0eXBlb2YgZ2xvYmFsVGhpcy5QdWJsaWNLZXlDcmVkZW50aWFsID09PSAnZnVuY3Rpb24nKTtcbn1cbi8qKlxuICogTWFrZSBpdCBwb3NzaWJsZSB0byBzdHViIHRoZSByZXR1cm4gdmFsdWUgZHVyaW5nIHRlc3RpbmdcbiAqIEBpZ25vcmUgRG9uJ3QgaW5jbHVkZSB0aGlzIGluIGRvY3Mgb3V0cHV0XG4gKi9cbmV4cG9ydCBjb25zdCBfYnJvd3NlclN1cHBvcnRzV2ViQXV0aG5JbnRlcm5hbHMgPSB7XG4gICAgc3R1YlRoaXM6ICh2YWx1ZSkgPT4gdmFsdWUsXG59O1xuIiwiaW1wb3J0IHsgYnJvd3NlclN1cHBvcnRzV2ViQXV0aG4gfSBmcm9tICcuL2Jyb3dzZXJTdXBwb3J0c1dlYkF1dGhuLmpzJztcbi8qKlxuICogRGV0ZXJtaW5lIGlmIHRoZSBicm93c2VyIHN1cHBvcnRzIGNvbmRpdGlvbmFsIFVJLCBzbyB0aGF0IFdlYkF1dGhuIGNyZWRlbnRpYWxzIGNhblxuICogYmUgc2hvd24gdG8gdGhlIHVzZXIgaW4gdGhlIGJyb3dzZXIncyB0eXBpY2FsIHBhc3N3b3JkIGF1dG9maWxsIHBvcHVwLlxuICovXG5leHBvcnQgZnVuY3Rpb24gYnJvd3NlclN1cHBvcnRzV2ViQXV0aG5BdXRvZmlsbCgpIHtcbiAgICBpZiAoIWJyb3dzZXJTdXBwb3J0c1dlYkF1dGhuKCkpIHtcbiAgICAgICAgcmV0dXJuIF9icm93c2VyU3VwcG9ydHNXZWJBdXRobkF1dG9maWxsSW50ZXJuYWxzLnN0dWJUaGlzKG5ldyBQcm9taXNlKChyZXNvbHZlKSA9PiByZXNvbHZlKGZhbHNlKSkpO1xuICAgIH1cbiAgICAvKipcbiAgICAgKiBJIGRvbid0IGxpa2UgdGhlIGBhcyB1bmtub3duYCBoZXJlIGJ1dCB0aGVyZSdzIGEgYGRlY2xhcmUgdmFyIFB1YmxpY0tleUNyZWRlbnRpYWxgIGluXG4gICAgICogVFMnIERPTSBsaWIgdGhhdCdzIG1ha2luZyBpdCBkaWZmaWN1bHQgZm9yIG1lIHRvIGp1c3QgZ28gYGFzIFB1YmxpY0tleUNyZWRlbnRpYWxGdXR1cmVgIGFzIElcbiAgICAgKiB3YW50LiBJIHRoaW5rIEknbSBmaW5lIHdpdGggdGhpcyBmb3Igbm93IHNpbmNlIGl0J3MgX3N1cHBvc2VkXyB0byBiZSB0ZW1wb3JhcnksIHVudGlsIFRTIHR5cGVzXG4gICAgICogaGF2ZSBhIGNoYW5jZSB0byBjYXRjaCB1cC5cbiAgICAgKi9cbiAgICBjb25zdCBnbG9iYWxQdWJsaWNLZXlDcmVkZW50aWFsID0gZ2xvYmFsVGhpc1xuICAgICAgICAuUHVibGljS2V5Q3JlZGVudGlhbDtcbiAgICBpZiAoZ2xvYmFsUHVibGljS2V5Q3JlZGVudGlhbD8uaXNDb25kaXRpb25hbE1lZGlhdGlvbkF2YWlsYWJsZSA9PT0gdW5kZWZpbmVkKSB7XG4gICAgICAgIHJldHVybiBfYnJvd3NlclN1cHBvcnRzV2ViQXV0aG5BdXRvZmlsbEludGVybmFscy5zdHViVGhpcyhuZXcgUHJvbWlzZSgocmVzb2x2ZSkgPT4gcmVzb2x2ZShmYWxzZSkpKTtcbiAgICB9XG4gICAgcmV0dXJuIF9icm93c2VyU3VwcG9ydHNXZWJBdXRobkF1dG9maWxsSW50ZXJuYWxzLnN0dWJUaGlzKGdsb2JhbFB1YmxpY0tleUNyZWRlbnRpYWwuaXNDb25kaXRpb25hbE1lZGlhdGlvbkF2YWlsYWJsZSgpKTtcbn1cbi8vIE1ha2UgaXQgcG9zc2libGUgdG8gc3R1YiB0aGUgcmV0dXJuIHZhbHVlIGR1cmluZyB0ZXN0aW5nXG5leHBvcnQgY29uc3QgX2Jyb3dzZXJTdXBwb3J0c1dlYkF1dGhuQXV0b2ZpbGxJbnRlcm5hbHMgPSB7XG4gICAgc3R1YlRoaXM6ICh2YWx1ZSkgPT4gdmFsdWUsXG59O1xuIiwiLyoqXG4gKiBDb252ZXJ0IHRoZSBnaXZlbiBhcnJheSBidWZmZXIgaW50byBhIEJhc2U2NFVSTC1lbmNvZGVkIHN0cmluZy4gSWRlYWwgZm9yIGNvbnZlcnRpbmcgdmFyaW91c1xuICogY3JlZGVudGlhbCByZXNwb25zZSBBcnJheUJ1ZmZlcnMgdG8gc3RyaW5nIGZvciBzZW5kaW5nIGJhY2sgdG8gdGhlIHNlcnZlciBhcyBKU09OLlxuICpcbiAqIEhlbHBlciBtZXRob2QgdG8gY29tcGxpbWVudCBgYmFzZTY0VVJMU3RyaW5nVG9CdWZmZXJgXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBidWZmZXJUb0Jhc2U2NFVSTFN0cmluZyhidWZmZXIpIHtcbiAgICBjb25zdCBieXRlcyA9IG5ldyBVaW50OEFycmF5KGJ1ZmZlcik7XG4gICAgbGV0IHN0ciA9ICcnO1xuICAgIGZvciAoY29uc3QgY2hhckNvZGUgb2YgYnl0ZXMpIHtcbiAgICAgICAgc3RyICs9IFN0cmluZy5mcm9tQ2hhckNvZGUoY2hhckNvZGUpO1xuICAgIH1cbiAgICBjb25zdCBiYXNlNjRTdHJpbmcgPSBidG9hKHN0cik7XG4gICAgcmV0dXJuIGJhc2U2NFN0cmluZy5yZXBsYWNlKC9cXCsvZywgJy0nKS5yZXBsYWNlKC9cXC8vZywgJ18nKS5yZXBsYWNlKC89L2csICcnKTtcbn1cbiIsImltcG9ydCB7IGlzVmFsaWREb21haW4gfSBmcm9tICcuL2lzVmFsaWREb21haW4uanMnO1xuaW1wb3J0IHsgV2ViQXV0aG5FcnJvciB9IGZyb20gJy4vd2ViQXV0aG5FcnJvci5qcyc7XG4vKipcbiAqIEF0dGVtcHQgdG8gaW50dWl0IF93aHlfIGFuIGVycm9yIHdhcyByYWlzZWQgYWZ0ZXIgY2FsbGluZyBgbmF2aWdhdG9yLmNyZWRlbnRpYWxzLmdldCgpYFxuICovXG5leHBvcnQgZnVuY3Rpb24gaWRlbnRpZnlBdXRoZW50aWNhdGlvbkVycm9yKHsgZXJyb3IsIG9wdGlvbnMsIH0pIHtcbiAgICBjb25zdCB7IHB1YmxpY0tleSB9ID0gb3B0aW9ucztcbiAgICBpZiAoIXB1YmxpY0tleSkge1xuICAgICAgICB0aHJvdyBFcnJvcignb3B0aW9ucyB3YXMgbWlzc2luZyByZXF1aXJlZCBwdWJsaWNLZXkgcHJvcGVydHknKTtcbiAgICB9XG4gICAgaWYgKGVycm9yLm5hbWUgPT09ICdBYm9ydEVycm9yJykge1xuICAgICAgICBpZiAob3B0aW9ucy5zaWduYWwgaW5zdGFuY2VvZiBBYm9ydFNpZ25hbCkge1xuICAgICAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tY3JlYXRlQ3JlZGVudGlhbCAoU3RlcCAxNilcbiAgICAgICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICAgICAgbWVzc2FnZTogJ0F1dGhlbnRpY2F0aW9uIGNlcmVtb255IHdhcyBzZW50IGFuIGFib3J0IHNpZ25hbCcsXG4gICAgICAgICAgICAgICAgY29kZTogJ0VSUk9SX0NFUkVNT05ZX0FCT1JURUQnLFxuICAgICAgICAgICAgICAgIGNhdXNlOiBlcnJvcixcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgfVxuICAgIGVsc2UgaWYgKGVycm9yLm5hbWUgPT09ICdOb3RBbGxvd2VkRXJyb3InKSB7XG4gICAgICAgIC8qKlxuICAgICAgICAgKiBQYXNzIHRoZSBlcnJvciBkaXJlY3RseSB0aHJvdWdoLiBQbGF0Zm9ybXMgYXJlIG92ZXJsb2FkaW5nIHRoaXMgZXJyb3IgYmV5b25kIHdoYXQgdGhlIHNwZWNcbiAgICAgICAgICogZGVmaW5lcyBhbmQgd2UgZG9uJ3Qgd2FudCB0byBvdmVyd3JpdGUgcG90ZW50aWFsbHkgdXNlZnVsIGVycm9yIG1lc3NhZ2VzLlxuICAgICAgICAgKi9cbiAgICAgICAgcmV0dXJuIG5ldyBXZWJBdXRobkVycm9yKHtcbiAgICAgICAgICAgIG1lc3NhZ2U6IGVycm9yLm1lc3NhZ2UsXG4gICAgICAgICAgICBjb2RlOiAnRVJST1JfUEFTU1RIUk9VR0hfU0VFX0NBVVNFX1BST1BFUlRZJyxcbiAgICAgICAgICAgIGNhdXNlOiBlcnJvcixcbiAgICAgICAgfSk7XG4gICAgfVxuICAgIGVsc2UgaWYgKGVycm9yLm5hbWUgPT09ICdTZWN1cml0eUVycm9yJykge1xuICAgICAgICBjb25zdCBlZmZlY3RpdmVEb21haW4gPSBnbG9iYWxUaGlzLmxvY2F0aW9uLmhvc3RuYW1lO1xuICAgICAgICBpZiAoIWlzVmFsaWREb21haW4oZWZmZWN0aXZlRG9tYWluKSkge1xuICAgICAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tZGlzY292ZXItZnJvbS1leHRlcm5hbC1zb3VyY2UgKFN0ZXAgNSlcbiAgICAgICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICAgICAgbWVzc2FnZTogYCR7Z2xvYmFsVGhpcy5sb2NhdGlvbi5ob3N0bmFtZX0gaXMgYW4gaW52YWxpZCBkb21haW5gLFxuICAgICAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9JTlZBTElEX0RPTUFJTicsXG4gICAgICAgICAgICAgICAgY2F1c2U6IGVycm9yLFxuICAgICAgICAgICAgfSk7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSBpZiAocHVibGljS2V5LnJwSWQgIT09IGVmZmVjdGl2ZURvbWFpbikge1xuICAgICAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tZGlzY292ZXItZnJvbS1leHRlcm5hbC1zb3VyY2UgKFN0ZXAgNilcbiAgICAgICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICAgICAgbWVzc2FnZTogYFRoZSBSUCBJRCBcIiR7cHVibGljS2V5LnJwSWR9XCIgaXMgaW52YWxpZCBmb3IgdGhpcyBkb21haW5gLFxuICAgICAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9JTlZBTElEX1JQX0lEJyxcbiAgICAgICAgICAgICAgICBjYXVzZTogZXJyb3IsXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgIH1cbiAgICBlbHNlIGlmIChlcnJvci5uYW1lID09PSAnVW5rbm93bkVycm9yJykge1xuICAgICAgICAvLyBodHRwczovL3d3dy53My5vcmcvVFIvd2ViYXV0aG4tMi8jc2N0bi1vcC1nZXQtYXNzZXJ0aW9uIChTdGVwIDEpXG4gICAgICAgIC8vIGh0dHBzOi8vd3d3LnczLm9yZy9UUi93ZWJhdXRobi0yLyNzY3RuLW9wLWdldC1hc3NlcnRpb24gKFN0ZXAgMTIpXG4gICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICBtZXNzYWdlOiAnVGhlIGF1dGhlbnRpY2F0b3Igd2FzIHVuYWJsZSB0byBwcm9jZXNzIHRoZSBzcGVjaWZpZWQgb3B0aW9ucywgb3IgY291bGQgbm90IGNyZWF0ZSBhIG5ldyBhc3NlcnRpb24gc2lnbmF0dXJlJyxcbiAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9BVVRIRU5USUNBVE9SX0dFTkVSQUxfRVJST1InLFxuICAgICAgICAgICAgY2F1c2U6IGVycm9yLFxuICAgICAgICB9KTtcbiAgICB9XG4gICAgcmV0dXJuIGVycm9yO1xufVxuIiwiaW1wb3J0IHsgaXNWYWxpZERvbWFpbiB9IGZyb20gJy4vaXNWYWxpZERvbWFpbi5qcyc7XG5pbXBvcnQgeyBXZWJBdXRobkVycm9yIH0gZnJvbSAnLi93ZWJBdXRobkVycm9yLmpzJztcbi8qKlxuICogQXR0ZW1wdCB0byBpbnR1aXQgX3doeV8gYW4gZXJyb3Igd2FzIHJhaXNlZCBhZnRlciBjYWxsaW5nIGBuYXZpZ2F0b3IuY3JlZGVudGlhbHMuY3JlYXRlKClgXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBpZGVudGlmeVJlZ2lzdHJhdGlvbkVycm9yKHsgZXJyb3IsIG9wdGlvbnMsIH0pIHtcbiAgICBjb25zdCB7IHB1YmxpY0tleSB9ID0gb3B0aW9ucztcbiAgICBpZiAoIXB1YmxpY0tleSkge1xuICAgICAgICB0aHJvdyBFcnJvcignb3B0aW9ucyB3YXMgbWlzc2luZyByZXF1aXJlZCBwdWJsaWNLZXkgcHJvcGVydHknKTtcbiAgICB9XG4gICAgaWYgKGVycm9yLm5hbWUgPT09ICdBYm9ydEVycm9yJykge1xuICAgICAgICBpZiAob3B0aW9ucy5zaWduYWwgaW5zdGFuY2VvZiBBYm9ydFNpZ25hbCkge1xuICAgICAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tY3JlYXRlQ3JlZGVudGlhbCAoU3RlcCAxNilcbiAgICAgICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICAgICAgbWVzc2FnZTogJ1JlZ2lzdHJhdGlvbiBjZXJlbW9ueSB3YXMgc2VudCBhbiBhYm9ydCBzaWduYWwnLFxuICAgICAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9DRVJFTU9OWV9BQk9SVEVEJyxcbiAgICAgICAgICAgICAgICBjYXVzZTogZXJyb3IsXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgIH1cbiAgICBlbHNlIGlmIChlcnJvci5uYW1lID09PSAnQ29uc3RyYWludEVycm9yJykge1xuICAgICAgICBpZiAocHVibGljS2V5LmF1dGhlbnRpY2F0b3JTZWxlY3Rpb24/LnJlcXVpcmVSZXNpZGVudEtleSA9PT0gdHJ1ZSkge1xuICAgICAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tb3AtbWFrZS1jcmVkIChTdGVwIDQpXG4gICAgICAgICAgICByZXR1cm4gbmV3IFdlYkF1dGhuRXJyb3Ioe1xuICAgICAgICAgICAgICAgIG1lc3NhZ2U6ICdEaXNjb3ZlcmFibGUgY3JlZGVudGlhbHMgd2VyZSByZXF1aXJlZCBidXQgbm8gYXZhaWxhYmxlIGF1dGhlbnRpY2F0b3Igc3VwcG9ydGVkIGl0JyxcbiAgICAgICAgICAgICAgICBjb2RlOiAnRVJST1JfQVVUSEVOVElDQVRPUl9NSVNTSU5HX0RJU0NPVkVSQUJMRV9DUkVERU5USUFMX1NVUFBPUlQnLFxuICAgICAgICAgICAgICAgIGNhdXNlOiBlcnJvcixcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgICAgIGVsc2UgaWYgKFxuICAgICAgICAvLyBAdHMtaWdub3JlOiBgbWVkaWF0aW9uYCBkb2Vzbid0IHlldCBleGlzdCBvbiBDcmVkZW50aWFsQ3JlYXRpb25PcHRpb25zIGJ1dCBpdCdzIHBvc3NpYmxlIGFzIG9mIFNlcHQgMjAyNFxuICAgICAgICBvcHRpb25zLm1lZGlhdGlvbiA9PT0gJ2NvbmRpdGlvbmFsJyAmJlxuICAgICAgICAgICAgcHVibGljS2V5LmF1dGhlbnRpY2F0b3JTZWxlY3Rpb24/LnVzZXJWZXJpZmljYXRpb24gPT09ICdyZXF1aXJlZCcpIHtcbiAgICAgICAgICAgIC8vIGh0dHBzOi8vdzNjLmdpdGh1Yi5pby93ZWJhdXRobi8jc2N0bi1jcmVhdGVDcmVkZW50aWFsIChTdGVwIDIyLjQpXG4gICAgICAgICAgICByZXR1cm4gbmV3IFdlYkF1dGhuRXJyb3Ioe1xuICAgICAgICAgICAgICAgIG1lc3NhZ2U6ICdVc2VyIHZlcmlmaWNhdGlvbiB3YXMgcmVxdWlyZWQgZHVyaW5nIGF1dG9tYXRpYyByZWdpc3RyYXRpb24gYnV0IGl0IGNvdWxkIG5vdCBiZSBwZXJmb3JtZWQnLFxuICAgICAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9BVVRPX1JFR0lTVEVSX1VTRVJfVkVSSUZJQ0FUSU9OX0ZBSUxVUkUnLFxuICAgICAgICAgICAgICAgIGNhdXNlOiBlcnJvcixcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgICAgIGVsc2UgaWYgKHB1YmxpY0tleS5hdXRoZW50aWNhdG9yU2VsZWN0aW9uPy51c2VyVmVyaWZpY2F0aW9uID09PSAncmVxdWlyZWQnKSB7XG4gICAgICAgICAgICAvLyBodHRwczovL3d3dy53My5vcmcvVFIvd2ViYXV0aG4tMi8jc2N0bi1vcC1tYWtlLWNyZWQgKFN0ZXAgNSlcbiAgICAgICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICAgICAgbWVzc2FnZTogJ1VzZXIgdmVyaWZpY2F0aW9uIHdhcyByZXF1aXJlZCBidXQgbm8gYXZhaWxhYmxlIGF1dGhlbnRpY2F0b3Igc3VwcG9ydGVkIGl0JyxcbiAgICAgICAgICAgICAgICBjb2RlOiAnRVJST1JfQVVUSEVOVElDQVRPUl9NSVNTSU5HX1VTRVJfVkVSSUZJQ0FUSU9OX1NVUFBPUlQnLFxuICAgICAgICAgICAgICAgIGNhdXNlOiBlcnJvcixcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgfVxuICAgIGVsc2UgaWYgKGVycm9yLm5hbWUgPT09ICdJbnZhbGlkU3RhdGVFcnJvcicpIHtcbiAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tY3JlYXRlQ3JlZGVudGlhbCAoU3RlcCAyMClcbiAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tb3AtbWFrZS1jcmVkIChTdGVwIDMpXG4gICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICBtZXNzYWdlOiAnVGhlIGF1dGhlbnRpY2F0b3Igd2FzIHByZXZpb3VzbHkgcmVnaXN0ZXJlZCcsXG4gICAgICAgICAgICBjb2RlOiAnRVJST1JfQVVUSEVOVElDQVRPUl9QUkVWSU9VU0xZX1JFR0lTVEVSRUQnLFxuICAgICAgICAgICAgY2F1c2U6IGVycm9yLFxuICAgICAgICB9KTtcbiAgICB9XG4gICAgZWxzZSBpZiAoZXJyb3IubmFtZSA9PT0gJ05vdEFsbG93ZWRFcnJvcicpIHtcbiAgICAgICAgLyoqXG4gICAgICAgICAqIFBhc3MgdGhlIGVycm9yIGRpcmVjdGx5IHRocm91Z2guIFBsYXRmb3JtcyBhcmUgb3ZlcmxvYWRpbmcgdGhpcyBlcnJvciBiZXlvbmQgd2hhdCB0aGUgc3BlY1xuICAgICAgICAgKiBkZWZpbmVzIGFuZCB3ZSBkb24ndCB3YW50IHRvIG92ZXJ3cml0ZSBwb3RlbnRpYWxseSB1c2VmdWwgZXJyb3IgbWVzc2FnZXMuXG4gICAgICAgICAqL1xuICAgICAgICByZXR1cm4gbmV3IFdlYkF1dGhuRXJyb3Ioe1xuICAgICAgICAgICAgbWVzc2FnZTogZXJyb3IubWVzc2FnZSxcbiAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9QQVNTVEhST1VHSF9TRUVfQ0FVU0VfUFJPUEVSVFknLFxuICAgICAgICAgICAgY2F1c2U6IGVycm9yLFxuICAgICAgICB9KTtcbiAgICB9XG4gICAgZWxzZSBpZiAoZXJyb3IubmFtZSA9PT0gJ05vdFN1cHBvcnRlZEVycm9yJykge1xuICAgICAgICBjb25zdCB2YWxpZFB1YktleUNyZWRQYXJhbXMgPSBwdWJsaWNLZXkucHViS2V5Q3JlZFBhcmFtcy5maWx0ZXIoKHBhcmFtKSA9PiBwYXJhbS50eXBlID09PSAncHVibGljLWtleScpO1xuICAgICAgICBpZiAodmFsaWRQdWJLZXlDcmVkUGFyYW1zLmxlbmd0aCA9PT0gMCkge1xuICAgICAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tY3JlYXRlQ3JlZGVudGlhbCAoU3RlcCAxMClcbiAgICAgICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICAgICAgbWVzc2FnZTogJ05vIGVudHJ5IGluIHB1YktleUNyZWRQYXJhbXMgd2FzIG9mIHR5cGUgXCJwdWJsaWMta2V5XCInLFxuICAgICAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9NQUxGT1JNRURfUFVCS0VZQ1JFRFBBUkFNUycsXG4gICAgICAgICAgICAgICAgY2F1c2U6IGVycm9yLFxuICAgICAgICAgICAgfSk7XG4gICAgICAgIH1cbiAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tb3AtbWFrZS1jcmVkIChTdGVwIDIpXG4gICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICBtZXNzYWdlOiAnTm8gYXZhaWxhYmxlIGF1dGhlbnRpY2F0b3Igc3VwcG9ydGVkIGFueSBvZiB0aGUgc3BlY2lmaWVkIHB1YktleUNyZWRQYXJhbXMgYWxnb3JpdGhtcycsXG4gICAgICAgICAgICBjb2RlOiAnRVJST1JfQVVUSEVOVElDQVRPUl9OT19TVVBQT1JURURfUFVCS0VZQ1JFRFBBUkFNU19BTEcnLFxuICAgICAgICAgICAgY2F1c2U6IGVycm9yLFxuICAgICAgICB9KTtcbiAgICB9XG4gICAgZWxzZSBpZiAoZXJyb3IubmFtZSA9PT0gJ1NlY3VyaXR5RXJyb3InKSB7XG4gICAgICAgIGNvbnN0IGVmZmVjdGl2ZURvbWFpbiA9IGdsb2JhbFRoaXMubG9jYXRpb24uaG9zdG5hbWU7XG4gICAgICAgIGlmICghaXNWYWxpZERvbWFpbihlZmZlY3RpdmVEb21haW4pKSB7XG4gICAgICAgICAgICAvLyBodHRwczovL3d3dy53My5vcmcvVFIvd2ViYXV0aG4tMi8jc2N0bi1jcmVhdGVDcmVkZW50aWFsIChTdGVwIDcpXG4gICAgICAgICAgICByZXR1cm4gbmV3IFdlYkF1dGhuRXJyb3Ioe1xuICAgICAgICAgICAgICAgIG1lc3NhZ2U6IGAke2dsb2JhbFRoaXMubG9jYXRpb24uaG9zdG5hbWV9IGlzIGFuIGludmFsaWQgZG9tYWluYCxcbiAgICAgICAgICAgICAgICBjb2RlOiAnRVJST1JfSU5WQUxJRF9ET01BSU4nLFxuICAgICAgICAgICAgICAgIGNhdXNlOiBlcnJvcixcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgICAgIGVsc2UgaWYgKHB1YmxpY0tleS5ycC5pZCAhPT0gZWZmZWN0aXZlRG9tYWluKSB7XG4gICAgICAgICAgICAvLyBodHRwczovL3d3dy53My5vcmcvVFIvd2ViYXV0aG4tMi8jc2N0bi1jcmVhdGVDcmVkZW50aWFsIChTdGVwIDgpXG4gICAgICAgICAgICByZXR1cm4gbmV3IFdlYkF1dGhuRXJyb3Ioe1xuICAgICAgICAgICAgICAgIG1lc3NhZ2U6IGBUaGUgUlAgSUQgXCIke3B1YmxpY0tleS5ycC5pZH1cIiBpcyBpbnZhbGlkIGZvciB0aGlzIGRvbWFpbmAsXG4gICAgICAgICAgICAgICAgY29kZTogJ0VSUk9SX0lOVkFMSURfUlBfSUQnLFxuICAgICAgICAgICAgICAgIGNhdXNlOiBlcnJvcixcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgfVxuICAgIGVsc2UgaWYgKGVycm9yLm5hbWUgPT09ICdUeXBlRXJyb3InKSB7XG4gICAgICAgIGlmIChwdWJsaWNLZXkudXNlci5pZC5ieXRlTGVuZ3RoIDwgMSB8fCBwdWJsaWNLZXkudXNlci5pZC5ieXRlTGVuZ3RoID4gNjQpIHtcbiAgICAgICAgICAgIC8vIGh0dHBzOi8vd3d3LnczLm9yZy9UUi93ZWJhdXRobi0yLyNzY3RuLWNyZWF0ZUNyZWRlbnRpYWwgKFN0ZXAgNSlcbiAgICAgICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICAgICAgbWVzc2FnZTogJ1VzZXIgSUQgd2FzIG5vdCBiZXR3ZWVuIDEgYW5kIDY0IGNoYXJhY3RlcnMnLFxuICAgICAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9JTlZBTElEX1VTRVJfSURfTEVOR1RIJyxcbiAgICAgICAgICAgICAgICBjYXVzZTogZXJyb3IsXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgIH1cbiAgICBlbHNlIGlmIChlcnJvci5uYW1lID09PSAnVW5rbm93bkVycm9yJykge1xuICAgICAgICAvLyBodHRwczovL3d3dy53My5vcmcvVFIvd2ViYXV0aG4tMi8jc2N0bi1vcC1tYWtlLWNyZWQgKFN0ZXAgMSlcbiAgICAgICAgLy8gaHR0cHM6Ly93d3cudzMub3JnL1RSL3dlYmF1dGhuLTIvI3NjdG4tb3AtbWFrZS1jcmVkIChTdGVwIDgpXG4gICAgICAgIHJldHVybiBuZXcgV2ViQXV0aG5FcnJvcih7XG4gICAgICAgICAgICBtZXNzYWdlOiAnVGhlIGF1dGhlbnRpY2F0b3Igd2FzIHVuYWJsZSB0byBwcm9jZXNzIHRoZSBzcGVjaWZpZWQgb3B0aW9ucywgb3IgY291bGQgbm90IGNyZWF0ZSBhIG5ldyBjcmVkZW50aWFsJyxcbiAgICAgICAgICAgIGNvZGU6ICdFUlJPUl9BVVRIRU5USUNBVE9SX0dFTkVSQUxfRVJST1InLFxuICAgICAgICAgICAgY2F1c2U6IGVycm9yLFxuICAgICAgICB9KTtcbiAgICB9XG4gICAgcmV0dXJuIGVycm9yO1xufVxuIiwiLyoqXG4gKiBBIHNpbXBsZSB0ZXN0IHRvIGRldGVybWluZSBpZiBhIGhvc3RuYW1lIGlzIGEgcHJvcGVybHktZm9ybWF0dGVkIGRvbWFpbiBuYW1lXG4gKlxuICogQSBcInZhbGlkIGRvbWFpblwiIGlzIGRlZmluZWQgaGVyZTogaHR0cHM6Ly91cmwuc3BlYy53aGF0d2cub3JnLyN2YWxpZC1kb21haW5cbiAqXG4gKiBSZWdleCBzb3VyY2VkIGZyb20gaGVyZTpcbiAqIGh0dHBzOi8vd3d3Lm9yZWlsbHkuY29tL2xpYnJhcnkvdmlldy9yZWd1bGFyLWV4cHJlc3Npb25zLWNvb2tib29rLzk3ODE0NDkzMjc0NTMvY2gwOHMxNS5odG1sXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBpc1ZhbGlkRG9tYWluKGhvc3RuYW1lKSB7XG4gICAgcmV0dXJuIChcbiAgICAvLyBDb25zaWRlciBsb2NhbGhvc3QgdmFsaWQgYXMgd2VsbCBzaW5jZSBpdCdzIG9rYXkgd3J0IFNlY3VyZSBDb250ZXh0c1xuICAgIGhvc3RuYW1lID09PSAnbG9jYWxob3N0JyB8fFxuICAgICAgICAvXihbYS16MC05XSsoLVthLXowLTldKykqXFwuKStbYS16XXsyLH0kL2kudGVzdChob3N0bmFtZSkpO1xufVxuIiwiaW1wb3J0IHsgYnJvd3NlclN1cHBvcnRzV2ViQXV0aG4gfSBmcm9tICcuL2Jyb3dzZXJTdXBwb3J0c1dlYkF1dGhuLmpzJztcbi8qKlxuICogRGV0ZXJtaW5lIHdoZXRoZXIgdGhlIGJyb3dzZXIgY2FuIGNvbW11bmljYXRlIHdpdGggYSBidWlsdC1pbiBhdXRoZW50aWNhdG9yLCBsaWtlXG4gKiBUb3VjaCBJRCwgQW5kcm9pZCBmaW5nZXJwcmludCBzY2FubmVyLCBvciBXaW5kb3dzIEhlbGxvLlxuICpcbiAqIFRoaXMgbWV0aG9kIHdpbGwgX25vdF8gYmUgYWJsZSB0byB0ZWxsIHlvdSB0aGUgbmFtZSBvZiB0aGUgcGxhdGZvcm0gYXV0aGVudGljYXRvci5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHBsYXRmb3JtQXV0aGVudGljYXRvcklzQXZhaWxhYmxlKCkge1xuICAgIGlmICghYnJvd3NlclN1cHBvcnRzV2ViQXV0aG4oKSkge1xuICAgICAgICByZXR1cm4gbmV3IFByb21pc2UoKHJlc29sdmUpID0+IHJlc29sdmUoZmFsc2UpKTtcbiAgICB9XG4gICAgcmV0dXJuIFB1YmxpY0tleUNyZWRlbnRpYWwuaXNVc2VyVmVyaWZ5aW5nUGxhdGZvcm1BdXRoZW50aWNhdG9yQXZhaWxhYmxlKCk7XG59XG4iLCJjb25zdCBhdHRhY2htZW50cyA9IFsnY3Jvc3MtcGxhdGZvcm0nLCAncGxhdGZvcm0nXTtcbi8qKlxuICogSWYgcG9zc2libGUgY29lcmNlIGEgYHN0cmluZ2AgdmFsdWUgaW50byBhIGtub3duIGBBdXRoZW50aWNhdG9yQXR0YWNobWVudGBcbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHRvQXV0aGVudGljYXRvckF0dGFjaG1lbnQoYXR0YWNobWVudCkge1xuICAgIGlmICghYXR0YWNobWVudCkge1xuICAgICAgICByZXR1cm47XG4gICAgfVxuICAgIGlmIChhdHRhY2htZW50cy5pbmRleE9mKGF0dGFjaG1lbnQpIDwgMCkge1xuICAgICAgICByZXR1cm47XG4gICAgfVxuICAgIHJldHVybiBhdHRhY2htZW50O1xufVxuIiwiaW1wb3J0IHsgYmFzZTY0VVJMU3RyaW5nVG9CdWZmZXIgfSBmcm9tICcuL2Jhc2U2NFVSTFN0cmluZ1RvQnVmZmVyLmpzJztcbmV4cG9ydCBmdW5jdGlvbiB0b1B1YmxpY0tleUNyZWRlbnRpYWxEZXNjcmlwdG9yKGRlc2NyaXB0b3IpIHtcbiAgICBjb25zdCB7IGlkIH0gPSBkZXNjcmlwdG9yO1xuICAgIHJldHVybiB7XG4gICAgICAgIC4uLmRlc2NyaXB0b3IsXG4gICAgICAgIGlkOiBiYXNlNjRVUkxTdHJpbmdUb0J1ZmZlcihpZCksXG4gICAgICAgIC8qKlxuICAgICAgICAgKiBgZGVzY3JpcHRvci50cmFuc3BvcnRzYCBpcyBhbiBhcnJheSBvZiBvdXIgYEF1dGhlbnRpY2F0b3JUcmFuc3BvcnRGdXR1cmVgIHRoYXQgaW5jbHVkZXMgbmV3ZXJcbiAgICAgICAgICogdHJhbnNwb3J0cyB0aGF0IFR5cGVTY3JpcHQncyBET00gbGliIGlzIGlnbm9yYW50IG9mLiBDb252aW5jZSBUUyB0aGF0IG91ciBsaXN0IG9mIHRyYW5zcG9ydHNcbiAgICAgICAgICogYXJlIGZpbmUgdG8gcGFzcyB0byBXZWJBdXRobiBzaW5jZSBicm93c2VycyB3aWxsIHJlY29nbml6ZSB0aGUgbmV3IHZhbHVlLlxuICAgICAgICAgKi9cbiAgICAgICAgdHJhbnNwb3J0czogZGVzY3JpcHRvci50cmFuc3BvcnRzLFxuICAgIH07XG59XG4iLCJjbGFzcyBCYXNlV2ViQXV0aG5BYm9ydFNlcnZpY2Uge1xuICAgIGNvbnN0cnVjdG9yKCkge1xuICAgICAgICBPYmplY3QuZGVmaW5lUHJvcGVydHkodGhpcywgXCJjb250cm9sbGVyXCIsIHtcbiAgICAgICAgICAgIGVudW1lcmFibGU6IHRydWUsXG4gICAgICAgICAgICBjb25maWd1cmFibGU6IHRydWUsXG4gICAgICAgICAgICB3cml0YWJsZTogdHJ1ZSxcbiAgICAgICAgICAgIHZhbHVlOiB2b2lkIDBcbiAgICAgICAgfSk7XG4gICAgfVxuICAgIGNyZWF0ZU5ld0Fib3J0U2lnbmFsKCkge1xuICAgICAgICAvLyBBYm9ydCBhbnkgZXhpc3RpbmcgY2FsbHMgdG8gbmF2aWdhdG9yLmNyZWRlbnRpYWxzLmNyZWF0ZSgpIG9yIG5hdmlnYXRvci5jcmVkZW50aWFscy5nZXQoKVxuICAgICAgICBpZiAodGhpcy5jb250cm9sbGVyKSB7XG4gICAgICAgICAgICBjb25zdCBhYm9ydEVycm9yID0gbmV3IEVycm9yKCdDYW5jZWxsaW5nIGV4aXN0aW5nIFdlYkF1dGhuIEFQSSBjYWxsIGZvciBuZXcgb25lJyk7XG4gICAgICAgICAgICBhYm9ydEVycm9yLm5hbWUgPSAnQWJvcnRFcnJvcic7XG4gICAgICAgICAgICB0aGlzLmNvbnRyb2xsZXIuYWJvcnQoYWJvcnRFcnJvcik7XG4gICAgICAgIH1cbiAgICAgICAgY29uc3QgbmV3Q29udHJvbGxlciA9IG5ldyBBYm9ydENvbnRyb2xsZXIoKTtcbiAgICAgICAgdGhpcy5jb250cm9sbGVyID0gbmV3Q29udHJvbGxlcjtcbiAgICAgICAgcmV0dXJuIG5ld0NvbnRyb2xsZXIuc2lnbmFsO1xuICAgIH1cbiAgICBjYW5jZWxDZXJlbW9ueSgpIHtcbiAgICAgICAgaWYgKHRoaXMuY29udHJvbGxlcikge1xuICAgICAgICAgICAgY29uc3QgYWJvcnRFcnJvciA9IG5ldyBFcnJvcignTWFudWFsbHkgY2FuY2VsbGluZyBleGlzdGluZyBXZWJBdXRobiBBUEkgY2FsbCcpO1xuICAgICAgICAgICAgYWJvcnRFcnJvci5uYW1lID0gJ0Fib3J0RXJyb3InO1xuICAgICAgICAgICAgdGhpcy5jb250cm9sbGVyLmFib3J0KGFib3J0RXJyb3IpO1xuICAgICAgICAgICAgdGhpcy5jb250cm9sbGVyID0gdW5kZWZpbmVkO1xuICAgICAgICB9XG4gICAgfVxufVxuLyoqXG4gKiBBIHNlcnZpY2Ugc2luZ2xldG9uIHRvIGhlbHAgZW5zdXJlIHRoYXQgb25seSBhIHNpbmdsZSBXZWJBdXRobiBjZXJlbW9ueSBpcyBhY3RpdmUgYXQgYSB0aW1lLlxuICpcbiAqIFVzZXJzIG9mICoqQHNpbXBsZXdlYmF1dGhuL2Jyb3dzZXIqKiBzaG91bGRuJ3QgdHlwaWNhbGx5IG5lZWQgdG8gdXNlIHRoaXMsIGJ1dCBpdCBjYW4gaGVscCBlLmcuXG4gKiBkZXZlbG9wZXJzIGJ1aWxkaW5nIHByb2plY3RzIHRoYXQgdXNlIGNsaWVudC1zaWRlIHJvdXRpbmcgdG8gYmV0dGVyIGNvbnRyb2wgdGhlIGJlaGF2aW9yIG9mXG4gKiB0aGVpciBVWCBpbiByZXNwb25zZSB0byByb3V0ZXIgbmF2aWdhdGlvbiBldmVudHMuXG4gKi9cbmV4cG9ydCBjb25zdCBXZWJBdXRobkFib3J0U2VydmljZSA9IG5ldyBCYXNlV2ViQXV0aG5BYm9ydFNlcnZpY2UoKTtcbiIsIi8qKlxuICogQSBjdXN0b20gRXJyb3IgdXNlZCB0byByZXR1cm4gYSBtb3JlIG51YW5jZWQgZXJyb3IgZGV0YWlsaW5nIF93aHlfIG9uZSBvZiB0aGUgZWlnaHQgZG9jdW1lbnRlZFxuICogZXJyb3JzIGluIHRoZSBzcGVjIHdhcyByYWlzZWQgYWZ0ZXIgY2FsbGluZyBgbmF2aWdhdG9yLmNyZWRlbnRpYWxzLmNyZWF0ZSgpYCBvclxuICogYG5hdmlnYXRvci5jcmVkZW50aWFscy5nZXQoKWA6XG4gKlxuICogLSBgQWJvcnRFcnJvcmBcbiAqIC0gYENvbnN0cmFpbnRFcnJvcmBcbiAqIC0gYEludmFsaWRTdGF0ZUVycm9yYFxuICogLSBgTm90QWxsb3dlZEVycm9yYFxuICogLSBgTm90U3VwcG9ydGVkRXJyb3JgXG4gKiAtIGBTZWN1cml0eUVycm9yYFxuICogLSBgVHlwZUVycm9yYFxuICogLSBgVW5rbm93bkVycm9yYFxuICpcbiAqIEVycm9yIG1lc3NhZ2VzIHdlcmUgZGV0ZXJtaW5lZCB0aHJvdWdoIGludmVzdGlnYXRpb24gb2YgdGhlIHNwZWMgdG8gZGV0ZXJtaW5lIHVuZGVyIHdoaWNoXG4gKiBzY2VuYXJpb3MgYSBnaXZlbiBlcnJvciB3b3VsZCBiZSByYWlzZWQuXG4gKi9cbmV4cG9ydCBjbGFzcyBXZWJBdXRobkVycm9yIGV4dGVuZHMgRXJyb3Ige1xuICAgIGNvbnN0cnVjdG9yKHsgbWVzc2FnZSwgY29kZSwgY2F1c2UsIG5hbWUsIH0pIHtcbiAgICAgICAgLy8gQHRzLWlnbm9yZTogaGVscCBSb2xsdXAgdW5kZXJzdGFuZCB0aGF0IGBjYXVzZWAgaXMgb2theSB0byBzZXRcbiAgICAgICAgc3VwZXIobWVzc2FnZSwgeyBjYXVzZSB9KTtcbiAgICAgICAgT2JqZWN0LmRlZmluZVByb3BlcnR5KHRoaXMsIFwiY29kZVwiLCB7XG4gICAgICAgICAgICBlbnVtZXJhYmxlOiB0cnVlLFxuICAgICAgICAgICAgY29uZmlndXJhYmxlOiB0cnVlLFxuICAgICAgICAgICAgd3JpdGFibGU6IHRydWUsXG4gICAgICAgICAgICB2YWx1ZTogdm9pZCAwXG4gICAgICAgIH0pO1xuICAgICAgICB0aGlzLm5hbWUgPSBuYW1lID8/IGNhdXNlLm5hbWU7XG4gICAgICAgIHRoaXMuY29kZSA9IGNvZGU7XG4gICAgfVxufVxuIiwiZXhwb3J0ICogZnJvbSAnLi9tZXRob2RzL3N0YXJ0UmVnaXN0cmF0aW9uLmpzJztcbmV4cG9ydCAqIGZyb20gJy4vbWV0aG9kcy9zdGFydEF1dGhlbnRpY2F0aW9uLmpzJztcbmV4cG9ydCAqIGZyb20gJy4vaGVscGVycy9icm93c2VyU3VwcG9ydHNXZWJBdXRobi5qcyc7XG5leHBvcnQgKiBmcm9tICcuL2hlbHBlcnMvcGxhdGZvcm1BdXRoZW50aWNhdG9ySXNBdmFpbGFibGUuanMnO1xuZXhwb3J0ICogZnJvbSAnLi9oZWxwZXJzL2Jyb3dzZXJTdXBwb3J0c1dlYkF1dGhuQXV0b2ZpbGwuanMnO1xuZXhwb3J0ICogZnJvbSAnLi9oZWxwZXJzL2Jhc2U2NFVSTFN0cmluZ1RvQnVmZmVyLmpzJztcbmV4cG9ydCAqIGZyb20gJy4vaGVscGVycy9idWZmZXJUb0Jhc2U2NFVSTFN0cmluZy5qcyc7XG5leHBvcnQgKiBmcm9tICcuL2hlbHBlcnMvd2ViQXV0aG5BYm9ydFNlcnZpY2UuanMnO1xuZXhwb3J0ICogZnJvbSAnLi9oZWxwZXJzL3dlYkF1dGhuRXJyb3IuanMnO1xuZXhwb3J0ICogZnJvbSAnLi90eXBlcy9pbmRleC5qcyc7XG4iLCJpbXBvcnQgeyBidWZmZXJUb0Jhc2U2NFVSTFN0cmluZyB9IGZyb20gJy4uL2hlbHBlcnMvYnVmZmVyVG9CYXNlNjRVUkxTdHJpbmcuanMnO1xuaW1wb3J0IHsgYmFzZTY0VVJMU3RyaW5nVG9CdWZmZXIgfSBmcm9tICcuLi9oZWxwZXJzL2Jhc2U2NFVSTFN0cmluZ1RvQnVmZmVyLmpzJztcbmltcG9ydCB7IGJyb3dzZXJTdXBwb3J0c1dlYkF1dGhuIH0gZnJvbSAnLi4vaGVscGVycy9icm93c2VyU3VwcG9ydHNXZWJBdXRobi5qcyc7XG5pbXBvcnQgeyBicm93c2VyU3VwcG9ydHNXZWJBdXRobkF1dG9maWxsIH0gZnJvbSAnLi4vaGVscGVycy9icm93c2VyU3VwcG9ydHNXZWJBdXRobkF1dG9maWxsLmpzJztcbmltcG9ydCB7IHRvUHVibGljS2V5Q3JlZGVudGlhbERlc2NyaXB0b3IgfSBmcm9tICcuLi9oZWxwZXJzL3RvUHVibGljS2V5Q3JlZGVudGlhbERlc2NyaXB0b3IuanMnO1xuaW1wb3J0IHsgaWRlbnRpZnlBdXRoZW50aWNhdGlvbkVycm9yIH0gZnJvbSAnLi4vaGVscGVycy9pZGVudGlmeUF1dGhlbnRpY2F0aW9uRXJyb3IuanMnO1xuaW1wb3J0IHsgV2ViQXV0aG5BYm9ydFNlcnZpY2UgfSBmcm9tICcuLi9oZWxwZXJzL3dlYkF1dGhuQWJvcnRTZXJ2aWNlLmpzJztcbmltcG9ydCB7IHRvQXV0aGVudGljYXRvckF0dGFjaG1lbnQgfSBmcm9tICcuLi9oZWxwZXJzL3RvQXV0aGVudGljYXRvckF0dGFjaG1lbnQuanMnO1xuLyoqXG4gKiBCZWdpbiBhdXRoZW50aWNhdG9yIFwibG9naW5cIiB2aWEgV2ViQXV0aG4gYXNzZXJ0aW9uXG4gKlxuICogQHBhcmFtIG9wdGlvbnNKU09OIE91dHB1dCBmcm9tICoqQHNpbXBsZXdlYmF1dGhuL3NlcnZlcioqJ3MgYGdlbmVyYXRlQXV0aGVudGljYXRpb25PcHRpb25zKClgXG4gKiBAcGFyYW0gdXNlQnJvd3NlckF1dG9maWxsIChPcHRpb25hbCkgSW5pdGlhbGl6ZSBjb25kaXRpb25hbCBVSSB0byBlbmFibGUgbG9nZ2luZyBpbiB2aWEgYnJvd3NlciBhdXRvZmlsbCBwcm9tcHRzLiBEZWZhdWx0cyB0byBgZmFsc2VgLlxuICogQHBhcmFtIHZlcmlmeUJyb3dzZXJBdXRvZmlsbElucHV0IChPcHRpb25hbCkgRW5zdXJlIGEgc3VpdGFibGUgYDxpbnB1dD5gIGVsZW1lbnQgaXMgcHJlc2VudCB3aGVuIGB1c2VCcm93c2VyQXV0b2ZpbGxgIGlzIGB0cnVlYC4gRGVmYXVsdHMgdG8gYHRydWVgLlxuICovXG5leHBvcnQgYXN5bmMgZnVuY3Rpb24gc3RhcnRBdXRoZW50aWNhdGlvbihvcHRpb25zKSB7XG4gICAgLy8gQHRzLWlnbm9yZTogSW50ZW50aW9uYWxseSBjaGVjayBmb3Igb2xkIGNhbGwgc3RydWN0dXJlIHRvIHdhcm4gYWJvdXQgaW1wcm9wZXIgQVBJIGNhbGxcbiAgICBpZiAoIW9wdGlvbnMub3B0aW9uc0pTT04gJiYgb3B0aW9ucy5jaGFsbGVuZ2UpIHtcbiAgICAgICAgY29uc29sZS53YXJuKCdzdGFydEF1dGhlbnRpY2F0aW9uKCkgd2FzIG5vdCBjYWxsZWQgY29ycmVjdGx5LiBJdCB3aWxsIHRyeSB0byBjb250aW51ZSB3aXRoIHRoZSBwcm92aWRlZCBvcHRpb25zLCBidXQgdGhpcyBjYWxsIHNob3VsZCBiZSByZWZhY3RvcmVkIHRvIHVzZSB0aGUgZXhwZWN0ZWQgY2FsbCBzdHJ1Y3R1cmUgaW5zdGVhZC4gU2VlIGh0dHBzOi8vc2ltcGxld2ViYXV0aG4uZGV2L2RvY3MvcGFja2FnZXMvYnJvd3NlciN0eXBlZXJyb3ItY2Fubm90LXJlYWQtcHJvcGVydGllcy1vZi11bmRlZmluZWQtcmVhZGluZy1jaGFsbGVuZ2UgZm9yIG1vcmUgaW5mb3JtYXRpb24uJyk7XG4gICAgICAgIC8vIEB0cy1pZ25vcmU6IFJlYXNzaWduIHRoZSBvcHRpb25zLCBwYXNzZWQgaW4gYXMgYSBwb3NpdGlvbmFsIGFyZ3VtZW50LCB0byB0aGUgZXhwZWN0ZWQgdmFyaWFibGVcbiAgICAgICAgb3B0aW9ucyA9IHsgb3B0aW9uc0pTT046IG9wdGlvbnMgfTtcbiAgICB9XG4gICAgY29uc3QgeyBvcHRpb25zSlNPTiwgdXNlQnJvd3NlckF1dG9maWxsID0gZmFsc2UsIHZlcmlmeUJyb3dzZXJBdXRvZmlsbElucHV0ID0gdHJ1ZSwgfSA9IG9wdGlvbnM7XG4gICAgaWYgKCFicm93c2VyU3VwcG9ydHNXZWJBdXRobigpKSB7XG4gICAgICAgIHRocm93IG5ldyBFcnJvcignV2ViQXV0aG4gaXMgbm90IHN1cHBvcnRlZCBpbiB0aGlzIGJyb3dzZXInKTtcbiAgICB9XG4gICAgLy8gV2UgbmVlZCB0byBhdm9pZCBwYXNzaW5nIGVtcHR5IGFycmF5IHRvIGF2b2lkIGJsb2NraW5nIHJldHJpZXZhbFxuICAgIC8vIG9mIHB1YmxpYyBrZXlcbiAgICBsZXQgYWxsb3dDcmVkZW50aWFscztcbiAgICBpZiAob3B0aW9uc0pTT04uYWxsb3dDcmVkZW50aWFscz8ubGVuZ3RoICE9PSAwKSB7XG4gICAgICAgIGFsbG93Q3JlZGVudGlhbHMgPSBvcHRpb25zSlNPTi5hbGxvd0NyZWRlbnRpYWxzPy5tYXAodG9QdWJsaWNLZXlDcmVkZW50aWFsRGVzY3JpcHRvcik7XG4gICAgfVxuICAgIC8vIFdlIG5lZWQgdG8gY29udmVydCBzb21lIHZhbHVlcyB0byBVaW50OEFycmF5cyBiZWZvcmUgcGFzc2luZyB0aGUgY3JlZGVudGlhbHMgdG8gdGhlIG5hdmlnYXRvclxuICAgIGNvbnN0IHB1YmxpY0tleSA9IHtcbiAgICAgICAgLi4ub3B0aW9uc0pTT04sXG4gICAgICAgIGNoYWxsZW5nZTogYmFzZTY0VVJMU3RyaW5nVG9CdWZmZXIob3B0aW9uc0pTT04uY2hhbGxlbmdlKSxcbiAgICAgICAgYWxsb3dDcmVkZW50aWFscyxcbiAgICB9O1xuICAgIC8vIFByZXBhcmUgb3B0aW9ucyBmb3IgYC5nZXQoKWBcbiAgICBjb25zdCBnZXRPcHRpb25zID0ge307XG4gICAgLyoqXG4gICAgICogU2V0IHVwIHRoZSBwYWdlIHRvIHByb21wdCB0aGUgdXNlciB0byBzZWxlY3QgYSBjcmVkZW50aWFsIGZvciBhdXRoZW50aWNhdGlvbiB2aWEgdGhlIGJyb3dzZXInc1xuICAgICAqIGlucHV0IGF1dG9maWxsIG1lY2hhbmlzbS5cbiAgICAgKi9cbiAgICBpZiAodXNlQnJvd3NlckF1dG9maWxsKSB7XG4gICAgICAgIGlmICghKGF3YWl0IGJyb3dzZXJTdXBwb3J0c1dlYkF1dGhuQXV0b2ZpbGwoKSkpIHtcbiAgICAgICAgICAgIHRocm93IEVycm9yKCdCcm93c2VyIGRvZXMgbm90IHN1cHBvcnQgV2ViQXV0aG4gYXV0b2ZpbGwnKTtcbiAgICAgICAgfVxuICAgICAgICAvLyBDaGVjayBmb3IgYW4gPGlucHV0PiB3aXRoIFwid2ViYXV0aG5cIiBpbiBpdHMgYGF1dG9jb21wbGV0ZWAgYXR0cmlidXRlXG4gICAgICAgIGNvbnN0IGVsaWdpYmxlSW5wdXRzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbChcImlucHV0W2F1dG9jb21wbGV0ZSQ9J3dlYmF1dGhuJ11cIik7XG4gICAgICAgIC8vIFdlYkF1dGhuIGF1dG9maWxsIHJlcXVpcmVzIGF0IGxlYXN0IG9uZSB2YWxpZCBpbnB1dFxuICAgICAgICBpZiAoZWxpZ2libGVJbnB1dHMubGVuZ3RoIDwgMSAmJiB2ZXJpZnlCcm93c2VyQXV0b2ZpbGxJbnB1dCkge1xuICAgICAgICAgICAgdGhyb3cgRXJyb3IoJ05vIDxpbnB1dD4gd2l0aCBcIndlYmF1dGhuXCIgYXMgdGhlIG9ubHkgb3IgbGFzdCB2YWx1ZSBpbiBpdHMgYGF1dG9jb21wbGV0ZWAgYXR0cmlidXRlIHdhcyBkZXRlY3RlZCcpO1xuICAgICAgICB9XG4gICAgICAgIC8vIGBDcmVkZW50aWFsTWVkaWF0aW9uUmVxdWlyZW1lbnRgIGRvZXNuJ3Qga25vdyBhYm91dCBcImNvbmRpdGlvbmFsXCIgeWV0IGFzIG9mXG4gICAgICAgIC8vIHR5cGVzY3JpcHRANC42LjNcbiAgICAgICAgZ2V0T3B0aW9ucy5tZWRpYXRpb24gPSAnY29uZGl0aW9uYWwnO1xuICAgICAgICAvLyBDb25kaXRpb25hbCBVSSByZXF1aXJlcyBhbiBlbXB0eSBhbGxvdyBsaXN0XG4gICAgICAgIHB1YmxpY0tleS5hbGxvd0NyZWRlbnRpYWxzID0gW107XG4gICAgfVxuICAgIC8vIEZpbmFsaXplIG9wdGlvbnNcbiAgICBnZXRPcHRpb25zLnB1YmxpY0tleSA9IHB1YmxpY0tleTtcbiAgICAvLyBTZXQgdXAgdGhlIGFiaWxpdHkgdG8gY2FuY2VsIHRoaXMgcmVxdWVzdCBpZiB0aGUgdXNlciBhdHRlbXB0cyBhbm90aGVyXG4gICAgZ2V0T3B0aW9ucy5zaWduYWwgPSBXZWJBdXRobkFib3J0U2VydmljZS5jcmVhdGVOZXdBYm9ydFNpZ25hbCgpO1xuICAgIC8vIFdhaXQgZm9yIHRoZSB1c2VyIHRvIGNvbXBsZXRlIGFzc2VydGlvblxuICAgIGxldCBjcmVkZW50aWFsO1xuICAgIHRyeSB7XG4gICAgICAgIGNyZWRlbnRpYWwgPSAoYXdhaXQgbmF2aWdhdG9yLmNyZWRlbnRpYWxzLmdldChnZXRPcHRpb25zKSk7XG4gICAgfVxuICAgIGNhdGNoIChlcnIpIHtcbiAgICAgICAgdGhyb3cgaWRlbnRpZnlBdXRoZW50aWNhdGlvbkVycm9yKHsgZXJyb3I6IGVyciwgb3B0aW9uczogZ2V0T3B0aW9ucyB9KTtcbiAgICB9XG4gICAgaWYgKCFjcmVkZW50aWFsKSB7XG4gICAgICAgIHRocm93IG5ldyBFcnJvcignQXV0aGVudGljYXRpb24gd2FzIG5vdCBjb21wbGV0ZWQnKTtcbiAgICB9XG4gICAgY29uc3QgeyBpZCwgcmF3SWQsIHJlc3BvbnNlLCB0eXBlIH0gPSBjcmVkZW50aWFsO1xuICAgIGxldCB1c2VySGFuZGxlID0gdW5kZWZpbmVkO1xuICAgIGlmIChyZXNwb25zZS51c2VySGFuZGxlKSB7XG4gICAgICAgIHVzZXJIYW5kbGUgPSBidWZmZXJUb0Jhc2U2NFVSTFN0cmluZyhyZXNwb25zZS51c2VySGFuZGxlKTtcbiAgICB9XG4gICAgLy8gQ29udmVydCB2YWx1ZXMgdG8gYmFzZTY0IHRvIG1ha2UgaXQgZWFzaWVyIHRvIHNlbmQgYmFjayB0byB0aGUgc2VydmVyXG4gICAgcmV0dXJuIHtcbiAgICAgICAgaWQsXG4gICAgICAgIHJhd0lkOiBidWZmZXJUb0Jhc2U2NFVSTFN0cmluZyhyYXdJZCksXG4gICAgICAgIHJlc3BvbnNlOiB7XG4gICAgICAgICAgICBhdXRoZW50aWNhdG9yRGF0YTogYnVmZmVyVG9CYXNlNjRVUkxTdHJpbmcocmVzcG9uc2UuYXV0aGVudGljYXRvckRhdGEpLFxuICAgICAgICAgICAgY2xpZW50RGF0YUpTT046IGJ1ZmZlclRvQmFzZTY0VVJMU3RyaW5nKHJlc3BvbnNlLmNsaWVudERhdGFKU09OKSxcbiAgICAgICAgICAgIHNpZ25hdHVyZTogYnVmZmVyVG9CYXNlNjRVUkxTdHJpbmcocmVzcG9uc2Uuc2lnbmF0dXJlKSxcbiAgICAgICAgICAgIHVzZXJIYW5kbGUsXG4gICAgICAgIH0sXG4gICAgICAgIHR5cGUsXG4gICAgICAgIGNsaWVudEV4dGVuc2lvblJlc3VsdHM6IGNyZWRlbnRpYWwuZ2V0Q2xpZW50RXh0ZW5zaW9uUmVzdWx0cygpLFxuICAgICAgICBhdXRoZW50aWNhdG9yQXR0YWNobWVudDogdG9BdXRoZW50aWNhdG9yQXR0YWNobWVudChjcmVkZW50aWFsLmF1dGhlbnRpY2F0b3JBdHRhY2htZW50KSxcbiAgICB9O1xufVxuIiwiaW1wb3J0IHsgYnVmZmVyVG9CYXNlNjRVUkxTdHJpbmcgfSBmcm9tICcuLi9oZWxwZXJzL2J1ZmZlclRvQmFzZTY0VVJMU3RyaW5nLmpzJztcbmltcG9ydCB7IGJhc2U2NFVSTFN0cmluZ1RvQnVmZmVyIH0gZnJvbSAnLi4vaGVscGVycy9iYXNlNjRVUkxTdHJpbmdUb0J1ZmZlci5qcyc7XG5pbXBvcnQgeyBicm93c2VyU3VwcG9ydHNXZWJBdXRobiB9IGZyb20gJy4uL2hlbHBlcnMvYnJvd3NlclN1cHBvcnRzV2ViQXV0aG4uanMnO1xuaW1wb3J0IHsgdG9QdWJsaWNLZXlDcmVkZW50aWFsRGVzY3JpcHRvciB9IGZyb20gJy4uL2hlbHBlcnMvdG9QdWJsaWNLZXlDcmVkZW50aWFsRGVzY3JpcHRvci5qcyc7XG5pbXBvcnQgeyBpZGVudGlmeVJlZ2lzdHJhdGlvbkVycm9yIH0gZnJvbSAnLi4vaGVscGVycy9pZGVudGlmeVJlZ2lzdHJhdGlvbkVycm9yLmpzJztcbmltcG9ydCB7IFdlYkF1dGhuQWJvcnRTZXJ2aWNlIH0gZnJvbSAnLi4vaGVscGVycy93ZWJBdXRobkFib3J0U2VydmljZS5qcyc7XG5pbXBvcnQgeyB0b0F1dGhlbnRpY2F0b3JBdHRhY2htZW50IH0gZnJvbSAnLi4vaGVscGVycy90b0F1dGhlbnRpY2F0b3JBdHRhY2htZW50LmpzJztcbi8qKlxuICogQmVnaW4gYXV0aGVudGljYXRvciBcInJlZ2lzdHJhdGlvblwiIHZpYSBXZWJBdXRobiBhdHRlc3RhdGlvblxuICpcbiAqIEBwYXJhbSBvcHRpb25zSlNPTiBPdXRwdXQgZnJvbSAqKkBzaW1wbGV3ZWJhdXRobi9zZXJ2ZXIqKidzIGBnZW5lcmF0ZVJlZ2lzdHJhdGlvbk9wdGlvbnMoKWBcbiAqIEBwYXJhbSB1c2VBdXRvUmVnaXN0ZXIgKE9wdGlvbmFsKSBUcnkgdG8gc2lsZW50bHkgY3JlYXRlIGEgcGFzc2tleSB3aXRoIHRoZSBwYXNzd29yZCBtYW5hZ2VyIHRoYXQgdGhlIHVzZXIganVzdCBzaWduZWQgaW4gd2l0aC4gRGVmYXVsdHMgdG8gYGZhbHNlYC5cbiAqL1xuZXhwb3J0IGFzeW5jIGZ1bmN0aW9uIHN0YXJ0UmVnaXN0cmF0aW9uKG9wdGlvbnMpIHtcbiAgICAvLyBAdHMtaWdub3JlOiBJbnRlbnRpb25hbGx5IGNoZWNrIGZvciBvbGQgY2FsbCBzdHJ1Y3R1cmUgdG8gd2FybiBhYm91dCBpbXByb3BlciBBUEkgY2FsbFxuICAgIGlmICghb3B0aW9ucy5vcHRpb25zSlNPTiAmJiBvcHRpb25zLmNoYWxsZW5nZSkge1xuICAgICAgICBjb25zb2xlLndhcm4oJ3N0YXJ0UmVnaXN0cmF0aW9uKCkgd2FzIG5vdCBjYWxsZWQgY29ycmVjdGx5LiBJdCB3aWxsIHRyeSB0byBjb250aW51ZSB3aXRoIHRoZSBwcm92aWRlZCBvcHRpb25zLCBidXQgdGhpcyBjYWxsIHNob3VsZCBiZSByZWZhY3RvcmVkIHRvIHVzZSB0aGUgZXhwZWN0ZWQgY2FsbCBzdHJ1Y3R1cmUgaW5zdGVhZC4gU2VlIGh0dHBzOi8vc2ltcGxld2ViYXV0aG4uZGV2L2RvY3MvcGFja2FnZXMvYnJvd3NlciN0eXBlZXJyb3ItY2Fubm90LXJlYWQtcHJvcGVydGllcy1vZi11bmRlZmluZWQtcmVhZGluZy1jaGFsbGVuZ2UgZm9yIG1vcmUgaW5mb3JtYXRpb24uJyk7XG4gICAgICAgIC8vIEB0cy1pZ25vcmU6IFJlYXNzaWduIHRoZSBvcHRpb25zLCBwYXNzZWQgaW4gYXMgYSBwb3NpdGlvbmFsIGFyZ3VtZW50LCB0byB0aGUgZXhwZWN0ZWQgdmFyaWFibGVcbiAgICAgICAgb3B0aW9ucyA9IHsgb3B0aW9uc0pTT046IG9wdGlvbnMgfTtcbiAgICB9XG4gICAgY29uc3QgeyBvcHRpb25zSlNPTiwgdXNlQXV0b1JlZ2lzdGVyID0gZmFsc2UgfSA9IG9wdGlvbnM7XG4gICAgaWYgKCFicm93c2VyU3VwcG9ydHNXZWJBdXRobigpKSB7XG4gICAgICAgIHRocm93IG5ldyBFcnJvcignV2ViQXV0aG4gaXMgbm90IHN1cHBvcnRlZCBpbiB0aGlzIGJyb3dzZXInKTtcbiAgICB9XG4gICAgLy8gV2UgbmVlZCB0byBjb252ZXJ0IHNvbWUgdmFsdWVzIHRvIFVpbnQ4QXJyYXlzIGJlZm9yZSBwYXNzaW5nIHRoZSBjcmVkZW50aWFscyB0byB0aGUgbmF2aWdhdG9yXG4gICAgY29uc3QgcHVibGljS2V5ID0ge1xuICAgICAgICAuLi5vcHRpb25zSlNPTixcbiAgICAgICAgY2hhbGxlbmdlOiBiYXNlNjRVUkxTdHJpbmdUb0J1ZmZlcihvcHRpb25zSlNPTi5jaGFsbGVuZ2UpLFxuICAgICAgICB1c2VyOiB7XG4gICAgICAgICAgICAuLi5vcHRpb25zSlNPTi51c2VyLFxuICAgICAgICAgICAgaWQ6IGJhc2U2NFVSTFN0cmluZ1RvQnVmZmVyKG9wdGlvbnNKU09OLnVzZXIuaWQpLFxuICAgICAgICB9LFxuICAgICAgICBleGNsdWRlQ3JlZGVudGlhbHM6IG9wdGlvbnNKU09OLmV4Y2x1ZGVDcmVkZW50aWFscz8ubWFwKHRvUHVibGljS2V5Q3JlZGVudGlhbERlc2NyaXB0b3IpLFxuICAgIH07XG4gICAgLy8gUHJlcGFyZSBvcHRpb25zIGZvciBgLmNyZWF0ZSgpYFxuICAgIGNvbnN0IGNyZWF0ZU9wdGlvbnMgPSB7fTtcbiAgICAvKipcbiAgICAgKiBUcnkgdG8gdXNlIGNvbmRpdGlvbmFsIGNyZWF0ZSB0byByZWdpc3RlciBhIHBhc3NrZXkgZm9yIHRoZSB1c2VyIHdpdGggdGhlIHBhc3N3b3JkIG1hbmFnZXJcbiAgICAgKiB0aGUgdXNlciBqdXN0IHVzZWQgdG8gYXV0aGVudGljYXRlIHdpdGguIFRoZSB1c2VyIHdvbid0IGJlIHNob3duIGFueSBwcm9taW5lbnQgVUkgYnkgdGhlXG4gICAgICogYnJvd3Nlci5cbiAgICAgKi9cbiAgICBpZiAodXNlQXV0b1JlZ2lzdGVyKSB7XG4gICAgICAgIC8vIEB0cy1pZ25vcmU6IGBtZWRpYXRpb25gIGRvZXNuJ3QgeWV0IGV4aXN0IG9uIENyZWRlbnRpYWxDcmVhdGlvbk9wdGlvbnMgYnV0IGl0J3MgcG9zc2libGUgYXMgb2YgU2VwdCAyMDI0XG4gICAgICAgIGNyZWF0ZU9wdGlvbnMubWVkaWF0aW9uID0gJ2NvbmRpdGlvbmFsJztcbiAgICB9XG4gICAgLy8gRmluYWxpemUgb3B0aW9uc1xuICAgIGNyZWF0ZU9wdGlvbnMucHVibGljS2V5ID0gcHVibGljS2V5O1xuICAgIC8vIFNldCB1cCB0aGUgYWJpbGl0eSB0byBjYW5jZWwgdGhpcyByZXF1ZXN0IGlmIHRoZSB1c2VyIGF0dGVtcHRzIGFub3RoZXJcbiAgICBjcmVhdGVPcHRpb25zLnNpZ25hbCA9IFdlYkF1dGhuQWJvcnRTZXJ2aWNlLmNyZWF0ZU5ld0Fib3J0U2lnbmFsKCk7XG4gICAgLy8gV2FpdCBmb3IgdGhlIHVzZXIgdG8gY29tcGxldGUgYXR0ZXN0YXRpb25cbiAgICBsZXQgY3JlZGVudGlhbDtcbiAgICB0cnkge1xuICAgICAgICBjcmVkZW50aWFsID0gKGF3YWl0IG5hdmlnYXRvci5jcmVkZW50aWFscy5jcmVhdGUoY3JlYXRlT3B0aW9ucykpO1xuICAgIH1cbiAgICBjYXRjaCAoZXJyKSB7XG4gICAgICAgIHRocm93IGlkZW50aWZ5UmVnaXN0cmF0aW9uRXJyb3IoeyBlcnJvcjogZXJyLCBvcHRpb25zOiBjcmVhdGVPcHRpb25zIH0pO1xuICAgIH1cbiAgICBpZiAoIWNyZWRlbnRpYWwpIHtcbiAgICAgICAgdGhyb3cgbmV3IEVycm9yKCdSZWdpc3RyYXRpb24gd2FzIG5vdCBjb21wbGV0ZWQnKTtcbiAgICB9XG4gICAgY29uc3QgeyBpZCwgcmF3SWQsIHJlc3BvbnNlLCB0eXBlIH0gPSBjcmVkZW50aWFsO1xuICAgIC8vIENvbnRpbnVlIHRvIHBsYXkgaXQgc2FmZSB3aXRoIGBnZXRUcmFuc3BvcnRzKClgIGZvciBub3csIGV2ZW4gd2hlbiBMMyB0eXBlcyBzYXkgaXQncyByZXF1aXJlZFxuICAgIGxldCB0cmFuc3BvcnRzID0gdW5kZWZpbmVkO1xuICAgIGlmICh0eXBlb2YgcmVzcG9uc2UuZ2V0VHJhbnNwb3J0cyA9PT0gJ2Z1bmN0aW9uJykge1xuICAgICAgICB0cmFuc3BvcnRzID0gcmVzcG9uc2UuZ2V0VHJhbnNwb3J0cygpO1xuICAgIH1cbiAgICAvLyBMMyBzYXlzIHRoaXMgaXMgcmVxdWlyZWQsIGJ1dCBicm93c2VyIGFuZCB3ZWJ2aWV3IHN1cHBvcnQgYXJlIHN0aWxsIG5vdCBndWFyYW50ZWVkLlxuICAgIGxldCByZXNwb25zZVB1YmxpY0tleUFsZ29yaXRobSA9IHVuZGVmaW5lZDtcbiAgICBpZiAodHlwZW9mIHJlc3BvbnNlLmdldFB1YmxpY0tleUFsZ29yaXRobSA9PT0gJ2Z1bmN0aW9uJykge1xuICAgICAgICB0cnkge1xuICAgICAgICAgICAgcmVzcG9uc2VQdWJsaWNLZXlBbGdvcml0aG0gPSByZXNwb25zZS5nZXRQdWJsaWNLZXlBbGdvcml0aG0oKTtcbiAgICAgICAgfVxuICAgICAgICBjYXRjaCAoZXJyb3IpIHtcbiAgICAgICAgICAgIHdhcm5PbkJyb2tlbkltcGxlbWVudGF0aW9uKCdnZXRQdWJsaWNLZXlBbGdvcml0aG0oKScsIGVycm9yKTtcbiAgICAgICAgfVxuICAgIH1cbiAgICBsZXQgcmVzcG9uc2VQdWJsaWNLZXkgPSB1bmRlZmluZWQ7XG4gICAgaWYgKHR5cGVvZiByZXNwb25zZS5nZXRQdWJsaWNLZXkgPT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgdHJ5IHtcbiAgICAgICAgICAgIGNvbnN0IF9wdWJsaWNLZXkgPSByZXNwb25zZS5nZXRQdWJsaWNLZXkoKTtcbiAgICAgICAgICAgIGlmIChfcHVibGljS2V5ICE9PSBudWxsKSB7XG4gICAgICAgICAgICAgICAgcmVzcG9uc2VQdWJsaWNLZXkgPSBidWZmZXJUb0Jhc2U2NFVSTFN0cmluZyhfcHVibGljS2V5KTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICBjYXRjaCAoZXJyb3IpIHtcbiAgICAgICAgICAgIHdhcm5PbkJyb2tlbkltcGxlbWVudGF0aW9uKCdnZXRQdWJsaWNLZXkoKScsIGVycm9yKTtcbiAgICAgICAgfVxuICAgIH1cbiAgICAvLyBMMyBzYXlzIHRoaXMgaXMgcmVxdWlyZWQsIGJ1dCBicm93c2VyIGFuZCB3ZWJ2aWV3IHN1cHBvcnQgYXJlIHN0aWxsIG5vdCBndWFyYW50ZWVkLlxuICAgIGxldCByZXNwb25zZUF1dGhlbnRpY2F0b3JEYXRhO1xuICAgIGlmICh0eXBlb2YgcmVzcG9uc2UuZ2V0QXV0aGVudGljYXRvckRhdGEgPT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgdHJ5IHtcbiAgICAgICAgICAgIHJlc3BvbnNlQXV0aGVudGljYXRvckRhdGEgPSBidWZmZXJUb0Jhc2U2NFVSTFN0cmluZyhyZXNwb25zZS5nZXRBdXRoZW50aWNhdG9yRGF0YSgpKTtcbiAgICAgICAgfVxuICAgICAgICBjYXRjaCAoZXJyb3IpIHtcbiAgICAgICAgICAgIHdhcm5PbkJyb2tlbkltcGxlbWVudGF0aW9uKCdnZXRBdXRoZW50aWNhdG9yRGF0YSgpJywgZXJyb3IpO1xuICAgICAgICB9XG4gICAgfVxuICAgIHJldHVybiB7XG4gICAgICAgIGlkLFxuICAgICAgICByYXdJZDogYnVmZmVyVG9CYXNlNjRVUkxTdHJpbmcocmF3SWQpLFxuICAgICAgICByZXNwb25zZToge1xuICAgICAgICAgICAgYXR0ZXN0YXRpb25PYmplY3Q6IGJ1ZmZlclRvQmFzZTY0VVJMU3RyaW5nKHJlc3BvbnNlLmF0dGVzdGF0aW9uT2JqZWN0KSxcbiAgICAgICAgICAgIGNsaWVudERhdGFKU09OOiBidWZmZXJUb0Jhc2U2NFVSTFN0cmluZyhyZXNwb25zZS5jbGllbnREYXRhSlNPTiksXG4gICAgICAgICAgICB0cmFuc3BvcnRzLFxuICAgICAgICAgICAgcHVibGljS2V5QWxnb3JpdGhtOiByZXNwb25zZVB1YmxpY0tleUFsZ29yaXRobSxcbiAgICAgICAgICAgIHB1YmxpY0tleTogcmVzcG9uc2VQdWJsaWNLZXksXG4gICAgICAgICAgICBhdXRoZW50aWNhdG9yRGF0YTogcmVzcG9uc2VBdXRoZW50aWNhdG9yRGF0YSxcbiAgICAgICAgfSxcbiAgICAgICAgdHlwZSxcbiAgICAgICAgY2xpZW50RXh0ZW5zaW9uUmVzdWx0czogY3JlZGVudGlhbC5nZXRDbGllbnRFeHRlbnNpb25SZXN1bHRzKCksXG4gICAgICAgIGF1dGhlbnRpY2F0b3JBdHRhY2htZW50OiB0b0F1dGhlbnRpY2F0b3JBdHRhY2htZW50KGNyZWRlbnRpYWwuYXV0aGVudGljYXRvckF0dGFjaG1lbnQpLFxuICAgIH07XG59XG4vKipcbiAqIFZpc2libHkgd2FybiB3aGVuIHdlIGRldGVjdCBhbiBpc3N1ZSByZWxhdGVkIHRvIGEgcGFzc2tleSBwcm92aWRlciBpbnRlcmNlcHRpbmcgV2ViQXV0aG4gQVBJXG4gKiBjYWxsc1xuICovXG5mdW5jdGlvbiB3YXJuT25Ccm9rZW5JbXBsZW1lbnRhdGlvbihtZXRob2ROYW1lLCBjYXVzZSkge1xuICAgIGNvbnNvbGUud2FybihgVGhlIGJyb3dzZXIgZXh0ZW5zaW9uIHRoYXQgaW50ZXJjZXB0ZWQgdGhpcyBXZWJBdXRobiBBUEkgY2FsbCBpbmNvcnJlY3RseSBpbXBsZW1lbnRlZCAke21ldGhvZE5hbWV9LiBZb3Ugc2hvdWxkIHJlcG9ydCB0aGlzIGVycm9yIHRvIHRoZW0uXFxuYCwgY2F1c2UpO1xufVxuIiwiZXhwb3J0IHt9O1xuIiwiLy8gVGhlIG1vZHVsZSBjYWNoZVxudmFyIF9fd2VicGFja19tb2R1bGVfY2FjaGVfXyA9IHt9O1xuXG4vLyBUaGUgcmVxdWlyZSBmdW5jdGlvblxuZnVuY3Rpb24gX193ZWJwYWNrX3JlcXVpcmVfXyhtb2R1bGVJZCkge1xuXHQvLyBDaGVjayBpZiBtb2R1bGUgaXMgaW4gY2FjaGVcblx0dmFyIGNhY2hlZE1vZHVsZSA9IF9fd2VicGFja19tb2R1bGVfY2FjaGVfX1ttb2R1bGVJZF07XG5cdGlmIChjYWNoZWRNb2R1bGUgIT09IHVuZGVmaW5lZCkge1xuXHRcdHJldHVybiBjYWNoZWRNb2R1bGUuZXhwb3J0cztcblx0fVxuXHQvLyBDaGVjayBpZiBtb2R1bGUgZXhpc3RzIChkZXZlbG9wbWVudCBvbmx5KVxuXHRpZiAoX193ZWJwYWNrX21vZHVsZXNfX1ttb2R1bGVJZF0gPT09IHVuZGVmaW5lZCkge1xuXHRcdHZhciBlID0gbmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIiArIG1vZHVsZUlkICsgXCInXCIpO1xuXHRcdGUuY29kZSA9ICdNT0RVTEVfTk9UX0ZPVU5EJztcblx0XHR0aHJvdyBlO1xuXHR9XG5cdC8vIENyZWF0ZSBhIG5ldyBtb2R1bGUgKGFuZCBwdXQgaXQgaW50byB0aGUgY2FjaGUpXG5cdHZhciBtb2R1bGUgPSBfX3dlYnBhY2tfbW9kdWxlX2NhY2hlX19bbW9kdWxlSWRdID0ge1xuXHRcdC8vIG5vIG1vZHVsZS5pZCBuZWVkZWRcblx0XHQvLyBubyBtb2R1bGUubG9hZGVkIG5lZWRlZFxuXHRcdGV4cG9ydHM6IHt9XG5cdH07XG5cblx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG5cdF9fd2VicGFja19tb2R1bGVzX19bbW9kdWxlSWRdKG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG5cdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG5cdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbn1cblxuIiwiLy8gZGVmaW5lIGdldHRlciBmdW5jdGlvbnMgZm9yIGhhcm1vbnkgZXhwb3J0c1xuX193ZWJwYWNrX3JlcXVpcmVfXy5kID0gKGV4cG9ydHMsIGRlZmluaXRpb24pID0+IHtcblx0Zm9yKHZhciBrZXkgaW4gZGVmaW5pdGlvbikge1xuXHRcdGlmKF9fd2VicGFja19yZXF1aXJlX18ubyhkZWZpbml0aW9uLCBrZXkpICYmICFfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZXhwb3J0cywga2V5KSkge1xuXHRcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIGtleSwgeyBlbnVtZXJhYmxlOiB0cnVlLCBnZXQ6IGRlZmluaXRpb25ba2V5XSB9KTtcblx0XHR9XG5cdH1cbn07IiwiX193ZWJwYWNrX3JlcXVpcmVfXy5vID0gKG9iaiwgcHJvcCkgPT4gKE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbChvYmosIHByb3ApKSIsIi8vIGRlZmluZSBfX2VzTW9kdWxlIG9uIGV4cG9ydHNcbl9fd2VicGFja19yZXF1aXJlX18uciA9IChleHBvcnRzKSA9PiB7XG5cdGlmKHR5cGVvZiBTeW1ib2wgIT09ICd1bmRlZmluZWQnICYmIFN5bWJvbC50b1N0cmluZ1RhZykge1xuXHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCBTeW1ib2wudG9TdHJpbmdUYWcsIHsgdmFsdWU6ICdNb2R1bGUnIH0pO1xuXHR9XG5cdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCAnX19lc01vZHVsZScsIHsgdmFsdWU6IHRydWUgfSk7XG59OyIsIlwidXNlIHN0cmljdFwiO1xuXG5cbmltcG9ydCB7YXV0aGVudGljYXRlfSBmcm9tIFwiLi4vd2ViYXV0aG4vY2xpZW50XCI7XG5pbXBvcnQgeyBicm93c2VyU3VwcG9ydHNXZWJBdXRobkF1dG9maWxsLCBwbGF0Zm9ybUF1dGhlbnRpY2F0b3JJc0F2YWlsYWJsZSB9IGZyb20gJ0BzaW1wbGV3ZWJhdXRobi9icm93c2VyJztcbmxldCBLVFNpZ25pbkdlbmVyYWwgPSBmdW5jdGlvbiAoKSB7XG4gICAgLy8gRWxlbWVudHNcbiAgICBsZXQgZm9ybTtcbiAgICBsZXQgc3VibWl0QnV0dG9uO1xuICAgIGxldCB2YWxpZGF0b3I7XG4gICAgbGV0IHNpZ25JbldpdGhQYXNza2V5QnV0dG9uO1xuICAgIGxldCBzaWduSW5XaXRoVXNlcm5hbWVBbmRQYXNzd29yZExpbms7XG4gICAgbGV0IHNpZ25JbldpdGhQYXNza2V5Q29udGFpbmVyO1xuICAgIGxldCBzaWduSW5XaXRoVXNlcm5hbWVBbmRQYXNzd29yZENvbnRhaW5lcjtcblxuICAgIGNvbnN0IGxhbmcgPSBsb2NhbFN0b3JhZ2UuZ2V0SXRlbShcImt0X2F1dGhfbGFuZ1wiKT8/ZG9jdW1lbnQuZ2V0RWxlbWVudHNCeVRhZ05hbWUoJ2h0bWwnKVswXS5nZXRBdHRyaWJ1dGUoJ2xhbmcnKTtcblxuICAgIGNvbnN0IHRleHRzID0ge1xuICAgICAgICAnZW4nOiB7XG4gICAgICAgICAgICAnZW1haWxfaW52YWxpZCc6ICdUaGUgdmFsdWUgaXMgbm90IGEgdmFsaWQgZW1haWwgYWRkcmVzcycsXG4gICAgICAgICAgICAnZW1haWxfZW1wdHknOiAnRW1haWwgYWRkcmVzcyBpcyByZXF1aXJlZCcsXG4gICAgICAgICAgICAncGFzc3dvcmRfZW1wdHknOiAnVGhlIHBhc3N3b3JkIGlzIHJlcXVpcmVkJyxcbiAgICAgICAgICAgICdsb2dpbl9zdWNjZXNzJzogJ1lvdSBoYXZlIHN1Y2Nlc3NmdWxseSBsb2dnZWQgaW4hJyxcbiAgICAgICAgICAgICdsb2dpbl9lcnJvcic6ICdTb3JyeSwgbG9va3MgbGlrZSB0aGVyZSBhcmUgc29tZSBlcnJvcnMgZGV0ZWN0ZWQsIHBsZWFzZSB0cnkgYWdhaW4uJyxcbiAgICAgICAgICAgICdvayc6ICdPaywgZ290IGl0ISdcbiAgICAgICAgfSxcbiAgICAgICAgJ2RlJzoge1xuICAgICAgICAgICAgJ2VtYWlsX2ludmFsaWQnOiAnRGVyIFdlcnQgaXN0IGtlaW5lIGfDvGx0aWdlIEUtTWFpbC1BZHJlc3NlJyxcbiAgICAgICAgICAgICdlbWFpbF9lbXB0eSc6ICdFLU1haWwtQWRyZXNzZSBpc3QgZXJmb3JkZXJsaWNoJyxcbiAgICAgICAgICAgICdwYXNzd29yZF9lbXB0eSc6ICdEYXMgUGFzc3dvcnQgaXN0IGVyZm9yZGVybGljaCcsXG4gICAgICAgICAgICAnbG9naW5fc3VjY2Vzcyc6ICdTaWUgaGFiZW4gc2ljaCBlcmZvbGdyZWljaCBhbmdlbWVsZGV0IScsXG4gICAgICAgICAgICAnbG9naW5fZXJyb3InOiAnRW50c2NodWxkaWd1bmcsIGVzIHNjaGVpbnQsIGRhc3MgZWluaWdlIEZlaGxlciBlcmthbm50IHd1cmRlbiwgYml0dGUgdmVyc3VjaGVuIFNpZSBlcyBlcm5ldXQuJyxcbiAgICAgICAgICAgICdvayc6ICdPaywgdmVyc3RhbmRlbiEnXG4gICAgICAgIH0sXG4gICAgICAgICdmcic6IHtcbiAgICAgICAgICAgICdlbWFpbF9pbnZhbGlkJzogJ0xhIHZhbGV1ciBuXFwnZXN0IHBhcyB1bmUgYWRyZXNzZSBlLW1haWwgdmFsaWRlJyxcbiAgICAgICAgICAgICdlbWFpbF9lbXB0eSc6ICdMXFwnYWRyZXNzZSBlLW1haWwgZXN0IHJlcXVpc2UnLFxuICAgICAgICAgICAgJ3Bhc3N3b3JkX2VtcHR5JzogJ0xlIG1vdCBkZSBwYXNzZSBlc3QgcmVxdWlzJyxcbiAgICAgICAgICAgICdsb2dpbl9zdWNjZXNzJzogJ1ZvdXMgdm91cyDDqnRlcyBjb25uZWN0w6kgYXZlYyBzdWNjw6hzIScsXG4gICAgICAgICAgICAnbG9naW5fZXJyb3InOiAnRMOpc29sw6ksIGlsIHNlbWJsZSBxdVxcJ2lsIHkgYWl0IGRlcyBlcnJldXJzIGTDqXRlY3TDqWVzLCB2ZXVpbGxleiByw6llc3NheWVyLicsXG4gICAgICAgICAgICAnb2snOiAnT2ssIGNvbXByaXMhJ1xuICAgICAgICB9LFxuICAgICAgICAnaXQnOiB7XG4gICAgICAgICAgICAnZW1haWxfaW52YWxpZCc6ICdJbCB2YWxvcmUgbm9uIMOoIHVuIGluZGlyaXp6byBlLW1haWwgdmFsaWRvJyxcbiAgICAgICAgICAgICdlbWFpbF9lbXB0eSc6ICdMXFwnaW5kaXJpenpvIGUtbWFpbCDDqCByaWNoaWVzdG8nLFxuICAgICAgICAgICAgJ3Bhc3N3b3JkX2VtcHR5JzogJ0xhIHBhc3N3b3JkIMOoIHJpY2hpZXN0YScsXG4gICAgICAgICAgICAnbG9naW5fc3VjY2Vzcyc6ICdMXFwnYWNjZXNzbyDDqCBzdGF0byBlZmZldHR1YXRvIGNvbiBzdWNjZXNzbyEnLFxuICAgICAgICAgICAgJ2xvZ2luX2Vycm9yJzogJ1NwaWFjZW50ZSwgc2VtYnJhIGNoZSBzaWFubyBzdGF0aSByaWxldmF0aSBkZWdsaSBlcnJvcmksIHNpIHByZWdhIGRpIHJpcHJvdmFyZS4nLFxuICAgICAgICAgICAgJ29rJzogJ09rLCBjYXBpdG8hJ1xuICAgICAgICB9LFxuICAgIH1cblxuICAgIGxldCB0cmFuc2xhdGUgPSBmdW5jdGlvbiAodGV4dCkge1xuXG4gICAgICAgIHN3aXRjaCAobGFuZykge1xuICAgICAgICAgICAgY2FzZSAnR2VybWFuJzpcbiAgICAgICAgICAgIGNhc2UgJ2RlJzpcbiAgICAgICAgICAgICAgICByZXR1cm4gdGV4dHNbJ2RlJ11bdGV4dF07XG4gICAgICAgICAgICBjYXNlICdJdGFsaWFuJzpcbiAgICAgICAgICAgIGNhc2UgJ2l0JzpcbiAgICAgICAgICAgICAgICByZXR1cm4gdGV4dHNbJ2l0J11bdGV4dF07XG4gICAgICAgICAgICBjYXNlICdGcmVuY2gnOlxuICAgICAgICAgICAgY2FzZSAnZnInOlxuICAgICAgICAgICAgICAgIHJldHVybiB0ZXh0c1snZnInXVt0ZXh0XTtcbiAgICAgICAgICAgIGRlZmF1bHQ6XG4gICAgICAgICAgICAgICAgcmV0dXJuIHRleHRzWydlbiddW3RleHRdO1xuXG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvLyBIYW5kbGUgZm9ybVxuICAgIGxldCBoYW5kbGVWYWxpZGF0aW9uID0gZnVuY3Rpb24gKGUpIHtcbiAgICAgICAgLy8gSW5pdCBmb3JtIHZhbGlkYXRpb24gcnVsZXMuIEZvciBtb3JlIGluZm8gY2hlY2sgdGhlIEZvcm1WYWxpZGF0aW9uIHBsdWdpbidzIG9mZmljaWFsIGRvY3VtZW50YXRpb246aHR0cHM6Ly9mb3JtdmFsaWRhdGlvbi5pby9cbiAgICAgICAgdmFsaWRhdG9yID0gRm9ybVZhbGlkYXRpb24uZm9ybVZhbGlkYXRpb24oXG4gICAgICAgICAgICBmb3JtLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGZpZWxkczoge1xuICAgICAgICAgICAgICAgICAgICAnX3VzZXJuYW1lJzoge1xuICAgICAgICAgICAgICAgICAgICAgICAgdmFsaWRhdG9yczoge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIG5vdEVtcHR5OiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG1lc3NhZ2U6IHRyYW5zbGF0ZSgnZW1haWxfZW1wdHknKVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICAgICAgJ19wYXNzd29yZCc6IHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHZhbGlkYXRvcnM6IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBub3RFbXB0eToge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBtZXNzYWdlOiB0cmFuc2xhdGUoJ3Bhc3N3b3JkX2VtcHR5JylcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgIHBsdWdpbnM6IHtcbiAgICAgICAgICAgICAgICAgICAgdHJpZ2dlcjogbmV3IEZvcm1WYWxpZGF0aW9uLnBsdWdpbnMuVHJpZ2dlcigpLFxuICAgICAgICAgICAgICAgICAgICBib290c3RyYXA6IG5ldyBGb3JtVmFsaWRhdGlvbi5wbHVnaW5zLkJvb3RzdHJhcDUoe1xuICAgICAgICAgICAgICAgICAgICAgICAgcm93U2VsZWN0b3I6ICcuZnYtcm93JyxcbiAgICAgICAgICAgICAgICAgICAgICAgIGVsZUludmFsaWRDbGFzczogJycsICAvLyBjb21tZW50IHRvIGVuYWJsZSBpbnZhbGlkIHN0YXRlIGljb25zXG4gICAgICAgICAgICAgICAgICAgICAgICBlbGVWYWxpZENsYXNzOiAnJyAvLyBjb21tZW50IHRvIGVuYWJsZSB2YWxpZCBzdGF0ZSBpY29uc1xuICAgICAgICAgICAgICAgICAgICB9KVxuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgKTtcbiAgICB9XG5cblxuICAgIGxldCBoYW5kbGVTdWJtaXRBamF4ID0gYXN5bmMgZnVuY3Rpb24gKGUpIHtcbiAgICAgICAgLy8gSGFuZGxlIGZvcm0gc3VibWl0XG4gICAgICAgIHN1Ym1pdEJ1dHRvbi5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIGZ1bmN0aW9uIChlKSB7XG4gICAgICAgICAgICAvLyBQcmV2ZW50IGJ1dHRvbiBkZWZhdWx0IGFjdGlvblxuICAgICAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG4gICAgICAgICAgICAvLyBWYWxpZGF0ZSBmb3JtXG4gICAgICAgICAgICB2YWxpZGF0b3IudmFsaWRhdGUoKS50aGVuKGZ1bmN0aW9uIChzdGF0dXMpIHtcbiAgICAgICAgICAgICAgICBpZiAoc3RhdHVzID09PSAnVmFsaWQnKSB7XG4gICAgICAgICAgICAgICAgICAgIC8vIFNob3cgbG9hZGluZyBpbmRpY2F0aW9uXG4gICAgICAgICAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbi5zZXRBdHRyaWJ1dGUoJ2RhdGEta3QtaW5kaWNhdG9yJywgJ29uJyk7XG5cbiAgICAgICAgICAgICAgICAgICAgLy8gRGlzYWJsZSBidXR0b24gdG8gYXZvaWQgbXVsdGlwbGUgY2xpY2tcbiAgICAgICAgICAgICAgICAgICAgc3VibWl0QnV0dG9uLmRpc2FibGVkID0gdHJ1ZTtcbiAgICAgICAgICAgICAgICAgICAgLy8gZm9ybS5zdWJtaXQoKTtcbiAgICAgICAgICAgICAgICAgICAgLy8gcmV0dXJuO1xuXG4gICAgICAgICAgICAgICAgICAgIGxldCBmb3JtRGF0YSA9IG5ldyBGb3JtRGF0YShmb3JtKTtcblxuICAgICAgICAgICAgICAgICAgICBsZXQgbG9naW5JbmZvID0ge31cbiAgICAgICAgICAgICAgICAgICAgZm9ybURhdGEuZm9yRWFjaCggKHZhbHVlLCBrZXkpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGxvZ2luSW5mb1trZXldID0gdmFsdWU7XG4gICAgICAgICAgICAgICAgICAgIH0pXG5cbiAgICAgICAgICAgICAgICAgICAgYXhpb3MucG9zdChDTVNSb3V0aW5nLmdlbmVyYXRlKCdjbXNfYXBpX2xvZ2luJyksIGxvZ2luSW5mbywgey4uLmF4aW9zQ29uZmlnfSlcbiAgICAgICAgICAgICAgICAgICAgICAgIC50aGVuKChyZXNwb25zZSkgPT4ge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbi5kaXNhYmxlZCA9IGZhbHNlO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbi5yZW1vdmVBdHRyaWJ1dGUoJ2RhdGEta3QtaW5kaWNhdG9yJyk7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHJlZGlyZWN0ID0gcmVzcG9uc2UuZGF0YS5yZWRpcmVjdDtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBTd2FsLmZpcmUoe1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGV4dDogdHJhbnNsYXRlKCdsb2dpbl9zdWNjZXNzJyksXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpY29uOiBcInN1Y2Nlc3NcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRpbWVyOiAxMDAwLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc2hvd0NvbmZpcm1CdXR0b246IGZhbHNlLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KS50aGVuKCgpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBob3N0ID0gIHdpbmRvdy5sb2NhdGlvbi5wcm90b2NvbCArICcvLycgKyB3aW5kb3cubG9jYXRpb24uaG9zdFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYocmVkaXJlY3QuaW5kZXhPZihob3N0KSA9PT0gLTEgfHwgcmVkaXJlY3QuaW5kZXhPZignaHR0cCcpID09PSAtMSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJlZGlyZWN0ID0gaG9zdCArIHJlZGlyZWN0XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsb2NhdGlvbi5ocmVmID0gcmVkaXJlY3RcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9KVxuICAgICAgICAgICAgICAgICAgICAgICAgLmNhdGNoKChlcnJvcikgPT4ge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbi5kaXNhYmxlZCA9IGZhbHNlO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbi5yZW1vdmVBdHRyaWJ1dGUoJ2RhdGEta3QtaW5kaWNhdG9yJyk7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IGRhdGEgPSBlcnJvci5yZXNwb25zZS5kYXRhXG5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBTd2FsLmZpcmUoe1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0ZXh0OiBkYXRhLmVycm9yLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpY29uOiBcImVycm9yXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJ1dHRvbnNTdHlsaW5nOiBmYWxzZSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY29uZmlybUJ1dHRvblRleHQ6IHRyYW5zbGF0ZSgnb2snKSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY3VzdG9tQ2xhc3M6IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNvbmZpcm1CdXR0b246IFwiYnRuIGJ0bi1wcmltYXJ5XCJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHN1Ym1pdEJ1dHRvbi5kaXNhYmxlZCA9IGZhbHNlO1xuICAgICAgICAgICAgICAgICAgICAgICAgfSlcblxuICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgIC8vIFNob3cgZXJyb3IgcG9wdXAuIEZvciBtb3JlIGluZm8gY2hlY2sgdGhlIHBsdWdpbidzIG9mZmljaWFsIGRvY3VtZW50YXRpb246IGh0dHBzOi8vc3dlZXRhbGVydDIuZ2l0aHViLmlvL1xuICAgICAgICAgICAgICAgICAgICBTd2FsLmZpcmUoe1xuICAgICAgICAgICAgICAgICAgICAgICAgdGV4dDogdHJhbnNsYXRlKCdsb2dpbl9lcnJvcicpLFxuICAgICAgICAgICAgICAgICAgICAgICAgaWNvbjogXCJlcnJvclwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgYnV0dG9uc1N0eWxpbmc6IGZhbHNlLFxuICAgICAgICAgICAgICAgICAgICAgICAgY29uZmlybUJ1dHRvblRleHQ6IHRyYW5zbGF0ZSgnb2snKSxcbiAgICAgICAgICAgICAgICAgICAgICAgIGN1c3RvbUNsYXNzOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgY29uZmlybUJ1dHRvbjogXCJidG4gYnRuLXByaW1hcnlcIlxuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfSk7XG4gICAgfVxuXG4gICAgbGV0IGlzVmFsaWRVcmwgPSBmdW5jdGlvbih1cmwpIHtcbiAgICAgICAgdHJ5IHtcbiAgICAgICAgICAgIG5ldyBVUkwodXJsKTtcbiAgICAgICAgICAgIHJldHVybiB0cnVlO1xuICAgICAgICB9IGNhdGNoIChlKSB7XG4gICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBsZXQgd2ViYXV0aG5TaWduaW4gPSBhc3luYyBmdW5jdGlvbih1c2VybmFtZSl7XG4gICAgICAgIHRyeXtcbiAgICAgICAgICAgIC8vIElzIGNvbmRpdGlvbmFsIFVJIGF2YWlsYWJsZSBpbiB0aGlzIGJyb3dzZXI/XG4gICAgICAgICAgICBjb25zdCBjbWEgPSBhd2FpdCBQdWJsaWNLZXlDcmVkZW50aWFsLmlzQ29uZGl0aW9uYWxNZWRpYXRpb25BdmFpbGFibGUoKTtcbiAgICAgICAgICAgIGlmIChjbWEpIHtcbiAgICAgICAgICAgICAgICBjb25zdCByZXN1bHQgPSBhd2FpdCBhdXRoZW50aWNhdGUodXNlcm5hbWUpO1xuICAgICAgICAgICAgICAgIGlmIChyZXN1bHQgJiYgJ29rJyA9PT0gcmVzdWx0LnN0YXR1cykge1xuICAgICAgICAgICAgICAgICAgICByZXR1cm4gbG9jYXRpb24uaHJlZiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJyNyZWRpcmVjdF9yb3V0ZScpLnZhbHVlXG4gICAgICAgICAgICAgICAgfVxuXG5cbiAgICAgICAgICAgICAgICB0aHJvdyBuZXcgRXJyb3IocmVzdWx0KVxuICAgICAgICAgICAgfVxuICAgICAgICB9Y2F0Y2ggKGUpIHtcbiAgICAgICAgICAgIGxldCBtZXNzYWdlID0gZS5tZXNzYWdlO1xuXG4gICAgICAgICAgICBpZihlLm5hbWUgPT09ICdFcnJvcicpe1xuICAgICAgICAgICAgICAgIG1lc3NhZ2UgPSB0cmFuc2xhdGUoJ2xvZ2luX2Vycm9yJyk7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIGlmKGUubmFtZSA9PT0gJ05vdEFsbG93ZWRFcnJvcicpe1xuICAgICAgICAgICAgICAgIHJldHVybiBzaWduSW5XaXRoVXNlcm5hbWVBbmRQYXNzd29yZExpbmsuY2xpY2soKVxuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBpZihlLm1lc3NhZ2UgPT09ICdub19jcmVkZW50aWFscycpe1xuICAgICAgICAgICAgICAgIHJldHVybiBzaWduSW5XaXRoVXNlcm5hbWVBbmRQYXNzd29yZExpbmsuY2xpY2soKVxuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBTd2FsLmZpcmUoe1xuICAgICAgICAgICAgICAgIHRleHQ6IG1lc3NhZ2UsXG4gICAgICAgICAgICAgICAgaWNvbjogXCJlcnJvclwiLFxuICAgICAgICAgICAgICAgIGJ1dHRvbnNTdHlsaW5nOiBmYWxzZSxcbiAgICAgICAgICAgICAgICBjb25maXJtQnV0dG9uVGV4dDogdHJhbnNsYXRlKCdvaycpLFxuICAgICAgICAgICAgICAgIGN1c3RvbUNsYXNzOiB7XG4gICAgICAgICAgICAgICAgICAgIGNvbmZpcm1CdXR0b246IFwiYnRuIGJ0bi1wcmltYXJ5XCJcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8vIFB1YmxpYyBmdW5jdGlvbnNcbiAgICByZXR1cm4ge1xuICAgICAgICAvLyBJbml0aWFsaXphdGlvblxuICAgICAgICBpbml0OiBmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICBmb3JtID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI2t0X3NpZ25faW5fZm9ybScpO1xuICAgICAgICAgICAgc3VibWl0QnV0dG9uID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI2t0X3NpZ25faW5fc3VibWl0Jyk7XG4gICAgICAgICAgICBzaWduSW5XaXRoVXNlcm5hbWVBbmRQYXNzd29yZENvbnRhaW5lciA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJyNrdF9zaWduX2luX3dpdGhfdXNlcm5hbWVfYW5kX3Bhc3N3b3JkX2NvbnRhaW5lcicpO1xuICAgICAgICAgICAgbGV0IHdlYmF1dGhuRW5hYmxlZCA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoXCJtZXRhW25hbWU9J3dlYmF1dGhuLWVuYWJsZWQnXVwiKS5nZXRBdHRyaWJ1dGUoXCJjb250ZW50XCIpO1xuXG4gICAgICAgICAgICBpZiAoXG4gICAgICAgICAgICAgICAgd2ViYXV0aG5FbmFibGVkICYmXG4gICAgICAgICAgICAgICAgd2luZG93LlB1YmxpY0tleUNyZWRlbnRpYWwgJiZcbiAgICAgICAgICAgICAgICBQdWJsaWNLZXlDcmVkZW50aWFsLmlzQ29uZGl0aW9uYWxNZWRpYXRpb25BdmFpbGFibGVcbiAgICAgICAgICAgICkge1xuICAgICAgICAgICAgICAgIHNpZ25JbldpdGhQYXNza2V5QnV0dG9uID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI2t0X3NpZ25faW5fd2l0aF9wYXNza2V5Jyk7XG4gICAgICAgICAgICAgICAgc2lnbkluV2l0aFVzZXJuYW1lQW5kUGFzc3dvcmRMaW5rID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI2t0X3NpZ25faW5fd2l0aF91c2VybmFtZV9hbmRfcGFzc3dvcmQnKTtcbiAgICAgICAgICAgICAgICBzaWduSW5XaXRoUGFzc2tleUNvbnRhaW5lciA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJyNrdF9zaWduX2luX3dpdGhfcGFzc2tleV9jb250YWluZXInKTtcblxuXG4gICAgICAgICAgICAgICAgbGV0IHR5cGluZ1RpbWVyOyAgICAgICAgICAgICAgICAvL3RpbWVyIGlkZW50aWZpZXJcbiAgICAgICAgICAgICAgICBsZXQgZG9uZVR5cGluZ0ludGVydmFsID0gMTAwMDsgIC8vdGltZSBpbiBtcyAoNSBzZWNvbmRzKVxuICAgICAgICAgICAgICAgIGxldCB1c2VybmFtZUlucHV0ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI3VzZXJuYW1lJyk7XG4gICAgICAgICAgICAgICAgY29uc3QgdXNlcm5hbWVFdmVudCA9ICgpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgY2xlYXJUaW1lb3V0KHR5cGluZ1RpbWVyKTtcbiAgICAgICAgICAgICAgICAgICAgaWYgKHVzZXJuYW1lSW5wdXQudmFsdWUpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHR5cGluZ1RpbWVyID0gc2V0VGltZW91dChkb25lVHlwaW5nLCBkb25lVHlwaW5nSW50ZXJ2YWwpO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgbGV0IGV2ZW50TGlzdGVuZXIgPSB1c2VybmFtZUlucHV0LmFkZEV2ZW50TGlzdGVuZXIoJ2tleXVwJywgdXNlcm5hbWVFdmVudCk7XG5cbiAgICAgICAgICAgICAgICBmdW5jdGlvbiBkb25lVHlwaW5nICgpIHtcbiAgICAgICAgICAgICAgICAgICAgc2lnbkluV2l0aFBhc3NrZXlCdXR0b24uY2xpY2soKVxuICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgIHNpZ25JbldpdGhQYXNza2V5QnV0dG9uLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgZnVuY3Rpb24gKGUpIHtcbiAgICAgICAgICAgICAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICAgICAgICAgICAgICB0cnkge1xuICAgICAgICAgICAgICAgICAgICAgICAgd2ViYXV0aG5TaWduaW4odXNlcm5hbWVJbnB1dC52YWx1ZSk7XG4gICAgICAgICAgICAgICAgICAgIH0gY2F0Y2ggKGUpIHtcblxuICAgICAgICAgICAgICAgICAgICAgICAgLy8gQSBOb3RBbGxvd2VkRXJyb3IgaW5kaWNhdGVzIHRoYXQgdGhlIHVzZXIgY2FuY2VsZWQgdGhlIG9wZXJhdGlvbi5cbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChlLm5hbWUgIT09IFwiTm90QWxsb3dlZEVycm9yXCIpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAvL0NNU0FkbWluLmNyZWF0ZUluaXRDbXNNZXNzYWdlQm94KCdlcnJvcicsIGUubWVzc2FnZSk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB9KVxuXG4gICAgICAgICAgICAgICAgc2lnbkluV2l0aFVzZXJuYW1lQW5kUGFzc3dvcmRMaW5rLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgZnVuY3Rpb24gKGUpIHtcbiAgICAgICAgICAgICAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICAgICAgICAgICAgICB1c2VybmFtZUlucHV0LnJlbW92ZUV2ZW50TGlzdGVuZXIoJ2tleXVwJywgdXNlcm5hbWVFdmVudCk7XG4gICAgICAgICAgICAgICAgICAgIHNpZ25JbldpdGhQYXNza2V5Q29udGFpbmVyLmNsYXNzTGlzdC5hZGQoJ2Qtbm9uZScpO1xuICAgICAgICAgICAgICAgICAgICBzaWduSW5XaXRoVXNlcm5hbWVBbmRQYXNzd29yZENvbnRhaW5lci5jbGFzc0xpc3QucmVtb3ZlKCdkLW5vbmUnKTtcblxuICAgICAgICAgICAgICAgIH0pXG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIGhhbmRsZVZhbGlkYXRpb24oKTtcblxuICAgICAgICAgICAgaWYgKGlzVmFsaWRVcmwoc3VibWl0QnV0dG9uLmNsb3Nlc3QoJ2Zvcm0nKS5nZXRBdHRyaWJ1dGUoJ2FjdGlvbicpKSkge1xuICAgICAgICAgICAgICAgIGhhbmRsZVN1Ym1pdEFqYXgoKTsgLy8gdXNlIGZvciBhamF4IHN1Ym1pdFxuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfTtcbn0oKTtcblxuLy8gT24gZG9jdW1lbnQgcmVhZHlcbmRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ0RPTUNvbnRlbnRMb2FkZWQnLCAoKSA9PiB7XG4gICAgS1RTaWduaW5HZW5lcmFsLmluaXQoKTtcbn0pO1xuIl0sIm5hbWVzIjpbXSwic291cmNlUm9vdCI6IiJ9