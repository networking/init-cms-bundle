define(['exports', '@form-validation/core'], (function (exports, core) { 'use strict';

    /******************************************************************************
    Copyright (c) Microsoft Corporation.

    Permission to use, copy, modify, and/or distribute this software for any
    purpose with or without fee is hereby granted.

    THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
    REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
    INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
    LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
    OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
    PERFORMANCE OF THIS SOFTWARE.
    ***************************************************************************** */
    /* global Reflect, Promise */

    var extendStatics = function(d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };

    function __extends(d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    }

    /**
     * FormValidation (https://formvalidation.io)
     * The best validation library for JavaScript
     * (c) 2013 - 2023 Nguyen Huu Phuoc <me@phuoc.ng>
     */
    /**
     * This plugin will submit the form if all fields are valid after validating
     */
    var DefaultSubmit = /** @class */ (function (_super) {
        __extends(DefaultSubmit, _super);
        function DefaultSubmit() {
            var _this = _super.call(this, {}) || this;
            _this.onValidHandler = _this.onFormValid.bind(_this);
            return _this;
        }
        DefaultSubmit.prototype.install = function () {
            var form = this.core.getFormElement();
            if (form.querySelectorAll('[type="submit"][name="submit"]').length) {
                throw new Error('Do not use `submit` for the name attribute of submit button');
            }
            this.core.on('core.form.valid', this.onValidHandler);
        };
        DefaultSubmit.prototype.uninstall = function () {
            this.core.off('core.form.valid', this.onValidHandler);
        };
        DefaultSubmit.prototype.onFormValid = function () {
            var form = this.core.getFormElement();
            if (this.isEnabled && form instanceof HTMLFormElement) {
                form.submit();
            }
        };
        return DefaultSubmit;
    }(core.Plugin));

    exports.DefaultSubmit = DefaultSubmit;

}));
