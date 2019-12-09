<?php 

class ReferalBalanceCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @depends PromoCodeCest:checkPromocodeLimit
     */
    public function payByBonusNewUser(AcceptanceTester $I)
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
        $I->clearField('#payer-block-edit-1 input[name=user_promo_code].user_promo_code');

        $I->click('#payer-block-edit-1 .add-user-btn');

        $I->waitForText('YOUR FWDAYS BONUS');
        $I->seeInField('#user-bonus-input', 1000);
        $I->seeElement('#btn-apply-bonus');

        $I->click('#btn-apply-bonus');

        $I->waitForText('−1 000 UAH fwdays bonus', 15, '.payment-cart__hint');
        $I->see('0 UAH ', '.payment-cart__amount');

        $I->click('#buy-ticket-btn');
        $I->waitForText('Payment successful!');
        $I->seeCurrentUrlEquals('/app_test.php/en/payment/success');
    }
}
