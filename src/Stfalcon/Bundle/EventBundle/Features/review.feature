# language: ru

Функционал: Тест контроллера ReviewController
    Тестируем просмотр review

    Сценарий: Открыть подстранице события и убедиться в ее существовании
        Допустим я на странице "/event/zend-framework-day-2011/review/reviewSlug"
        Тогда код ответа сервера должен быть 200
        И я должен видеть "Review title" внутри элемента "#content h2"
        И я должен видеть "Review text" внутри элемента ".presentation"