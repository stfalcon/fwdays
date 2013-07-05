# language: ru

Функционал: Тест контроллера MailAdminController
Тестируем рассылки

    Сценарий: Проверяем создание рассылки без параметров
        Допустим я вхожу в учетную запись с именем "admin@fwdays.com" и паролем "qwerty"
        И я перехожу на "/admin/bundle/event/mail/list"
        Тогда код ответа сервера должен быть 200
        Если я кликаю по ссылке "Добавить новый"
        Тогда код ответа сервера должен быть 200
        И я на странице "admin/bundle/event/mail/create"
        И я заполняю поле "Title *" значением "Тестовый заголовок"
        И я заполняю поле "Text *" значением "Текст сообщение"
        Если я нажимаю "btn_create_and_edit"
        Тогда код ответа сервера должен быть 200
        Тогда я должен видеть "Элемент создан успешно"
        Если я кликаю по ссылке "Line items"
        Тогда код ответа сервера должен быть 200
        И я должен видеть "Jack Sparrow" внутри элемента ".table.table-bordered.table-striped"
        И я должен видеть "Michael Jordan" внутри элемента ".table.table-bordered.table-striped"

    Сценарий: Проверяем создание рассылки по евенту и статусу оплаты для несуществующих данных
        Допустим я вхожу в учетную запись с именем "admin@fwdays.com" и паролем "qwerty"
        И я перехожу на "/admin/bundle/event/mail/list"
        Тогда код ответа сервера должен быть 200
        Если я кликаю по ссылке "Добавить новый"
        Тогда код ответа сервера должен быть 200
        И я на странице "admin/bundle/event/mail/create"
        И я заполняю поле "Title *" значением "Тестовый заголовок"
        И я заполняю поле "Text *" значением "Текст сообщение"
        И я выбираю "PHP Frameworks Day" в поле "Event"
        И я выбираю "Оплачено" в поле "Payment Status"
        Если я нажимаю "btn_create_and_edit"
        Тогда код ответа сервера должен быть 200
        Тогда я должен видеть "Элемент создан успешно"
        Если я кликаю по ссылке "Line items"
        Тогда код ответа сервера должен быть 200
        И я не должен видеть элемент ".table.table-bordered.table-striped"

    Сценарий: Проверяем создание рассылки по евенту и статусу оплаты для существующих данных
        Допустим я вхожу в учетную запись с именем "admin@fwdays.com" и паролем "qwerty"
        И я перехожу на "/admin/bundle/event/mail/list"
        Тогда код ответа сервера должен быть 200
        Если я кликаю по ссылке "Добавить новый"
        Тогда код ответа сервера должен быть 200
        И я на странице "admin/bundle/event/mail/create"
        И я заполняю поле "Title *" значением "Тестовый заголовок"
        И я заполняю поле "Text *" значением "Текст сообщение"
        И я выбираю "PHP Frameworks Day" в поле "Event"
        И я выбираю "Не оплачено" в поле "Payment Status"
        Если я нажимаю "btn_create_and_edit"
        Тогда код ответа сервера должен быть 200
        Тогда я должен видеть "Элемент создан успешно"
        Если я кликаю по ссылке "Line items"
        Тогда код ответа сервера должен быть 200
        И я не должен видеть "Jack Sparrow" внутри элемента ".table.table-bordered.table-striped"
        И я должен видеть "Michael Jordan" внутри элемента ".table.table-bordered.table-striped"

    Сценарий: Отправка e-mail для пользователей
        Допустим я вхожу в учетную запись с именем "admin@fwdays.com" и паролем "qwerty"
        И я перехожу на "/admin/bundle/event/mail/list"
        Тогда код ответа сервера должен быть 200
        Если я кликаю по ссылке "Добавить новый"
        Тогда код ответа сервера должен быть 200
        И я на странице "admin/bundle/event/mail/create"
        И я заполняю поле "Title *" значением "Тестовый заголовок"
        И я заполняю поле "Text *" значением "Текст сообщение"
        И я ставлю галочку "Start"
        Если я нажимаю "btn_create_and_edit"
        Тогда код ответа сервера должен быть 200
        Тогда я должен видеть "Элемент создан успешно"
        Если я кликаю по ссылке "Line items"
        Тогда код ответа сервера должен быть 200
        И я должен видеть "Jack Sparrow" внутри элемента ".table.table-bordered.table-striped"
        И я должен видеть "Michael Jordan" внутри элемента ".table.table-bordered.table-striped"
        И я перехожу на "/admin/bundle/event/mail/user-send"
        Тогда email with subject "Тестовый заголовок" should have been sent to "admin@fwdays.com"
        Тогда email with subject "Тестовый заголовок" should have been sent to "user@fwdays.com"

    Сценарий: Отправка e-mail для админа
        Допустим я вхожу в учетную запись с именем "admin@fwdays.com" и паролем "qwerty"
        И я перехожу на "/admin/bundle/event/mail/list"
        Тогда код ответа сервера должен быть 200
        И I do not follow redirects
        Если я кликаю по ссылке "Отправить Админам"
        Тогда email with subject "test" should have been sent to "admin@fwdays.com"
        Тогда email with subject "test" should have not been sent to "user@fwdays.com"
