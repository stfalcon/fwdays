function detectmob() {

    return navigator.userAgent.match(/Android/i)
        || navigator.userAgent.match(/webOS/i)
        || navigator.userAgent.match(/iPhone/i)
        || navigator.userAgent.match(/iPad/i)
        || navigator.userAgent.match(/iPod/i)
        || navigator.userAgent.match(/BlackBerry/i)
        || navigator.userAgent.match(/Windows Phone/i);
}

function setModalHeader(e_slug, h_type) {
    $.post(Routing.generate('get_modal_header', {slug: e_slug, headerType:h_type}), function (data) {
        if (data.result) {
            $('.change-title').html(data.html);
        } else {
            console.log('Error:'+data.error);
        }
    });
}

function popupwindow(url, title, w, h) {
    var left = (screen.width/2)-(w/2);
    var top = (screen.height/2)-(h/2);
    return window.open(url, title, 'width='+w+', height='+h+', top='+top+', left='+left);
}

function setPaymentHtmlbyData(data, e_slug) {
    $('#payment').data('pay-type', data.pay_type).attr('action', data.form_action);
    $('#pay-form').html(data.html).data('event', e_slug);
    $('#payment-sums').html(data.paymentSums);
    $('#cancel-promo-code').click();
    $('#cancel-add-user').click();
    $('#user_phone').val(data.phoneNumber);
    var buy_btn = $('#buy-ticket-btn');
    if (data.form_action === '') {
        buy_btn.prop("disabled", true);
    } else {
        buy_btn.prop('disabled', false);
    }
    buy_btn.html(data.byeBtnCaption);
    var old_event = buy_btn.data('event');
    if (old_event) {
        buy_btn.removeClass('event-'+old_event);
    }

    buy_btn.addClass('event-'+e_slug).data('event', e_slug);
    if (!data.is_user_create_payment) {
        $('#add-user-trigger').hide();
        $('#promo-code-trigger').hide();
    }
}

function getPlaceByElem(elem) {
    if (elem) {
        var place = 'social';
        if (elem.hasClass('cost__buy--mob')) {
            place = 'event_pay_mob';
        } else if (elem.hasClass('cost__buy')) {
            place = 'event_pay';
        } else if (elem.hasClass('event_fix_header_mob') || elem.hasClass('event-action-mob__btn')
            || elem.hasClass('fix-event-header__btn--mob')) {
            place = 'event_mob';
        } else if (elem.hasClass('fix-event-header__btn') || elem.hasClass('event-header__btn')) {
            place = 'event';
        } else if (elem.hasClass('event-card__btn') || elem.hasClass('event-row__btn')) {
            place = 'main';
        }
        return place;
    }
}

function setPaymentHtml(e_slug, mobForce) {
    var inst = $('[data-remodal-id=modal-payment]').remodal();
    var promocode = Cookies.get('promocode');
    var promoevent = Cookies.get('promoevent');
    var route = '';
    if (promocode && promoevent === e_slug) {
        route = Routing.generate('event_pay', {eventSlug: e_slug, promoCode: promocode});
    } else {
        route = Routing.generate('event_pay', {eventSlug: e_slug});
    }
    $.ajax({
        type: 'GET',
        url: route,
        success: function (data) {
            if (data.result) {
                setPaymentHtmlbyData(data, e_slug);
                if (data.tickets_count === 0) {
                    $('#add-user-trigger').click();
                }
                if (!detectmob() && !mobForce) {
                    inst.open();
                }
                return true;
            } else {
                if (data.error_code === 1) {
                    window.location.pathname = homePath+"static-payment/"+e_slug;
                }
                console.log('Error:' + data.error);
                if (!detectmob() && !mobForce) {
                    inst.close();
                }

                return false;
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            switch (jqXHR.status) {
                case 401:
                    if (detectmob()) {
                        window.location.search = "?exception_login=1";
                        window.location.pathname = homePath+"login";
                    } else {
                        var inst = $('[data-remodal-id=modal-signin-payment]').remodal();
                        inst.open();
                    }
                    break;
                case 403: window.location.reload(true);
            }
            return false;
        }
    });
}

function setSpeakerHtml(e_slug, s_slug, with_review) {
    var inst = $('[data-remodal-id=modal-speaker]').remodal();
    $.get(Routing.generate('speaker_popup', { eventSlug: e_slug, speakerSlug:s_slug, withReview:with_review}),
        function (data) {
            if (data.result) {
                $('#speaker-popup-content').html(data.html);
                inst.open();
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

$(document).on('submit', '#payment', function (e) {
    var form = $(this);
    if (form.data('pay-type') === 'wayforpay') {
        e.preventDefault();
        if (!detectmob()) {
            var inst = $('[data-remodal-id=modal-payment]').remodal();
            inst.close();
        }
        paymentSystemPay();
    }
});


$(document).on('click', '.user-payment__remove', function () {
    var elem = $(this);
    var e_slug = $('#pay-form').data('event');
    $.post(Routing.generate('remove_ticket_from_payment',
        {
            eventSlug: e_slug,
            id: elem.data('ticket')
        }),
        function (data) {
            if (data.result) {
                setPaymentHtmlbyData(data, e_slug)
            } else {
                console.log('Error:'+data.error);
            }
        });
});

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

    $.validator.methods.email = function( value, element ) {
        return this.optional( element ) || /^\w([\-\.]{0,1}\w)*\@\w+([\-\.]{0,1}\w)*\.\w{2,4}$/.test( value );
    };

    $('#payment').validate({
        debug: false,
        errorClass: "text-error",
        errorElement: "p",
        onkeyup: false,
        highlight: function(element) {
            $(element).addClass('input--error');
        },
        unhighlight: function(element) {
            $(element).removeClass('input--error');
        }
    });

    $.validator.addClassRules({
        'valid-name': {
            required: true,
            pattern: /^[A-Za-zА-Яа-яЁёІіЇїЄє\-\s']+$/,
            minlength: 2,
            maxlength: 32,
        },
        'valid-plainPassword' : {
            required: true,
            minlength: 2,
            maxlength: 72,
        },
        'valid-email' : {
            required: true,
            email: true,
        },
        'valid-phone' : {
            required: false,
            minlength: 12,
            maxlength: 16,
            pattern: /\+[1-9]{1}[0-9]{10,14}$/i,
        }
    });

    $('#user_promo_code').rules("add", {
        minlength: 2,
        messages: {
            minlength: $.validator.format(Messages[locale].CORRECT_MIN),
            required: Messages[locale].FIELD_REQUIRED,
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

    $('.set-modal-header').on('click', function () {
        var e_slug = $(this).data('event');
        var h_type = '';
        if ($(this).hasClass('get-payment')) {
            h_type = 'buy';
        } else {
            h_type = 'reg';
        }
        setModalHeader(e_slug, h_type);
    });

    $('.get-payment').on('click', function () {
        var elem = $(this);
        var e_slug = elem.data('event');
        var promocode = Cookies.get('promocode');
        var promoevent = Cookies.get('promoevent');
        if (detectmob()) {
            var queryParams = '';
            if (promocode && promoevent === e_slug) {
                queryParams = '?promocode='+promocode;
            }
            window.location.pathname = homePath + "static-payment/" + e_slug + queryParams;
        } else {
            setModalHeader(e_slug, 'buy');
            setPaymentHtml(e_slug);
        }
    });

    $('.add-promo-code-btn').on('click', function () {
        if ($('#user_promo_code').valid()) {
            var e_slug = $('#pay-form').data('event');
            $.post(Routing.generate('add_promo_code', {code: $("input[name='user_promo_code']").val(), eventSlug: e_slug}),
                function (data) {
                    if (data.result) {
                        setPaymentHtmlbyData(data, e_slug);
                    } else {
                        var validator = $('#payment').validate();
                        var errors = { user_promo_code: data.error };
                        validator.showErrors(errors);
                    }
                });
        }
    });

    $('.add-user-btn').on('click', function () {
        if ($('#payment_user_name').valid() &&
            $('#payment_user_surname').valid() &&
            $('#payment_user_email').valid()) {
            var e_slug = $('#pay-form').data('event');
            $.post(Routing.generate('add_participant_to_payment',
                {
                    eventSlug: e_slug,
                    name: $("input[name='user-name']").val(),
                    surname: $("input[name='user-surname']").val(),
                    email: $("input[name='user-email']").val()
                }),
                function (data) {
                    if (data.result) {
                        setPaymentHtmlbyData(data, e_slug);
                    } else {
                        var validator = $('#payment').validate();
                        var errors = { "user-email": data.error };
                        validator.showErrors(errors);
                    }
                });
        }
    });

    $('#buy-ticket-btn').on('click', function () {
        var use_phone = $('#user_phone').val();
        if (use_phone !== '' && $('#user_phone').valid()) {
            $.post(Routing.generate('update_user_phone', {phoneNumber: use_phone}), function (data) {
            });
        }
    });

    $(document).on('click', '.like-btn-js', function (e) {
        e.preventDefault();
        var rv_slug = $(this).data('review');
        $.post(Routing.generate('like_review', {reviewSlug: rv_slug}),
            function (data) {
                if (data.result) {
                    $("div[data-review='"+ rv_slug+"']").html('<i class="icon-like like-btn__icon"></i>'+data.likesCount);
                } else {
                    $("div[data-review='"+rv_slug+"']").html('<i class="icon-like like-btn__icon"></i>error');
                }
        });
    });

    $('iframe').each(function() {
        if ($(this).data('src')) {
            $(this).attr('src', $(this).data('src'));
        }
    });
});
