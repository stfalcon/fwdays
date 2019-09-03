function editTicketRow(index, ticketBlock, ticket, canBeDelete) {
    if (index) {
        ticketBlock.find('.payer__number').html(index);
    }
    ticketBlock.find('.user-payment__name').attr('data-name', ticket.user.name).attr('data-surname', ticket.user.surname).html(ticket.user.name+' '+ticket.user.surname);
    ticketBlock.find('.user-payment__email').html(ticket.user.email);
    if (ticket.has_discount) {
        ticketBlock.find('.user-payment__price').html('<span class="user-payment__price-strike" style="display: none">'+ticket.amount_without_discount+'</span>'+ticket.amount);
        ticketBlock.find('.user-payment__price-strike').show();
        ticketBlock.find('.user-payment__discount').html(ticket.discount_description).show();
        if (ticket.promo_code) {
            ticketBlock.attr('data-promocode', ticket.promo_code.code);
        }
    } else {
        ticketBlock.find('.user-payment__price').html(ticket.amount);
    }
    ticketBlock.data('ticket-id', ticket.id);
    if (canBeDelete) {
        ticketBlock.find('.ticket-delete-btn').show();
    } else {
        ticketBlock.find('.ticket-delete-btn').hide();
    }
}

function addTicketRowBlock(index, ticket, canBeDelete, replaceId = null) {
    var newTicketRow = $('#payer-block').clone();
    newTicketRow.attr('id', ticket.id);
    editTicketRow(index, newTicketRow, ticket, canBeDelete);
    newTicketRow.show();
    if (replaceId) {
        $('#'+replaceId).replaceWith(newTicketRow[0].outerHTML);
        console.log('replace');
    } else {
        newTicketRow.appendTo('#payment-list');
        console.log('add');
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
        $('#user-bonus-input').val(paymentData.user.balance).attr('max', paymentData.user.balance);
        elem.find('.payment-use-fwdays-bonus').show();
        elem.find('.payment-user-bonus-label').show();
        elem.find('.payment-user-bonus-tooltip').show();
    } else {
        elem.find('.payment-use-fwdays-bonus').hide();
        elem.find('.payment-user-bonus-label').hide();
        elem.find('.payment-user-bonus-tooltip').hide();
    }

    elem.find('.payment-cart__amount').html(paymentData.amount);
}

function getPaymentData() {
    $.ajax({
        type: 'GET',
        url: Routing.generate('get_payment_data'),
        success: function(data) {
            if (data.result) {
                console.log(data.payment_data);
                $.each(data.payment_data.tickets, function( index, value ) {
                    addTicketRowBlock(index+1, value, data.payment_data.ticket_count > 1);
                });
                editPaymentBlock(data.payment_data);
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
}

function applyFwdaysBonus(amount) {
    $.post(Routing.generate('payment_apply_fwdays_bonus',
        {
            'amount': amount
        }),
        function (data) {
            if (data.result) {
                editPaymentBlock(data.payment_data);
            } else {
                console.log('Error:'+data.error);
            }
        }).always(function() {
            $('#btn-apply-bonus').removeClass('disabled');
        });
}

$('#btn-apply-bonus').click(function (event) {
    var amount = $('#user-bonus-input').val();
    $('#btn-apply-bonus').addClass('disabled');
    applyFwdaysBonus(amount);
});

$('#user-bonus-input').change(function (event) {
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
    var e_slug = $('#payment-list').data('event');
    $.post(Routing.generate('remove_ticket_from_payment',
        {
            eventSlug: e_slug,
            id: elem.attr('id')
        }),
        function (data) {
            if (data.result) {
                elem.remove();
                editPaymentBlock(data.payment_data);
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
});

function copyDataFromRowToBlock(ticketRow, editTicketBlock)
{
    var user_name = ticketRow.find('.user-payment__name').data('name');
    var user_sur_name = ticketRow.find('.user-payment__name').data('surname');
    var user_email = ticketRow.find('.user-payment__email').html();
    var promo_code = ticketRow.data('promocode');
    editTicketBlock.attr('data-ticket-id', ticketRow.attr('id'));
    editTicketBlock.find('.payer__number').html(ticketRow.find('.payer__number').html());
    editTicketBlock.find('.payment_user_name').val(user_name).attr('data-old-value', user_name);
    editTicketBlock.find('.payment_user_surname').val(user_sur_name).attr('data-old-value', user_sur_name);
    editTicketBlock.find('.payment_user_email').val(user_email).attr('data-old-value', user_email);
    editTicketBlock.find('.user_promo_code').val(promo_code).attr('data-old-value', promo_code);
    editTicketBlock.find('.add-user-btn').removeClass('add-user-btn').addClass('edit-user-btn');
}

$('#add-user-form').on('click', function () {
    var newTicketBlock = $('#payer-block-edit').clone();
    ++ticket_count;
    newTicketBlock.attr('id', 'payer-block-edit-'+ticket_count);
    newTicketBlock.find('.payer__number').html(ticket_count);
    newTicketBlock.appendTo('#payment-list').show();
});

function recalculateTicketsCount()
{
    var numbers = $('.payer__number');
    $.each(numbers, function( index, value ) {
        $(value).html(index-1);
    });
    ticket_count = numbers.length-2;
}

$(document).on('click', '.add-user-btn', function () {
    var ticketBlock = $(this).closest('.payer-form');
    var input_name = ticketBlock.find('.payment_user_name');
    var input_surname = ticketBlock.find('.payment_user_surname');
    var input_email = ticketBlock.find('.payment_user_email');
    if (input_name.valid() &&
        input_surname.valid() &&
        input_email.valid()) {
        var e_slug = $('#payment-list').data('event');
        $.post(Routing.generate('add_participant_to_payment',
            {
                eventSlug: e_slug,
                name: input_name.val(),
                surname: input_surname.val(),
                email: input_email.val()
            }),
            function (data) {
                if (data.result) {
                    addTicketRowBlock(
                        data.payment_data.ticket_count,
                        data.payment_data.tickets[data.payment_data.ticket_count-1],
                        data.payment_data.ticket_count > 1,
                        ticketBlock.attr('id')
                    );
                    editPaymentBlock(data.payment_data);
                    recalculateTicketsCount();
                } else {
                    var validator = ticketBlock.validate();
                    var errors = { "user_email": data.error };
                    validator.showErrors(errors);
                }
            });
    }
});

$(document).on('click', '.edit-user-btn', function () {
    var ticketBlock = $(this).closest('.payer-form');
    var input_name = ticketBlock.find('.payment_user_name');
    var input_surname = ticketBlock.find('.payment_user_surname');
    var input_email = ticketBlock.find('.payment_user_email');
    var ticket_id = ticketBlock.data('ticket-id');
    var parent_row = $('#'+ticket_id);

    if (input_name.val() === input_name.data('old-value') &&
        input_surname.val() === input_surname.data('old-value') &&
        input_email.val() === input_email.data('old-value')
    ) {
        parent_row.show();
        ticketBlock.remove();
        return;
    }

    if (input_name.valid() &&
        input_surname.valid() &&
        input_email.valid()) {

        var e_slug = $('#payment-list').data('event');
        $.post(Routing.generate('edit_ticket_participant',
            {
                eventSlug: e_slug,
                id: ticket_id,
                name: input_name.val(),
                surname: input_surname.val(),
                email: input_email.val()
            }),
            function (data) {
                if (data.result) {
                    editTicketRow(null, parent_row, data.ticket_data, data.payment_data.ticket_count > 1,);
                    editPaymentBlock(data.payment_data);
                    parent_row.show();
                    ticketBlock.remove();

                    recalculateTicketsCount();
                } else {
                    var validator = ticketBlock.validate();
                    var errors = { "user_email": data.error };
                    validator.showErrors(errors);
                }
            });
    }
});

$('#buy-ticket-btn').on('click', function () {
    var user_phone_elem = $('#user_phone');
    var use_phone = user_phone_elem.val();
    if (user_phone_elem.data('old-phone') === use_phone) {
        return;
    }
    if (use_phone !== '' && user_phone_elem.valid()) {
        $.post(Routing.generate('update_user_phone', {phoneNumber: use_phone}), function (data) {});
    }
});