<?php

namespace App\EventListener;

use App\Traits\RouterTrait;
use JMS\I18nRoutingBundle\Router\I18nRouter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * LoginEntryPoint.
 */
class LoginEntryPoint implements AuthenticationEntryPointInterface
{
    use RouterTrait;

    /**
     * @param Request                      $request
     * @param AuthenticationException|null $authException
     *
     * @return JsonResponse|RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        if ($request->isXmlHttpRequest()) {
            $session = $request->getSession();
            if ($session instanceof SessionInterface) {
                $session->set('request_params', $request->attributes->all());
            }

            return new JsonResponse('', 401);
        }

        return new RedirectResponse($this->router->generate('fos_user_security_login'));
    }
}
