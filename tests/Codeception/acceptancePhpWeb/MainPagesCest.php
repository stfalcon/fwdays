<?php

/**
 * MainPagesCest.
 */
class MainPagesCest
{
    private const PAGES = [
        [
            'url' => '/',
            'see' => ['Технічні конференції в Україні'],
        ],
        [
            'url' => '/page/about',
            'see' => [],
        ],
        [
            'url' => '/events',
            'see' => ['Майбутні події', 'Минулі події'],
        ],
        [
            'url' => '/page/contacts',
            'see' => ['Контактна інформація'],
        ],
        [
            'url' => '/event/javaScript-framework-day-2018',
            'see' => [
                    'Конференция JavaScript fwdays \'18',
                    'JavaScript Frameworks Day 2018 - V международная конференция, посвященная популярным JavaScript фреймворкам.',
                    'отель "Казацкий"',
                    'Програма',
                    'Купити квиток',
                    'Квитки від 1 000 грн',
                    'Как прошла конференция',

                    'Доповідачі',
                    'Андрей Воробей',
                    'Simple API via Zend Framework',

                    'На розгляді',
                    'speaker0',
                    'speaker4',
                    'Тема доповіді уточнюється',

                    'Main Stage',
                    'Q&A session',
                    'О драконах ни слова',

                    'Вартість участі',
                    '50 квитків',
                    'Купити квиток',
                    'для учасників попередніх конференцій',
                    '−50% для студентів денної форми навчання',
                    'Golden sponsor',
                    'Silver sponsor',
                    'Місце проведення',
                    'отель "Казацкий"',
                    'Як дістатися',
                ],
        ],
        [
            'url' => '/event/javaScript-framework-day-2018/page/venue',
            'see' => [
                    'Доповідачі',
                    'Програма',
                    'Вартість участі',
                    'Місце проведення',
                    'Купити квиток',
                ],
        ],
    ];

    /**
     * @param AcceptancePhpWebTester $I
     */
    public function openMainPages(AcceptancePhpWebTester $I)
    {
        $I->wantTo('Check Main Pages open.');
        foreach (self::PAGES as $page) {
            $I->amOnPage($page['url']);
            $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);
            foreach ($page['see'] as $see) {
                $I->see($see);
            }
        }
    }
}
