<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140415140243 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE event__tickets DROP FOREIGN KEY FK_66E295594C3A3BB");
        $this->addSql("ALTER TABLE event__tickets ADD CONSTRAINT FK_66E295594C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE SET NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE event__tickets DROP FOREIGN KEY FK_66E295594C3A3BB");
        $this->addSql("ALTER TABLE event__tickets ADD CONSTRAINT FK_66E295594C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE CASCADE");
    }
}
