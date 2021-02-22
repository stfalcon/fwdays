<?php

namespace App\Security;

use App\Entity\User;
use App\Exception\NeedUserDataException;
use App\Helper\MailerHelper;
use App\Traits\RequestStackTrait;
use App\Traits\SessionTrait;
use App\Traits\TranslatorTrait;
use App\Traits\ValidatorTrait;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * OAuthUserProvider.
 */
class OAuthUserProvider extends FOSUBUserProvider
{
    use RequestStackTrait;
    use ValidatorTrait;
    use SessionTrait;
    use TranslatorTrait;

    private $mailerHelper;

    /**
     * @param UserManagerInterface $userManager
     * @param array                $oAuthUserProviderProperties
     * @param MailerHelper         $mailerHelper
     */
    public function __construct(UserManagerInterface $userManager, array $oAuthUserProviderProperties, MailerHelper $mailerHelper)
    {
        parent::__construct($userManager, $oAuthUserProviderProperties);
        $this->mailerHelper = $mailerHelper;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NeedUserDataException
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $socialID = $response->getUsername();
        $service = $response->getResourceOwner()->getName();

        /** @var User|null $user */
        $user = $socialID ? $this->userManager->findUserBy([$this->getProperty($response) => $socialID]) : null;
        if (!$user instanceof User) {
            $email = $response->getEmail();
            /** @var User|null $user */
            $user = $this->userManager->findUserByEmail($email);

            if (!$user || !$user instanceof UserInterface) {
                try {
                    /** @var User $user */
                    $user = $this->userManager->createUser();
                    $user->setName($response->getFirstName())
                        ->setSurname($response->getLastName())
                        ->setEmail($email)
                        ->setPlainPassword(md5(uniqid()))
                        ->setEnabled(true)
                    ;
                    $request = $this->requestStack->getCurrentRequest();
                    if ($request instanceof Request) {
                        $user->setEmailLanguage($request->getLocale());
                    }
                    $errors = $this->validator->validate($user, null, ['registration']);
                    if ($errors->count() > 0) {
                        throw new NeedUserDataException('need_data');
                    }
                    $this->userManager->updateUser($user);
                } catch (NeedUserDataException $needUserData) {
                    $responseArr = [
                        'first_name' => $response->getFirstName(),
                        'last_name' => $response->getLastName(),
                        'email' => $email,
                        'socialID' => $socialID,
                        'service' => $service,
                    ];

                    $needUserData->setResponse($responseArr);
                    $this->session->getFlashBag()->set('fos_user_success', 'registration.flash.user_need_data');
                    throw $needUserData;
                }

                $this->mailerHelper->sendEasyEmail(
                    $this->translator->trans('registration.email.subject'),
                    '@FOSUser/Registration/email.on_registration.html.twig',
                    ['user' => $user],
                    $user
                );

                $this->session->getFlashBag()->set('fos_user_success', 'registration.flash.user_created');
            }

            if ('google' === $service) {
                $user->setGoogleID($socialID);
            } elseif ('facebook' === $service) {
                $user->setFacebookID($socialID);
            }

            $this->userManager->updateUser($user);
        } else {
            $checker = new UserChecker();
            $checker->checkPreAuth($user);

            $this->session->getFlashBag()->set('app_social_user_login', \sprintf('%s_login_event', $service));
        }

        return $user;
    }
}
