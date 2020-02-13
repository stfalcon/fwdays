<?php 

class ReferalBalanceCest
{
    private const PAY_USER_DATA = [
        '#payer-block-edit-1 input[name=name].payment_user_name' => 'TesterName',
        '#payer-block-edit-1 input[name=surname].payment_user_surname' => 'TesterSurname',
        '#payer-block-edit-1 input[name=email].payment_user_email' => 'tester-email@gmail.com',
    ];

    /**
     * @param AcceptanceTester $I
     *
     * @depends PromoCodeCest:checkPromocodeLimit
     */
    public function payByBonusNewUser(AcceptanceTester $I)
    {
        $I->wantTo('Check buy ticket by user referal bonus.');

        $I->amOnPage('/event/javaScript-framework-day-2018/pay');

        $I->dontSeeElement('#buy-ticket-btn-javaScript-framework-day-2018');

        foreach (self::PAY_USER_DATA as $field => $value) {
            $I->seeElement($field);
            $I->fillField($field, $value);
        }

        $I->clearField('#payer-block-edit-1 input[name=user_promo_code].user_promo_code');

        $I->click('#payer-block-edit-1 .add-user-btn');

        $I->waitForText('YOUR FWDAYS BONUS');
        $I->seeInField('#user-bonus-input', 1000);

        $I->seeElement('#btn-apply-bonus');
        $I->click('#btn-apply-bonus');

        $I->waitForText('−1 000 UAH fwdays bonus');
        $I->see('0 UAH ', '.payment-cart__amount');

        $I->click('#buy-ticket-btn-javaScript-framework-day-2018');
        $I->waitForText('Payment successful!');

        $I->seeCurrentUrlEquals('/app_test.php/en/payment/success');
    }
}
