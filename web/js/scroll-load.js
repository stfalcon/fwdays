$(document).ready(function (){
    var $participantsWrapper = $('ul.participants');
    var $preLoader = $('.preloader');
    $(window).scroll(function () {
        var footerOffsetTop = $('#footer').offset().top;
        if ($(window).scrollTop() >= (footerOffsetTop-$(window).height() - 100)) {
            var offset = $('ul.participants li').length;
            $.ajax({
                cache: false,
                async: false,
                url: window.location.href + '/' + offset,
                beforeSend: function (xhr) {
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