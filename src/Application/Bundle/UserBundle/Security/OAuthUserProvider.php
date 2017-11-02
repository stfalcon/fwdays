<?php

namespace Application\Bundle\UserBundle\Security;

use Application\Bundle\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class OAuthUserProvider.
 */
class OAuthUserProvider extends BaseClass
{

    protected $requestStack;

    public function __construct(UserManagerInterface $userManager, array $properties, RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        parent::__construct($userManager, $properties);
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
        if (!$user) {
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
        } else {
            //and then login the user
            $checker = new UserChecker();
            $checker->checkPreAuth($user);
        }
        $request = $this->requestStack->getCurrentRequest();
        $request->getSession()->set('login_by_provider', true);

        return $user;
    }
}
