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

    // Restore value from hidden input
    $('input[type=hidden]', '.date').each(function () {
        if ($(this).val()) {
            $(this).parent().datetimepicker('setValue');
        }
    });

    $('.modal').on('show.bs.modal', function () {
        $('.modal .modal-body').css('overflow-y', 'auto');
        $('.modal .modal-body').css('max-height', $(window).height() * 0.7);
    });

    $('[data-provider="datepicker"]').datetimepicker({
        autoclose: true,
        format: 'dd.mm.yyyy',
        language: '{{ app.request.getLocale()|slice(0, 2) }}',
        minView: 'month',
        todayBtn: true,
        startView: 'month'
    }).on('show', function () {
        setTimeout(function () {
            $('.modal .modal-body').css('max-height', $(window).height() * 0.7);
        }, 0.1)
    });

    $('[data-provider="datetimepicker"]').datetimepicker({
        autoclose: true,
        format: 'dd.mm.yyyy hh:ii',
        language: '{{ app.request.getLocale()|slice(0, 2) }}',
        todayBtn: true
    });

    $('[data-provider="timepicker"]').datetimepicker({
        autoclose: true,
        format: 'hh:ii',
        formatViewType: 'time',
        maxView: 'day',
        minView: 'hour',
        startView: 'day'
    }).on('show', function () {
        setTimeout(function () {
            $('.modal .modal-body').css('max-height', $(window).height() * 0.7);
        }, 0.1)
    });

    // Restore value from hidden input
    $('input[type=hidden]', '.date').each(function () {
        if ($(this).val()) {
            $(this).parent().datetimepicker('setValue');
        }
    });

})(jQuery);

$.fn.modal.Constructor.prototype.enforceFocus = function () {
    modal_this = this
    $(document).on('focusin.modal', function (e) {
        if (modal_this.$element[0] !== e.target && !modal_this.$element.has(e.target).length
            // add whatever conditions you need here:
            && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_select') && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_text')) {
            modal_this.$element.focus()
        }
    })
};