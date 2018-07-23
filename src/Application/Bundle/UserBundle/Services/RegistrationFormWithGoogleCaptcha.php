<?php

namespace Application\Bundle\UserBundle\Services;

use FOS\UserBundle\Form\Handler\RegistrationFormHandler;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class RegistrationFormWithGoogleCaptcha.
 *
 * Класс для перевизначення/обертання сервіса fos_user.registration.form.handler, який викликається в registerAction
 * FOS бандла.
 * Перевизначений для додавання перевірки гугл капчі.
 */
class RegistrationFormWithGoogleCaptcha extends RegistrationFormHandler
{
    protected $captchaSecretKey;
    protected $captchaCheckUrl = 'https://www.google.com/recaptcha/api/siteverify';

    protected $request;

    /** @var Logger */
    protected $logger;

    protected $buzz;

    /** @var string */
    protected $environment;

    /**
     * RegistrationFormWithGoogleCaptcha constructor.
     *
     * @param $regForm
     * @param RequestStack $requestStack
     * @param $useManager
     * @param $mailer
     * @param $tokenGenerator
     * @param Logger $logger
     * @param string $captchaSecretKey
     * @param $buzz
     * @param string $environment
     */
    public function __construct($regForm, $requestStack, $useManager, $mailer, $tokenGenerator, $logger, $captchaSecretKey, $buzz, $environment)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->logger = $logger;
        $this->captchaSecretKey = $captchaSecretKey;
        $this->buzz = $buzz;
        $this->environment = $environment;

        parent::__construct($regForm, $this->request, $useManager, $mailer, $tokenGenerator);
    }

    /**
     * @param bool $confirmation
     *
     * @return bool
     */
    public function process($confirmation = false)
    {
        $captcha = $this->request->request->get('g-recaptcha-response');

        return $this->isGoogleCaptchaTrue($captcha) && parent::process($confirmation);
    }

    /**
     * Перевіряєм капчу.
     *
     * @see https://www.google.com/recaptcha/admin#list
     *
     * @param string $captcha
     *
     * @return bool
     */
    private function isGoogleCaptchaTrue($captcha)
    {
        if ('stag' === $this->environment) {
            return true;
        }

        if (empty($captcha)) {
            return false;
        }

        $params = [
            'secret' => $this->captchaSecretKey,
            'response' => $captcha,
            'remoteip' => $_SERVER['REMOTE_ADDR'],
        ];

        $response = json_decode(
            $this->buzz->submit(
                $this->captchaCheckUrl,
                $params
            )->getContent(),
            true
        );
        if (!isset($response['success'])) {
            $this->logger->addError('google captcha api response missing');

            return false;
        }
        if (isset($response['error-codes'])) {
            $this->logger->addError('google captcha api error: '.$response['error-codes'][0]);

            return false;
        }

        return (bool) $response['success'];
    }
}
