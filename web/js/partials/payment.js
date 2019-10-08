var buyTicketButton = $('#buy-ticket-btn');
var addTicketBtn = $('#add-user-form');
var userBonusInput = $('#user-bonus-input');
var applyBonusBtn = $('#btn-apply-bonus');
var paymentList = $('#payment-list');

function editTicketRow(index, ticketBlock, ticket) {
    if (index) {
        ticketBlock.find('.payer__number').html(index);
    }
    ticketBlock.find('.user-payment__name').html(ticket.user.name+' '+ticket.user.surname);
    ticketBlock.find('.label-hidden-user-name').html(ticket.user.name);
    ticketBlock.find('.label-hidden-user-surname').html(ticket.user.surname);
    ticketBlock.find('.user-payment__email').html(ticket.user.email);
    if (ticket.has_discount) {
        ticketBlock.find('.user-payment__price').html('<span class="user-payment__price-strike" style="display: none">'+ticket.amount_without_discount+'</span>'+ticket.amount);
        ticketBlock.find('.user-payment__price-strike').show();
        ticketBlock.find('.user-payment__discount').html(ticket.discount_description).show();
        if (ticket.promo_code) {
            ticketBlock.find('.label-hidden-user-promocode').html(ticket.promo_code.code);
        } else {
            ticketBlock.find('.label-hidden-user-promocode').html('');
        }
    } else {
        ticketBlock.find('.user-payment__price').html(ticket.amount);
        ticketBlock.find('.user-payment__discount').hide();
        ticketBlock.find('.label-hidden-user-promocode').html('');
    }
    ticketBlock.attr('id', ticket.id);
}

function addTicketRowBlock(index, ticket, replaceId) {
    var newTicketRow = $('#payer-block').clone();
    editTicketRow(index, newTicketRow, ticket);
    newTicketRow.show();
    if (replaceId) {
        $('#'+replaceId).replaceWith(newTicketRow[0].outerHTML);
    } else {
        newTicketRow.appendTo('#payment-list');
    }
}

function editPaymentBlock(paymentData) {
    var elem = $('#payment-total-data');

    if (paymentData.fwdays_amount > 0) {
        elem.find('.payment-cart__text--strike').html(paymentData.base_amount).show();
        elem.find('.payment-cart__hint').html(paymentData.fwdays_amount_formatted+'<i class="icon-close-sm delete-fwdays-bonus"></i>').show();
    } else {
        elem.find('.payment-cart__hint').hide();
        elem.find('.payment-cart__text--strike').hide();
    }

    if (paymentData.fwdays_amount === 0 && paymentData.user.balance > 0) {
        userBonusInput.val(paymentData.user.balance).attr('max', paymentData.user.balance);
        elem.find('.payment-use-fwdays-bonus').show();
        elem.find('.payment-user-bonus-label').show();
        elem.find('.payment-user-bonus-tooltip').show();
    } else {
        elem.find('.payment-use-fwdays-bonus').hide();
        elem.find('.payment-user-bonus-label').hide();
        elem.find('.payment-user-bonus-tooltip').hide();
    }

    elem.find('.payment-cart__amount').html(paymentData.amount_formatted);

    if (!elem.is(":visible")) {
        elem.show();
        addTicketBtn.show();
    }

    if (paymentData.ticket_count === 0) {
        elem.hide();
        addTicketBtn.hide();
    }
}

function reloadingPage($this) {
    if ($this.hasClass('reload_page')) {
        window.location.reload();
        return true;
    }
    return false;
}

function applyFwdaysBonus(amount) {
    $.post(Routing.generate('payment_apply_fwdays_bonus',
        {
            'amount': amount
        }),
        function (data) {
            if (data.result) {
                saved_payment_amount = data.payment_data.amount;
                editPaymentBlock(data.payment_data);
            } else {
                console.log('Error:'+data.error);
            }
        }).always(function() {
            applyBonusBtn.removeClass('disabled');
        });
}

applyBonusBtn.click(function () {
    var amount = userBonusInput.val();
    applyBonusBtn.addClass('disabled');
    applyFwdaysBonus(amount);
});

userBonusInput.change(function (event) {
    var max = $(this).attr('max');
    if ($(this).val() > max) {
        $(this).val(max);
    }
    if ($(this).val() < 0) {
        $(this).val(0);
    }
});

$(document).on('click', '.delete-fwdays-bonus', function () {
    applyFwdaysBonus(0);
});

$(document).on('click', '.ticket-delete-btn', function () {
    var elem = $(this).closest('.payer');
    var e_slug = paymentList.data('event');
    $.post(Routing.generate('remove_ticket_from_payment',
        {
            eventSlug: e_slug,
            id: elem.attr('id')
        }),
        function (data) {
            if (data.result) {
                saved_payment_amount = data.payment_data.amount;
                elem.remove();
                editPaymentBlock(data.payment_data);
                refreshDeleteButtons();
                recalculateTicketsCount();
            } else {
                console.log('Error:'+data.error);
            }
        });
});

$(document).on('click', '.ticket-edit-btn', function () {
    var ticketRow = $(this).closest( ".payer" );
    var editTicketBlock = $('#payer-block-edit').clone();
    var number = ticketRow.find('.payer__number').html();
    editTicketBlock.attr('id', 'payer-block-edit-'+number);
    editTicketBlock.show();
    editTicketBlock.insertAfter(ticketRow);
    ticketRow.hide();
    editTicketBlock = $('#'+editTicketBlock.attr('id'));
    copyDataFromRowToBlock(ticketRow, editTicketBlock);
    addValidator(editTicketBlock);
    buyTicketButton.prop("disabled", true);
});

function copyDataFromRowToBlock(ticketRow, editTicketBlock) {
    var user_name = ticketRow.find('.label-hidden-user-name').html();
    var user_sur_name = ticketRow.find('.label-hidden-user-surname').html();
    var user_email = ticketRow.find('.user-payment__email').html();
    var promo_code = ticketRow.find('.label-hidden-user-promocode').html();

    editTicketBlock.attr('data-ticket-id', ticketRow.attr('id'));
    editTicketBlock.find('.payer__number').html(ticketRow.find('.payer__number').html());
    editTicketBlock.find('.payment_user_name').val(user_name).attr('data-old-value', user_name);
    editTicketBlock.find('.payment_user_surname').val(user_sur_name).attr('data-old-value', user_sur_name);
    editTicketBlock.find('.payment_user_email').val(user_email).attr('data-old-value', user_email);
    editTicketBlock.find('.user_promo_code').val(promo_code).attr('data-old-value', promo_code);
    editTicketBlock.find('.add-user-btn').removeClass('add-user-btn').addClass('edit-user-btn');
}

addTicketBtn.on('click', function () {
    var newTicketBlock = $('#payer-block-edit').clone();
    ++ticket_count;
    newTicketBlock.attr('id', 'payer-block-edit-'+ticket_count);
    newTicketBlock.find('.payer__number').html(ticket_count);
    newTicketBlock.appendTo('#payment-list').show();
    addValidator(newTicketBlock);
    buyTicketButton.prop("disabled", true);
});

function refreshBuyTicketBtn() {
    var forms = $('.payer-form');
    buyTicketButton.prop("disabled", forms.length > 1);
}

function recalculateTicketsCount() {
    var numbers = $('.payer__number');
    $.each(numbers, function( index, value ) {
        $(value).html(index-1);
    });
    ticket_count = numbers.length-2;
}

function refreshDeleteButtons() {
    var buttons = $('.ticket-delete-btn');
    if (buttons.length > 2) {
        $.each(buttons, function (index, value) {
            $(value).show();
        });
    } else {
        $.each(buttons, function (index, value) {
            $(value).hide();
        });
    }
}

$(document).ready(function () {
   var $firstEditForm = $('#payer-block-edit-1');
   if ($firstEditForm.length) {
       addValidator($firstEditForm);
   }
});

$(document).on('click', '.add-user-btn', function () {
    var $this = $(this);
    var ticketBlock = $this.closest('.payer-form');
    var input_name = ticketBlock.find('.payment_user_name');
    var input_surname = ticketBlock.find('.payment_user_surname');
    var input_email = ticketBlock.find('.payment_user_email');
    var input_promocode = ticketBlock.find('.user_promo_code');

    if (input_name.valid() &&
        input_surname.valid() &&
        input_email.valid() &&
        input_promocode.valid()) {

        var e_slug = paymentList.data('event');
        $.ajax({
            url: Routing.generate('add_ticket_participant', {eventSlug: e_slug}),
            method: 'POST',
            data: {name: input_name.val(), surname: input_surname.val(), email: input_email.val(), promocode: input_promocode.val()},
            success: function (data) {
                if (data.result && !reloadingPage($this)) {
                    saved_payment_amount = data.payment_data.amount;
                    addTicketRowBlock(
                        data.payment_data.ticket_count,
                        data.ticket_data,
                        ticketBlock.attr('id')
                    );
                    editPaymentBlock(data.payment_data);
                    refreshDeleteButtons();
                    recalculateTicketsCount();
                } else {
                    var validator = ticketBlock.validate();
                    validator.showErrors(data.error );
                }
            }
        });
        refreshBuyTicketBtn();
    }
});

$(document).on('click', '.edit-user-btn', function () {
    var $this = $(this);
    var ticketBlock = $this.closest('.payer-form');
    var input_name = ticketBlock.find('.payment_user_name');
    var input_surname = ticketBlock.find('.payment_user_surname');
    var input_email = ticketBlock.find('.payment_user_email');
    var input_promocode = ticketBlock.find('.user_promo_code');
    var ticket_id = ticketBlock.data('ticket-id');
    var parent_row = $('#'+ticket_id);

    if (input_name.val() === input_name.data('old-value') &&
        input_surname.val() === input_surname.data('old-value') &&
        input_email.val() === input_email.data('old-value') &&
        input_promocode.val() === input_promocode.data('old-value')
    ) {
        parent_row.show();
        ticketBlock.remove();
        refreshBuyTicketBtn();
        return;
    }

    if (input_name.valid() &&
        input_surname.valid() &&
        input_email.valid() &&
        input_promocode.valid()) {

        var e_slug = paymentList.data('event');
        $.ajax({
            url: Routing.generate('edit_ticket_participant', {eventSlug: e_slug, id: ticket_id}),
            method: 'POST',
            data: {name: input_name.val(), surname: input_surname.val(), email: input_email.val(), promocode: input_promocode.val()},
            success: function (data) {
                if (data.result && !reloadingPage($this)) {
                    saved_payment_amount = data.payment_data.amount;
                    editTicketRow(null, parent_row, data.ticket_data);
                    editPaymentBlock(data.payment_data);
                    parent_row.show();
                    ticketBlock.remove();
                    refreshDeleteButtons();
                    recalculateTicketsCount();
                } else {
                    var validator = ticketBlock.validate();
                    validator.showErrors(data.error);
                }
            }
        });
        refreshBuyTicketBtn();
    }
});

buyTicketButton.on('click', function (e) {
    e.preventDefault();
    var submit_btn = $(this);
    submit_btn.prop("disabled", true);
    var user_phone_elem = $('#user_phone');
    var use_phone = user_phone_elem.val();

    if (!$('#payment-form').valid()) {
        submit_btn.prop("disabled", false);
        return;
    }

    if (user_phone_elem.data('old-phone') !== use_phone) {
        $.post(Routing.generate('update_user_phone', {phoneNumber: use_phone}), function (data) {});
    }

    var e_slug = paymentList.data('event');

    $.ajax({
        url: Routing.generate('event_paying', {slug: e_slug}),
        method: 'POST',
        data: {'saved_data': saved_payment_amount},
        success: function (data) {
            submit_btn.prop("disabled", false);
            if (data.result) {
                if (data.amount_changed) {
                    saved_payment_amount = data.payment_data.amount;
                    paymentList.empty();
                    $.each(data.payment_data.tickets, function( index, value ) {
                        addTicketRowBlock(index+1, value);
                    });
                    editPaymentBlock(data.payment_data);

                    if (data.payment_data.ticket_count === 0) {
                        ticket_count = 0;
                        addTicketBtn.click();
                    }

                    alert(data.payment_data.amount_changed_text);
                } else {
                    var $form = $(data.form);
                    paymentList.after($form);
                    $form.submit();
                }
            } else {
                alert(data.error);
                console.log(data.error);
            }
        },
        error: function () {
            submit_btn.prop("disabled", false);
            console.log('error');
        },
        always: function () {
            submit_btn.prop("disabled", false);
        }
    })
});