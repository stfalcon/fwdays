function addValidator(form) {
    form.validate({
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
};

$(document).ready(function () {
    $('.payer-form').validate({
        debug: false,
        errorClass: "text-error",
        errorElement: "p",
        onkeyup: false,
        highlight: function (element) {
            $(element).addClass('input--error');
        },
        unhighlight: function (element) {
            $(element).removeClass('input--error');
        }
    });

    $('#payment-form').validate({
        debug: false,
        errorClass: "text-error",
        errorElement: "p",
        onkeyup: false,
        highlight: function (element) {
            $(element).addClass('input--error');
        },
        unhighlight: function (element) {
            $(element).removeClass('input--error');
        }
    });

    $.validator.addClassRules({
        'valid-name': {
            required: true,
            pattern: XRegExp("^[\\pL\-\s']+$"),
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
            pattern: /\+[1-9][0-9]{10,14}$/i,
        },
        'valid-promo_code' : {
            required: false,
            minlength: 2,
            pattern: XRegExp("[\\pL\-\s0-9]+$"),
        }
    });

    $('.user_promo_code').rules("add", {
        minlength: 2,
        messages: {
            minlength: $.validator.format(Messages[locale].CORRECT_MIN),
            required: Messages[locale].FIELD_REQUIRED,
        }
    });
});