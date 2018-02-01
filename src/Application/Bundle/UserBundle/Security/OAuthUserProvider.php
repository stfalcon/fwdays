<?php

namespace Application\Bundle\UserBundle\Security;

use Application\Bundle\DefaultBundle\Exception\NeedUserDataException;
use Application\Bundle\UserBundle\Entity\User;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class OAuthUserProvider.
 */
class OAuthUserProvider extends BaseClass
{
    private $container;

    /**
     * OAuthUserProvider constructor.
     *
     * @param UserManagerInterface $userManager
     * @param array                $properties
     * @param $container
     */
    public function __construct(UserManagerInterface $userManager, array $properties, $container)
    {
        parent::__construct($userManager, $properties);

        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $socialID = $response->getUsername();
        /** @var User $user */
        $user = $this->userManager->findUserBy([$this->getProperty($response) => $socialID]);
        $email = $response->getEmail();
        if (!$user) {
            $user = $this->userManager->findUserByEmail($email);

            if (!$user || !$user instanceof UserInterface) {
                try {
                    $user = $this->userManager->createUser();
                    $user->setName($response->getFirstName());
                    $user->setSurname($response->getLastName());
                    $user->setEmail($email);
                    $user->setPlainPassword(md5(uniqid()));
                    $user->setEnabled(true);
                    $this->userManager->updateUser($user);
                } catch (NotNullConstraintViolationException $e) {
                    $needUserData = new NeedUserDataException('needUserData');
                    $responseArr = $response->getResponse();
                    $responseArr = array_merge(
                        $responseArr,
                        ['socialID' => $socialID],
                        ['service' => $response->getResourceOwner()->getName()]
                    );
                    $needUserData->setResponse($responseArr);
                    throw $needUserData;
                }

                $this->container->get('stfalcon_event.mailer_helper')->sendEasyEmail(
                    $this->container->get('translator')->trans('registration.email.subject'),
                    '@FOSUser/Registration/email.on_registration.html.twig',
                    ['user' => $user],
                    $user
                );

                $this->container->get('session')->getFlashBag()->set('fos_user_success', 'registration.flash.user_created');
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
