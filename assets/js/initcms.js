const InitCms = {

    config: null,
    translations: null,

    getConfig(key) {
        if (this.config == null) {
            this.setupConfig();
        }

        return this.config[key];
    },
    setupConfig(){
        const config = document.head.querySelector('meta[name="init-cms-setup"]');
        this.config = JSON.parse(config.dataset.initCmsConfig);
        this.translations = JSON.parse(config.dataset.initCmsTranslations);
    },
    setupDefaultBehaviour(){

        // Opera 8.0+
        var isOpera = (!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;

        // Firefox 1.0+
        var isFirefox = typeof InstallTrigger !== 'undefined';

        // Safari 3.0+ "[object HTMLElementConstructor]"
        var isSafari = /constructor/i.test(window.HTMLElement) || (function (p) {
            return p.toString() === "[object SafariRemoteNotification]";
        })(!window['safari'] || (typeof safari !== 'undefined' && safari.pushNotification));

        // Internet Explorer 6-11
        var isIE = /*@cc_on!@*/false || !!document.documentMode;

        // Edge 20+
        var isEdge = !isIE && !!window.StyleMedia;

        // Chrome 1+
        var isChrome = !!window.chrome && (!!window.chrome.webstore || !!window.chrome.runtime);

        var noticeBlock = $('.notice-block');

        noticeBlock.on('DOMNodeInserted', function () {
            $(this).fadeIn().delay('3000').fadeOut(500);
        });

        noticeBlock.each( (k, e) => {
            if (this.trim($(e).html()) != '') {
                $(e).fadeIn().delay('3000').fadeOut(500);
            }
        });


        var openModals = [];

        $(document).on('shown.bs.modal', '.modal', function (e) {
            var modalBody = $(this).find('.modal-body');
            modalBody.css('overflow-y', 'auto');
            modalBody.css('max-height', 'calc(55vh)');
            if(isChrome){
                var windowHeight = $(window).height();
                if(windowHeight > 860){
                    modalBody.css('max-height', '70vh');
                }else if(windowHeight > 740){
                    modalBody.css('max-height', '65vh');
                }else if(windowHeight > 640){
                    modalBody.css('max-height', '60vh');
                }else{
                    modalBody.css('max-height', '55vh');
                }
            }else{
                modalBody.css('max-height', '70vh');
            }

            openModals.push($(this).attr('id'));
        }).on('hide.bs.modal', '.modal', function(){
            var index = openModals.indexOf($(this).attr('id'));
            if (index > -1) {
                openModals.splice(index, 1);
            }
        }).on('hidden.bs.modal', function(){
            if(openModals.length > 0){
                $('body').addClass('modal-open');
            }
        });

        // handle the #toggle click event
        $("#toggleNav").on("click", function () {
            // apply/remove the active class to the row-offcanvas element
            $(".row-offcanvas").toggleClass("active");
            return false;
        });
        var toggleWidthBtn = $('#toggleWidth');
        var resizeIcon = toggleWidthBtn.find('i.glyphicon');
        var initialSizeSmall = resizeIcon.hasClass('glyphicon-resize-full');


        toggleWidthBtn.on('click', function(){
            if(resizeIcon.hasClass('glyphicon-resize-full')){
                resizeWindowFull();
                $.ajax('/admin/set_admin_portal_width', {data: {'size': 'full'}});
            }else{
                resizeWindowSmall();
                $.ajax('/admin/set_admin_portal_width', {data: {'size': 'small'}});
            }
            return false;
        });



        $( window ).resize(function() {
            if($( window ).width() < 993 && initialSizeSmall){
                resizeWindowFull();
            }

            if($( window ).width() > 994 && initialSizeSmall){
                resizeWindowSmall();
            }
        });

        function resizeWindowFull(){
            $(".container").switchClass('container', "container-fluid", 500, "easeOutSine");
            resizeIcon.removeClass('glyphicon-resize-full').addClass('glyphicon-resize-small');
        }

        function resizeWindowSmall(){
            $(".container-fluid").switchClass('container-fluid', "container", 500, "easeOutSine");
            resizeIcon.removeClass('glyphicon-resize-small').addClass('glyphicon-resize-full');
        }

        $(window).trigger('resize');


        // Restore value from hidden input
        $('input[type=hidden]', '.date').each(function(){
            if($(this).val()) {
                $(this).parent().datetimepicker('setValue');
            }
        });

        this.toggleFilters()
        this.setupEditibaleFields()
        this.pagerSelector()

    },
    toggleFilters(subject) {
        this.debugMessage('[core|add_filters] configure filters on', subject);
        if (
            $('.advanced-filter :input:visible', subject).filter(function filterWithoutValue() {return $(this).val(); }).length === 0
        ) {
            $('.advanced-filter').hide();
        }

        $('[data-toggle="advanced-filter"]', subject).on('click', () => {
            $('.advanced-filter').toggle();
        });
    },
    setupEditibaleFields(subject){
        jQuery('.x-editable', subject).editable({
            emptyclass: 'editable-empty btn btn-sm btn-default',
            emptytext: '<i class="fas fa-pencil-alt"></i>',
            container: 'body',
            placement: 'auto',
            success(response) {
                const html = jQuery(response);
                InitCms.setupEditibaleFields(html);
                jQuery(this).closest('td').replaceWith(html);
            },
            error: (xhr) => {
                // On some error responses, we return JSON.
                if (xhr.getResponseHeader('Content-Type') === 'application/json') {
                    return JSON.parse(xhr.responseText);
                }

                return xhr.responseText;
            },
        });
    },
    confirmExit(formElement) {
        formElement.dataset.original = new URLSearchParams(new FormData(formElement)).toString()
        formElement.addEventListener('submit', () => {
            formElement.removeAttribute('data-original')
        })
    },
    setupConfirmExit(){
        window.addEventListener('load', () => {
            document.querySelectorAll('.sonata-ba-form form').forEach((formElement) => {
                this.confirmExit(formElement);
            });
        });
        window.addEventListener('beforeunload', (event) => {
            const e = event || window.event;
            const message = tranlstions['CONFIRM_EXIT'];
            let changes = false;
            e.preventDefault()

            document.querySelectorAll('form[data-original]').forEach((formElement) => {
                const newData = new URLSearchParams(new FormData(formElement)).toString()
                if (formElement.dataset.original !== newData) {
                    changes = true;
                }
            })

            if (changes) {
                // For old IE and Firefox
                if (e) {
                    e.returnValue = message;
                }

                return message;
            }
        });
    },
    trim(str) {
        str = str.replace(/^\s+/, '');
        for (var i = str.length - 1; i >= 0; i--) {
            if (/\S/.test(str.charAt(i))) {
                str = str.substring(0, i + 1);
                break;
            }
        }
        return str;
    },
    pagerSelector() {

        jQuery('select.per-page').change(function(event) {
            jQuery('input[type=submit]').hide();

            window.top.location.href=this.options[this.selectedIndex].value;
        });
    },
    createInitCmsMessageBox(status, message) {
        status = status == 'error'?'danger':status;
        var messageHtml = '<div class="alert alert-' + status + '"><a class="close" data-dismiss="alert" href="#">×</a>' + message + '</div>';

        document.querySelectorAll('.notice-block').forEach((e) => {
            e.innerHTML = messageHtml
        })
    },
    debugMessage(...args) {
        if (!this.getConfig('DEBUG')) {
            return;
        }

        const msg = `[DEBUG] ${Array.prototype.join.call(args, ', ')}`;
        if (window.console && window.console.log) {
            window.console.log(msg);
        } else if (window.opera && window.opera.postError) {
            window.opera.postError(msg);
        }
    },
}

export default InitCms