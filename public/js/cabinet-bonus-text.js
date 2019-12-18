$(document).ready(function () {
    $('#ref-dropdown').on('change', function () {
        var bonus = $('option:selected', this).data('bonus');
        var bonus_text = bonus_translate.replace('%referal_bonus%', bonus);
        $('#bonus_text').text(bonus_text);
    });
})