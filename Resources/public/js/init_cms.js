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

    $('.show-tooltip').tooltip({placement:'bottom', delay:{ show:800, hide:100 }});

    $(document).on('show', '.modal', function(){
        var modalBody = $(this).find('.modal-body');
        modalBody.css('overflow-y', 'auto');
        if(modalBody.parents('.modal-full').length > 0){
            modalBody.css('height', $(window).height() -190);
            modalBody.css('max-height', '900px');
        }else{
            modalBody.css('max-height', $(window).height() * 0.7);
        }
    });

    $("#toggleWidth").on('click', function(){
        var el = $(this).find('i.icon');
        if(el.hasClass('icon-resize-full')){
            $(".container").switchClass('container', "container-fluid", 500, "easeOutSine");
            el.removeClass('icon-resize-full').addClass('icon-resize-small');
            $.ajax('/admin/set_admin_portal_width', {data: {'size': 'full'}});
        }else{
            $(".container-fluid").switchClass('container-fluid', "container", 500, "easeOutSine");
            el.removeClass('icon-resize-small').addClass('icon-resize-full');
            $.ajax('/admin/set_admin_portal_width', {data: {'size': 'small'}});
        }
    });

})(jQuery);

$.fn.modal.Constructor.prototype.enforceFocus = function() {
  $(document).on('focusin.modal', $.proxy(function (e) {
      if (this.$element[0] !== e.target && !this.$element.has(e.target).length
              // add whatever conditions you need here:
          && !$(e.target).hasClass('select2-input')
          && !$(e.target.parentNode).hasClass('cke')
      ) {
          this.$element.focus()
      }
  }, this));
};