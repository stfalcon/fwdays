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
        $userAdmin->setAllowShareContacts(true);
        $manager->persist($userAdmin);
        $this->addReference('user-admin', $userAdmin);

        $userVolunteer = new User();
        $userVolunteer->setUsername('Волонтер');
        $userVolunteer->setFullname('Volunteer Jack');
        $userVolunteer->setEmail('volunteer@fwdays.com');
        $userVolunteer->setPlainPassword('qwerty');
        $userVolunteer->setRoles(array('ROLE_VOLUNTEER'));
        $userVolunteer->setEnabled(true);
        $userVolunteer->setExpired(false);
        $userVolunteer->setLocked(false);
        $userVolunteer->setAllowShareContacts(true);
        $manager->persist($userVolunteer);

        $this->addReference('user-volunteer', $userVolunteer);

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
        $userDefault->setAllowShareContacts(true);
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
        $userDefault2->setAllowShareContacts(true);
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
        $userDefault3->setAllowShareContacts(true);
        $manager->persist($userDefault3);
        $this->addReference('user-default3', $userDefault3);

        $userDefault4 = new User();
        $userDefault4->setUsername('Rasmus');
        $userDefault4->setFullname('Rasmus Lerdorf');
        $userDefault4->setEmail('rasmus.lerdorf@fwdays.com');
        $userDefault4->setPlainPassword('qwerty');
        $userDefault4->addRole('ROLE_USER');
        $userDefault4->setCountry('Greenland');
        $userDefault4->setCity('Tortuga');
        $userDefault4->setCompany('PHP');
        $userDefault4->setPost('Core developer');
        $userDefault4->setEnabled(true);
        $userDefault4->setSubscribe(false);
        $userDefault4->setExpired(false);
        $userDefault4->setLocked(false);
        $userDefault4->setAllowShareContacts(true);
        $manager->persist($userDefault4);
        $this->addReference('user-default4', $userDefault4);

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
            $userDefault->setAllowShareContacts(true);
            $manager->persist($userDefault);
            $this->addReference('user-default-' . $i, $userDefault);
        }

        $manager->flush();
    }
}
