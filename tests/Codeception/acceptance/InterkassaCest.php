<?php

class InterkassaCest
{
    public function openInterkassaPage(AcceptanceTester $I): void
    {
        static::signIn($I);

        $I->amOnPage('/event/javaScript-framework-day-2018/pay');
        $I->click('#buy-ticket-btn');

        $I->seeCurrentUrlEquals('sci.interkassa.com');
    }

    /**
     * For sing in.
     *
     * @skip
     */
    public static function signIn(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        static::fillLoginFieldsAdmin($I);
        $I->click('#login-form- button[type=submit]');
    }

    private static function fillLoginFieldsAdmin(AcceptanceTester $I): void
    {
        $I->fillField('_username', 'admin@fwdays.com');
        $I->fillField('_password', 'qwerty');
    }

    private static function iAmSigned(AcceptanceTester $I): void
    {
        $I->seeLink('Кабінет');
        $I->dontSeeLink('Увійти');
    }

    private static function iAmNotSigned(AcceptanceTester $I): void
    {
        $I->dontSeeLink('Кабінет');
        $I->seeLink('Увійти');
    }
}
