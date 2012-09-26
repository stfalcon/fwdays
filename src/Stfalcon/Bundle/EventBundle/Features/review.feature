# language: ru

Функционал: Тест контроллера ReviewController
    Тестируем просмотр review

    Сценарий: Открыть подстранице события и убедиться в ее существовании
        Допустим я на странице "/event/zend-framework-day-2011/review/simple-api-via-zend-framework"
        Тогда код ответа сервера должен быть 200
        И я должен видеть "Simple API via Zend Framework" внутри элемента "#content h2"
        И я должен видеть "How to do simple API via Zend Framework" внутри элемента ".presentation"
