$(document).ready(function (){
    var $participantsWrapper = $('ul.participants');

    /** Get preloader element */
    var $preLoader = $('.preloader');
    $(window).scroll(function () {

        /** Get Footer top offset */
        var footerOffsetTop = $('#footer').offset().top;
        if ($(window).scrollTop() >= (footerOffsetTop-$(window).height() - 100)) {

            /** Count of showed participants */
            var offset = $('ul.participants li').length;

            /** Sent AJAX request for get next part of participants */
            $.ajax({
                cache: false,
                async: false,
                url: window.location.href + '/' + offset,
                beforeSend: function () {
                    $preLoader.show();
                },
                success: function (data) {
                    $participantsWrapper.append(data);
                    $preLoader.hide();
                }
            });
        }
    });
})