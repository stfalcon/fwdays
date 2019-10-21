<?php

namespace Application\Bundle\DefaultBundle\DataFixtures\ORM;

use Application\Bundle\DefaultBundle\Entity\EventPage;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadEventPagesData Class.
 */
class LoadEventPagesData extends AbstractFixture implements DependentFixtureInterface
{
    private $oldProgram = '<table class="event-program">
    <tbody>
        <tr>
            <td class="ep-time">09:30–10:00</td>
            <td class="ep-keypoint">Регистрация участников</td>
        </tr>
        <tr>
            <td class="ep-time">10:00–10:15</td>
            <td class="ep-keypoint">Открытие и вступительная часть</td>
        </tr>
        <tr>
            <td class="ep-time">10:15–11:00</td>
            <td class="ep-event">
                <div class="epe-name"><a href="http://frameworksdays.com/event/js-frameworks-day-2013/review/Amazing-UI-with-CSS-and-Anima-js">Amazing UI with CSS and Anima.js
</a></div>
                <div class="epe-presenter">Егор Львовский</div>
            </td>
        </tr>
        <tr>
            <td class="ep-time">11:20–11:50</td>
            <td class="ep-event">
                <div class="epe-name"><a href="http://frameworksdays.com/event/js-frameworks-day-2013/review/Knockout">Knockout: как, зачем, почему.</a></div>
                <div class="epe-presenter">Андрей Листочкин</div>
                <div class="epe-presenter-company">Grammarly</div>
            </td>
        </tr>
        <tr>
            <td class="ep-time">11:45–12:15</td>
            <td class="ep-keypoint">Кофе пауза #1</td>
        </tr>
        <tr>
            <td class="ep-time">11:50–12:35</td>
            <td class="ep-event">
                <div class="epe-name"><a href="http://frameworksdays.com/event/js-frameworks-day-2013/review/Building-Maps-with-Leaflet">Создание интерактивных карт с Leaflet.</a></div>
                <div class="epe-presenter">Владимир Агафонкин</div>
                <div class="epe-presenter-company">Universal Mind</div>
            </td>
        </tr>
        <tr>
            <td class="ep-time">13:00–13:45</td>
            <td class="ep-event">
                <div class="epe-name"><a href="http://frameworksdays.com/event/js-frameworks-day-2013/review/opyt-razrabotki-mobilnogo-web-prilozhenia-s-ispolzovaniem-Backbone-js-Require-js-Zepto-js">Опыт разработки мобильного веб приложения с использованием Backbone.js, Require.js, Zepto.js</a></div>
                <div class="epe-presenter">Павел Юрийчук</div>
                <div class="epe-presenter-company">GlobalLogic</div>
            </td>
        </tr>
        <tr>
            <td class="ep-time">13:45–15:00</td>
            <td class="ep-keypoint">Обеденный перерыв</td>
        </tr>
        <tr>
            <td class="ep-time">15:00–15:50</td>
            <td class="ep-event">
                <div class="epe-name"><a href="http://frameworksdays.com/event/js-frameworks-day-2013/review/Native-look-and-feel-of-mobile-JS-interfaces-with-HTML5-canvas">Native look and feel of mobile JS interfaces with
HTML5 canvas.</a></div>
                <div class="epe-presenter">Денис Радин</div>
                <div class="epe-presenter-company">Pixels Research Institute</div>
            </td>            
        </tr>
        <tr>
            <td class="ep-time">15:50–16:35</td>
            <td class="ep-event">
                <div class="epe-name"><a href="http://frameworksdays.com/event/js-frameworks-day-2013/review/Expanding-Chaplin-js-experience-1-year-passed">Expanding Chaplin.js experience. 1 year passed.</a></div>
                <div class="epe-presenter">Егор Назаркин</div>
                <div class="epe-presenter-company">MediaSapiens</div>            
            </td>
        </tr>
        <tr>
            <td class="ep-time">16:35–17:00</td>
            <td class="ep-keypoint">Кофе пауза #2</td>
        </tr>
        <tr>
            <td class="ep-time">17:00–17:45</td>
            <td class="ep-event">
                <div class="epe-name"><a href="http://frameworksdays.com/event/js-frameworks-day-2013/review/AtomJS-LibCanvas">AtomJS и LibCanvas
</a></div>
                <div class="epe-presenter">Павел Пономаренко</div>
                <div class="epe-presenter-company">Persha Studia</div>            
            </td>
        </tr>
        <tr>
            <td class="ep-time">17:45–18:30</td>
            <td class="ep-event">
                <div class="epe-name"><a href="http://frameworksdays.com/event/js-frameworks-day-2013/review/Functional-Reactive-Programming-%26-ClojureScript">Functional Reactive Programming & ClojureScript
</a></div>
                <div class="epe-presenter">Александр Соловьев</div>
                <div class="epe-presenter-company">Socialabs</div>
            </td>
        </tr>
        <tr>
            <td class="ep-time">18:30–19:00</td>
            <td class="ep-keypoint">Закрытие конференции</td>
        </tr>
        <tr>
            <td class="ep-time">19:00–и до утра :)</td>
            <td class="ep-keypoint">AfterParty в пабе «Сундук» (на Михайловской)</td>
        </tr>
    </tbody>
</table>';

    private $newProgram = '<div class="program">
                    <div class="program-header">
                        <div class="program-header__td program-header__td--active" style="background-color: #FF700A;">
                            <span class="program-header__text">Main Stage</span>
                        </div>
                        <div class="program-header__td" style="background-color: #FECD4A; color: #121314;">
                            <span class="program-header__text">Track A</span>
                        </div>
                        <div class="program-header__td" style="background-color: #00C1AA;">
                            <span class="program-header__text">Track B</span>
                        </div>
                        <div class="program-header__td" style="background-color: #09A0BD;">
                            <span class="program-header__text">Q&A session</span>
                        </div>
                    </div>
                    <div class="program-body">
                        <div class="program-body__tr program-body__tr--pause">
                            <div class="program-body__td program-body__td--time">09:00</div>
                            <div class="program-body__td">
                                <div class="event-outside">
                                    <div class="event-outside__title">Registration & morning tea/coffee</div>
                                    <div class="event-outside__duration">1 час 30 мин</div>
                                </div>
                            </div>
                        </div>
                        <div class="program-body__tr program-body__tr--pause">
                            <div class="program-body__td program-body__td--time">10:30</div>
                            <div class="program-body__td">
                                <div class="event-outside">
                                    <div class="event-outside__title">Opening ceremony</div>
                                    <div class="event-outside__duration">10 мин</div>
                                </div>
                            </div>
                        </div>
                        <div class="program-body__tr program-body__tr--event">
                            <div class="program-body__td program-body__td--time">10:40</div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">О драконах ни слова</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Илья Климов</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td"></div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">Разработка realtime SPA с&nbsp;использованием
                                        VueJS
                                        и&nbsp;RethinkDB</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Сергей Морковкин</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td"></div>
                        </div>
                        <div class="program-body__tr program-body__tr--pause">
                            <div class="program-body__td program-body__td--time">11:20</div>
                            <div class="program-body__td">
                                <div class="event-outside">
                                    <div class="event-outside__title">Coffee-break #1</div>
                                    <div class="event-outside__duration">30 мин</div>
                                </div>
                            </div>
                        </div>
                        <div class="program-body__tr program-body__tr--event">
                            <div class="program-body__td program-body__td--time">11:50</div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">Web Apps Performance & JavaScript
                                        Compilers</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Роман Лютиков</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">Let\'s Build a Web Application (and Talk
                                        About
                                        Ways to Improve Bad Parts)</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Игорь Фесенко</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">Riot.JS, или как приготовить современные
                                        Web
                                        Components</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Анджей Гужовский</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td">
                                Илья Климов, Сергей Морковкин, Андрей Шумада
                            </div>
                        </div>
                        <div class="program-body__tr program-body__tr--pause">
                            <div class="program-body__td program-body__td--time">12:30</div>
                            <div class="program-body__td">
                                <div class="event-outside">
                                    <div class="event-outside__title">Break</div>
                                    <div class="event-outside__duration">10 мин</div>
                                </div>
                            </div>
                        </div>
                        <div class="program-body__tr program-body__tr--event">
                            <div class="program-body__td program-body__td--time">12:40</div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">Еще несколько слов об архитектуре</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Алексей Волков</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">Hyperops</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Mathias Buus [eng]</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">Architecting React Native app</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Филипп Шурпик</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td">
                                Анджей Гужовский, Игорь Фесенко, Роман Лютиков, Борис Могила
                            </div>
                        </div>
                        <div class="program-body__tr program-body__tr--pause">
                            <div class="program-body__td program-body__td--time">13:20</div>
                            <div class="program-body__td">
                                <div class="event-outside">
                                    <div class="event-outside__title">Lunch</div>
                                    <div class="event-outside__duration">1 час 10 мин</div>
                                </div>
                            </div>
                        </div>
                        <div class="program-body__tr program-body__tr--event">
                            <div class="program-body__td program-body__td--time">14:30</div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">The Road to Native Web Components</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Michael North [eng]</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">Критерии выбора JS-фреймворков</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Юрий Лучанинов</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">React Native vs. React+WebView</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Алексей Косинский</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td">
                                Филипп Шурпик, Paul Miller, Алексей Волков, Григорий Шехет
                            </div>
                        </div>
                        <div class="program-body__tr program-body__tr--pause">
                            <div class="program-body__td program-body__td--time">15:10</div>
                            <div class="program-body__td">
                                <div class="event-outside">
                                    <div class="event-outside__title">Break</div>
                                    <div class="event-outside__duration">10 мин</div>
                                </div>
                            </div>
                        </div>
                        <div class="program-body__tr program-body__tr--event">
                            <div class="program-body__td program-body__td--time">15:20</div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">The Road to Native Web Components</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Michael North [eng]</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">Treasure hunt in the land of Reactive
                                        frameworks</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Григорий Шехет</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">Міграція даних в Node.js REST API і
                                        MongoDB</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Андрей Шумада</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td">Алексей Косинский, Юлия Пучнина, Юрий Лучанинов, Евгений
                                Жарков
                            </div>
                        </div>
                        <div class="program-body__tr program-body__tr--pause">
                            <div class="program-body__td program-body__td--time">16:00</div>
                            <div class="program-body__td">
                                <div class="event-outside">
                                    <div class="event-outside__title">Coffee-break #2</div>
                                    <div class="event-outside__duration">30 мин</div>
                                </div>
                            </div>
                        </div>
                        <div class="program-body__tr program-body__tr--event">
                            <div class="program-body__td program-body__td--time">16:30</div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">Как подняться на open source</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Paul Miller</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">PhaserJS for advertisement: игры внутри
                                        баннеров</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Юлия Пучнина</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">Isomorphic React apps in production</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Борис Могила</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td">Tero Parviainen, Mathias Buus, Michael North</div>
                        </div>
                        <div class="program-body__tr program-body__tr--pause">
                            <div class="program-body__td program-body__td--time">17:10</div>
                            <div class="program-body__td">
                                <div class="event-outside">
                                    <div class="event-outside__title">Break</div>
                                    <div class="event-outside__duration">10 мин</div>
                                </div>
                            </div>
                        </div>
                        <div class="program-body__tr program-body__tr--event">
                            <div class="program-body__td program-body__td--time">17:20</div>
                            <div class="program-body__td">
                                <div class="report-details">
                                    <a href="#" class="report-details__title">Как быть хорошим
                                        фронтенд-разработчиком</a>
                                    <div class="report-details__bottom">
                                        <div class="report-details__speaker">Евгений Жарков</div>
                                        <div class="report-details__duration">40 мин</div>
                                    </div>
                                </div>
                            </div>
                            <div class="program-body__td"></div>
                            <div class="program-body__td"></div>
                            <div class="program-body__td"></div>
                        </div>
                        <div class="program-body__tr program-body__tr--pause">
                            <div class="program-body__td program-body__td--time">18:00</div>
                            <div class="program-body__td">
                                <div class="event-outside">
                                    <div class="event-outside__title">Prize drawing from partners</div>
                                    <div class="event-outside__duration">20 мин</div>
                                </div>
                            </div>
                        </div>
                        <div class="program-body__tr program-body__tr--pause">
                            <div class="program-body__td program-body__td--time">18:20</div>
                            <div class="program-body__td">
                                <div class="event-outside">
                                    <div class="event-outside__title">Closing ceremony & family photo</div>
                                    <div class="event-outside__duration">10 мин</div>
                                </div>
                            </div>
                        </div>
                        <div class="program-body__tr program-body__tr--pause">
                            <div class="program-body__td program-body__td--time">18:30</div>
                            <div class="program-body__td">
                                <div class="event-outside">
                                    <div class="event-outside__title">AfterParty</div>
                                    <div class="event-outside__duration">2 часа 30 мин</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';

    private $oldVenue = '<h2>Конференц-холл "ДЕПО", г. Киев, ул. Антоновича, 52, (М Олимпийская)</h2>

<p><strong>Самый простой способ добраться:</strong> доехать до станции метро Олимпийская (синяя линия метро) и оттуда пешком (3 мин) вниз по ул. Физкультуры, перейти через дорогу к остановке общественного транспорта и пройти еще 20 метров влево.</p>


<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2541.5009712269566!2d30.510586415730703!3d50.43176867947275!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40d4cee2ec27f855%3A0x3ec42adccce090e8!2z0LLRg9C70LjRhtGPINCQ0L3RgtC-0L3QvtCy0LjRh9CwLCA1Miwg0JrQuNGX0LI!5e0!3m2!1sru!2sua!4v1505028807534" width="600" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>
</p>



<p>Около конференц-холла есть <strong>платная парковка</strong> "Мегамаркета".</br>
<p>Номер телефона для связи:<strong> +380992159622 Татьяна </storng></p>


<p><img src="https://farm5.staticflickr.com/4390/36042716510_15e26a2c2b_z.jpg" width="640" /></p>';

    private $newVenue = 'Бизнес-центр «Инком» г. Киев, ул. Смоленская, 31-33, 2-й этаж — вход с&nbsp;улицы по внешней
                    лестнице (М&nbsp;Шулявская)';

    /**
     * Return fixture classes fixture is dependent on.
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadEventData',
        ];
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $page = (new EventPage())
        ->setTitle('New Програма')
        ->setSlug('program')
        ->setText($this->newProgram)
        ->setEvent($manager->merge($this->getReference('event-jsday2018')))
        ->setShowInMenu(true)
        ->setSortOrder(1);
        $manager->persist($page);

        $page = (new EventPage())
            ->setTitle('New Програма')
            ->setSlug('program')
            ->setText($this->newProgram)
            ->setEvent($manager->merge($this->getReference('event-phpday2018')))
            ->setShowInMenu(true)
            ->setSortOrder(1);
        $manager->persist($page);

        $page = (new EventPage())
            ->setTitle('Old Програма')
            ->setSlug('program')
            ->setText($this->oldProgram)
            ->setEvent($manager->merge($this->getReference('event-phpday2017')))
            ->setShowInMenu(true)
            ->setSortOrder(1);
        $manager->persist($page);

        $page = (new EventPage())
            ->setTitle('Old Програма')
            ->setSlug('program')
            ->setText($this->oldProgram)
            ->setEvent($manager->merge($this->getReference('event-not-active')))
            ->setShowInMenu(true)
            ->setSortOrder(1);
        $manager->persist($page);

        $page = (new EventPage())
            ->setTitle('New venue')
            ->setSlug('venue')
            ->setText($this->newVenue)
            ->setEvent($manager->merge($this->getReference('event-jsday2018')))
            ->setShowInMenu(true)
            ->setSortOrder(1);
        $manager->persist($page);

        $page = (new EventPage())
            ->setTitle('New venue')
            ->setSlug('venue')
            ->setText($this->newVenue)
            ->setEvent($manager->merge($this->getReference('event-highload-day')))
            ->setShowInMenu(true)
            ->setSortOrder(1);
        $manager->persist($page);

        $page = (new EventPage())
            ->setTitle('Old venue')
            ->setSlug('venue')
            ->setText($this->oldVenue)
            ->setEvent($manager->merge($this->getReference('event-phpday2017')))
            ->setShowInMenu(true)
            ->setSortOrder(1);
        $manager->persist($page);

        $page = (new EventPage())
            ->setTitle('Old venue')
            ->setSlug('venue')
            ->setText($this->oldVenue)
            ->setEvent($manager->merge($this->getReference('event-not-active')))
            ->setShowInMenu(true)
            ->setSortOrder(1);
        $manager->persist($page);

        $manager->flush();
    }
}
