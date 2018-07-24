<?php

namespace Application\Bundle\UserBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use FOS\UserBundle\Model\UserInterface;

class RegistrationController extends BaseController
{
    /**
     * @return mixed|RedirectResponse|Response
     *
     * @throws \Twig_Error
     */
    public function registerAction()
    {
        $form = $this->container->get('fos_user.registration.form');
        $fromOAuth = false;
        if ($this->container->get('session')->has('social-response')) {
            $oAuthData = $this->container->get('session')->get('social-response');
            $user = new User();
            $user = $this->setUserFromOAuthResponse($user, $oAuthData);
            if ($user instanceof User) {
                $form = $this->container->get('form.factory')->create('application_user_registration', $user);
                $errors = $this->container->get('validator')->validate($user);
                foreach ($errors as $error) {
                    $form->get($error->getPropertyPath())->addError(new FormError($error->getMessage()));
                }
                $fromOAuth = true;
            }
            $this->container->get('session')->remove('social-response');
        }
        $formHandler = $this->container->get('fos_user.registration.form.handler');
        $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');

        $process = $fromOAuth ? false : $formHandler->process($confirmationEnabled);
        if ($process) {
            $user = $form->getData();

            $authUser = false;
            if ($confirmationEnabled) {
                $this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
                $route = 'fos_user_registration_check_email';
            } else {
                $authUser = true;
                $route = 'fos_user_registration_confirmed';

                $this->container->get('stfalcon_event.mailer_helper')->sendEasyEmail(
                    $this->container->get('translator')->trans('registration.email.subject'),
                    '@FOSUser/Registration/email.on_registration.html.twig',
                    ['user' => $user],
                    $user
                );
            }

            $request = $this->container->get('request_stack')->getCurrentRequest();
            $query = $request->getQueryString();
            $this->setFlash('fos_user_success', 'registration.flash.user_created');
            $url = $this->container->get('router')->generate($route);
            if ($query) {
                $url .= '?'.$query;
            }
            $response = new RedirectResponse($url);

            if ($authUser) {
                $this->authenticateUser($user, $response);
            }

            return $response;
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.'.$this->getEngine(), array(
            'regForm' => $form->createView(),
        ));
    }

    /**
     * Receive the confirmation token from user email provider, login the user.
     */
    public function confirmAction($token)
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $user->setLastLogin(new \DateTime());

        $this->container->get('fos_user.user_manager')->updateUser($user);
        $response = new RedirectResponse($this->container->get('router')->generate('events'));
        $this->authenticateUser($user, $response);

        return $this->container->get('user.handler.login_handler')->processAuthSuccess($request, $user);
    }

    /**
     * Tell the user his account is now confirmed.
     */
    public function confirmedAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->container->get('user.handler.login_handler')->processAuthSuccess($request, $user);
    }

    /**
     * @param User  $user
     * @param array $response
     *
     * @return User
     */
    private function setUserFromOAuthResponse(User $user, array $response)
    {
        $user->setName($response['first_name']);
        $user->setSurname($response['last_name']);
        $user->setEmail($response['email']);

        $socialID = $response['socialID'];
        switch ($response['service']) {
            case 'google':
                $user->setGoogleID($socialID);
                break;
            case 'facebook':
                $user->setFacebookID($socialID);
                break;
        }

        return $user;
    }
}
