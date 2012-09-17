# language: ru

Функционал: Тест контроллера EventController
    Тестируем список событий, просмотр события принятия участия и просмотр моих событий, оплата события

    Сценарий: Открыть страницу событий и убедиться в ее существовании и выводе всех событий
        Допустим я на странице "/events"
        Тогда код ответа сервера должен быть 200
        ### события
        И я должен видеть "отель \"Казацкий\"" внутри элемента ".conferences-list"
        И я должен видеть "Zend Framework Day посвящен популярному PHP фреймворку Zend Framework" внутри элемента ".conferences-list"
        И я должен видеть "Пока неизвестно" внутри элемента ".conferences-list"
        И я должен видеть "PHP frameworks day это конференция по современным PHP фреймворкам (Zend Framework 2, Symfony 2, Silex, Lithium и др.)" внутри элемента ".conferences-list"

    Сценарий: Перейти на конкретное событие
        Допустим я на странице "/events"
        И кликаю по ссылке "Детальная информация о Zend Framework Day"
        Тогда код ответа сервера должен быть 200
        И я должен быть на странице "/event/zend-framework-day-2011"
        И я должен видеть "19 апреля 2012, Киев отель \"Казацкий\"" внутри элемента "header .event-head-text"
        И я должен видеть "Описание события" внутри элемента "article.about-event"

    Сценарий: Проверить в списке докладов докладчика только те доклады, которые назначены на конкретное событие
        Допустим я на странице "/event/php-frameworks-day-2012/speakers"
        Тогда код ответа сервера должен быть 200
#        проверяем актуальные доклады Андрея Шкодяка для PHP frameworks day
        И я должен видеть "Андрей Шкодяк" внутри элемента ".presenters"
        И я должен видеть "Symfony Forever" внутри элемента ".presenters"
        И I should not see "Simple API via Zend Framework" in the ".presenters" element
#        проверяем актуальные доклады Валерия Рабиевского для PHP frameworks day
        И я должен видеть "Валерий Рабиевский" внутри элемента ".presenters"
        И я должен видеть "Symfony 2.1 first steps" внутри элемента ".presenters"
        И I should not see "ZF first steps" in the ".presenters" element
#        на другой странице все должно быть наоборт, т.е. должны видеть другие доклады и не видить первые
        Допустим я на странице "/event/zend-framework-day-2011/speakers"
        Тогда код ответа сервера должен быть 200
#        проверяем актуальные доклады Андрея Шкодяка для Zend Framework Day
        И я должен видеть "Андрей Шкодяк" внутри элемента ".presenters"
        И я должен видеть "Simple API via Zend Framework" внутри элемента ".presenters"
        И I should not see "Symfony Forever" in the ".presenters" element
#        проверяем актуальные доклады Валерия Рабиевского для Zend Framework Day
        И я должен видеть "Валерий Рабиевский" внутри элемента ".presenters"
        И я должен видеть "ZF first steps" внутри элемента ".presenters"
        И I should not see "Symfony 2.1 first steps" in the ".presenters" element
