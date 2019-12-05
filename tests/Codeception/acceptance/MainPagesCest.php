<?php 

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
    ];

    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function openMainPages(AcceptanceTester $I)
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
