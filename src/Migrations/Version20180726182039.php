<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180726182039 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE mail_eventaudience (mail_id INT NOT NULL, eventaudience_id INT NOT NULL, INDEX IDX_16F0DEC6C8776F01 (mail_id), INDEX IDX_16F0DEC6BF29EF41 (eventaudience_id), PRIMARY KEY(mail_id, eventaudience_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_audience (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE events_audiences (eventaudience_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_916BFFEBBF29EF41 (eventaudience_id), INDEX IDX_916BFFEB71F7E88B (event_id), PRIMARY KEY(eventaudience_id, event_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mail_eventaudience ADD CONSTRAINT FK_16F0DEC6C8776F01 FOREIGN KEY (mail_id) REFERENCES event__mails (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mail_eventaudience ADD CONSTRAINT FK_16F0DEC6BF29EF41 FOREIGN KEY (eventaudience_id) REFERENCES event_audience (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE events_audiences ADD CONSTRAINT FK_916BFFEBBF29EF41 FOREIGN KEY (eventaudience_id) REFERENCES event_audience (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE events_audiences ADD CONSTRAINT FK_916BFFEB71F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mail_eventaudience DROP FOREIGN KEY FK_16F0DEC6BF29EF41');
        $this->addSql('ALTER TABLE events_audiences DROP FOREIGN KEY FK_916BFFEBBF29EF41');
        $this->addSql('DROP TABLE mail_eventaudience');
        $this->addSql('DROP TABLE event_audience');
        $this->addSql('DROP TABLE events_audiences');
    }
}
