<?php

namespace App\EventListener\Auth;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

/**
 * UserAuthenticationSubscriber.
 */
class UserAuthenticationSubscriber implements EventSubscriberInterface
{
    public const SESSION_REMEMBER_ME_KEY = 'remember_me_state';

    private $session;
    private $requestStack;

    /**
     * @param Session      $session
     * @param RequestStack $requestStack
     */
    public function __construct(Session $session, RequestStack $requestStack)
    {
        $this->session = $session;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationEvents::AUTHENTICATION_FAILURE => ['onAuthFail'],
        ];
    }

    /**
     * Save "remember me" status for output to another authorization page
     *
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthFail(AuthenticationFailureEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request instanceof Request) {
            $value = $request->request->has('_remember_me') ? 'checked' : '';
            $this->session->set(self::SESSION_REMEMBER_ME_KEY, $value);
        }
    }
}
