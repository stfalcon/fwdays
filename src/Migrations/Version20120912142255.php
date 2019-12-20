<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20120912142255 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("ALTER TABLE pages ADD meta_keywords VARCHAR(255) DEFAULT NULL, ADD meta_description VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE event__reviews ADD meta_keywords VARCHAR(255) DEFAULT NULL, ADD meta_description VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE event__pages ADD meta_keywords VARCHAR(255) DEFAULT NULL, ADD meta_description VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE users DROP algorithm");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("ALTER TABLE event__pages DROP meta_keywords, DROP meta_description");
        $this->addSql("ALTER TABLE event__reviews DROP meta_keywords, DROP meta_description");
        $this->addSql("ALTER TABLE pages DROP meta_keywords, DROP meta_description");
        $this->addSql("ALTER TABLE users ADD algorithm VARCHAR(255) NOT NULL");
    }
}
