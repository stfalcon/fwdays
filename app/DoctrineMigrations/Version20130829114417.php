<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130829114417 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE mail_event (mail_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_F6DA8C8C8776F01 (mail_id), INDEX IDX_F6DA8C871F7E88B (event_id), PRIMARY KEY(mail_id, event_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE mail_event ADD CONSTRAINT FK_F6DA8C8C8776F01 FOREIGN KEY (mail_id) REFERENCES event__mails (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE mail_event ADD CONSTRAINT FK_F6DA8C871F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE event__mails DROP FOREIGN KEY FK_DBF9BBCB71F7E88B");
        $this->addSql("DROP INDEX IDX_DBF9BBCB71F7E88B ON event__mails");
        $this->addSql("ALTER TABLE event__mails DROP event_id");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE mail_event");
        $this->addSql("ALTER TABLE event__mails ADD event_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE event__mails ADD CONSTRAINT FK_DBF9BBCB71F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON DELETE CASCADE");
        $this->addSql("CREATE INDEX IDX_DBF9BBCB71F7E88B ON event__mails (event_id)");
    }
}
