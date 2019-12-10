<?php

/**
 * UserCest.
 */
class UserCest
{
    private const PROFILE_FIELDS = [
        '#fos_user_profile_form_name' => 'userName',
        '#fos_user_profile_form_surname' => 'userSurname',
        '#fos_user_profile_form_email' => 'user@gmail.com',
        '#fos_user_profile_form_phone' => '+380681234567',
        '#fos_user_profile_form_country' => 'Ukraine',
        '#fos_user_profile_form_city' => 'City',
        '#fos_user_profile_form_company' => 'Company',
        '#fos_user_profile_form_post' => 'Post',
    ];

    private const SIGN_IN_FIELDS = [
        '#user_email_modal-signup' => 'user@gmail.com',
        '#user_password_modal-signup' => 'new_password',
    ];

    private const CHANGE_PASSWORD_FIELDS = [
        '#fos_user_change_password_form_current_password' => 'qwerty',
        '#fos_user_change_password_form_plainPassword_first' => 'new_password',
        '#fos_user_change_password_form_plainPassword_second' => 'new_password',
    ];

    private const CABINET_PAGE_TEXTS = [
        'My events',
        'Account',
        'Sign out',
        'Invite your friends and collect bonuses!',
        'Get 100 UAH per ticket purchased by your link.',
        'your referral link',
        'My past events',
    ];

    private $newLogin = false;

    /**
     * @param AcceptanceTester $I
     */
    public function loginModal(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        static::iAmNotSigned($I);

        static::seeAndClick($I, '.header__auth--sign-in');

        foreach (self::SIGN_IN_FIELDS as $field => $value) {
            $I->seeElement($field);
            if ($this->newLogin) {
                $I->fillField($field, $value);
            }
        }

        if (!$this->newLogin) {
            static::fillLoginFieldsAdmin($I);
        }

        static::seeAndClick($I, '#login-form-modal-signup button[type=submit]');

        $I->waitForText('ACCOUNT');
        $I->seeCurrentUrlEquals('/app_test.php/en/');
        static::iAmSigned($I);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @depends loginModal
     */
    public function changeProfile(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        static::iAmSigned($I);
        $I->amOnPage('/cabinet');

        static::seeAndClick($I, '.cabinet-head__link');

        $I->waitForText('User info');
        foreach (self::PROFILE_FIELDS as $field => $value) {
            $I->seeElement($field);
            $I->fillField($field, $value);
        }
        $I->seeElement('#profile-check', ['checked' => true]);
        $I->click('#profile-check');

        static::seeAndClick($I, 'form button[type=submit]');

        $I->waitForText('Your profile updated');

        // check update

        $I->amOnPage('/cabinet');

        static::seeAndClick($I, '.cabinet-head__link');

        $I->waitForText('User info');
        foreach (self::PROFILE_FIELDS as $field => $value) {
            $I->seeElement($field);
        }
        $I->seeElement('#profile-check', ['checked' => false]);

        foreach (self::PROFILE_FIELDS as $field => $value) {
            $I->seeInField($field, $value);
        }
    }

    /**
     * @param AcceptanceTester $I
     *
     * @depends changeProfile
     */
    public function changePassword(AcceptanceTester $I): void
    {
        $I->amOnPage('/cabinet');

        static::seeAndClick($I, '.cabinet-head__link');
        $I->waitForText('User info');

        static::seeAndClick($I, 'a[href="/app_test.php/en/change-password"]');
        $I->waitForText('Change password');

        $I->seeCurrentUrlEquals('/app_test.php/en/change-password');

        foreach (self::CHANGE_PASSWORD_FIELDS as $field => $value) {
            $I->seeElement($field);
            $I->fillField($field, $field.$value);
        }

        static::seeAndClick($I, 'form button[type=submit]');
        $I->see('The entered password is invalid.');
        $I->see('The entered passwords don\'t match.');

        foreach (self::CHANGE_PASSWORD_FIELDS as $field => $value) {
            $I->fillField($field, $value);
        }
        static::seeAndClick($I, 'form button[type=submit]');

        $I->waitForText('The password has been changed.');
        $I->seeCurrentUrlEquals('/app_test.php/en/');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @depends changePassword
     */
    public function checkNewPassword(AcceptanceTester $I)
    {
        $I->amOnPage('/logout');
        $this->newLogin = true;
        $this->loginModal($I);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @depends checkNewPassword
     */
    public function forgotPassword(AcceptanceTester $I)
    {
        $I->amOnPage('/change-password');

        static::seeAndClick($I, 'a[href="/app_test.php/en/resetting/check-email"]');
        $I->waitForText('Forgot password?');

        $I->seeElement('#forgot_user_email');
        $I->fillField('#forgot_user_email', 'user@gmail.com');

        static::seeAndClick($I, 'form button[type=submit]');
        $I->waitForText('Reset password');

        $I->see('An email has been sent to user@gmail.com. It contains a link you have to click on to reset your password.');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @depends loginModal
     */
    public function cabinetPage(AcceptanceTester $I)
    {
        $I->wantTo('Check User Cabinet Page.');
        $I->amOnPage('/');
        static::iAmSigned($I);
        $I->amOnPage('/cabinet');

        foreach (self::CABINET_PAGE_TEXTS as $page) {
            $I->see($page);
        }

        $I->seeElement('#ref-input');
        $I->seeElement('#share-ref__facebook');
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
