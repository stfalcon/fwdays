$(document).ready(function () {
    $('span.eye').on('click', function(e){
        var id = $(this).siblings('input').first().attr('id');

        if ($('#' + id).get(0).type == 'text') {
            $('#' + id).get(0).type = 'password';
        } else {
            $('#' + id).get(0).type = 'text';
        }
    });
});
