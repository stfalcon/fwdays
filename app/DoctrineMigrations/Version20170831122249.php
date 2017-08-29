<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170831122249 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event__events ADD dateEnd DATETIME DEFAULT NULL');
        $this->addSql('CREATE TABLE event_speakers_candidate (speaker_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_BB90FF25D04A0F27 (speaker_id), INDEX IDX_BB90FF2571F7E88B (event_id), PRIMARY KEY(speaker_id, event_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_speakers_candidate ADD CONSTRAINT FK_BB90FF25D04A0F27 FOREIGN KEY (speaker_id) REFERENCES event__speakers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_speakers_candidate ADD CONSTRAINT FK_BB90FF2571F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event__events ADD background_color VARCHAR(7) DEFAULT \'#0000FF\' NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE event_speakers_candidate');
        $this->addSql('ALTER TABLE event__events DROP background_color');
        $this->addSql('ALTER TABLE event__events DROP dateEnd');
    }
}
