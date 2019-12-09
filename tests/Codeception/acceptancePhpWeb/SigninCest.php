<?php

class SigninCest
{
    public function loginFromPage(AcceptancePhpWebTester $I): void
    {
        $I->amOnPage('/');
        static::iAmNotSigned($I);

        $I->amOnPage('/login');
        $I->seeElement('#user_email_');
        $I->seeElement('#user_password_');

        self::fillLoginFieldsAdmin($I);

        $I->click('#login-form- button[type=submit]');

        $I->seeCurrentUrlEquals('/');
        $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        static::iAmSigned($I);
    }

    public function loginFromModal(AcceptancePhpWebTester $I): void
    {
        $I->amOnPage('/');
        static::iAmNotSigned($I);

        $I->click('Увійти', '.header__auth--sign-in');

        $I->seeElement('#user_email_modal-signup');
        $I->seeElement('#user_password_modal-signup');

        static::fillLoginFieldsAdmin($I);

        $I->click('#login-form-modal-signup button[type=submit]');

        $I->seeCurrentUrlEquals('/');
        $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        static::iAmSigned($I);
    }

    public function unauthenticatedRedirectToLoginAndThanBack(AcceptancePhpWebTester $I): void
    {
        $I->amOnPage('/event/javaScript-framework-day-2018');
        $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $I->seeLink('Купити за');
        $I->click('Купити за');

        $I->seeCurrentUrlEquals('/login');

        static::fillLoginFieldsAdmin($I);

        $I->click('#login-form- button[type=submit]');

        $I->seeCurrentUrlEquals('/event/javaScript-framework-day-2018/pay');
        $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);
    }

    /**
     * For sing in.
     *
     * @skip
     */
    public static function signIn(AcceptancePhpWebTester $I): void
    {
        $I->amOnPage('/login');
        static::fillLoginFieldsAdmin($I);
        $I->click('#login-form- button[type=submit]');
    }

    private static function fillLoginFieldsAdmin(AcceptancePhpWebTester $I): void
    {
        $I->fillField('_username', 'admin@fwdays.com');
        $I->fillField('_password', 'qwerty');
    }

    private static function iAmSigned(AcceptancePhpWebTester $I): void
    {
        $I->seeLink('Кабінет');
        $I->dontSeeLink('Увійти');
    }

    private static function iAmNotSigned(AcceptancePhpWebTester $I): void
    {
        $I->dontSeeLink('Кабінет');
        $I->seeLink('Увійти');
    }
}
