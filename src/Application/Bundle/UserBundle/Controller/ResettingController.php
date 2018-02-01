<?php

namespace Application\Bundle\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Controller\ResettingController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Controller managing the resetting of the password.
 */
class ResettingController extends BaseController
{
    /**
     * Request reset user password: show form.
     *
     * @return Response
     */
    public function requestAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $params = $request->query->all();
        if ($params) {
            return $this->container->get('templating')->renderResponse('FOSUserBundle:Resetting:request.html.'.$this->getEngine(), $params);
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Resetting:request.html.'.$this->getEngine());
    }

    /**
     * Request reset user password: submit form and send email.
     *
     * @return Response
     */
    public function sendEmailAction()
    {
        $username = $this->container->get('request')->request->get('username');

        /** @var $user UserInterface */
        $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);

        if (null === $user) {
            return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting_request', ['invalid_username' => $username]));
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return new RedirectResponse($this->container->get('router')->generate('password_already_requested'));
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $this->container->get('session')->set(static::SESSION_EMAIL, $this->getObfuscatedEmail($user));
        $this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);

        $url = $this->container->get('router')->generate('fos_user_resetting_reset', ['token' => $user->getConfirmationToken()], true);
        $this->container->get('stfalcon_event.mailer_helper')->sendEasyEmail(
            $this->container->get('translator')->trans(
                'resetting.email.subject',
                ['%username%' => $user->getUsername(), '%confirmationUrl%' => $user],
                'FOSUserBundle'
            ),
            '@FOSUser/Resetting/email.html.twig',
            ['user' => $user, 'confirmationUrl' => $url],
            $user
        );

        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);

        return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting_check_email'));
    }

    /**
     * @Route("/password-already-requested", name="password_already_requested")
     *
     * @return Response
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function passwordAlreadyRequestedAction()
    {
        $response = new Response();
        $response->setContent($this->container->get('twig')->render('FOSUserBundle:Resetting:passwordAlreadyRequested.html.twig'));

        return $response;
    }
}
