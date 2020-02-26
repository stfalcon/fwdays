<?php declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\City;
use App\Entity\Event;
use App\Entity\Translation\CityTranslation;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200226101300 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function getDescription() : string
    {
        return 'Set default city';
    }

    public function up(Schema $schema) : void
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $cities = $em->getRepository(City::class)->findAll();

        if (\count($cities) > 0) {
            return;
        }

        $kyivCity = (new City())
            ->setActive(true)
            ->setDefault(true)
            ->setName('Київ')
            ->setUrlName('kyiv')
        ;

        $translations = (new CityTranslation())
            ->setLocale('en')
            ->setField('name')
            ->setContent('Kyiv')
            ->setObject($kyivCity)
        ;

        $em->persist($kyivCity);
        $em->persist($translations);

        $events = $em->getRepository(Event::class)->findAll();
        /** @var Event $event */
        foreach ($events as $event) {
            if (null === $event->getCity()) {
                $event->setCity($kyivCity);
            }
        }

        $em->flush();
    }

    public function down(Schema $schema) : void
    {
    }
}
