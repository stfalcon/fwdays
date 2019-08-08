<?php

namespace Application\Bundle\DefaultBundle\EventListener\FOSUserEvents;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * UserChangePasswordSubscriber.
 */
class UserChangePasswordSubscriber implements EventSubscriberInterface
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
            FOSUserEvents::CHANGE_PASSWORD_COMPLETED => ['onChangePasswordCompleted'],
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => ['onChangePasswordSuccess'],
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onChangePasswordSuccess(FormEvent $event): void
    {
        $event->setResponse(new RedirectResponse($this->router->generate('homepage')));
    }

    /**
     * onProfileEditCompleted.
     */
    public function onChangePasswordCompleted(): void
    {
        $this->session->getFlashBag()->set('fos_user_success', 'change_password.flash.success');
    }
}
