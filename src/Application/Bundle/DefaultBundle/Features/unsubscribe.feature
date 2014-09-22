# language: ru

Функционал: Тест контроллера UnsubscribeController
Тестируем отписку от рассылки

    Сценарий: Проверяем отписку пользователя от рассылки
        Допустим пользователь "peter.parker@fwdays.com" подписан на рассылку
        Тогда пользователь "peter.parker@fwdays.com" перешел на ссылку отписаться от рассылки
        И пользователь "peter.parker@fwdays.com" должен быть отписан от рассылки