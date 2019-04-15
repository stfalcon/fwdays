$(document).ready(function () {
    function getCurrentDateStr() {
        var month = d.getMonth() + 1;
        var day = d.getDate();
        return d.getFullYear() + '-' +
            (month < 10 ? '0' : '') + month + '-' +
            (day < 10 ? '0' : '') + day;
    }

    function getCurrentTimeStr() {
        return d.getHours() + ":" + d.getMinutes();
    }

    function scrollToNearReport() {
        var scrollTo = null;
        var current_report = null;
        var prev_report = null;
        var now_time = getCurrentTimeStr();

        $('.program-body__td--time').each(function (index, value) {
            if (current_report !== null) {
                prev_report = current_report;
            }
            current_report = $(this);
            if (now_time < current_report.text()) {
                if (prev_report !== null) {
                    scrollTo = prev_report;
                } else {
                    scrollTo = current_report;
                }
                return false;
            }
        });
        if (scrollTo !== null) {
            $('body,html').animate({
                scrollTop: scrollTo.offset().top - 125
            }, 600);
        }
    }

    var event_header_date_element = $('.event-header__date');
    if (event_header_date_element.length) {
        var d = new Date();
        var now_date = getCurrentDateStr();
        var event_date = event_header_date_element.attr('datetime');

        if ('scrollRestoration' in history) {
            history.scrollRestoration = event_date === now_date ? 'manual' : 'auto';
        }
        if (event_date === now_date) {
            scrollToNearReport();
        }
    }
});