<?php

/**
 * PromoCodeCest.
 */
class PromoCodeCest
{
    private const PAY_USER_DATA = [
        '#payer-block-edit-1 input[name=name].payment_user_name' => 'TesterName',
        '#payer-block-edit-1 input[name=surname].payment_user_surname' => 'TesterSurname',
        '#payer-block-edit-1 input[name=email].payment_user_email' => 'tester-email@gmail.com',
        '#payer-block-edit-1 input[name=user_promo_code]' => 'JsDays_100',
    ];

    /**
     * @param AcceptanceTester $I
     *
     * @depends UserCest:loginModal
     */
    public function promocodeFromQueryFirst(AcceptanceTester $I)
    {
        $I->wantTo('Check if got promocode from query url.');

        $I->amOnPage('/en/event/javaScript-framework-day-2018?promocode=AnyCode');
        $I->seeCurrentUrlEquals('/index_test.php/en/event/javaScript-framework-day-2018');

        $I->amOnPage('/en/event/javaScript-framework-day-2018/pay');
        $I->seeCurrentUrlEquals('/index_test.php/en/event/javaScript-framework-day-2018/pay');

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
        $I->wantTo('Check error message if not found promocode and dicount message on found.');

        $I->amOnPage('/en/event/javaScript-framework-day-2018/pay');

        $this->clickEditUser($I);

        $I->fillField('#payer-block-edit-1 input[name=user_promo_code]', 'AnyCode');
        $I->click('#payer-block-edit-1 .edit-user-btn');
        $I->waitForText('Promo code not found!');

        $I->fillField('#payer-block-edit-1 input[name=user_promo_code]', 'Promo code for JsDays');
        $I->click('#payer-block-edit-1 .edit-user-btn');
        $I->wait(1); //if validation error before - need second click
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
        $I->wantTo('Check buy ticket for 100% promocode and see download link.');

        $I->amOnPage('/en');
        $I->see('Buy ticket', '#card-javaScript-framework-day-2018');
        $I->dontSee('Download ticket');

        $I->amOnPage('/en/event/javaScript-framework-day-2018/pay');

        $this->clickEditUser($I);

        $I->fillField('#payer-block-edit-1 input[name=user_promo_code]', 'JsDays_100');
        $I->click('#payer-block-edit-1 .edit-user-btn');
        $I->waitForText('(coupon discount 100%)');
        $I->dontSee('Promo code not found!');

        $I->seeElement('#buy-ticket-btn');
        $I->click('#buy-ticket-btn');
        $I->waitForText('Payment successful!');

        $I->seeCurrentUrlEquals('/index_test.php/en/payment/success');
        $I->amOnPage('/en');
        $I->seeCurrentUrlEquals('/index_test.php/en/');
        $I->see('Download ticket');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @depends payByPromocode
     */
    public function checkPromocodeLimit(AcceptanceTester $I)
    {
        $I->wantTo('Check using limited promocode and see error text.');

        $I->amOnPage('/en/event/javaScript-framework-day-2018/pay');

        $I->dontSeeElement('#buy-ticket-btn');

        foreach (self::PAY_USER_DATA as $field => $value) {
            $I->fillField($field, $value);
        }

        $I->dontSee('Promo code used!');
        $I->click('#payer-block-edit-1 .add-user-btn');

        $I->waitForText('Promo code used!');
    }

    private function clickEditUser(AcceptanceTester $I)
    {
        $I->click('#payment-list .ticket-edit-btn');
        $I->waitForElement('#payer-block-edit-1 input[name=user_promo_code]');
        $I->seeElement('#payer-block-edit-1 input[name=user_promo_code]');
    }

    /**
     * @param AcceptanceTester $I
     * @param string           $element
     */
    private static function seeAndClick(AcceptanceTester $I, string $element): void
    {
        $I->seeElement($element);
        $I->click($element);
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
    private static function iAmSigned(AcceptanceTester $I, string $lang = 'en'): void
    {
        if ('en' === $lang) {
            $I->seeLink('ACCOUNT');
            $I->dontSeeLink('SIGN IN');
        } else {
            $I->seeLink('КАБІНЕТ');
            $I->dontSeeLink('УВІЙТИ');
        }
    }

    /**
     * @param AcceptanceTester $I
     */
    private static function iAmNotSigned(AcceptanceTester $I, string $lang = 'en'): void
    {
        if ('en' === $lang) {
            $I->dontSeeLink('ACCOUNT');
            $I->seeLink('SIGN IN');
        } else {
            $I->dontSeeLink('КАБІНЕТ');
            $I->seeLink('УВІЙТИ');
        }
    }
}
