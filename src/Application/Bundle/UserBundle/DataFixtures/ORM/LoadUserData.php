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
        $userAdmin->setFullname('Jack Sparrow');
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

        $manager->flush();
    }
}
