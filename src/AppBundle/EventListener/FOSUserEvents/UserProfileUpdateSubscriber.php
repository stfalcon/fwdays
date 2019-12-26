<?php

namespace App\EventListener\FOSUserEvents;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * UserProfileUpdateSubscriber.
 */
class UserProfileUpdateSubscriber implements EventSubscriberInterface
{
    private $session;
    private $router;

    /**
     * @param Session $session
     * @param Router  $router
     */
    public function __construct(Session $session, Router $router)
    {
        $this->session = $session;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FOSUserEvents::PROFILE_EDIT_SUCCESS => ['onProfileEditSuccess'],
            FOSUserEvents::PROFILE_EDIT_COMPLETED => ['onProfileEditCompleted'],
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onProfileEditSuccess(FormEvent $event): void
    {
        $event->setResponse(new RedirectResponse($this->router->generate('cabinet')));
    }

    /**
     * onProfileEditCompleted.
     */
    public function onProfileEditCompleted(): void
    {
        $this->session->getFlashBag()->set('fos_user_success', 'profile.flash.updated');
    }
}
