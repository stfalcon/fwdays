<?php

error_reporting(E_ALL ^ E_DEPRECATED);

/**
 * AdminPagesCest.
 */
class AdminPagesCest
{
    private const PAGES = [
        [
            'url' => '/admin',
            'see' => [
                'События',
                'Пользователи',
                'Переводы',
                'Статистика',
                'Спонсоры',
                'Рассылки',
                'Билеты',
                'Страницы',
                'Логи',
            ],
        ],
        [
            'url' => '/admin/app/eventaudience/list',
        ],
        [
            'url' => '/admin/app/event/list',
        ],
        [
            'url' => '/admin/app/eventgroup/list',
        ],
        [
            'url' => '/admin/app/review/list',
        ],
        [
            'url' => '/admin/app/speaker/list',
        ],
        [
            'url' => '/admin/app/eventpage/list',
        ],
        [
            'url' => '/admin/app/user/list',
        ],
        [
            'url' => '/admin/lexik/translation/transunit/list',
        ],
        [
            'url' => '/admin/app/sponsor/list',
        ],
        [
            'url' => '/admin/app/category/list',
        ],
        [
            'url' => '/admin/app/mail/list',
        ],
        [
            'url' => '/admin/app/mailqueue/list',
        ],
        [
            'url' => '/admin/app/ticket/list',
        ],
        [
            'url' => '/admin/app/payment/list',
        ],
        [
            'url' => '/admin/app/promocode/list',
        ],
        [
            'url' => '/admin/app/usereventregistration/list',
        ],
        [
            'url' => '/admin/app/page/list',
        ],
        [
            'url' => '/admin/app/wayforpaylog/list',
        ],
        [
            'url' => '/admin/statistic',
        ],
        [
            'url' => '/admin/app/eventaudience/create',
        ],
        [
            'url' => '/admin/app/event/create',
        ],
        [
            'url' => '/admin/app/eventgroup/create',
        ],
        [
            'url' => '/admin/app/review/create',
        ],
        [
            'url' => '/admin/app/speaker/create',
        ],
        [
            'url' => '/admin/app/eventpage/create',
        ],
        [
            'url' => '/admin/app/sponsor/create',
        ],
        [
            'url' => '/admin/app/category/create',
        ],
        [
            'url' => '/admin/app/mail/create',
        ],
        [
            'url' => '/admin/app/mailqueue/create',
        ],
        [
            'url' => '/admin/app/promocode/create',
        ],
        [
            'url' => '/admin/app/page/create',
        ],
        [
            'url' => '/admin/app/user/create',
        ],
        [
            'url' => '/admin/ticket/check',
        ],
    ];

    /**
     * @param AcceptancePhpWebTester $I
     */
    private static function fillLoginFieldsAdmin(AcceptancePhpWebTester $I): void
    {
        $I->fillField('_username', 'admin@fwdays.com');
        $I->fillField('_password', 'qwerty');
    }

    /**
     * @param AcceptancePhpWebTester $I
     */
    public function openAdminPages(AcceptancePhpWebTester $I)
    {
        $I->wantTo('Check Admin Pages open.');
        $I->amOnPage('/admin');
        $I->seeCurrentUrlEquals('/index_test.php/login');
        self::fillLoginFieldsAdmin($I);
        $I->click('#login-form- button[type=submit]');
        $I->seeCurrentUrlEquals('/index_test.php/admin/dashboard');

        foreach (self::PAGES as $page) {
            $I->amOnPage($page['url']);
            $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);
            if (isset($page['see'])) {
                foreach ($page['see'] as $see) {
                    $I->see($see);
                }
            }
        }
    }
}
