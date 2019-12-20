<?php

namespace App\EventListener\FOSUserEvents;

use App\Traits;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * UserChangePasswordSubscriber.
 */
class UserChangePasswordSubscriber implements EventSubscriberInterface
{
    use Traits\SessionTrait;
    use Traits\RouterTrait;

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
