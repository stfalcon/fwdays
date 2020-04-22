$(document).ready(function () {
    $('#filter_email_value').change(function() {
        var txt = $(this).val();
        txt = txt.replace(/[^0-9a-zA-Z@.\-_]/gi, '');
        $(this).val(txt);
    });
});
