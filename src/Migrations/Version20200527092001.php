<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200527092001 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event__events ADD type VARCHAR(255) DEFAULT NULL, ADD online TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE events_audiences DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE events_audiences ADD PRIMARY KEY (event_id, eventaudience_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event__events DROP type, DROP online');
        $this->addSql('ALTER TABLE events_audiences DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE events_audiences ADD PRIMARY KEY (eventaudience_id, event_id)');
    }
}
