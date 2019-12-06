<?php 

class PromoCodeCest
{
    public function promocodeFromQuery(AcceptanceTester $I)
    {
        $I->amOnPage('/event/javaScript-framework-day-2018?promocode=Promo code for JsDays');

        $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $I->seeLink('Купити за');
        $I->click('Купити за');

        $I->seeCurrentUrlEquals('/login');

        $I->fillField('_username', 'admin@fwdays.com');
        $I->fillField('_password', 'qwerty');
        $I->click('#login-form- button[type=submit]');

        $I->seeCurrentUrlEquals('/event/javaScript-framework-day-2018/pay');
        $I->canSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->canSeeInField('user_promo_code', 'Promo code for JsDays');
    }

    /**
     * For sing in.
     *
     * @skip
     */
    private static function signIn(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        static::fillLoginFieldsAdmin($I);
        $I->click('#login-form- button[type=submit]');
    }

    /**
     * For sing in.
     *
     * @skip
     */
    private static function fillLoginFieldsAdmin(AcceptanceTester $I): void
    {
        $I->fillField('_username', 'admin@fwdays.com');
        $I->fillField('_password', 'qwerty');
    }
}
