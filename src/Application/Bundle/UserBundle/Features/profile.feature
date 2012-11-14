# language: ru
Функционал: Проверяем регистрацию и данные пользователя в профиле

Сценарий: Открыть страницу регистрации, убедиться в ее существовании, заполнить все поля, и получить успешное сообщение
    Допустим я на странице "/register/"
    Тогда код ответа сервера должен быть 200
    И я заполняю поле "fos_user_registration_form_fullname" значением "Jack Smith"
    И я заполняю поле "fos_user_registration_form_email" значением "test@fwdays.com"
    И я заполняю поле "fos_user_registration_form_plainPassword" значением "qwerty"
    И я заполняю поле "fos_user_registration_form_company" значением "Stfalcon"
    И я заполняю поле "fos_user_registration_form_city" значением "Kiev"
    И я заполняю поле "fos_user_registration_form_post" значением "developer"
    И я заполняю поле "fos_user_registration_form_country" значением "Ukraine"
    И я нажимаю "Регистрация"
    Тогда код ответа сервера должен быть 200
    И я должен быть на странице "/register/check-email"
    И обязательные поля должны быть заполнены у "test@fwdays.com"
    И не обязательные поля должны быть заполнены у "test@fwdays.com"
    И я активирую свой "test@fwdays.com" профиль
#Открыть страницу логина, убедиться в ее существовании, войти в свою учетную запись
    Допустим я на странице "/login"
    Тогда код ответа сервера должен быть 200
    И я заполняю поле "username" значением "test@fwdays.com"
    И я заполняю поле "password" значением "qwerty"
    И я нажимаю "Войти"
    Тогда код ответа сервера должен быть 200
#Открыть страницу профиля, убедиться в ее существовании, проверить все поля профиля
    Допустим я на странице "/profile/edit"
    Тогда код ответа сервера должен быть 200
    И я должен видеть "Электронная почта:"
    И поле "fos_user_profile_form_email" должно содержать "test@fwdays.com"
    И поле "fos_user_profile_form_fullname" должно содержать "Jack Smith"
    И поле "fos_user_profile_form_company" должно содержать "Stfalcon"
    И поле "fos_user_profile_form_post" должно содержать "developer"
