<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\User\UseRegistrationCompletedEvent;
use App\Event\User\UseRegistrationSuccessEvent;
use App\Handler\LoginHandler;
use App\Traits;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Security\LoginManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

/**
 * RegistrationController.
 */
class RegistrationController extends BaseController
{
    use Traits\SessionTrait;
    use Traits\ValidatorTrait;
    use Traits\EventDispatcherTrait;
    use Traits\LoggerTrait;
    use Traits\HttpClientTrait;
    use Traits\MailerHelperTrait;

    private $captchaCheckUrl = 'https://www.google.com/recaptcha/api/siteverify';

    private $formFactory;
    private $userManager;
    private $loginManager;
    private $loginHandler;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param FactoryInterface         $formFactory
     * @param UserManagerInterface     $userManager
     * @param TokenStorageInterface    $tokenStorage
     * @param LoginManager             $loginManager
     * @param LoginHandler             $loginHandler
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, FactoryInterface $formFactory, UserManagerInterface $userManager, TokenStorageInterface $tokenStorage, LoginManager $loginManager, LoginHandler $loginHandler)
    {
        parent::__construct($eventDispatcher, $formFactory, $userManager, $tokenStorage);

        $this->formFactory = $formFactory;
        $this->userManager = $userManager;
        $this->loginManager = $loginManager;
        $this->loginHandler = $loginHandler;
    }

    /**
     * @param Request $request
     *
     * @return mixed|RedirectResponse|Response
     */
    public function registerAction(Request $request)
    {
        $user = $this->userManager->createUser();
        $user->setEnabled(true);

        $form = $this->formFactory->createForm();
        $form->setData($user);

        $fromOAuth = false;
        if ($this->session->has('social-response')) {
            $oAuthData = $this->session->get('social-response');
            if ($user instanceof User) {
                $this->setUserFromOAuthResponse($user, $oAuthData);
                $form->setData($user);
                $errors = $this->validator->validate($user);
                foreach ($errors as $error) {
                    $form->get($error->getPropertyPath())->addError(new FormError($error->getMessage()));
                }
                $fromOAuth = true;
            }
            $this->session->remove('social-response');
        } else {
            $form->handleRequest($request);
        }
        $captcha = $request->get('g-recaptcha-response');
        $process = $fromOAuth ? false : $this->isGoogleCaptchaTrue($captcha);

        if ($process) {
            if ($form->isSubmitted() && $form->isValid()) {
                $event = new UseRegistrationSuccessEvent($form, $request);
                $this->eventDispatcher->dispatch($event);

                $user = $form->getData();
                $this->userManager->updateUser($user);

                $this->mailerHelper->sendEasyEmail(
                    $this->container->get('translator')->trans('registration.email.subject'),
                    '@FOSUser/Registration/email.on_registration.html.twig',
                    ['user' => $user],
                    $user
                );

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('fos_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }
                $this->eventDispatcher->dispatch(new UseRegistrationCompletedEvent($user, $request, $response));
                $this->authenticateUser($user, $response);

                return $response;
            }
        }

        return $this->render('@FOSUser/Registration/register.html.twig', [
            'regForm' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param string  $token
     *
     * @return RedirectResponse
     */
    public function confirmAction(Request $request, $token): RedirectResponse
    {
        $user = $this->userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $user->setLastLogin(new \DateTime());

        $this->userManager->updateUser($user);
        $response = new RedirectResponse($this->container->get('router')->generate('events'));
        $this->authenticateUser($user, $response);

        return $this->loginHandler->processAuthSuccess($request, $user);
    }

    /**
     * Tell the user his account is now confirmed.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function confirmedAction(Request $request): RedirectResponse
    {
        $user = $this->getUser();
        if (!\is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->loginHandler->processAuthSuccess($request, $user);
    }

    /**
     * @param User  $user
     * @param array $response
     */
    private function setUserFromOAuthResponse(User $user, array $response): void
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
    }

    /**
     * Перевіряєм капчу.
     *
     * @see https://www.google.com/recaptcha/admin#list
     *
     * @param string|null $captcha
     *
     * @return bool
     */
    private function isGoogleCaptchaTrue(?string $captcha): bool
    {
        if (null === $captcha) {
            return false;
        }

        $content = $this->httpClient->request(
            Request::METHOD_POST,
            $this->captchaCheckUrl,
            ['body' => [
                    'secret' => $this->getParameter('google_captcha_secret_key'),
                    'response' => $captcha,
                    'remoteip' => $_SERVER['REMOTE_ADDR'],
                ],
            ]
        )->getContent();

        $response = \json_decode($content, true);
        if (!isset($response['success'])) {
            $this->logger->addError('google captcha api response missing');

            return false;
        }
        if (isset($response['error-codes'])) {
            $this->logger->addError('google captcha api error: '.$response['error-codes'][0]);

            return false;
        }

        return (bool) $response['success'];
    }

    /**
     * @param UserInterface $user
     * @param Response      $response
     */
    private function authenticateUser(UserInterface $user, Response $response): void
    {
        try {
            $this->loginManager->loginUser(
                $this->getParameter('fos_user.firewall_name'),
                $user,
                $response
            );
        } catch (AccountStatusException $ex) {
        }
    }
}
