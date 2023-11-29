import { startRegistration, startAuthentication } from '@simplewebauthn/browser';

export async function _fetch(path, payload = '') {
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


export async function registerCredential(username, displayName) {

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
        attResp = await startRegistration(options);
    } catch (error) {

        throw error;
    }

    return await _fetch(CMSRouting.generate('initcms_webauthn_register_response'), attResp);

}
export async function authenticate(username) {
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
        asseResp = await startAuthentication(options);
    } catch (error) {
        throw error;
    }

    return _fetch('/admin/assertion', asseResp);
}

export async function unregisterCredential(credId) {
    return _fetch(CMSRouting.generate('admin_networking_initcms_user_remove_webauthn_key') + `?credId=${encodeURIComponent(credId)}`);
}

export async function updateCredential(credId, name) {
    return _fetch(CMSRouting.generate('admin_networking_initcms_user_rename_webauthn_key') + `?credId=${encodeURIComponent(credId)}&name=${encodeURIComponent(name)}`);
}
