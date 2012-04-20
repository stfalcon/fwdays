<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20120419181544 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("ALTER TABLE users DROP algorithm");
        $this->addSql("ALTER TABLE event__tickets DROP FOREIGN KEY FK_66E295594C3A3BB");
        $this->addSql("ALTER TABLE event__tickets DROP FOREIGN KEY FK_66E2955971F7E88B");
        $this->addSql("ALTER TABLE event__tickets DROP FOREIGN KEY FK_66E29559A76ED395");
        $this->addSql("ALTER TABLE event__tickets ADD CONSTRAINT FK_66E295594C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE event__tickets ADD CONSTRAINT FK_66E2955971F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE event__tickets ADD CONSTRAINT FK_66E29559A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE event__mails DROP FOREIGN KEY FK_DBF9BBCB71F7E88B");
        $this->addSql("ALTER TABLE event__mails ADD CONSTRAINT FK_DBF9BBCB71F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE payments DROP FOREIGN KEY FK_65D29B32A76ED395");
        $this->addSql("ALTER TABLE payments ADD CONSTRAINT FK_65D29B32A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("ALTER TABLE event__mails DROP FOREIGN KEY FK_DBF9BBCB71F7E88B");
        $this->addSql("ALTER TABLE event__mails ADD CONSTRAINT FK_DBF9BBCB71F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON UPDATE CASCADE ON DELETE CASCADE");
        $this->addSql("ALTER TABLE event__tickets DROP FOREIGN KEY FK_66E2955971F7E88B");
        $this->addSql("ALTER TABLE event__tickets DROP FOREIGN KEY FK_66E29559A76ED395");
        $this->addSql("ALTER TABLE event__tickets DROP FOREIGN KEY FK_66E295594C3A3BB");
        $this->addSql("ALTER TABLE event__tickets ADD CONSTRAINT FK_66E2955971F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON UPDATE CASCADE ON DELETE CASCADE");
        $this->addSql("ALTER TABLE event__tickets ADD CONSTRAINT FK_66E29559A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE");
        $this->addSql("ALTER TABLE event__tickets ADD CONSTRAINT FK_66E295594C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON UPDATE CASCADE ON DELETE CASCADE");
        $this->addSql("ALTER TABLE payments DROP FOREIGN KEY FK_65D29B32A76ED395");
        $this->addSql("ALTER TABLE payments ADD CONSTRAINT FK_65D29B32A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE");
        $this->addSql("ALTER TABLE users ADD algorithm VARCHAR(255) NOT NULL");
    }
}
