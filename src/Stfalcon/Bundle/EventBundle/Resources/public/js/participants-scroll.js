$(document).ready(function (){
    var $participantsWrapper = $('ul.participants');

    lock = false;//fix double content

    /** Get preloader element */
    var $preLoader = $('.preloader');
    $(window).scroll(function () {

        /** Get ul.participants bottom offset */
        var participantsWrapperOffsetBottom = ($participantsWrapper.offset().top + $participantsWrapper.height()) - $(window).height();
        if ($(window).scrollTop() >= (participantsWrapperOffsetBottom) && lock===false) {

            /** Count of showed participants */
            var offset = $('ul.participants li').length;
            lock = true;
            loadContent(offset,$participantsWrapper, $preLoader);
        }
    });

    /** Sent AJAX request for get next part of participants */
    function loadContent(offset, wrapper, loader) {
        $.ajax({
            cache: false,
            async: false,
            url: window.location.href + '/' + offset,
            beforeSend: function () {
                loader.show();
            },
            success: function (data) {
                wrapper.append(data);
                loader.hide();
                lock = false;
            }
        });
    }

})