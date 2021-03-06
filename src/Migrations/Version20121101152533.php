<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20121101152533 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("ALTER TABLE event__events_sponsors DROP on_main");
        $this->addSql("ALTER TABLE sponsors ADD on_main TINYINT(1) NOT NULL");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("ALTER TABLE event__events_sponsors ADD on_main TINYINT(1) NOT NULL");
        $this->addSql("ALTER TABLE sponsors DROP on_main");
    }
}
