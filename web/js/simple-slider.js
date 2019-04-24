$(document).ready(function () {
    var count = $("div.event-slider div.slide").length;
    /**
     * execute js if there is more then one text slider (events)
     */
    if(count > 1) {
        var isAutomatic = true;
        var currentSlide = 0;
        var enabledOpacity = 1;
        var removedOpacity = 0.1;
        var animationInterval = 5000;

        /**
         * automatic scrolling slide
         *
         * @return {void}
         */
        window.setInterval(function () {
            if (!isAutomatic) {
                return;
            }

            if (currentSlide < count - 1) {
                currentSlide++;
            } else {
                currentSlide = 0;
            }

            changeSlide();
        }, animationInterval);

        /**
         * add click event to events switches
         *
         * @return {void}
         */
        $('div#wrapper div.events-switches ul li').click(function (event) {
            isAutomatic = false;
            currentSlide = $(this).index();

            changeSlide();
        });

        /**
         * add animation to text slide
         *
         * @return {void}
         */
        function changeSlide() {
            $("div.event-slider div.slide")
                .animate({ opacity: removedOpacity }, "fast", function() {
                    // hide all slides
                    $("div.event-slider div.slide").hide();

                    // change active link in nav switcher
                    $("div.events-switches div.wrap ul li").removeClass("active");
                    $("div#wrapper div.events-switches ul li:eq(" + currentSlide + ")").addClass('active');

                    // snow next slide
                    $("div.event-slider div.slide:eq(" + currentSlide + ")")
                        .css({opacity: removedOpacity })
                        .show()
                        .animate({ opacity: enabledOpacity }, "fast");
                });
        }
    }

    /**
     * initialization of images slider
     *
     */
    setSlider();

    /**
     * set slider FX, set slider size on orientationEvent
     */
    $(window).on(orientationEvent, function () {
        clearTimeout($.data(this, 'sliderResizeTimer'));
        $.data(this, 'sliderResizeTimer', setTimeout(function () {
            setSlider();
        }, 50));
    });
});

/**
 * check orientation support
 *
 * @type {Boolean}
 */
var supportsOrientationChange = "onorientationchange" in window,
    orientationEvent = supportsOrientationChange ? "orientationchange" : "resize";

/**
 * set slider FX, set slider size
 *
 * @method setSlider
 *
 * @return {void}
 */
function setSlider() {
    $('#header .photo-slider').cycle('destroy');
    $('#header .photo-slider li').attr('style', '');
    if ($('#header .photo-slider img').height() > $('#header .photo-slider').height()) {
        $('#header .photo-slider img').css('top', '-' + ( ($('#header .photo-slider img').height() - $('#header .photo-slider').height()) / 2 ) + 'px');
    } else {
        $('#header .photo-slider img').css('top', '0');
    }
    if ($('#header .photo-slider img').width() > $('#header .photo-slider').width()) {
        $('#header .photo-slider img').css('left', '-' + ( ($('#header .photo-slider img').width() - $('#header .photo-slider').width()) / 2 ) + 'px');
    } else {
        $('#header .photo-slider img').css('left', '0');
    }

    $('#header .photo-slider').cycle({
//        fx: 'scrollLeft',
        speed:1500,
        timeout:10000
    });
}