<?php

namespace Application\Bundle\UserBundle\Services;

use FOS\UserBundle\Form\Handler\RegistrationFormHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class RegistrationFormWithGoogleCaptcha.
 *
 * Класс для перевизначення/обертання сервіса fos_user.registration.form.handler, який викликається в registerAction
 * FOS бандла.
 * Перевизначений для додавання перевірки гугл капчі.
 */
class RegistrationFormWithGoogleCaptcha extends RegistrationFormHandler
{
    private $captchaSecretKey;
    private $captchaCheckUrl = 'https://www.google.com/recaptcha/api/siteverify';
    private $container;

    /**
     * RegistrationFormWithGoogleCaptcha constructor.
     *
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        /*
         * викликаем рідний конструктор fos_user.registration.form.handler
         */
        parent::__construct(
            $this->container->get('fos_user.registration.form'),
            $this->container->get('request_stack')->getCurrentRequest(),
            $this->container->get('fos_user.user_manager'),
            $this->container->get('fos_user.mailer'),
            $this->container->get('fos_user.util.token_generator')
        );
        $this->captchaSecretKey = $this->container->getParameter('google_captcha_secret_key');
    }

    /**
     * @param bool $confirmation
     *
     * @return bool
     */
    public function process($confirmation = false)
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $captcha = $request->request->get('g-recaptcha-response');

        return $this->isGoogleCaptchaTrue($captcha) && parent::process($confirmation);
    }

    /**
     * Перевіряєм капчу.
     *
     * @see https://www.google.com/recaptcha/admin#list
     *
     * @param string $captcha
     *
     * @throws
     *
     * @return bool
     */
    private function isGoogleCaptchaTrue($captcha)
    {
        if (empty($captcha)) {
            return false;
        }

        $params = [
            'secret' => $this->captchaSecretKey,
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
