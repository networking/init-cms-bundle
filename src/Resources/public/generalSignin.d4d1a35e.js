(()=>{"use strict";function e(e){const t=new Uint8Array(e);let n="";for(const e of t)n+=String.fromCharCode(e);return btoa(n).replace(/\+/g,"-").replace(/\//g,"_").replace(/=/g,"")}function t(e){const t=e.replace(/-/g,"+").replace(/_/g,"/"),n=(4-t.length%4)%4,r=t.padEnd(t.length+n,"="),i=atob(r),o=new ArrayBuffer(i.length),a=new Uint8Array(o);for(let e=0;e<i.length;e++)a[e]=i.charCodeAt(e);return o}function n(){return void 0!==window?.PublicKeyCredential&&"function"==typeof window.PublicKeyCredential}function r(e){const{id:n}=e;return{...e,id:t(n),transports:e.transports}}function i(e){return"localhost"===e||/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i.test(e)}class o extends Error{constructor({message:e,code:t,cause:n,name:r}){super(e,{cause:n}),this.name=r??n.name,this.code=t}}const a=new class{createNewAbortSignal(){if(this.controller){const e=new Error("Cancelling existing WebAuthn API call for new one");e.name="AbortError",this.controller.abort(e)}const e=new AbortController;return this.controller=e,e.signal}cancelCeremony(){if(this.controller){const e=new Error("Manually cancelling existing WebAuthn API call");e.name="AbortError",this.controller.abort(e),this.controller=void 0}}},s=["cross-platform","platform"];function l(e){if(e&&!(s.indexOf(e)<0))return e}async function c(s,c=!1){if(!n())throw new Error("WebAuthn is not supported in this browser");let u;0!==s.allowCredentials?.length&&(u=s.allowCredentials?.map(r));const d={...s,challenge:t(s.challenge),allowCredentials:u},m={};if(c){if(!await function(){const e=window.PublicKeyCredential;return void 0===e.isConditionalMediationAvailable?new Promise((e=>e(!1))):e.isConditionalMediationAvailable()}())throw Error("Browser does not support WebAuthn autofill");if(document.querySelectorAll("input[autocomplete$='webauthn']").length<1)throw Error('No <input> with "webauthn" as the only or last value in its `autocomplete` attribute was detected');m.mediation="conditional",d.allowCredentials=[]}let w;m.publicKey=d,m.signal=a.createNewAbortSignal();try{w=await navigator.credentials.get(m)}catch(e){throw function({error:e,options:t}){const{publicKey:n}=t;if(!n)throw Error("options was missing required publicKey property");if("AbortError"===e.name){if(t.signal instanceof AbortSignal)return new o({message:"Authentication ceremony was sent an abort signal",code:"ERROR_CEREMONY_ABORTED",cause:e})}else{if("NotAllowedError"===e.name)return new o({message:e.message,code:"ERROR_PASSTHROUGH_SEE_CAUSE_PROPERTY",cause:e});if("SecurityError"===e.name){const t=window.location.hostname;if(!i(t))return new o({message:`${window.location.hostname} is an invalid domain`,code:"ERROR_INVALID_DOMAIN",cause:e});if(n.rpId!==t)return new o({message:`The RP ID "${n.rpId}" is invalid for this domain`,code:"ERROR_INVALID_RP_ID",cause:e})}else if("UnknownError"===e.name)return new o({message:"The authenticator was unable to process the specified options, or could not create a new assertion signature",code:"ERROR_AUTHENTICATOR_GENERAL_ERROR",cause:e})}return e}({error:e,options:m})}if(!w)throw new Error("Authentication was not completed");const{id:f,rawId:g,response:h,type:p}=w;let _;var y;return h.userHandle&&(y=h.userHandle,_=new TextDecoder("utf-8").decode(y)),{id:f,rawId:e(g),response:{authenticatorData:e(h.authenticatorData),clientDataJSON:e(h.clientDataJSON),signature:e(h.signature),userHandle:_},type:p,clientExtensionResults:w.getClientExtensionResults(),authenticatorAttachment:l(w.authenticatorAttachment)}}async function u(e,t=""){!t||t instanceof FormData||(t=JSON.stringify(t));const n=await fetch(e,{method:"POST",credentials:"same-origin",headers:{"X-Requested-With":"XMLHttpRequest","Content-Type":"application/json"},body:t});if(200===n.status||201===n.status)return n.json();{const e=await n.json();if(e.message)throw new Error(e.message);throw new Error(e.error)}}let d=function(){let e,t,n,r,i,o,a;const s=localStorage.getItem("kt_auth_lang")??document.getElementsByTagName("html")[0].getAttribute("lang"),l={en:{email_invalid:"The value is not a valid email address",email_empty:"Email address is required",password_empty:"The password is required",login_success:"You have successfully logged in!",login_error:"Sorry, looks like there are some errors detected, please try again.",ok:"Ok, got it!"},de:{email_invalid:"Der Wert ist keine gültige E-Mail-Adresse",email_empty:"E-Mail-Adresse ist erforderlich",password_empty:"Das Passwort ist erforderlich",login_success:"Sie haben sich erfolgreich angemeldet!",login_error:"Entschuldigung, es scheint, dass einige Fehler erkannt wurden, bitte versuchen Sie es erneut.",ok:"Ok, verstanden!"},fr:{email_invalid:"La valeur n'est pas une adresse e-mail valide",email_empty:"L'adresse e-mail est requise",password_empty:"Le mot de passe est requis",login_success:"Vous vous êtes connecté avec succès!",login_error:"Désolé, il semble qu'il y ait des erreurs détectées, veuillez réessayer.",ok:"Ok, compris!"},it:{email_invalid:"Il valore non è un indirizzo e-mail valido",email_empty:"L'indirizzo e-mail è richiesto",password_empty:"La password è richiesta",login_success:"L'accesso è stato effettuato con successo!",login_error:"Spiacente, sembra che siano stati rilevati degli errori, si prega di riprovare.",ok:"Ok, capito!"}};let d=function(e){switch(s){case"German":case"de":return l.de[e];case"Italian":case"it":return l.it[e];case"French":case"fr":return l.fr[e];default:return l.en[e]}},m=async function(e){try{if(await PublicKeyCredential.isConditionalMediationAvailable()){const t=await async function(e){let t={requireUserVerification:"preferred"};e&&(t.username=e);const n=await u("/admin/assertion/options",t);if(e&&void 0===n.allowCredentials)throw new Error("no_credentials");let r;try{r=await c(n)}catch(e){throw e}return u("/admin/assertion",r)}(e);if(t&&"ok"===t.status)return location.href=document.querySelector("#redirect_route").value;throw new Error(t)}}catch(e){let t=e.message;if("Error"===e.name&&(t=d("login_error")),"NotAllowedError"===e.name)return i.click();if("no_credentials"===e.message)return i.click();Swal.fire({text:t,icon:"error",buttonsStyling:!1,confirmButtonText:d("ok"),customClass:{confirmButton:"btn btn-primary"}})}};return{init:function(){if(e=document.querySelector("#kt_sign_in_form"),t=document.querySelector("#kt_sign_in_submit"),a=document.querySelector("#kt_sign_in_with_username_and_password_container"),document.querySelector("meta[name='webauthn-enabled']").getAttribute("content")&&window.PublicKeyCredential&&PublicKeyCredential.isConditionalMediationAvailable){let s;r=document.querySelector("#kt_sign_in_with_passkey"),i=document.querySelector("#kt_sign_in_with_username_and_password"),o=document.querySelector("#kt_sign_in_with_passkey_container");let l=1e3,c=document.querySelector("#username");const u=()=>{clearTimeout(s),c.value&&(s=setTimeout(w,l))};c.addEventListener("keyup",u);function w(){r.click()}r.addEventListener("click",(function(e){e.preventDefault();try{m(c.value)}catch(e){e.name}})),i.addEventListener("click",(function(e){e.preventDefault(),c.removeEventListener("keyup",u),o.classList.add("d-none"),a.classList.remove("d-none")}))}n=FormValidation.formValidation(e,{fields:{_username:{validators:{notEmpty:{message:d("email_empty")}}},_password:{validators:{notEmpty:{message:d("password_empty")}}}},plugins:{trigger:new FormValidation.plugins.Trigger,bootstrap:new FormValidation.plugins.Bootstrap5({rowSelector:".fv-row",eleInvalidClass:"",eleValidClass:""})}}),function(e){try{return new URL(e),!0}catch(e){return!1}}(t.closest("form").getAttribute("action"))&&async function(r){t.addEventListener("click",(function(r){r.preventDefault(),n.validate().then((function(n){if("Valid"===n){t.setAttribute("data-kt-indicator","on"),t.disabled=!0;let n=new FormData(e),r={};n.forEach(((e,t)=>{r[t]=e})),axios.post(CMSRouting.generate("cms_api_login"),r,{...axiosConfig}).then((e=>{t.disabled=!1,t.removeAttribute("data-kt-indicator");let n=e.data.redirect;Swal.fire({text:d("login_success"),icon:"success",timer:1e3,showConfirmButton:!1}).then((()=>{let e=window.location.protocol+"//"+window.location.host;-1!==n.indexOf(e)&&-1!==n.indexOf("http")||(n=e+n),location.href=n}))})).catch((e=>{t.disabled=!1,t.removeAttribute("data-kt-indicator");let n=e.response.data;Swal.fire({text:n.error,icon:"error",buttonsStyling:!1,confirmButtonText:d("ok"),customClass:{confirmButton:"btn btn-primary"}}),t.disabled=!1}))}else Swal.fire({text:d("login_error"),icon:"error",buttonsStyling:!1,confirmButtonText:d("ok"),customClass:{confirmButton:"btn btn-primary"}})}))}))}()}}}();document.addEventListener("DOMContentLoaded",(()=>{d.init()}))})();
//# sourceMappingURL=generalSignin.d4d1a35e.js.map