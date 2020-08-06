

function createInitCmsMessageBox(status, message) {
    status = status == 'error'?'danger':status;

    var messageHtml = '<div class="toast" role="alert" aria-live="polite" aria-atomic="true" data-delay="3000" style="position: absolute; top: 0; right: 0; width: 300px">\n' +
        '    <div class="toast-header">\n' +
        '      <button type="button" class="ml-auto mb-1 close" data-dismiss="toast" aria-label="Close">\n' +
        '        <span aria-hidden="true">&times;</span>\n' +
        '      </button>\n' +
        '    </div>\n' +
        '    <div class="toast-body bg-lighter-'+status+'  text-'+status+'">\n' +
            message +
        '    </div>\n' +
        '  </div>';

    jQuery('#toaster-panel').html(messageHtml);
    $('.toast').toast('show');
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
(function ($) {

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

    $('.show-tooltip, [data-toggle="tooltip"]').tooltip({placement:'bottom', container: 'body', delay:{ show:800, hide:100 }});

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
    $('#body-row .collapse').collapse('hide');

// Collapse click
    $('[data-toggle=sidebar-colapse]').click(function() {
        SidebarCollapse();
    });

    function SidebarCollapse () {
        $('.menu-collapsed').toggleClass('d-none');
        $('.sidebar-submenu').toggleClass('d-none');
        $('.submenu-icon').toggleClass('d-none');
        $('.cms-brand').toggleClass('d-none');
        $('#sidebar-container').toggleClass('sidebar-expanded sidebar-collapsed');

        // Treating d-flex/d-none on separators with title
        var SeparatorTitle = $('.sidebar-separator-title');
        if (SeparatorTitle.hasClass('d-flex')) {
            SeparatorTitle.removeClass('d-flex');
        } else {
            SeparatorTitle.addClass('d-flex');
        }
    }



    // $('body').on('mouseenter', '#sidebar-container.sidebar-collapsed li', function (){
    //     $('#sidebar-container').addClass('sidebar-hover');
    //     SidebarCollapse();
    // }).on('mouseleave', '#sidebar-container.sidebar-hover li', function (){
    //     $('#sidebar-container').removeClass('sidebar-hover');
    //     SidebarCollapse();
    // })

})(jQuery);

$.fn.select2.defaults.set( "theme", "bootstrap" );
$.fn.modal.Constructor.prototype.enforceFocus = function() {
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