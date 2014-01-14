<?php

namespace Application\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\Persistence\ObjectManager;

use Application\Bundle\UserBundle\Entity\User;

/**
 * LoadUserData class
 */
class LoadUserData extends AbstractFixture
{
    /**
     * Create and load user fixtures to database
     *
     * @param ObjectManager $manager Entity manager object
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $userAdmin = new User();
        $userAdmin->setUsername('Администратор');
        $userAdmin->setFullname('Super Admin');
        $userAdmin->setEmail('admin@fwdays.com');
        $userAdmin->setPlainPassword('qwerty');
        $userAdmin->setRoles(array('ROLE_SUPER_ADMIN'));
        $userAdmin->setEnabled(true);
        $userAdmin->setExpired(false);
        $userAdmin->setLocked(false);
        $manager->persist($userAdmin);
        $this->addReference('user-admin', $userAdmin);

        $userDefault = new User();
        $userDefault->setUsername('Пользователь');
        $userDefault->setFullname('Michael Jordan');
        $userDefault->setEmail('user@fwdays.com');
        $userDefault->setPlainPassword('qwerty');
        $userDefault->addRole('ROLE_USER');
        $userDefault->setCountry('USA');
        $userDefault->setCity('Boston');
        $userDefault->setCompany('NBA');
        $userDefault->setPost('Point Guard');
        $userDefault->setEnabled(true);
        $userDefault->setExpired(false);
        $userDefault->setLocked(false);
        $manager->persist($userDefault);
        $this->addReference('user-default', $userDefault);

        $userDefault2 = new User();
        $userDefault2->setUsername('Pirate');
        $userDefault2->setFullname('Jack Sparrow');
        $userDefault2->setEmail('jack.sparrow@fwdays.com');
        $userDefault2->setPlainPassword('qwerty');
        $userDefault2->addRole('ROLE_USER');
        $userDefault2->setCountry('Haiti');
        $userDefault2->setCity('Tortuga');
        $userDefault2->setCompany('Pirates of the Caribbean');
        $userDefault2->setPost('Captain');
        $userDefault2->setEnabled(true);
        $userDefault2->setExpired(false);
        $userDefault2->setLocked(false);
        $manager->persist($userDefault2);
        $this->addReference('user-default2', $userDefault2);

        $userDefault3 = new User();
        $userDefault3->setUsername('Spiderman');
        $userDefault3->setFullname('Peter Parker');
        $userDefault3->setEmail('peter.parker@fwdays.com');
        $userDefault3->setPlainPassword('qwerty');
        $userDefault3->addRole('ROLE_USER');
        $userDefault3->setCountry('USA');
        $userDefault3->setCity('New-York');
        $userDefault3->setCompany('The New-York Times');
        $userDefault3->setPost('Journalist');
        $userDefault3->setEnabled(true);
        $userDefault3->setExpired(false);
        $userDefault3->setLocked(false);
        $manager->persist($userDefault3);
        $this->addReference('user-default3', $userDefault3);

        for ($i = 1; $i <= 100; $i++) {
            $userDefault = new User();
            $userDefault->setUsername('Пользователь ' . $i);
            $userDefault->setFullname('Default User ' . $i);
            $userDefault->setEmail('user' . $i . '@fwdays.com');
            $userDefault->setPlainPassword('qwerty');
            $userDefault->addRole('ROLE_USER');
            $userDefault->setCountry('Ukraine');
            $userDefault->setCity('Khmelnytskyi');
            $userDefault->setCompany('Anonumous');
            $userDefault->setPost('Tester');
            $userDefault->setEnabled(true);
            $userDefault->setExpired(false);
            $userDefault->setLocked(false);
            $manager->persist($userDefault);
            $this->addReference('user-default-' . $i, $userDefault);
        }

        $manager->flush();
    }
}
