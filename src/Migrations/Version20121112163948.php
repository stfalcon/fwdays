<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20121112163948 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("UPDATE event__events SET logo = TRIM(LEADING '/uploads/events/' FROM logo)");
        $this->addSql("UPDATE event__speakers SET photo = TRIM(LEADING '/uploads/speakers/' FROM photo)");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("UPDATE event__events SET logo = CONCAT('/uploads/events/', logo)");
        $this->addSql("UPDATE event__speakers SET photo = CONCAT('/uploads/speakers/', photo)");
    }
}
