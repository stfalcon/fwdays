<?php

/**
 * PromoCodeCest.
 */
class PromoCodeCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @depends SignInModalCest:loginFromModal
     */
    public function promocodeFromQueryFirst(AcceptanceTester $I)
    {
        $I->amOnPage('/event/javaScript-framework-day-2018?promocode=AnyCode');
        $I->seeCurrentUrlEquals('/app_test.php/en/event/javaScript-framework-day-2018');

        $I->amOnPage('/event/javaScript-framework-day-2018/pay');
        $I->seeCurrentUrlEquals('/app_test.php/en/event/javaScript-framework-day-2018/pay');

        $I->seeElement('#payer-block-edit-1 input[name=user_promo_code]');
        $I->seeInField('#payer-block-edit-1 input[name=user_promo_code]', 'AnyCode');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @depends promocodeFromQueryFirst
     */
    public function promocodeNotFoundedAndFounded(AcceptanceTester $I)
    {
        $I->amOnPage('/event/javaScript-framework-day-2018/pay');

        $this->clickEditUser($I);

        $I->fillField("#payer-block-edit-1 input[name=user_promo_code]", 'AnyCode');
        $I->click('#payer-block-edit-1 .edit-user-btn');
        $I->waitForText('Promo code not found!');

        $I->fillField("#payer-block-edit-1 input[name=user_promo_code]", 'Promo code for JsDays');
        $I->click('#payer-block-edit-1 .edit-user-btn');
        $I->waitForText('(coupon discount 10%)');
        $I->dontSee('Promo code not found!');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @depends promocodeNotFoundedAndFounded
     */
    public function payByPromocode(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('Buy ticket', '#card-javaScript-framework-day-2018');
        $I->dontSee('Download ticket');

        $I->amOnPage('/event/javaScript-framework-day-2018/pay');

        $this->clickEditUser($I);

        $I->fillField("#payer-block-edit-1 input[name=user_promo_code]", 'JsDays_100');
        $I->click('#payer-block-edit-1 .edit-user-btn');
        $I->waitForText('(coupon discount 100%)');
        $I->dontSee('Promo code not found!');

        $I->seeElement('#buy-ticket-btn');
        $I->click('#buy-ticket-btn');
        $I->waitForText('Payment successful!');

        $I->seeCurrentUrlEquals('/app_test.php/en/payment/success');
        $I->amOnPage('/');
        $I->seeCurrentUrlEquals('/app_test.php/en/');
        $I->see('Download ticket');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @depends payByPromocode
     */
    public function checkPromocodeLimit(AcceptanceTester $I)
    {
        $I->amOnPage('/event/javaScript-framework-day-2018/pay');

        $I->dontSeeElement('#buy-ticket-btn');

        $I->seeElement('#payer-block-edit-1 input[name=name].payment_user_name');
        $I->seeElement('#payer-block-edit-1 input[name=surname].payment_user_surname');
        $I->seeElement('#payer-block-edit-1 input[name=email].payment_user_email');
        $I->seeElement('#payer-block-edit-1 input[name=user_promo_code].user_promo_code');

        $I->fillField("#payer-block-edit-1 input[name=name].payment_user_name", 'TesterName');
        $I->fillField("#payer-block-edit-1 input[name=surname].payment_user_surname", 'TesterSurname');
        $I->fillField("#payer-block-edit-1 input[name=email].payment_user_email", 'tester-email@gmail.com');
        $I->fillField("#payer-block-edit-1 input[name=user_promo_code]", 'JsDays_100');

        $I->dontSee('Promo code used!');
        $I->click('#payer-block-edit-1 .add-user-btn');

        $I->waitForText('Promo code used!');
    }

//    /**
//     * @param AcceptanceTester $I
//     */
//    public function signIn(AcceptanceTester $I): void
//    {
//        $I->amOnPage('/login');
//        $I->fillField('_username', 'admin@fwdays.com');
//        $I->fillField('_password', 'qwerty');
//        $I->click('#login-form- button[type=submit]');
//    }

    private function clickEditUser(AcceptanceTester $I)
    {
        $I->click('#payment-list .ticket-edit-btn');
        $I->waitForElement('#payer-block-edit-1 input[name=user_promo_code]');
        $I->seeElement('#payer-block-edit-1 input[name=user_promo_code]');
    }
}
