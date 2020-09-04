function createInitCmsMessageBox(status, message) {
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "2000",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "linear",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    if (status === 'success') {
        toastr.success(message);
    } else if (status === 'warning') {
        toastr.warning(message);
    } else if (status === 'error' || status === 'danger') {
        toastr.error(message);
    } else {
        toastr.info(message);
    }
}

function trim(str) {
    str = str.replace(/^\s+/, '');
    for (var i = str.length - 1; i >= 0; i--) {
        if (/\S/.test(str.charAt(i))) {
            str = str.substring(0, i + 1);
            break;
        }
    }
    return str;
}

function uploadError(xhr) {
    alert(xhr.error);
}


$.fn.modal.Constructor.prototype.enforceFocus = function () {
    $(document)
        .off('focusin.bs.modal') // guard against infinite focus loop
        .on('focusin.bs.modal', $.proxy(function (e) {
            if (this.$element[0] !== e.target && !this.$element.has(e.target).length
                // add whatever conditions you need here:
                && !$(e.target).hasClass('select2-input')
                && !$(e.target.parentNode).hasClass('cke')
            ) {
                this.$element.blur(); //fix for IE (focus to blur for IE 11)
            }
        }, this))
};

$(function () {
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

    $('.show-tooltip, [data-toggle="tooltip"]').tooltip({
        placement: 'bottom',
        container: 'body',
        delay: {show: 800, hide: 100}
    });

    var openModals = [];

    $(document).on('shown.bs.modal', '.modal', function (e) {
        var modalBody = $(this).find('.modal-body');
        modalBody.css('overflow-y', 'auto');
        modalBody.css('max-height', 'calc(55vh)');
        if (isChrome) {
            var windowHeight = $(window).height();
            if (windowHeight > 860) {
                modalBody.css('max-height', '70vh');
            } else if (windowHeight > 740) {
                modalBody.css('max-height', '65vh');
            } else if (windowHeight > 640) {
                modalBody.css('max-height', '60vh');
            } else {
                modalBody.css('max-height', '55vh');
            }
        } else {
            modalBody.css('max-height', '70vh');
        }

        openModals.push($(this).attr('id'));
    }).on('hide.bs.modal', '.modal', function () {
        var index = openModals.indexOf($(this).attr('id'));
        if (index > -1) {
            openModals.splice(index, 1);
        }
    }).on('hidden.bs.modal', function () {
        if (openModals.length > 0) {
            $('body').addClass('modal-open');
        }
    }).on('click', 'a.init-cms-ajax-link', function (e) {
        console.log('hello')
        e.preventDefault();
        var that = this;
        axios.post(KTUtil.attr(that, 'href'), [], axiosConfig)
            .then(function (response) {
                var elm = that.closest('td');
                KTUtil.setHTML(elm,'');
                KTUtil.setHTML(elm,$(response.data.replace(/<!--[\s\S]*?-->/g, "")).html());
                KTUtil.animateClass(elm, 'animate__animated animate__pulse bg-success-o-50')
            })
            .catch(function (error) {
                KTUtil.animateClass(elm, 'animate__animated  animate__pulse bg-danger')
            })

    });

    $('.editable-click').each(function (i, e) {

        var link = $(e);
        var url = link.data('url');
        var locale = link.data('locale');
        var name = link.data('name');
        var value = link.data('value');
        var pk = link.data('pk');
        link.editable({
            url: url,
            name: name,
            value: value,
            params: function (params) {
                // make sure pk is always loaded from element
                params.pk = pk;
                params.locale = locale;
                return params;
            },
            error: function (response) {
                response = JSON.parse(response.responseText);
                return response ? response.message : response;
            },
            success: function (response, newValue) {
                link.data('pk', response.pk);
                return response;
            }
        });
    })

});

var Admin = {
    /**
     * render log message
     * @param mixed
     */
    log: function () {

        var msg = '[InitCms Log] ' + Array.prototype.join.call(arguments, ', ');
        if (window.console && window.console.log) {
            window.console.log(msg);
        } else if (window.opera && window.opera.postError) {
            window.opera.postError(msg);
        }
    },
}

var axiosConfig = {headers: {'X-Requested-With': 'XMLHttpRequest'}}