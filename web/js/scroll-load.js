$(document).ready(function (){
    /**
     * Participants wrapper
     *
     * @type {*|jQuery|HTMLElement}
     */
    var $participantsWrapper = $('ul.participants');

    /**
     * Preloader element
     *
     * @type {*|jQuery|HTMLElement}
     */
    var $preLoader = $('.preloader');
    $(window).scroll(function () {
        /**
         * Footer top offset
         *
         * @type {Function|jQuery.offset.top|jQuery}
         */
        var footerOffsetTop = $('#footer').offset().top;
        if ($(window).scrollTop() >= (footerOffsetTop-$(window).height() - 100)) {
            /**
             * Count of showed participants
             *
             * @type {Number|jQuery}
             */
            var offset = $('ul.participants li').length;
            /**
             * Sent AJAX request for get next part of participants
             */
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