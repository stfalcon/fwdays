<?php

namespace App\EventListener;

use App\Exception\NeedUserDataException;
use App\Traits;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * ExceptionListener.
 */
class ExceptionListener implements EventSubscriberInterface
{
    use Traits\SessionTrait;
    use Traits\RouterTrait;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): \Generator
    {
        yield KernelEvents::EXCEPTION => 'onKernelException';
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof NeedUserDataException) {
            $oAuthResponse = $exception->getResponse();
            $this->session->set('social-response', $oAuthResponse);
            $response = new RedirectResponse($this->router->generate('fos_user_registration_register'));

            $event->setResponse($response);
        }
    }
}
