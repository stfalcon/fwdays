<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180503103953 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event__events ADD group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event__events ADD CONSTRAINT FK_2FE31966FE54D947 FOREIGN KEY (group_id) REFERENCES event_group (id)');
        $this->addSql('CREATE INDEX IDX_2FE31966FE54D947 ON event__events (group_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event__events DROP FOREIGN KEY FK_2FE31966FE54D947');
        $this->addSql('DROP TABLE event_group');
        $this->addSql('DROP INDEX IDX_2FE31966FE54D947 ON event__events');
        $this->addSql('ALTER TABLE event__events DROP group_id');
    }
}
