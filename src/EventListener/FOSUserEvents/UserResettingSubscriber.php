<?php

namespace App\EventListener\FOSUserEvents;

use App\Traits;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * UserResettingSubscriber.
 */
class UserResettingSubscriber implements EventSubscriberInterface
{
    use Traits\SessionTrait;
    use Traits\RouterTrait;

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
