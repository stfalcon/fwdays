<?php

namespace App\Model;

use App\Entity\User;
use App\Exception\BadAutoRegistrationDataException;
use App\Helper\MailerHelper;
use App\Traits;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Doctrine\UserManager as FosUserManager;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * UserManager.
 */
class UserManager extends FosUserManager
{
    use Traits\ValidatorTrait;
    use Traits\SessionTrait;

    public const NEW_USERS_SESSION_KEY = 'new_users';

    private $mailHelper;

    /**
     * @param PasswordUpdaterInterface $passwordUpdater
     * @param CanonicalFieldsUpdater   $canonicalFieldsUpdater
     * @param ObjectManager            $om
     * @param MailerHelper             $mailHelper
     */
    public function __construct(PasswordUpdaterInterface $passwordUpdater, CanonicalFieldsUpdater $canonicalFieldsUpdater, ObjectManager $om, MailerHelper $mailHelper)
    {
        parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, User::class);

        $this->mailHelper = $mailHelper;
    }

    /**
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
            throw new BadAutoRegistrationDataException('Bad credentials!', $this->getErrorMap($errors));
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
            throw new BadAutoRegistrationDataException('Bad credentials!', $this->getErrorMap($errors));
        }

        if ($oldEmail !== $email) {
            $plainPassword = \substr(md5(\uniqid(\mt_rand(), true).\time()), 0, 8);

            $user->setPlainPassword($plainPassword);
            $this->mailHelper->sendAutoRegistration($user, $plainPassword);
        }
        $this->updateUser($user);
    }

    /**
     * @param ConstraintViolationList $errors
     *
     * @return array
     */
    private function getErrorMap(ConstraintViolationListInterface $errors): array
    {
        $errorsMap = [];
        foreach ($errors as $error) {
            $errorsMap[$error->getPropertyPath()] = $error->getMessage();
        }

        return $errorsMap;
    }
}
