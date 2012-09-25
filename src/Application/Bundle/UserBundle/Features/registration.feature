# language: ru
Функционал: Регистрируем пользователя, проверяем автоподписку на активные ивенты

Сценарий: Открыть страницу регистрации, убедиться в ее существовании, заполнить форму и получить успешное сообщение
    Допустим я на странице "/register/"
    Тогда код ответа сервера должен быть 200
    И я заполняю поле "fos_user_registration_form_fullname" значением "Jack Smith"
    И я заполняю поле "fos_user_registration_form_email" значением "test@fwdays.com"
    И я заполняю поле "fos_user_registration_form_plainPassword" значением "qwerty"
    И я нажимаю "Регистрация"
    Тогда код ответа сервера должен быть 200
    И я должен быть на странице "/register/check-email"
    И у меня должна быть подписка на все активные ивенты
