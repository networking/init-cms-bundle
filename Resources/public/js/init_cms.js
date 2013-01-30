function createInitCmsMessageBox(status, message) {
        var messageHtml = '<div class="alert alert-' + status + '"><a class="close" data-dismiss="alert" href="#">×</a>' + message + '</div>';

        jQuery('.notice-block').html(messageHtml);
    }

(function($){
    $('.notice-block').on('DOMNodeInserted', function () {
        $(this).fadeIn().delay('3000').fadeOut(500);
    });
})(jQuery);