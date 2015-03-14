function createInitCmsMessageBox(status, message) {
    var messageHtml = '<div class="col-sm-12 col-xs-12 col-md-offset-2 col-md-10 alert alert-' + status + '"><a class="close" data-dismiss="alert" href="#">×</a>' + message + '</div>';

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

var magnificPopupOptions = {
    tClose: window.MAGNIFIC_TRANSLATIONS.close, // Alt text on close button
    tLoading: window.MAGNIFIC_TRANSLATIONS.loading,
    tError: window.MAGNIFIC_TRANSLATIONS.error,
    image: {titleSrc: 'title'},
    iframe: {
        markup: '<div class="mfp-iframe-scaler">'+
        '<div class="mfp-close"></div>'+
        '<iframe class="mfp-iframe" frameborder="0" height="800px;" allowfullscreen></iframe>'+
        '</div>', // HTML markup of popup, `mfp-close` will be replaced by the close button
        patterns: {
            youtube: {
                index: 'youtube.com/', // String that detects type of video (in this case YouTube). Simply via url.indexOf(index).

                id: 'v=', // String that splits URL in a two parts, second part should be %id%
                // Or null - full URL will be returned
                // Or a function that should return %id%, for example:
                // id: function(url) { return 'parsed id'; }
                src: '//www.youtube.com/embed/%id%?autoplay=1' // URL that will be set as a source for iframe.
            },
            vimeo: {
                index: 'vimeo.com/',
                id: '/',
                src: '//player.vimeo.com/video/%id%?autoplay=1'
            },
            gview: {
                index: '//docs.google.',
                src: '%id%'
            }
        }
    }
};
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
    });

    $("#toggleWidth").on('click', function(){
        var el = $(this).find('i.glyphicon');
        if(el.hasClass('glyphicon-resize-full')){
            $(".container").switchClass('container', "container-fluid", 500, "easeOutSine");
            el.removeClass('glyphicon-resize-full').addClass('glyphicon-resize-small');
            $.ajax('/admin/set_admin_portal_width', {data: {'size': 'full'}});
        }else{
            $(".container-fluid").switchClass('container-fluid', "container", 500, "easeOutSine");
            el.removeClass('glyphicon-resize-small').addClass('glyphicon-resize-full');
            $.ajax('/admin/set_admin_portal_width', {data: {'size': 'small'}});
        }
    });

    $('.image-preview-link').magnificPopup(magnificPopupOptions);
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
                this.$element.focus()
            }
        }, this))
};