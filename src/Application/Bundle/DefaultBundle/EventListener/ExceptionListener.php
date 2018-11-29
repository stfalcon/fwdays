<?php

namespace Application\Bundle\DefaultBundle\EventListener;

use Application\Bundle\DefaultBundle\Exception\NeedUserDataException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\Router;

/**
 * Class ExceptionListener.
 */
class ExceptionListener
{
    /**
     * @var Router
     */
    protected $router;

    /** @var Session */
    protected $session;

    /**
     * ExceptionListener constructor.
     *
     * @param Session $session
     */
    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param Router $router
     */
    public function setRouter($router)
    {
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
