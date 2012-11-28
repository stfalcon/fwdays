# language: ru
Функционал: Проверяем данные пользователя в профиле

    Сценарий: Зайти на страницу логина и войти в свою учетную запись
        Допустим я на странице "/login"
        Когда я вхожу в учетную запись с именем "user@fwdays.com" и паролем "qwerty"
        Тогда I am on homepage
        И я должен видеть меню для пользователя "user@fwdays.com"

    Сценарий: Зайти в учетную запись, в которой заполнены только обязательные поля, проверить информацию в них на странице профиля,
        Допустим я на странице "/login"
        И я вхожу в учетную запись с именем "user@fwdays.com" и паролем "qwerty"
        Когда я перехожу на "/profile/edit"
        Тогда поле "fos_user_profile_form_email" должно содержать "user@fwdays.com"
        И поле "fos_user_profile_form_fullname" должно содержать "Michael Jordan"
        И поле "fos_user_profile_form_country" должно содержать "USA"
        И поле "fos_user_profile_form_city" должно содержать "Boston"
        И поле "fos_user_profile_form_company" должно содержать "NBA"
        И поле "fos_user_profile_form_post" должно содержать "Point Guard"
