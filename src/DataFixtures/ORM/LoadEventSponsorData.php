<?php

namespace App\DataFixtures\ORM;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\EventSponsor;
use App\Entity\Sponsor;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * LoadEventSponsorData class.
 */
class LoadEventSponsorData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Return fixture classes fixture is dependent on.
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadCategoryData::class,
            LoadSponsorData::class,
            LoadEventData::class,
        ];
    }

    /**
     * @param ObjectManager $manager
     * @param Category      $sponsorCtg
     * @param Event         $event
     * @param Sponsor       $sponsor
     */
    public function setEventSponsor(ObjectManager $manager, $sponsorCtg, $event, $sponsor): void
    {
        $eventSponsor = (new EventSponsor())
            ->setCategory($sponsorCtg)
            ->setEvent($event)
            ->setSponsor($sponsor);
        $manager->persist($eventSponsor);
    }

    /**
     * @param ObjectManager $manager
     * @param Event         $event
     */
    public function setComplectSponsor(ObjectManager $manager, $event): void
    {
        $goldenSponsor = $manager->merge($this->getReference('golden-sponsor'));
        $silverSponsor = $manager->merge($this->getReference('silver-sponsor'));
        $partnerSponsor = $manager->merge($this->getReference('partner-sponsor'));
        $infoPartnerSponsor = $manager->merge($this->getReference('info-partner-sponsor'));

        $sponsorODesk = $manager->merge($this->getReference('sponsor-odesk'));
        $sponsorMagento = $manager->merge($this->getReference('sponsor-magento'));
        $sponsorEpochta = $manager->merge($this->getReference('sponsor-epochta'));

        $partners = [];
        for ($i = 0; $i < 3; ++$i) {
            $partners[] = $manager->merge($this->getReference('partner-'.$i));
        }
        $infoPartners = [];
        for ($i = 4; $i < 10; ++$i) {
            $infoPartners[] = $manager->merge($this->getReference('info-partner-'.$i));
        }

        $this->setEventSponsor($manager, $goldenSponsor, $event, $sponsorODesk);
        $this->setEventSponsor($manager, $goldenSponsor, $event, $sponsorEpochta);
        $this->setEventSponsor($manager, $silverSponsor, $event, $sponsorMagento);
        $this->setEventSponsor($manager, $partnerSponsor, $event, $partners[0]);
        $this->setEventSponsor($manager, $partnerSponsor, $event, $partners[1]);
        $this->setEventSponsor($manager, $partnerSponsor, $event, $partners[2]);
        $this->setEventSponsor($manager, $infoPartnerSponsor, $event, $infoPartners[0]);
        $this->setEventSponsor($manager, $infoPartnerSponsor, $event, $infoPartners[1]);
        $this->setEventSponsor($manager, $infoPartnerSponsor, $event, $infoPartners[2]);
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $eventZFDay = $manager->merge($this->getReference('event-jsday2018'));
        $eventPHPDay = $manager->merge($this->getReference('event-phpday2017'));
        $eventPHPDay2018 = $manager->merge($this->getReference('event-phpday2018'));
        $eventHighLoad = $manager->merge($this->getReference('event-highload-day'));

        $this->setComplectSponsor($manager, $eventPHPDay);
        $this->setComplectSponsor($manager, $eventZFDay);
        $this->setComplectSponsor($manager, $eventPHPDay2018);
        $this->setComplectSponsor($manager, $eventPHPDay);
        $this->setComplectSponsor($manager, $eventHighLoad);

        $manager->flush();
    }
}
