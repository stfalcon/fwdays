<?php

/**
 * SigninModalCest.
 */
class SignInModalCest
{
    /**
     * @param AcceptanceTester $I
     */
    public function loginFromModal(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        static::iAmNotSigned($I);

        $I->click('.header__auth--sign-in');

        $I->seeElement('#user_email_modal-signup');
        $I->seeElement('#user_password_modal-signup');
        $I->seeElement('#login-form-modal-signup button[type=submit]');

        static::fillLoginFieldsAdmin($I);

        $I->click('#login-form-modal-signup button[type=submit]');

        $I->waitForText('ACCOUNT');
        $I->seeCurrentUrlEquals('/app_test.php/en/');
        static::iAmSigned($I);
    }

    /**
     * @param AcceptanceTester $I
     */
    private static function fillLoginFieldsAdmin(AcceptanceTester $I): void
    {
        $I->fillField('_username', 'admin@fwdays.com');
        $I->fillField('_password', 'qwerty');
    }

    /**
     * @param AcceptanceTester $I
     */
    private static function iAmSigned(AcceptanceTester $I): void
    {
        $I->seeLink('ACCOUNT');
        $I->dontSeeLink('SIGN IN');
    }

    /**
     * @param AcceptanceTester $I
     */
    private static function iAmNotSigned(AcceptanceTester $I): void
    {
        $I->dontSeeLink('ACCOUNT');
        $I->seeLink('SIGN IN');
    }
}
