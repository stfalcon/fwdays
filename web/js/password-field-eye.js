$(document).ready(function () {
    $('span.eye').on('click', function(e){
        var id = $(this).siblings('input').first().attr('id');

        if ($('#' + id).get(0).type == 'text') {
            $('#' + id).get(0).type = 'password';
            this.title = 'Показать пароль';
        } else {
            $('#' + id).get(0).type = 'text';
            this.title = 'Спрятать пароль';
        }
    });
});
