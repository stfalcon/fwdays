function detectmob() {

    return navigator.userAgent.match(/Android/i)
        || navigator.userAgent.match(/webOS/i)
        || navigator.userAgent.match(/iPhone/i)
        || navigator.userAgent.match(/iPad/i)
        || navigator.userAgent.match(/iPod/i)
        || navigator.userAgent.match(/BlackBerry/i)
        || navigator.userAgent.match(/Windows Phone/i);
}

function popupwindow(url, title, w, h) {
    var left = (screen.width/2)-(w/2);
    var top = (screen.height/2)-(h/2);
    return window.open(url, title, 'width='+w+', height='+h+', top='+top+', left='+left);
}

function setSpeakerHtml(e_slug, s_slug, with_review) {
    var inst = $('[data-remodal-id=modal-speaker]').remodal();
    $.get(Routing.generate('speaker_popup', { slug: e_slug, speakerSlug:s_slug, withReview:with_review}),
        function (data) {
            if (data.result) {
                $('#speaker-popup-content').html(data.html);
                inst.open();
                loadSrc();
            } else {
                inst.close();
                console.log('Error:' + data.html);
            }
        });
}

var registrationFormId = null;

function submitRegistrationForm(token) {
    if (null !== registrationFormId) {
        $('<input />').attr('type', 'hidden')
            .attr('name', "g-recaptcha-response")
            .attr('value', token)
            .appendTo('#' + registrationFormId);

        $('#' + registrationFormId).submit();
    }
}

function submitValidForm(rId, withCaptcha) {
    registrationFormId = rId;
    var form = $('#'+registrationFormId);
    form.validate({
        debug: false,
        errorClass: "text-error",
        errorElement: "p",
        onkeyup: false,
        highlight: function(element) {
            $(element).addClass('input--error');
            var p = $(element).next('p');
            if ($(p).hasClass('text-error') && undefined === $(p).attr('id')) {
                $(p).hide();
            }
        },
        unhighlight: function(element) {
            $(element).removeClass('input--error');
            var p = $(element).next('p');
            if ($(p).hasClass('text-error') && undefined === $(p).attr('id')) {
                $(p).hide();
            }
        }
    });

    if (form.valid()) {
        if (withCaptcha) {
            grecaptcha.execute();
        } else {
            form.submit();
        }
    }
}

$(document).on('click', '.social-login', function () {
    var elem = $(this);
    if (elem.hasClass('bye-after-login')) {
        Cookies.set('bye-event', 'event');
    } else {
        Cookies.remove('bye-event', { path: '/', http: false, secure : false });
    }
});

var hideTimer = null;

function hideFlash(text) {
    $('#flash-user').removeClass('alert--show').fadeOut(400, function () {
        $('#flash-user-content').html(text);
    });
}

function showFlash() {
    $('#flash-user').addClass('alert--show').fadeIn();
}

function setFlashTextAndShow(text) {
    var flashDiv = $('#flash-user');
    if (flashDiv.hasClass('alert--show')) {
        hideFlash(text);
    } else {
        $('#flash-user-content').html(text);
    }
    showFlash();
    if (hideTimer) {
        clearTimeout(hideTimer);
    }
    hideTimer = setTimeout(hideFlash, 3000);
}

$(document).on('click', '.alert__close', function () {
    hideFlash();
    if (hideTimer) {
        clearTimeout(hideTimer);
    }
});

$(document).on('click', '.language_switcher', function (e) {
    Cookies.set('hl', $(this).data('lang'));
});

$(document).on('click', '.add-wants-visit-event', function () {
        var elem = $(this);
        var e_slug = elem.data('event');
        $.ajax({
            type: 'POST',
            url: Routing.generate('add_wants_to_visit_event', { slug: e_slug}),
            success: function(data) {
                if (data.result) {
                    $('.add-wants-visit-event').each(function() {
                        if ($( this ).data('event') === e_slug) {
                            $( this ).removeClass('add-wants-visit-event').addClass('sub-wants-visit-event').html(data.html);
                        }
                    });
                    if ('prod' === environment) {
                        dataLayer.push({'event': 'register_event'});
                    }
                    setFlashTextAndShow(data.flash);
                } else {
                    console.log('Error:'+data.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                switch (jqXHR.status) {
                    case 401:
                        if (detectmob()) {
                            window.location.href = homePath+"login?exception_login=1";
                        } else {
                            var inst = $('[data-remodal-id=modal-signin-payment]').remodal();
                            inst.open();
                        }
                        break;
                    case 403:
                        window.location.reload(true);
                }
            }
        });
});

$(document).on('click', '.sub-wants-visit-event', function () {
        var elem = $(this);
        var e_slug = elem.data('event');
        $.post(Routing.generate('sub_wants_to_visit_event', {slug: e_slug}), function (data) {
            if (data.result) {
                $('.sub-wants-visit-event').each(function() {
                    if ($( this ).data('event') === e_slug) {
                        $( this ).removeClass('sub-wants-visit-event').addClass('add-wants-visit-event').html(data.html);
                    }
                });
                setFlashTextAndShow(data.flash);
            } else {
                console.log('Error:'+data.error);
            }
        });
});

$('[data-testid="dialog_iframe"]').on('load', function() {
    $(this).removeClass('fb_customer_chat_bounce_in').addClass('fb_customer_chat_bounce_out').hide();
});

$(document).ready(function () {
    $('.add_recapcha').on('click', function () {
        var s = $("<script></script>");
        s.attr('src', 'https://www.google.com/recaptcha/api.js?hl='+locale);
        s.prop('async', true);
        s.prop('defer', true);
        $("body").append(s);
    });

    $('#share-ref__facebook').on('click', function () {
        popupwindow('http://www.facebook.com/sharer/sharer.php?u='+$('#ref-input').val(), 'facebook', 500, 350);
    });

    $('.mask-phone-input--js').bind('input', function() {
        $(this).val(function(_, v){
            return v.replace(/[-\s\(\)]+/g, '');
        });
    }).on('focus', function () {
        if ($(this).val() === '') {
            $(this).val('+380');
        }
    }).on('focusout', function () {
        if ($(this).val() === '+380') {
            $(this).val('');
        }
    });

    $('.open-speaker-popup').on('click', function () {
        var speakerElement = $('.speaker-card__top[data-speaker='+$(this).data('speaker')+']'),
             e_slug = speakerElement.data('event'),
             s_slug = speakerElement.data('speaker'),
             with_review = speakerElement.data('review');
        setSpeakerHtml(e_slug, s_slug, with_review);
    });

    $('.speaker-card__top').on('click', function () {
        var e_slug = $(this).data('event');
        var s_slug = $(this).data('speaker');
        var with_review = $(this).data('review');
        setSpeakerHtml(e_slug, s_slug, with_review);
    });

    $(document).on('click', '.like-btn-js', function (e) {
        e.preventDefault();
        var rv_slug = $(this).data('review');
        $.post(Routing.generate('like_review', {slug: rv_slug}),
            function (data) {
                if (data.result) {
                    $("div[data-review='"+ rv_slug+"']").html('<i class="icon-like like-btn__icon"></i>'+data.likesCount);
                } else {
                    $("div[data-review='"+rv_slug+"']").html('<i class="icon-like like-btn__icon"></i>error');
                }
        });
    });
});
