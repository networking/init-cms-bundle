import { algorithms } from '../core/index.js';

/**
 * FormValidation (https://formvalidation.io)
 * The best validation library for JavaScript
 * (c) 2013 - 2023 Nguyen Huu Phuoc <me@phuoc.ng>
 */
var luhn = algorithms.luhn;
function meid() {
    return {
        /**
         * Validate MEID (Mobile Equipment Identifier)
         * @see http://en.wikipedia.org/wiki/Mobile_equipment_identifier
         */
        validate: function (input) {
            if (input.value === '') {
                return { valid: true };
            }
            var v = input.value;
            if (/^[0-9A-F]{15}$/i.test(v) ||
                /^[0-9A-F]{2}[- ][0-9A-F]{6}[- ][0-9A-F]{6}[- ][0-9A-F]$/i.test(v) ||
                /^\d{19}$/.test(v) ||
                /^\d{5}[- ]\d{5}[- ]\d{4}[- ]\d{4}[- ]\d$/.test(v)) {
                var cd = v.charAt(v.length - 1).toUpperCase();
                v = v.replace(/[- ]/g, '');
                if (v.match(/^\d*$/i)) {
                    return { valid: luhn(v) };
                }
                v = v.slice(0, -1);
                var checkDigit = '';
                var i = void 0;
                for (i = 1; i <= 13; i += 2) {
                    checkDigit += (parseInt(v.charAt(i), 16) * 2).toString(16);
                }
                var sum = 0;
                for (i = 0; i < checkDigit.length; i++) {
                    sum += parseInt(checkDigit.charAt(i), 16);
                }
                return {
                    valid: sum % 10 === 0
                        ? cd === '0'
                        : // Subtract it from the next highest 10s number (64 goes to 70) and subtract the sum
                            // Double it and turn it into a hex char
                            cd === ((Math.floor((sum + 10) / 10) * 10 - sum) * 2).toString(16).toUpperCase(),
                };
            }
            if (/^[0-9A-F]{14}$/i.test(v) ||
                /^[0-9A-F]{2}[- ][0-9A-F]{6}[- ][0-9A-F]{6}$/i.test(v) ||
                /^\d{18}$/.test(v) ||
                /^\d{5}[- ]\d{5}[- ]\d{4}[- ]\d{4}$/.test(v)) {
                return { valid: true };
            }
            return { valid: false };
        },
    };
}

export { meid };
