# language: ru

Функционал: Тест контроллера SponsorController
    Тестируем список спонсоров

    Сценарий: Открыть страницу события Zend Framework Day и убедиться в наличии баннеров прикрепленных спонсоров,
        а также в отсутствии баннеров все остальных спонсоров
        Допустим я на странице "/event/zend-framework-day-2011"
        Тогда код ответа сервера должен быть 200
        И я должен видеть "Спонсоры" внутри элемента ".partners h2"
        И я должен видеть картинку с исходником "/images/partners/epochta.png"
        И я должен видеть картинку с исходником "/images/partners/magento/small_logo.png"
        И я должен видеть картинку с исходником "/images/partners/symfonycamp.png"
        И я должен видеть элемент ".become-partner"
        И я не должен видеть картинку с исходником "/images/partners/smartme.png"

        Допустим я на странице "/event/php-frameworks-day-2012"
        Тогда код ответа сервера должен быть 200
        И я должен видеть "Спонсоры" внутри элемента ".partners h2"
        И я должен видеть картинку с исходником "/images/partners/magento/small_logo.png"
        И я должен видеть картинку с исходником "/images/partners/smartme.png"
        И я должен видеть картинку с исходником "/images/partners/symfonycamp.png"
        И я должен видеть элемент ".become-partner"
        И я не должен видеть картинку с исходником "/images/partners/epochta.png"

        Допустим я на странице "/event/not-active-frameworks-day"
        Тогда код ответа сервера должен быть 200
        И я должен видеть "Спонсоры" внутри элемента ".partners h2"
        И я должен видеть "Здесь может быть ваш логотип" внутри элемента ".become-partner"
        И я не должен видеть картинку с исходником "/images/partners/epochta.png"
        И я не должен видеть картинку с исходником "/images/partners/magento/small_logo.png"
        И я не должен видеть картинку с исходником "/images/partners/smartme.png"
        И я не должен видеть картинку с исходником "/images/partners/symfonycamp.png"

