<?php

namespace App\EventListener;

use App\Exception\NeedUserDataException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\Router;

/**
 * Class ExceptionListener.
 */
class ExceptionListener
{
    /** @var Router */
    private $router;

    /** @var Session */
    private $session;

    /**
     * @param Session $session
     * @param Router  $router
     */
    public function __construct($session, $router)
    {
        $this->session = $session;
        $this->router = $router;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof NeedUserDataException) {
            $oAuthResponse = $exception->getResponse();
            $this->session->set('social-response', $oAuthResponse);
            $response = new RedirectResponse($this->router->generate('fos_user_registration_register'));

            $event->setResponse($response);
        }
    }
}
