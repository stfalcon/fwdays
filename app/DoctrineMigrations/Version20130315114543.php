<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130315114543 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE event__mails_queues (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, mail_id INT DEFAULT NULL, is_sent TINYINT(1) NOT NULL, INDEX IDX_3C9FC166A76ED395 (user_id), INDEX IDX_3C9FC166C8776F01 (mail_id), PRIMARY KEY(id)) ENGINE = InnoDB");
        $this->addSql("ALTER TABLE event__mails_queues ADD CONSTRAINT FK_3C9FC166A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)");
        $this->addSql("ALTER TABLE event__mails_queues ADD CONSTRAINT FK_3C9FC166C8776F01 FOREIGN KEY (mail_id) REFERENCES event__mails (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE event__mails_queues");
    }
}
