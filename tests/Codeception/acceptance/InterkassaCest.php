<?php 

class InterkassaCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @depends PromoCodeCest:promocodeFromQueryFirst
     *
     * @skip
     */
    public function interkassaPage(AcceptanceTester $I)
    {
        $I->amOnPage('/event/javaScript-framework-day-2018/pay');
        $I->click('#buy-ticket-btn');
        $I->waitForText('Checkout [your_interkassa_shop_key] is not found');
        $I->seeCurrentUrlEquals('https://sci.interkassa.com/');
    }
}
