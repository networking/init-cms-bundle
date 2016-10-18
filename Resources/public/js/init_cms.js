function createInitCmsMessageBox(status, message) {
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

    $(document).on('show.bs.modal', '.modal', function (e) {
        var modalBody = $(this).find('.modal-body');
        modalBody.css('overflow-y', 'auto');
        if(modalBody.parents('.modal-full').length > 0){
            modalBody.css('height', $(window).height() -190);
            modalBody.css('max-height', '900px');
        }else{
            modalBody.css('max-height', $(window).height() * 0.7);
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