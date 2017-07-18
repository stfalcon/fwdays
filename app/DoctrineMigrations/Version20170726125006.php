<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170726125006 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE general_news_translations (id INT AUTO_INCREMENT NOT NULL, object_id INT DEFAULT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, INDEX IDX_79DD4AA0232D562B (object_id), UNIQUE INDEX General_news_lookup_unique_idx (locale, object_id, field), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE general_news_translations ADD CONSTRAINT FK_79DD4AA0232D562B FOREIGN KEY (object_id) REFERENCES news (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE general_page_translations DROP FOREIGN KEY FK_F9058F2F232D562B');
        $this->addSql('DROP INDEX idx_f9058f2f232d562b ON general_page_translations');
        $this->addSql('CREATE INDEX IDX_218B8F59232D562B ON general_page_translations (object_id)');
        $this->addSql('ALTER TABLE general_page_translations ADD CONSTRAINT FK_F9058F2F232D562B FOREIGN KEY (object_id) REFERENCES pages (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE general_news_translations');
        $this->addSql('ALTER TABLE general_page_translations DROP FOREIGN KEY FK_218B8F59232D562B');
        $this->addSql('DROP INDEX idx_218b8f59232d562b ON general_page_translations');
        $this->addSql('CREATE INDEX IDX_F9058F2F232D562B ON general_page_translations (object_id)');
        $this->addSql('ALTER TABLE general_page_translations ADD CONSTRAINT FK_218B8F59232D562B FOREIGN KEY (object_id) REFERENCES pages (id) ON DELETE CASCADE');
    }
}
