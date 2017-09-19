<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\DependentFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\EventBundle\Entity\Review;

/**
 * LoadReviewData Class
 */
class LoadReviewData extends AbstractFixture implements DependentFixtureInterface
{
    private $reviews = [
        '<ul>
<li>Общие понятия, используемые паттерны, применение на практике.</li>
<li>Реализация в Zend Framework 2</li>
<li>Практические примеры использования EventManager</li>
<li>MVC + Event </li>
</ul>

<h2 id="slides">Слайды к докладу</h2>
<script async class="speakerdeck-embed" data-id="a56515c01b60013084a11231380fad16" data-ratio="1.33333333333333" src="//speakerdeck.com/assets/embed.js"></script>

<h2 id="slides">Видео</h2>
<iframe width="648" height="365" src="//www.youtube.com/embed/QQTqvoHiRj4" frameborder="0" allowfullscreen></iframe>
',
        '<p>В докладе я расскажу о своем опыте оптимизации “тяжелых” e-commerce проектов написанных на платформе Magento. В ходе доклада мы рассмотрим, практики и стратегии кэширования такие как: </p>
<ul>
<li>Точечное кэширование</li> 
    <li>“Клонирование” приложения и генерация статического контента</li>
    <li>Кэширование вместо оптимизации</li>
    <li>Автоматическая генерация кэшей и др.</li>
</ul>
<p>Немного остановимся на рассмотрении TwoLevels Cache в Zend 1 и математических моделях кэширования (LRU, LFU, LRU2, MRU).</p>
<p>Попробуем подобрать адекватное хранилище для кеша. Посмотрим чем нам может помочь cобытийно-ориентированная архитектура поддерживать валидность кэша.</p>
<p>И сравним Zend\Cache 2.0 с Zend_Cache 1.0.</p>

<h2 id="slides">Слайды к докладу</h2>
<script async class="speakerdeck-embed" data-id="117f80701b630130270e1231380fad16" data-ratio="1.33333333333333" src="//speakerdeck.com/assets/embed.js"></script>

<h2 id="slides">Видео</h2>
<iframe width="648" height="365" src="//www.youtube.com/embed/7ovH9oT-ZqQ" frameborder="0" allowfullscreen></iframe>',
        '<br />
<ul>
<li>AtomJS. Павел Пономаренко</li>
<li>About Opensource. Владимир Агафонкин</li>
<li>KnockoutJS and Anima.js. Андрей Листочкин</li>
<li>Использование crossroads.js для маршрутизации на фронтенде. Максим Григорян</li>
<li>Интерактивные слои API карт 2GIS. Андрей Геоня</li>
</ul>

<h2 id="slides">Слайды к докладам</h2>

<script async class="speakerdeck-embed" data-id="e9d0c4f06e4b01307288123139180569" data-ratio="1.33333333333333" src="//speakerdeck.com/assets/embed.js"></script>

<h2 id="slides">Видео</h2>
<br />

<p>AtomJS. Павел Пономаренко <a href="http://frameworksdays.com/uploads/video/jsfwday-2013/8-jsfwdays-2013-lightning-talk-1.mp4">download video</a></p>
<script type=\'text/javascript\' src=\'/uploads/jwplayer6/jwplayer.js\'></script>

<iframe width="686" height="386" src="http://www.youtube.com/embed/jMcbNj8yK9Y" frameborder="0" allowfullscreen></iframe>

<br />

<p>About Opensource. Владимир Агафонкин <a href="http://frameworksdays.com/uploads/video/jsfwday-2013/8-jsfwdays-2013-lightning-talk-3.mp4">download video</a></p>

<iframe width="686" height="386" src="http://www.youtube.com/embed/sJPYcQhuN4M" frameborder="0" allowfullscreen></iframe>

<br />

<p>KnockoutJS and Anima.js. Андрей Листочкин <a href="http://frameworksdays.com/uploads/video/jsfwday-2013/8-jsfwdays-2013-lightning-talk-4.mp4">download video</a></p>

<iframe width="686" height="386" src="http://www.youtube.com/embed/y5K0fJ6gbMc" frameborder="0" allowfullscreen></iframe>

<br />

<p>Использование crossroads.js для маршрутизации на фронтенде. Максим Григорян <a href="http://frameworksdays.com/uploads/video/jsfwday-2013/8-jsfwdays-2013-lightning-talk-2.mp4">download video</a></p>

<iframe width="686" height="386" src="http://www.youtube.com/embed/BrLXvoTCiuQ" frameborder="0" allowfullscreen></iframe>

<br />

<p>Интерактивные слои API карт 2GIS. Андрей Геоня <a href="http://frameworksdays.com/uploads/video/jsfwday-2013/8-jsfwdays-2013-lightning-talk-0.mp4">download video</a></p>
<iframe width="686" height="386" src="http://www.youtube.com/embed/FpgpleHtRqg" frameborder="0" allowfullscreen></iframe>'
    ];


    /**
     * Return fixture classes fixture is dependent on
     *
     * @return array
     */
    public function getDependencies()
    {
        return array(
            'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData',
            'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadSpeakerData',
        );
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // Get references for event fixtures
        $eventJsDay  = $manager->merge($this->getReference('event-jsday2018'));
        $eventPHPDay2017 = $manager->merge($this->getReference('event-phpday2017'));
        $eventPHPDay2018 = $manager->merge($this->getReference('event-phpday2018'));
        $eventHighLoad = $manager->merge($this->getReference('event-highload-day'));
        $eventNotActive = $manager->merge($this->getReference('event-not-active'));

        // Get references for speaker fixtures
        $rabievskiy = $manager->merge($this->getReference('speaker-rabievskiy'));
        $shkodyak   = $manager->merge($this->getReference('speaker-shkodyak'));
        $speakers = [];
        for ($i = 0; $i < 4; $i++ ) {
            $speakers[] = $manager->merge($this->getReference('speaker-' . $i));
        }

        $review = (new Review())
            ->setTitle('PHP steps')
            ->setSlug('php-first-steps')
            ->setText($this->reviews[0])
            ->setEvent($eventPHPDay2018)
            ->setSpeaker([$rabievskiy, $speakers[0]]);
        $manager->persist($review);

        $review = (new Review())
            ->setTitle('Symfony 2.1 first steps')
            ->setSlug('symfony-2.1-first-steps')
            ->setText($this->reviews[0])
            ->setEvent($eventNotActive)
            ->setSpeaker([$rabievskiy, $speakers[1]]);
        $manager->persist($review);

        $review = (new Review())
            ->setTitle('Simple API via Zend Framework')
            ->setSlug('simple-api-via-zend-framework')
            ->setText($this->reviews[1])
            ->setEvent($eventJsDay)
            ->setSpeaker([$shkodyak]);
        $manager->persist($review);

        $review = (new Review())
            ->setTitle('Symfony Forever')
            ->setSlug('symfony-forever')
            ->setText($this->reviews[1])
            ->setEvent($eventNotActive)
            ->setSpeaker([$shkodyak, $speakers[2]]);
        $manager->persist($review);

        for ($i = 0; $i < 4; $i++ ) {
            $review1 = (new Review())
                ->setTitle('Review '.$i)
                ->setSlug('review-'.$i)
                ->setText($this->reviews[2])
                ->setEvent($eventPHPDay2017)
                ->setSpeaker([$speakers[$i]]);
            $manager->persist($review1);
            $this->addReference('review-'.$i, $review1);

            $review2 = (new Review())
                ->setTitle('Review '.($i+4))
                ->setSlug('review-'.($i+4))
                ->setText($this->reviews[2])
                ->setEvent($eventHighLoad)
                ->setSpeaker([$speakers[$i]]);
            $manager->persist($review2);
            $this->addReference('review-'.($i+4), $review2);
        }

        $manager->flush();
    }
}
