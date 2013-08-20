/**
 * Clear slider timer.
 *
 * @return {void}
 */
$(document).ready(function () {
    setSlider();
    $(window).on(orientationEvent, function () {
        clearTimeout($.data(this, 'sliderResizeTimer'));
        $.data(this, 'sliderResizeTimer', setTimeout(function () {
            setSlider();
        }, 50));
    });

});

var supportsOrientationChange = "onorientationchange" in window,
    orientationEvent = supportsOrientationChange ? "orientationchange" : "resize";

/**
 * Set slider interval, make slider size.
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
