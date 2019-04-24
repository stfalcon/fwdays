<?php

namespace Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\SponsorBundle\Entity\Category;
use Stfalcon\Bundle\SponsorBundle\Entity\EventSponsor;
use Stfalcon\Bundle\SponsorBundle\Entity\Sponsor;

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
        return array(
            'Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM\LoadCategoryData',
            'Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM\LoadSponsorData',
            'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData',
        );
    }

    /**
     * @param ObjectManager $manager
     * @param Category      $sponsorCtg
     * @param Event         $event
     * @param Sponsor       $sponsor
     */
    public function setEventSponsor(ObjectManager $manager, $sponsorCtg, $event, $sponsor)
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
    public function setComplectSponsor(ObjectManager $manager, $event)
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
    public function load(ObjectManager $manager)
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
