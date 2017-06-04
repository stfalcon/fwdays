<?php

namespace Application\Bundle\UserBundle\Controller;

use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;

class RegistrationController extends BaseController
{
    /**
     * Receive the confirmation token from user email provider, login the user
     */
    public function confirmAction($token)
    {
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $user->setLastLogin(new \DateTime());

        $this->container->get('fos_user.user_manager')->updateUser($user);

        $session = $this->container->get('session');
        $session->set('just_registered', true);

        $response = new RedirectResponse($this->container->get('router')->generate('events'));

        $this->authenticateUser($user, $response);

        // Убрал согласно тикету #24389
//        $activeEvents = $this->container->get('doctrine')->getManager()
//            ->getRepository('StfalconEventBundle:Event')
//            ->findBy(array('active' => true ));
//
//        // Подписываем пользователя на все активные евенты
//        $em = $this->container->get('doctrine')->getManagerForClass('StfalconEventBundle:Ticket');
//        foreach ($activeEvents as $activeEvent) {
//            $ticket = new Ticket();
//            $ticket->setEvent($activeEvent);
//            $ticket->setUser($user);
//            $ticket->setAmount($activeEvent->getCost());
//            $ticket->setAmountWithoutDiscount($activeEvent->getCost());
//            $em->persist($ticket);
//            $em->flush();
//        }

        return $response;
    }

    public function registerAction()
    {
        $form = $this->container->get('fos_user.registration.form');
        $formHandler = $this->container->get('fos_user.registration.form.handler');
        $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');

        $request = Request::createFromGlobals();
        $captcha = $request->request->get('g-recaptcha-response');

        $process = $this->getGoogleCaptchaCheck($captcha) && $formHandler->process($confirmationEnabled);

        if ($process) {
            $user = $form->getData();

            $authUser = false;
            if ($confirmationEnabled) {
                $this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
                $route = 'fos_user_registration_check_email';
            } else {
                $authUser = true;
                $route = 'fos_user_registration_confirmed';
            }

            $this->setFlash('fos_user_success', 'registration.flash.user_created');
            $url = $this->container->get('router')->generate($route);
            $response = new RedirectResponse($url);

            if ($authUser) {
                $this->authenticateUser($user, $response);
            }

            return $response;
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.'.$this->getEngine(), array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Перевіряєм капчу
     *
     * @link https://www.google.com/recaptcha/admin#list
     *
     * @param string $captcha
     * @throws
     * @return bool
     */
    private function getGoogleCaptchaCheck($captcha)
    {
        if (empty($captcha)) {

            return false;
        }

        $params = [
            'secret'  => $this->container->getParameter('captcha_secret_key'),
            'response' => $captcha,
            'remoteip' => $_SERVER['REMOTE_ADDR'],
        ];

        $response = json_decode(
            $this->container->get('buzz')->submit(
                $this->container->getParameter('captcha_check_url'),
                $params
            )->getContent(),
            true
        );
        if (!isset($response['success'])) {

            throw new \Exception('google captcha api response missing');
        } elseif (isset($response['error-codes'])) {

            throw new \Exception('google captcha api error: '.$response['error-codes']);
        }

        return (bool) $response['success'];
    }
}
