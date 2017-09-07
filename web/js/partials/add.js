$(document).ready(function () {
    $('.speaker-card__top').on('click', function () {
        var e_slug = $(this).data('content-event');
        var s_slug = $(this).data('content-speaker');

        $.get(Routing.generate('speaker_popup', { event_slug: e_slug, speaker_slug:s_slug}), function (data) {
            $('#speaker-popup-content').html(data.html);
        });
    });

    $('.set-modal-header').on('click', function () {
        var elem = $(this);
        var e_slug = elem.data('event');
        $.post(Routing.generate('get_modal_header', {slug: e_slug}), function (data) {
            if (data.result) {
                $('.remodal__title').html(data.html);
            } else {
                console.log('Error:'+data.error);
            }
        });
    });

    function setAddWantsOnclick() {
        $('.add-wants-visit-event').prop('onclick',null).off('click').
        on('click', function () {
            var elem = $(this);
            var e_slug = elem.data('event');
            $.ajax({
                type: 'POST',
                url: Routing.generate('add_wants_to_visit_event', { slug: e_slug}),
                success: function(data) {
                    if (data.result) {
                        elem.removeClass('add-wants-visit-event').addClass('sub-wants-visit-event').prop('onclick',null)
                            .off('click').html(data.html);
                        setSubWantsOnclick();
                    } else {
                        console.log('Error:'+data.error);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    switch (jqXHR.status) {
                        case 401:
                            window.location.replace('#modal-signin');
                            break;
                        case 403: // (Invalid CSRF token for example)
                            // Reload page from server
                            window.location.reload(true);
                    }
                }
            });
        });
    }

    function setSubWantsOnclick() {
        $('.sub-wants-visit-event').prop('onclick', null).off('click').
        on('click', function () {
            var elem = $(this);
            var e_slug = elem.data('event');
            $.post(Routing.generate('sub_wants_to_visit_event', {slug: e_slug}), function (data) {
                if (data.result) {
                    elem.removeClass('sub-wants-visit-event').addClass('add-wants-visit-event').prop('onclick', null)
                        .off('click').html(data.html);
                    setAddWantsOnclick();
                } else {
                    console.log('Error:'+data.error);
                }
            });
        })
    }

    setAddWantsOnclick();
    setSubWantsOnclick();
});

