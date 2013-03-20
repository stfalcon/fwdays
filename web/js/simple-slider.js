$(document).ready(function () {
    var count = $("div.event-slider div.slide").length;
    var currentSlide = 0;
    var enabledOpacity = 1;
    var removedOpacity = 0.1;
    var animationInterval = 5000;
    var isAutomatic = true;

    // automatic scrolling slide
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

    // click on next/prev slide button
    $('.event-slider .btn').click(function () {
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

        changeSlide();
    });

    $('div#wrapper div.events-switches ul li').click(function (event) {
        isAutomatic = false;
        currentSlide = $(this).index();

        changeSlide();
    });

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
});