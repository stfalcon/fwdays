<?php

namespace Application\Bundle\DefaultBundle\DataFixtures\ORM;

use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\PromoCode;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadPromoCodeData Class.
 */
class LoadPromoCodeData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Return fixture classes fixture is dependent on.
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadEventData',
        ];
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Event $eventZFDays, $eventPHPDay */
        $eventJsDays = $manager->merge($this->getReference('event-jsday2018'));
        $eventPHPDay = $manager->merge($this->getReference('event-phpday2017'));

        // Promo code 1
        $promoCode = new PromoCode();
        $promoCode
            ->setTitle('Promo code for JsDays')
            ->setCode('Promo code for JsDays')
            ->setEvent($eventJsDays);
        $manager->persist($promoCode);
        $this->addReference('promoCode-1', $promoCode);

        // Promo code 2
        $promoCode = new PromoCode();
        $promoCode
            ->setTitle('Promo code for JsDays 5%')
            ->setCode('Promo code for JsDays 5%')
            ->setDiscountAmount(5)
            ->setEvent($eventJsDays);
        $manager->persist($promoCode);
        $this->addReference('promoCode-2', $promoCode);

        // Promo code 3
        $promoCode = new PromoCode();
        $promoCode
            ->setTitle('Promo code for JsDays overdue')
            ->setCode('Promo code for JsDays overdue')
            ->setEvent($eventJsDays)
            ->setEndDate(new \DateTime('-11 Days'));
        $manager->persist($promoCode);
        $this->addReference('promoCode-3', $promoCode);

        // Promo code 4
        $promoCode = new PromoCode();
        $promoCode
            ->setTitle('Promo code for PHPDay')
            ->setCode('Promo code for PHPDay')
            ->setEvent($eventPHPDay);
        $manager->persist($promoCode);
        $this->addReference('promoCode-4', $promoCode);

        $manager->flush();
    }
}
