<?php

const PAGES = [
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

$I = new AcceptanceTester($scenario);
$I->wantTo('Check pages');
foreach (PAGES as $page) {
    $I->amOnPage($page['url']);
    $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);
    foreach ($page['see'] as $see) {
        $I->see($see);
    }
}
$I->see('Як користувач соцмереж');
$I->click(' Увійти');
