var e_slug = '';
var s_slug = '';

$(document).on('opening', '#speaker-popup', function () {
    $.get(Routing.generate('speaker_popup', { event_slug: e_slug, speaker_slug:s_slug}), function (data) {
        $('#speaker-popup-content').html(data.html);
    });
});

$(document).ready(function () {
    $('.speaker-card__top').on('click', function () {
        e_slug = $(this).data('content-event');
        s_slug = $(this).data('content-speaker');
    });
});

