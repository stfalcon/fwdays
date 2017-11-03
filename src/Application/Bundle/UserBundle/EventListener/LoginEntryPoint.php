<?php

namespace Application\Bundle\UserBundle\EventListener;

use JMS\I18nRoutingBundle\Router\I18nRouter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class LoginEntryPoint.
 */
class LoginEntryPoint implements AuthenticationEntryPointInterface
{
    /** @var I18nRouter */
    protected $router;

    /**
     * LoginEntryPoint constructor.
     *
     * @param I18nRouter $router
     */
    public function __construct($router)
    {
        $this->router = $router;
    }

    /**
     * @param Request                      $request
     * @param AuthenticationException|null $authException
     *
     * @return JsonResponse|RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        if ($request->isXmlHttpRequest()) {
            $request->getSession()->set('request_params', $request->attributes->all());

            return new JsonResponse('', 401);
        }

        return new RedirectResponse($this->router->generate('fos_user_security_login'));
    }
}
