$(document).ready(function () {
    // Set event "onClick" for image "eye" near the password input on registration form
    // On click the 'type' option of input changes from 'text' to 'password' and vice versa
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
