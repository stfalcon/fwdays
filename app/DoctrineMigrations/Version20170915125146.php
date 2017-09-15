<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170915125146 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event_speakers_candidate (speaker_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_BB90FF25D04A0F27 (speaker_id), INDEX IDX_BB90FF2571F7E88B (event_id), PRIMARY KEY(speaker_id, event_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event__ticketsCost (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, count INT DEFAULT NULL, amount NUMERIC(10, 2) NOT NULL, alt_amount VARCHAR(10) DEFAULT NULL, sold_count INT DEFAULT NULL, enabled TINYINT(1) DEFAULT \'1\' NOT NULL, unlimited TINYINT(1) DEFAULT \'0\' NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_3D5054F271F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_wants_visit_event (user_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_D23C106DA76ED395 (user_id), INDEX IDX_D23C106D71F7E88B (event_id), PRIMARY KEY(user_id, event_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_speakers_candidate ADD CONSTRAINT FK_BB90FF25D04A0F27 FOREIGN KEY (speaker_id) REFERENCES event__speakers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_speakers_candidate ADD CONSTRAINT FK_BB90FF2571F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event__ticketsCost ADD CONSTRAINT FK_3D5054F271F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_wants_visit_event ADD CONSTRAINT FK_D23C106DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE user_wants_visit_event ADD CONSTRAINT FK_D23C106D71F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id)');
        $this->addSql('ALTER TABLE event__tickets ADD ticket_cost_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event__tickets ADD CONSTRAINT FK_66E295591173DC2D FOREIGN KEY (ticket_cost_id) REFERENCES event__ticketsCost (id)');
        $this->addSql('CREATE INDEX IDX_66E295591173DC2D ON event__tickets (ticket_cost_id)');
        $this->addSql('ALTER TABLE event__events ADD dateEnd DATETIME DEFAULT NULL, ADD wantsToVisitCount INT DEFAULT NULL, ADD background_color VARCHAR(7) DEFAULT \'#4e4e84\' NOT NULL');
        $this->addSql('ALTER TABLE sponsors_category ADD is_wide_container TINYINT(1) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event__tickets DROP FOREIGN KEY FK_66E295591173DC2D');
        $this->addSql('DROP TABLE event_speakers_candidate');
        $this->addSql('DROP TABLE event__ticketsCost');
        $this->addSql('DROP TABLE user_wants_visit_event');
        $this->addSql('ALTER TABLE event__events DROP dateEnd, DROP wantsToVisitCount, DROP background_color');
        $this->addSql('DROP INDEX IDX_66E295591173DC2D ON event__tickets');
        $this->addSql('ALTER TABLE event__tickets DROP ticket_cost_id');
        $this->addSql('ALTER TABLE sponsors_category DROP is_wide_container');
    }
}
