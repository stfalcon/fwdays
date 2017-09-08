<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170912144912 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event__ticketsCost (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, count INT DEFAULT NULL, amount NUMERIC(10, 2) NOT NULL, sold_count INT DEFAULT NULL, enabled TINYINT(1) DEFAULT \'1\' NOT NULL, unlimited TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_3D5054F271F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event__ticketsCost ADD CONSTRAINT FK_3D5054F271F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE event__ticketsCost');
    }
}
