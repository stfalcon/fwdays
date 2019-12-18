<?php

namespace App\EventListener\FOSUserEvents;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * UserRegistrationSubscriber.
 */
class UserRegistrationSubscriber implements EventSubscriberInterface
{
    private $session;
    private $router;
    private $request;

    /**
     * @param Session      $session
     * @param Router       $router
     * @param RequestStack $requestStack
     */
    public function __construct(Session $session, Router $router, RequestStack $requestStack)
    {
        $this->session = $session;
        $this->router = $router;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FOSUserEvents::REGISTRATION_SUCCESS => ['onRegistrationSuccess'],
            FOSUserEvents::REGISTRATION_COMPLETED => ['onRegistrationCompleted'],
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onRegistrationSuccess(FormEvent $event): void
    {
        $query = $this->request->getQueryString();
        $url = $this->router->generate('fos_user_registration_confirmed');
        if ($query) {
            $url .= '?'.$query;
        }
        $response = new RedirectResponse($url);

        $event->setResponse($response);
    }

    /**
     * onProfileEditCompleted.
     */
    public function onRegistrationCompleted(): void
    {
        $this->session->getFlashBag()->set('fos_user_success', 'registration.flash.user_created');
    }
}
