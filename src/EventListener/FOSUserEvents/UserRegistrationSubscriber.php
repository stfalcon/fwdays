<?php

namespace App\EventListener\FOSUserEvents;

use App\Event\User\UseRegistrationCompletedEvent;
use App\Event\User\UseRegistrationSuccessEvent;
use App\Traits;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * UserRegistrationSubscriber.
 */
class UserRegistrationSubscriber implements EventSubscriberInterface
{
    use Traits\SessionTrait;
    use Traits\RouterTrait;
    use Traits\RequestStackTrait;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UseRegistrationSuccessEvent::class => ['onRegistrationSuccess'],
            UseRegistrationCompletedEvent::class => ['onRegistrationCompleted'],
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onRegistrationSuccess(FormEvent $event): void
    {
        $query = $this->requestStack->getCurrentRequest()->getQueryString();
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
