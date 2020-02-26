<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20191008151716 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event__mails ADD city_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event__mails ADD CONSTRAINT FK_DBF9BBCB8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_DBF9BBCB8BAC62AF ON event__mails (city_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event__mails DROP FOREIGN KEY FK_DBF9BBCB8BAC62AF');
        $this->addSql('DROP INDEX IDX_DBF9BBCB8BAC62AF ON event__mails');
        $this->addSql('ALTER TABLE event__mails DROP city_id');
    }
}
