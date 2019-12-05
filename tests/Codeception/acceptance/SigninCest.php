<?php

class SigninCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function loginFromPage(AcceptanceTester $I)
    {
        $I->amOnPage('/login');
        $I->dontSeeLink('Кабінет');

        $I->seeElement('#user_email_');
        $I->fillField('_username', 'admin@fwdays.com');
        $I->seeElement('#user_password_');
        $I->fillField('_password', 'qwerty');
        $I->click('#login-form- button[type=submit]');

        $I->seeCurrentUrlEquals('/');
        $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $I->seeLink('Кабінет');
        $I->dontSeeLink('Увійти');
    }

    public function loginFromModal(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->dontSeeLink('Кабінет');
        $I->seeLink('Увійти');

        $I->click('Увійти', '.header__auth--sign-in');

        $I->seeElement('#user_email_modal-signup');
        $I->fillField('_username', 'admin@fwdays.com');
        $I->seeElement('#user_password_modal-signup');
        $I->fillField('_password', 'qwerty');
        $I->click('#login-form-modal-signup button[type=submit]');

        $I->seeCurrentUrlEquals('/');
        $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $I->seeLink('Кабінет');
        $I->dontSeeLink('Увійти');
    }

    public function unauthenticatedRedirectToLoginAndThanBack(AcceptanceTester $I)
    {
        $I->amOnPage('/event/javaScript-framework-day-2018');
        $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $I->seeLink('Купити за');
        $I->click('Купити за');

        $I->seeCurrentUrlEquals('/login');

        $I->fillField('_username', 'admin@fwdays.com');
        $I->fillField('_password', 'qwerty');
        $I->click('#login-form- button[type=submit]');

        $I->seeCurrentUrlEquals('/event/javaScript-framework-day-2018/pay');
        $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);
    }
}
