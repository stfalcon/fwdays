<?php

namespace Application\Bundle\UserBundle\Security;

use Application\Bundle\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Stfalcon\Bundle\EventBundle\Helper\StfalconMailerHelper;
use Symfony\Component\HttpFoundation\RequestStack;
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
     * @param array $properties
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
        //check if the user already has the corresponding social account
        if (!$user && $email) {
            //check if the user has a normal account
            $user = $this->userManager->findUserByEmail($email);

            if (!$user || !$user instanceof UserInterface) {
                //if the user does not have a normal account, set it up:
                $user = $this->userManager->createUser();
                $user->setName($response->getFirstName());
                $user->setSurname($response->getLastName());
                $user->setEmail($email);
                $user->setPlainPassword(md5(uniqid()));
                $user->setEnabled(true);

                $this->container->get('stfalcon_event.mailer_helper')->sendEasyEmail(
                    $this->container->get('translator')->trans('registration.email.subject'),
                    '@FOSUser/Registration/email.on_registration.html.twig',
                    ['user' => $user],
                    $user
                );

                $this->container->get('session')->getFlashBag()->set('fos_user_success', 'registration.flash.user_created');
            }
            //then set its corresponding social id
            $service = $response->getResourceOwner()->getName();
            switch ($service) {
                case 'google':
                    $user->setGoogleID($socialID);
                    break;
                case 'facebook':
                    $user->setFacebookID($socialID);
                    break;
            }
            $this->userManager->updateUser($user);
        } elseif ($user) {
            $checker = new UserChecker();
            $checker->checkPreAuth($user);
        } elseif (!$email) {

        }

        return $user;
    }
}
