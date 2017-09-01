<?php

namespace Application\Bundle\UserBundle\Services;

use FOS\UserBundle\Form\Handler\RegistrationFormHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class RegistrationFormWithGoogleCaptcha
 *
 * Класс для перевизначення/обертання сервіса fos_user.registration.form.handler, який викликається в registerAction
 * FOS бандла.
 * Перевизначений для додавання перевірки гугл капчі.
 *
 */
class RegistrationFormWithGoogleCaptcha extends RegistrationFormHandler
{
    private $captchaSecretKey;
    private $captchaCheckUrl;
    private $container;

    /**
     * RegistrationFormWithGoogleCaptcha constructor.
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        /**
         * викликаем рідний конструктор fos_user.registration.form.handler
         */
        parent::__construct(
            $this->container->get("fos_user.registration.form"),
            $this->container->get("request"),
            $this->container->get("fos_user.user_manager"),
            $this->container->get("fos_user.mailer"),
            $this->container->get("fos_user.util.token_generator")
        );
        $this->captchaSecretKey = $this->container->getParameter('captcha_secret_key');
        $this->captchaCheckUrl = $this->container->getParameter('captcha_check_url');
    }

    /**
     * @param boolean $confirmation
     */
    public function process($confirmation = false)
    {
        $request = $this->container->get("request");
        $captcha = $request->request->get('g-recaptcha-response');

        return $this->isGoogleCaptchaTrue($captcha) && parent::process($confirmation);
    }
    /**
     * Перевіряєм капчу
     *
     * @link https://www.google.com/recaptcha/admin#list
     *
     * @param string $captcha
     * @throws
     * @return bool
     */
    private function isGoogleCaptchaTrue($captcha)
    {
        if ('prod' !== $this->container->get("kernel")->getEnvironment()) {
            return true;
        }

        if (empty($captcha)) {
            return false;
        }

        $params = [
            'secret'  => $this->captchaSecretKey,
            'response' => $captcha,
            'remoteip' => $_SERVER['REMOTE_ADDR'],
        ];

        $response = json_decode(
            $this->container->get('buzz')->submit(
                $this->captchaCheckUrl,
                $params
            )->getContent(),
            true
        );
        if (!isset($response['success'])) {
            throw new \Exception('google captcha api response missing');
        } elseif (isset($response['error-codes'])) {
            throw new \Exception('google captcha api error: '.$response['error-codes'][0]);
        }

        return (bool) $response['success'];
    }
}
