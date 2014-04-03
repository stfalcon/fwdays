$(document).ready(function (){
    var $participantForm = $('form.participants-form');
    var $collectionHolder = $participantForm.find('#stfalcon_event_ticket_participants');
    var $participantFormWrapper = $participantForm.find('.wrap');
    var $addTagLink = $participantForm.find('a.add-new-participant');
    var $payOtherParticipants = $('.pay-for-other-participants a');

    $payOtherParticipants.on('click', function(e) {
        e.preventDefault();
        addParticipantForm($collectionHolder);
        $participantFormWrapper.show();

        $(this).hide();
    });

    $(document).on('click', '.remove-participant-form', function(e) {
        e.preventDefault();
        $(this).parents('.participant-form').remove();
        if (!$collectionHolder.find('.participant-form').length) {
            $participantFormWrapper.hide();
            $payOtherParticipants.show();
            $collectionHolder.data('index', 0);
        }
    });
    $collectionHolder.data('index', 0);

    $addTagLink.on('click', function(e) {
        e.preventDefault();
        addParticipantForm($collectionHolder);
    });

    function addParticipantForm($collectionHolder) {
        var prototype = $collectionHolder.data('prototype');
        var index = $collectionHolder.data('index');
        prototype = prototype.replace(/__name__label__/g, '');
        var newForm = prototype.replace(/__name__/g, index);
        $collectionHolder.data('index', index + 1);
        $collectionHolder.append(newForm);
    }
})