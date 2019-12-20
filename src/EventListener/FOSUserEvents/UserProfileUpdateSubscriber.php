<?php

namespace App\EventListener\FOSUserEvents;

use App\Traits;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * UserProfileUpdateSubscriber.
 */
class UserProfileUpdateSubscriber implements EventSubscriberInterface
{
    use Traits\SessionTrait;
    use Traits\RouterTrait;

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
