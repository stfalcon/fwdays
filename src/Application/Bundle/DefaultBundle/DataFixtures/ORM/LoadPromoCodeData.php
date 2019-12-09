<?php

namespace Application\Bundle\DefaultBundle\DataFixtures\ORM;

use Application\Bundle\DefaultBundle\Entity\PromoCode;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadPromoCodeData.
 */
class LoadPromoCodeData extends AbstractFixture implements DependentFixtureInterface
{
    private const PROMO_DATA = [
        [
            'title' => 'Promo code for JsDays',
            'code' => 'Promo code for JsDays',
            'event' => 'event-jsday2018',
            'discount' => 10,
            'date_end' => 'event',
            'max' => 0,
        ],
        [
            'title' => 'Promo code for JsDays 5%',
            'code' => 'Promo code for JsDays 5%',
            'event' => 'event-jsday2018',
            'discount' => 5,
            'date_end' => 'event',
            'max' => 0,
        ],
        [
            'title' => 'Promo code for JsDays overdue',
            'code' => 'Promo code for JsDays overdue',
            'event' => 'event-jsday2018',
            'discount' => 10,
            'date_end' => '-11 Days',
            'max' => 0,
        ],
        [
            'title' => 'Promo code for PHPDay',
            'code' => 'Promo code for PHPDay',
            'event' => 'event-phpday2017',
            'discount' => 5,
            'date_end' => null,
            'max' => 0,
        ],
        [
            'title' => 'LimitedJsDays_100',
            'code' => 'JsDays_100',
            'event' => 'event-jsday2018',
            'discount' => 100,
            'date_end' => 'event',
            'max' => 1,
        ],
    ];

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
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::PROMO_DATA as $key => $promoData) {
            $event = $this->getReference($promoData['event']);
            $promoCode = (new PromoCode())
                ->setTitle($promoData['title'])
                ->setCode($promoData['code'])
                ->setEvent($event)
                ->setDiscountAmount($promoData['discount'])
                ->setMaxUseCount($promoData['max'])
            ;
            if ($promoData['date_end']) {
                if ('event' === $promoData['date_end']) {
                    $promoCode->setEndDate($event->getDate());
                } else {
                    $promoCode->setEndDate(new \DateTime($promoData['date_end']));
                }
            }
            $manager->persist($promoCode);
            $this->addReference(\sprintf('promoCode-%s', $key), $promoCode);
        }

        $manager->flush();
    }
}
