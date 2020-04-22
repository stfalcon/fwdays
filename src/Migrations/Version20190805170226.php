<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190805170226 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, url_name VARCHAR(255) NOT NULL, default_city TINYINT(1) DEFAULT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_2D5B02345E237E06 (name), UNIQUE INDEX UNIQ_2D5B02344077B7BE (url_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event__events ADD city_id INT DEFAULT NULL, DROP city');
        $this->addSql('ALTER TABLE event__events ADD CONSTRAINT FK_2FE319668BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_2FE319668BAC62AF ON event__events (city_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event__events DROP FOREIGN KEY FK_2FE319668BAC62AF');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP INDEX IDX_2FE319668BAC62AF ON event__events');
        $this->addSql('ALTER TABLE event__events ADD city VARCHAR(255) DEFAULT NULL, DROP city_id');
    }
}
