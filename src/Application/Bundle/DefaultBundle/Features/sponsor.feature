# language: ru

Функционал: Тест контроллера SponsorController
    Тестируем список спонсоров

    Сценарий: Открыть главную страницу и проверить список главных спонсоров для активных событий
        Допустим я на странице "/"
        Тогда код ответа сервера должен быть 200
        И я должен видеть картинку "/uploads/sponsors/odesk.jpg" внутри элемента ".partners img"
        И я должен видеть картинку "/uploads/sponsors/magento.png" внутри элемента ".partners img"
        Но я не должен видеть картинку "/uploads/sponsors/epochta.png" внутри элемента ".partners img"

    Сценарий: Открыть страницу события PHP Frameworks Day и убедиться в наличии баннеров прикрепленных спонсоров, а также в отсутствии баннеров всех остальных спонсоров
        Допустим я на странице "/event/php-frameworks-day-2012"
        Тогда код ответа сервера должен быть 200
        И я должен видеть "Golden sponsor" внутри элемента ".partners h2.sort-order-1"
        И я должен видеть картинку "/uploads/sponsors/odesk.jpg" внутри элемента "div.sort-order-1 img"
        И я должен видеть "Silver sponsor" внутри элемента ".partners h2.sort-order-2"
        И я должен видеть картинку "/uploads/sponsors/epochta.png" внутри элемента "div.sort-order-1 img"
        И я должен видеть элемент ".become-partner"
        Но я не должен видеть картинку "/uploads/sponsors/magento.png" внутри элемента ".partners"

    Сценарий: Открыть страницу события Zend Framework Day и убедиться в наличии баннеров прикрепленных спонсоров, а также в отсутствии баннеров всех остальных спонсоров
        Допустим я на странице "/event/zend-framework-day-2011"
        Тогда код ответа сервера должен быть 200
        И я должен видеть "Golden sponsor" внутри элемента ".partners h2"
        И я должен видеть картинку "/uploads/sponsors/magento.png" внутри элемента "div.sort-order-1 img"
        И я должен видеть элемент ".become-partner"
        Но я не должен видеть картинку "/uploads/sponsors/epochta.png" внутри элемента ".partners"
        Но я не должен видеть картинку "/uploads/sponsors/odesk.jpg" внутри элемента ".partners"

    Сценарий: Открыть страницу неактивного события и убедиться в отсутствии спонсоров
        Допустим я на странице "/event/not-active-frameworks-day"
        Тогда код ответа сервера должен быть 200
        И я не должен видеть элемент ".partners h2"
        И я не должен видеть элемент "div.sort-order-1"
        И я должен видеть "Здесь может быть ваш логотип" внутри элемента ".become-partner"

        Сценарий: Открыть страницу Партнеров и убедиться в существовании партнеров
        Допустим я на странице "/partners"
        Тогда код ответа сервера должен быть 200
        И я должен видеть картинку "/uploads/sponsors/odesk.jpg" внутри элемента ".sp-logo img"
        И я должен видеть картинку "/uploads/sponsors/magento.png" внутри элемента ".sp-logo img"
        И я должен видеть "oDesk" внутри элемента ".sp-info-h"
        И я должен видеть "oDesk is a global marketplace" внутри элемента ".sp-info"
        И я должен видеть 2 элемента ".sp-logo"
        И я должен видеть 2 элемента ".sp-info"