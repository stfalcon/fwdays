<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170907105039 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_wants_visit_event (user_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_D23C106DA76ED395 (user_id), INDEX IDX_D23C106D71F7E88B (event_id), PRIMARY KEY(user_id, event_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_wants_visit_event ADD CONSTRAINT FK_D23C106DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE user_wants_visit_event ADD CONSTRAINT FK_D23C106D71F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id)');
        $this->addSql('ALTER TABLE event__events ADD wantsToVisitCount INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_wants_visit_event');
        $this->addSql('ALTER TABLE event__events DROP wantsToVisitCount');
    }
}
