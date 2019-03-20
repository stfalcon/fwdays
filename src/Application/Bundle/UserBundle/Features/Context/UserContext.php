<?php

namespace Application\Bundle\UserBundle\Features\Context;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Context\BehatContext;

/**
 * Feature context for ApplicationUserBundle.
 */
class UserContext extends BehatContext
{
    /**
     * @var \Behat\MinkExtension\Context\MinkContext
     */
    private $minkContext;

    /**
     * Constructor.
     *
     * @param \Behat\MinkExtension\Context\MinkContext $minkContext
     */
    public function __construct($minkContext)
    {
        $this->minkContext = $minkContext;
    }

    /**
     * Вход в учетную запись по логину и паролю.
     *
     * В этом методе заполняются поля: логин и пароль, после чего нажимается кнопка "Вход"
     *
     * @param string $username Имя пользователя
     * @param string $password Пароль учетной записи
     *
     * @Given /^я вхожу в учетную запись с именем "([^"]*)" и паролем "([^"]*)"$/
     */
    public function login($username, $password)
    {
        $this->minkContext->visit('/login');
        $this->minkContext->fillField('username', $username);
        $this->minkContext->fillField('password', $password);
        $this->minkContext->pressButton('Войти');
    }
}
