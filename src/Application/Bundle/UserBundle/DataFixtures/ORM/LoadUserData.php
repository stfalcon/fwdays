<?php

namespace Application\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Application\Bundle\UserBundle\Entity\User;

/**
 * LoadUserData class.
 */
class LoadUserData extends AbstractFixture
{
    /**
     * Create and load user fixtures to database.
     *
     * @param ObjectManager $manager Entity manager object
     */
    public function load(ObjectManager $manager)
    {
        $userAdmin = (new User())
            ->setUsername('Администратор')
            ->setName('Super')
            ->setSurname('Super')
            ->setEmail('admin@fwdays.com')
            ->setPlainPassword('qwerty')
            ->setRoles(array('ROLE_SUPER_ADMIN'))
            ->setEnabled(true)
            ->setExpired(false)
            ->setLocked(false);
        $manager->persist($userAdmin);
        $this->addReference('user-admin', $userAdmin);

        $userVolunteer = (new User())
            ->setUsername('Волонтер')
            ->setName('Jack')
            ->setSurname('Volunteer')
            ->setEmail('volunteer@fwdays.com')
            ->setPlainPassword('qwerty')
            ->setRoles(array('ROLE_VOLUNTEER'))
            ->setEnabled(true)
            ->setExpired(false)
            ->setLocked(false);
        $manager->persist($userVolunteer);
        $this->addReference('user-volunteer', $userVolunteer);

        $userDefault = (new User())
            ->setUsername('Пользователь')
            ->setName('Michael')
            ->setSurname('Jordan')
            ->setEmail('user@fwdays.com')
            ->setPlainPassword('qwerty')
            ->addRole('ROLE_USER')
            ->setCountry('USA')
            ->setCity('Boston')
            ->setCompany('NBA')
            ->setPost('Point Guard')
            ->setEnabled(true)
            ->setExpired(false)
            ->setLocked(false);
        $manager->persist($userDefault);
        $this->addReference('user-default', $userDefault);

        $userDefault2 = (new User())
            ->setUsername('Pirate')
            ->setName('Jack')
            ->setSurname('Sparrow')
            ->setEmail('jack.sparrow@fwdays.com')
            ->setPlainPassword('qwerty')
            ->addRole('ROLE_USER')
            ->setCountry('Haiti')
            ->setCity('Tortuga')
            ->setCompany('Pirates of the Caribbean')
            ->setPost('Captain')
            ->setEnabled(true)
            ->setExpired(false)
            ->setLocked(false);
        $manager->persist($userDefault2);
        $this->addReference('user-default2', $userDefault2);

        $userDefault3 = (new User())
            ->setUsername('Spiderman')
            ->setName('Peter')
            ->setSurname('Parker')
            ->setEmail('peter.parker@fwdays.com')
            ->setPlainPassword('qwerty')
            ->addRole('ROLE_USER')
            ->setCountry('USA')
            ->setCity('New-York')
            ->setCompany('The New-York Times')
            ->setPost('Journalist')
            ->setEnabled(true)
            ->setExpired(false)
            ->setLocked(false);
        $manager->persist($userDefault3);
        $this->addReference('user-default3', $userDefault3);

        $userDefault4 = (new User())
            ->setUsername('Rasmus')
            ->setName('Rasmus')
            ->setSurname('Lerdorf')
            ->setEmail('rasmus.lerdorf@fwdays.com')
            ->setPlainPassword('qwerty')
            ->addRole('ROLE_USER')
            ->setCountry('Greenland')
            ->setCity('Tortuga')
            ->setCompany('PHP')
            ->setPost('Core developer')
            ->setEnabled(true)
            ->setSubscribe(false)
            ->setExpired(false)
            ->setLocked(false);
        $manager->persist($userDefault4);
        $this->addReference('user-default4', $userDefault4);

        for ($i = 1; $i <= 100; ++$i) {
            $userDefault = (new User())
                ->setUsername('Пользователь '.$i)
                ->setName('User '.$i)
                ->setSurname('Default '.$i)
                ->setEmail('user'.$i.'@fwdays.com')
                ->setPlainPassword('qwerty')
                ->addRole('ROLE_USER')
                ->setCountry('Ukraine')
                ->setCity('Khmelnytskyi')
                ->setCompany('Anonumous')
                ->setPost('Tester')
                ->setEnabled(true)
                ->setExpired(false)
                ->setLocked(false);
            $manager->persist($userDefault);
            $this->addReference('user-default-'.$i, $userDefault);
        }

        $manager->flush();
    }
}
