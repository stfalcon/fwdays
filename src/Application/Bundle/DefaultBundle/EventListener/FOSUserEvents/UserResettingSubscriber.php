<?php

namespace Application\Bundle\DefaultBundle\EventListener\FOSUserEvents;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * UserResettingSubscriber.
 */
class UserResettingSubscriber implements EventSubscriberInterface
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
            FOSUserEvents::RESETTING_RESET_COMPLETED => ['onResittingCompleted'],
            FOSUserEvents::RESETTING_RESET_SUCCESS => ['onResettingSuccess'],
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onResettingSuccess(FormEvent $event): void
    {
        $event->setResponse(new RedirectResponse($this->router->generate('homepage')));
    }

    /**
     * onProfileEditCompleted.
     */
    public function onResittingCompleted(): void
    {
        $this->session->getFlashBag()->set('fos_user_success', 'resetting.flash.success');
    }
}
