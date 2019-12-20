<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190114115107 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
//        /** @var Event[] $events */
//        $em = $this->container->get('doctrine.orm.entity_manager');
//        $events = $em->getRepository(Event::class)->findAll();
//        foreach ($events as $event) {
//            $position = 1;
//            foreach ($event->getBlocks() as $block) {
//                $block->setPosition($position);
//                $position ++;
//            }
//        }
//
//        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
