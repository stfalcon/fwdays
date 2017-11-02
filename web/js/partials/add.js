function detectmob() {
    if( navigator.userAgent.match(/Android/i)
        || navigator.userAgent.match(/webOS/i)
        || navigator.userAgent.match(/iPhone/i)
        || navigator.userAgent.match(/iPad/i)
        || navigator.userAgent.match(/iPod/i)
        || navigator.userAgent.match(/BlackBerry/i)
        || navigator.userAgent.match(/Windows Phone/i)
    ){
        return true;
    }
    else {
        return false;
    }
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

function setPaymentHtml(e_slug) {
    var inst = $('[data-remodal-id=modal-payment]').remodal();
    $.ajax({
        type: 'POST',
        url: Routing.generate('event_pay', {eventSlug: e_slug}),
        success: function (data) {
            if (data.result) {
                $('#pay-form').html(data.html).data('event', e_slug);
                $('#payment-sums').html(data.paymentSums);
                $('#cancel-promo-code').click();
                $('#cancel-add-user').click();
                $('#user_phone').val(data.phoneNumber);
                if (!data.is_user_create_payment) {
                    $('#add-user-trigger').hide();
                    $('#promo-code-trigger').hide();
                }
                if (!detectmob()) {
                    inst.open();
                }
            } else {
                console.log('Error:' + data.error);
                if (!detectmob()) {
                    inst.close();
                }
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            switch (jqXHR.status) {
                case 401:
                    if (detectmob()) {
                        window.location.search = "?exception_login=1";
                        window.location.pathname = devpath+"/login";
                    } else {
                        var inst = $('[data-remodal-id=modal-signin-payment]').remodal();
                        inst.open();
                    }
                    break;
                case 403: window.location.reload(true);
            }
        }
    });
}
function setSpeakerHtml(e_slug, s_slug) {
    var inst = $('[data-remodal-id=modal-speaker]').remodal();
    $.get(Routing.generate('speaker_popup', { eventSlug: e_slug, speakerSlug:s_slug}),
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

function paymentAfterLogin() {
    var e_slug = Cookies.get('event');
    if (e_slug) {
        Cookies.remove('event', { path: '/', http: false, secure : false });
        setModalHeader(e_slug, 'buy');
        setPaymentHtml(e_slug);
    }
}

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
                    $('#pay-form').html(data.html);
                    $('#payment-sums').html(data.paymentSums);
                } else {
                    console.log('Error:'+data.error);
                }
            });
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
                } else {
                    console.log('Error:'+data.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                switch (jqXHR.status) {
                    case 401:
                        if (detectmob()) {
                            window.location.href = devpath+"/login?exception_login=1";
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
            } else {
                console.log('Error:'+data.error);
            }
        });
});

$(document).ready(function () {

    $('.mask-phone-input--js').bind('input', function(){
        $(this).val(function(_, v){
            return v.replace(/[-\s\(\)]+/g, '');
        });
    });

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

    $("#user_phone").rules( "add", {
        required: true,
        minlength: 13,
        maxlength: 16,
        pattern: /\+[1-9]{1}[0-9]{11,14}$/i,
        messages: {
            required: Messages[locale].FIELD_REQUIRED,
            minlength: jQuery.validator.format(Messages[locale].CORRECT_MIN),
            maxLength: jQuery.validator.format(Messages[locale].CORRECT_MAX),
            pattern: Messages[locale].CORRECT_PHONE
        }
    });
    $('#payment_user_email').rules("add", {
        pattern:/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/,
        messages: {
            pattern: Messages[locale].CORRECT_EMAIL,
            required: Messages[locale].FIELD_REQUIRED,
            email: Messages[locale].CORRECT_EMAIL,
        }
    });
    $('#payment_user_name').rules("add", {
        pattern:/^\D+$/,
        minlength: 2,
        maxlength: 32,
        messages: {
            pattern: Messages[locale].CORRECT_NAME,
            minlength: jQuery.validator.format(Messages[locale].CORRECT_MIN),
            maxLength: jQuery.validator.format(Messages[locale].CORRECT_MAX),
            required: Messages[locale].FIELD_REQUIRED,
        }
    });
    $('#payment_user_surname').rules("add", {
        pattern:/^\D+$/,
        minlength: 2,
        maxlength: 32,
        messages: {
            pattern: Messages[locale].CORRECT_SURNAME,
            minlength: jQuery.validator.format(Messages[locale].CORRECT_MIN),
            maxLength: jQuery.validator.format(Messages[locale].CORRECT_MAX),
            required: Messages[locale].FIELD_REQUIRED,
        }
    });

    $('#user_promo_code').rules("add", {
        minlength: 2,
        messages: {
            minlength: jQuery.validator.format(Messages[locale].CORRECT_MIN),
            required: Messages[locale].FIELD_REQUIRED,
        }
    });

    $('.speaker-card__top').on('click', function () {
        var e_slug = $(this).data('event');
        var s_slug = $(this).data('speaker');
        setSpeakerHtml(e_slug, s_slug);
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
        if (detectmob()) {
            window.location.pathname = devpath+"/payment/"+e_slug;
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
                        $('#pay-form').html(data.html);
                        $('#payment-sums').html(data.paymentSums);
                        $('#cancel-promo-code').click();
                    } else {
                        var validator = $('#payment').validate();
                        errors = { user_promo_code: Messages[locale].PROMO_NOT_VALID };
                        validator.showErrors(errors);
                        console.log('Error:' + data.error);
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
                        $('#pay-form').html(data.html);
                        $('#payment-sums').html(data.paymentSums);
                        $('#cancel-add-user').click();
                    } else {
                        var validator = $('#payment').validate();
                        errors = { payment_user_name: data.error };
                        validator.showErrors(errors);
                        console.log('Error:' + data.error);
                    }
                });
        }
    });
    $('#buy-ticket-btn').on('click', function () {
        if ($('#user_phone').valid()) {
            $.post(Routing.generate('update_user_phone', {phoneNumber: $('#user_phone').val()}), function (data) {
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
});

