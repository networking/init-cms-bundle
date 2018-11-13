function createInitCmsMessageBox(status, message) {
    status = status == 'error'?'danger':status;
    var messageHtml = '<div class="alert alert-' + status + '"><a class="close" data-dismiss="alert" href="#">×</a>' + message + '</div>';

    jQuery('.notice-block').html(messageHtml);
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
    var isChrome = !!window.chrome && !!window.chrome.webstore;
    
    var noticeBlock = $('.notice-block');

    noticeBlock.on('DOMNodeInserted', function () {
        $(this).fadeIn().delay('3000').fadeOut(500);
    });

    noticeBlock.each(function (k, e) {
        if (trim($(e).html()) != '') {
            $(e).fadeIn().delay('3000').fadeOut(500);
        }
    });

    $('.show-tooltip, [data-toggle="tooltip"]').tooltip({placement:'bottom', container: 'body', delay:{ show:800, hide:100 }});

    var openModals = [];
    
    $(document).on('shown.bs.modal', '.modal', function (e) {
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