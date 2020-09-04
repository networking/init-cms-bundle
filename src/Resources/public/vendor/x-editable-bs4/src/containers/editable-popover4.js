/**
* Editable Popover for Bootstrap 4 based on Popper.js
* ---------------------
* requires bootstrap-popover.js
*/
(function ($) {
    "use strict";
    //extend methods
    $.extend($.fn.editableContainer.Popup.prototype, {
        containerName: 'popover',
        containerDataName: 'bs.popover',
        innerCss: '.popover-body',
        defaults: $.fn.popover.Constructor.DEFAULTS,
        
        initContainer: function(){
            $.extend(this.containerOptions, {
                trigger: 'manual',
                selector: false,
                content: ' ',
                template: this.defaults.template
            });
            
            //as template property is used in inputs, hide it from popover
            var t;
            if(this.$element.data('template')) {
                t = this.$element.data('template');
                this.$element.removeData('template');
            }
            
            this.call(this.containerOptions);
            
            if(t) {
                //restore data('template')
                this.$element.data('template', t);
            }
        },
        
        /* show */
        innerShow: function () {
            this.call('show');
        },
        
        /* hide */
        innerHide: function () {
            this.call('hide');
        },
        
        /* destroy */
        innerDestroy: function() {
            this.call('dispose');
        },
        
        setContainerOption: function(key, value) {
            this.container().options[key] = value;
        },
        
        setPosition: function () {
            (function() {
                var tip = this.getTipElement();

                var placement = typeof this.config.placement === 'function' ? this.config.placement.call(this, tip, this.element) : this.config.placement;

                var attachment = this._getAttachment(placement);

                this.addAttachmentClass(attachment);

                var container = this._getContainer();

                this._popper = new window.Popper(this.element, tip, this._getPopperConfig(attachment));
            }).call(this.container());
        },
        
        tip: function() {
            return this.container() ? $(this.container().tip) : null;
        }
    });
    
}(window.jQuery));
