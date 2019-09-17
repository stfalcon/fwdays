<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190910145749 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event_speakers_expert (speaker_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_D09025BAD04A0F27 (speaker_id), INDEX IDX_D09025BA71F7E88B (event_id), PRIMARY KEY(speaker_id, event_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_speakers_expert ADD CONSTRAINT FK_D09025BAD04A0F27 FOREIGN KEY (speaker_id) REFERENCES event__speakers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_speakers_expert ADD CONSTRAINT FK_D09025BA71F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE event_speakers_expert');
    }
}
