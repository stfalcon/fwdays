# language: ru

Функционал: Тест контроллера InterkassaController

    Сценарий: Проверяем не созданный платеж
        Допустим я на странице "/payment/interaction?ik_pm_no=100"
        И я должен видеть "Платеж №100 не найден!"

    Сценарий: Проверяем оплату уже оплаченого платежа
        Допустим я на странице "/payment/interaction?ik_pm_no=1"
        Тогда код ответа сервера должен быть 400
        И я должен видеть "FAIL"

    Сценарий: Тестируем успешно оплаченый платеж
        И Interkassa API is available
        И я перехожу на страницу обработки платежа "2"
        Тогда email with subject "PHP Frameworks Day" should have been sent to "user@fwdays.com"
        Тогда платеж "2" должен быть помечен как оплачен

