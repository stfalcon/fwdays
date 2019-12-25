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
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $socialID = $response->getUsername();
        /** @var User $user */
        $user = $socialID ? $this->userManager->findUserBy([$this->getProperty($response) => $socialID]) : null;
        $email = $response->getEmail();
        if (!$user) {
            $user = $this->userManager->findUserByEmail($email);

            if (!$user || !$user instanceof UserInterface) {
                try {
                    /** @var User $user */
                    $user = $this->userManager->createUser();
                    $user->setName($response->getFirstName());
                    $user->setSurname($response->getLastName());
                    $user->setEmail($email);
                    $user->setPlainPassword(md5(uniqid()));
                    $user->setEnabled(true);
                    $request = $this->requestStack->getCurrentRequest();
                    if ($request instanceof Request) {
                        $user->setEmailLanguage($request->getLocale());
                    }
                    $errors = $this->validator->validate($user);
                    if ($errors->count() > 0) {
                        throw new \Exception('need_data');
                    }
                    $this->userManager->updateUser($user);
                } catch (\Exception $e) {
                    $needUserData = new NeedUserDataException('needUserData');
                    $responseArr = [];
                    $responseArr['first_name'] = $response->getFirstName();
                    $responseArr['last_name'] = $response->getLastName();
                    $responseArr['email'] = $email;

                    $responseArr = array_merge(
                        $responseArr,
                        ['socialID' => $socialID],
                        ['service' => $response->getResourceOwner()->getName()]
                    );
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
            $service = $response->getResourceOwner()->getName();
            $socialID = $response->getUsername();
            switch ($service) {
                case 'google':
                    $user->setGoogleID($socialID);
                    break;
                case 'facebook':
                    $user->setFacebookID($socialID);
                    break;
            }

            $this->userManager->updateUser($user);
        } else {
            $checker = new UserChecker();
            $checker->checkPreAuth($user);
        }

        return $user;
    }
}
