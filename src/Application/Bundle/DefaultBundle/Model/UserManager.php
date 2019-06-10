<?php

namespace Application\Bundle\DefaultBundle\Model;

use Application\Bundle\DefaultBundle\Entity\User;
use Application\Bundle\DefaultBundle\Helper\StfalconMailerHelper;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Doctrine\UserManager as FosUserManager;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UserManager.
 */
class UserManager extends FosUserManager
{
    private $validator;
    private $mailHelper;

    /**
     * UserManager constructor.
     *
     * @param PasswordUpdaterInterface $passwordUpdater
     * @param CanonicalFieldsUpdater   $canonicalFieldsUpdater
     * @param ObjectManager            $om
     * @param string                   $class
     * @param ValidatorInterface       $validator
     * @param StfalconMailerHelper     $mailHelper
     */
    public function __construct(PasswordUpdaterInterface $passwordUpdater, CanonicalFieldsUpdater $canonicalFieldsUpdater, ObjectManager $om, $class, $validator, $mailHelper)
    {
        parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, $class);

        $this->validator = $validator;
        $this->mailHelper = $mailHelper;
    }

    /**
     * Automatic user registration.
     *
     * @param array $participant
     *
     * @return UserInterface
     */
    public function autoRegistration(array $participant): UserInterface
    {
        /** @var User $user */
        $user = $this->createUser();
        $user->setEmail($participant['email']);
        $user->setName($participant['name']);
        $user->setSurname($participant['surname']);
        $user->setFullname($participant['surname'].' '.$participant['name']);

        $plainPassword = \substr(md5(\uniqid(\mt_rand(), true).\time()), 0, 8);

        $user->setPlainPassword($plainPassword);
        $user->setEnabled(true);

        $errors = $this->validator->validate($user);
        if ($errors->count() > 0) {
            throw new BadCredentialsException('Bad credentials!');
        }
        $this->updateUser($user);
        $this->mailHelper->sendAutoRegistration($user, $plainPassword);

        return $user;
    }
}
