<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20121024115954 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("ALTER TABLE event__events_sponsors ADD id INT NOT NULL AUTO_INCREMENT FIRST, ADD category_id INT DEFAULT NULL, CHANGE sponsor_id sponsor_id INT DEFAULT NULL, CHANGE event_id event_id INT DEFAULT NULL, ADD PRIMARY KEY (id)");
        $this->addSql("ALTER TABLE event__events_sponsors ADD CONSTRAINT FK_3CCEC92812469DE2 FOREIGN KEY (category_id) REFERENCES sponsors_category (id)");
        $this->addSql("CREATE INDEX IDX_3CCEC92812469DE2 ON event__events_sponsors (category_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("ALTER TABLE event__events_sponsors DROP FOREIGN KEY FK_3CCEC92812469DE2");
        $this->addSql("DROP INDEX IDX_3CCEC92812469DE2 ON event__events_sponsors");
        $this->addSql("ALTER TABLE event__events_sponsors DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE event__events_sponsors DROP id, DROP category_id, CHANGE sponsor_id sponsor_id INT NOT NULL, CHANGE event_id event_id INT NOT NULL");
    }
}
