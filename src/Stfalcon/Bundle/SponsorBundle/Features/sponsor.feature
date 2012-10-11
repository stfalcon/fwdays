# language: ru

Функционал: Тест контроллера SponsorController
    Тестируем список спонсоров

    Сценарий: Открыть страницу события Zend Framework Day и убедиться в наличии баннеров прикрепленных спонсоров, а также в отсутствии баннеров все остальных спонсоров
        Допустим я на странице "/event/zend-framework-day-2011"
        Тогда код ответа сервера должен быть 200
        И я должен видеть "Спонсоры" внутри элемента ".partners h2"
        И я должен видеть картинку с исходником "/bundles/stfalconsponsor/images/magento.png" внутри элемента "div.sort-order-1 img"
        И я должен видеть картинку с исходником "/bundles/stfalconsponsor/images/epochta.png" внутри элемента "div.sort-order-2 img"
        И я должен видеть картинку с исходником "/bundles/stfalconsponsor/images/symfonycamp.png" внутри элемента "div.sort-order-3 img"
        И я должен видеть элемент ".become-partner"
        И я не должен видеть картинку с исходником "/images/partners/smartme.png"

        Допустим я на странице "/event/php-frameworks-day-2012"
        Тогда код ответа сервера должен быть 200
        И я должен видеть "Спонсоры" внутри элемента ".partners h2"
        И я должен видеть картинку с исходником "/bundles/stfalconsponsor/images/smartme.png" внутри элемента "div.sort-order-1 img"
        И я должен видеть картинку с исходником "/bundles/stfalconsponsor/images/magento.png" внутри элемента "div.sort-order-2 img"
        И я должен видеть картинку с исходником "/bundles/stfalconsponsor/images/symfonycamp.png" внутри элемента "div.sort-order-3 img"
        И я должен видеть картинку с исходником "/bundles/stfalconsponsor/images/symfonycamp.png"
        И я должен видеть элемент ".become-partner"
        И я не должен видеть картинку с исходником "/images/partners/epochta.png"

        Допустим я на странице "/event/not-active-frameworks-day"
        Тогда код ответа сервера должен быть 200
        И я должен видеть "Спонсоры" внутри элемента ".partners h2"
        И я должен видеть "Здесь может быть ваш логотип" внутри элемента ".become-partner"
        И я не должен видеть картинку с исходником "/bundles/stfalconsponsor/images/epochta.png"
        И я не должен видеть картинку с исходником "/bundles/stfalconsponsor/images/magento.png"
        И я не должен видеть картинку с исходником "/bundles/stfalconsponsor/images/smartme.png"
        И я не должен видеть картинку с исходником "/bundles/stfalconsponsor/images/symfonycamp.png"
