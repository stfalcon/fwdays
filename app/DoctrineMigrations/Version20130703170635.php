<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130703170635 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        $this->addSql("ALTER TABLE event__mails_queues DROP FOREIGN KEY FK_3C9FC166C8776F01");
        $this->addSql("ALTER TABLE event__mails_queues ADD CONSTRAINT FK_3C9FC166C8776F01 FOREIGN KEY (mail_id) REFERENCES event__mails (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql("ALTER TABLE event__mails_queues DROP FOREIGN KEY FK_3C9FC166C8776F01");
        $this->addSql("ALTER TABLE event__mails_queues ADD CONSTRAINT FK_3C9FC166C8776F01 FOREIGN KEY (mail_id) REFERENCES event__mails (id)");
    }
}
