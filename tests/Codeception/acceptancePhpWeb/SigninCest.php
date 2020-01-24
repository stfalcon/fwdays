<?php

class SigninCest
{
    /**
     * @param AcceptancePhpWebTester $I
     */
    public function loginFromPage(AcceptancePhpWebTester $I): void
    {
        $I->wantTo('Check Login from static page.');

        $I->amOnPage('/');
        static::iAmNotSigned($I);

        $I->amOnPage('/login');
        $I->seeElement('#user_email_');
        $I->seeElement('#user_password_');

        self::fillLoginFieldsAdmin($I);

        $I->click('#login-form- button[type=submit]');

        $I->seeCurrentUrlEquals('/index_test.php');
        $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        static::iAmSigned($I);
    }

    /**
     * @param AcceptancePhpWebTester $I
     */
    public function unauthenticatedRedirectToLoginAndThanBack(AcceptancePhpWebTester $I): void
    {
        $I->wantTo('Check redirect to login if pressed buy button and back to pay page.');

        $I->amOnPage('/event/javaScript-framework-day-2018');
        $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $I->seeLink('Купити за');
        $I->click('Купити за');

        $I->seeCurrentUrlEquals('/index_test.php/login');

        static::fillLoginFieldsAdmin($I);

        $I->click('#login-form- button[type=submit]');

        $I->seeCurrentUrlEquals('/index_test.php/event/javaScript-framework-day-2018/pay');
        $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);
    }

    /**
     * @param AcceptancePhpWebTester $I
     */
    public function cabinetPageAllowed(AcceptancePhpWebTester $I): void
    {
        $I->wantTo('Check cabinet allowed for user.');
        $I->amOnPage('/');
        static::iAmNotSigned($I);
        $I->amOnPage('/login');
        static::fillLoginFieldsAdmin($I);
        $I->click('#login-form- button[type=submit]');
        $I->seeCurrentUrlEquals('/index_test.php');
        $I->amOnPage('/cabinet');
        $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);
    }

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
    private static function iAmSigned(AcceptancePhpWebTester $I): void
    {
        $I->seeLink('Кабінет');
        $I->dontSeeLink('Увійти');
    }

    /**
     * @param AcceptancePhpWebTester $I
     */
    private static function iAmNotSigned(AcceptancePhpWebTester $I): void
    {
        $I->dontSeeLink('Кабінет');
        $I->seeLink('Увійти');
    }
}
