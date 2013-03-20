$(document).ready(function () {
    var count = $("div.event-slider div.slide").length;
    var currentSlide = 0;
    var enabledOpacity = 1;
    var removedOpacity = 0.4;
    var animationInterval = 5000;
    var isAutomatic = true;

    $("div#wrapper div.events-switches ul li:eq(0) a").css('color', '#cd3b23');

    $('.btn').click(function () {
        isAutomatic = false;

        if ($(this).hasClass("btn-next")) {
            if (currentSlide < count - 1) {
                currentSlide++;
            } else {
                currentSlide = 0;
            }
        }

        if ($(this).hasClass("btn-prev")) {
            if (currentSlide > 0) {
                currentSlide--;
            } else {
                currentSlide = count - 1;
            }
        }

        slide();
    });

    $('div#wrapper div.events-switches ul li').click(function (event) {
        isAutomatic = false;
        var index = $(this).index();
        currentSlide = index;

        slide();
    });

    window.setInterval(automatic, animationInterval);

    function automatic() {
        if (!isAutomatic) {
            return;
        }

        if (currentSlide < count - 1) {
            currentSlide++;
        } else {
            currentSlide = 0;
        }

        slide();
    }

    function slide() {
        $("div.event-slider div.slide").hide().animate({
            opacity: removedOpacity
        }, "slow");
        $("div.event-slider div.slide:eq(" + currentSlide + ")").show().animate({
            opacity: enabledOpacity
        }, "slow");

        // @todo refact. mv harcode colors to css
        $("div.events-switches div.wrap ul li a").css('color', '#0091ad');
        $("div#wrapper div.events-switches ul li:eq(" + currentSlide + ") a").css('color', '#cd3b23');
    }
});