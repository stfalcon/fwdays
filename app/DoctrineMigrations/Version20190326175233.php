<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190326175233 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sponsor_translations (id INT AUTO_INCREMENT NOT NULL, object_id INT DEFAULT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, INDEX IDX_4C84E738232D562B (object_id), UNIQUE INDEX sponsor_lookup_unique_idx (locale, object_id, field), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sponsor_translations ADD CONSTRAINT FK_4C84E738232D562B FOREIGN KEY (object_id) REFERENCES sponsors (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event__events_sponsors DROP FOREIGN KEY FK_3CCEC92812F7FB51');
        $this->addSql('ALTER TABLE event__events_sponsors ADD CONSTRAINT FK_3CCEC92812F7FB51 FOREIGN KEY (sponsor_id) REFERENCES sponsors (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sponsors ADD about LONGTEXT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sponsor_translations');
        $this->addSql('ALTER TABLE event__events_sponsors DROP FOREIGN KEY FK_3CCEC92812F7FB51');
        $this->addSql('ALTER TABLE event__events_sponsors ADD CONSTRAINT FK_3CCEC92812F7FB51 FOREIGN KEY (sponsor_id) REFERENCES sponsors (id)');
        $this->addSql('ALTER TABLE sponsors DROP about');
    }
}
