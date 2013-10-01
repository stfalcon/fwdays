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

        $manager->flush();
    }
}
