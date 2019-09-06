<?php

namespace Application\Bundle\DefaultBundle\Model;

use Application\Bundle\DefaultBundle\Entity\User;
use Application\Bundle\DefaultBundle\Helper\StfalconMailerHelper;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Doctrine\UserManager as FosUserManager;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UserManager.
 */
class UserManager extends FosUserManager
{
    public const NEW_USERS_SESSION_KEY = 'new_users';

    private $validator;
    private $mailHelper;
    private $session;

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
    public function __construct(PasswordUpdaterInterface $passwordUpdater, CanonicalFieldsUpdater $canonicalFieldsUpdater, ObjectManager $om, $class, $validator, $mailHelper, Session $session)
    {
        parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, $class);

        $this->validator = $validator;
        $this->mailHelper = $mailHelper;
        $this->session = $session;
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

        $newUsersList = $this->session->get(self::NEW_USERS_SESSION_KEY, []);
        $newUsersList[] = $user->getId();
        $this->session->set(self::NEW_USERS_SESSION_KEY, $newUsersList);

        return $user;
    }

    /**
     * @param User   $user
     * @param string $name
     * @param string $surname
     * @param string $email
     */
    public function updateUserData(User $user, string $name, string $surname, string $email): void
    {
        if ($email === $user->getEmail() && $name === $user->getName() && $surname === $user->getSurname()) {
            return;
        }

        $newUsersList = $this->session->get(self::NEW_USERS_SESSION_KEY, []);
        if (!\in_array($user->getId(), $newUsersList, true)) {
            throw new BadRequestHttpException();
        }

        $oldEmail = $user->getEmail();

        $user->setEmail($email);
        $user->setName($name);
        $user->setSurname($surname);
        $user->setFullname($surname.' '.$name);

        $errors = $this->validator->validate($user);
        if ($errors->count() > 0) {
            throw new BadCredentialsException('Bad credentials!');
        }

        if ($oldEmail !== $email) {
            $plainPassword = \substr(md5(\uniqid(\mt_rand(), true).\time()), 0, 8);

            $user->setPlainPassword($plainPassword);
            $this->mailHelper->sendAutoRegistration($user, $plainPassword);
        }
        $this->updateUser($user);
    }
}
