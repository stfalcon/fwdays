<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190318110040 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sponsor_translations');
        $this->addSql('ALTER TABLE sponsors DROP slug, DROP about, DROP on_main');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sponsor_translations (id INT AUTO_INCREMENT NOT NULL, object_id INT DEFAULT NULL, locale VARCHAR(8) NOT NULL COLLATE utf8_unicode_ci, field VARCHAR(32) NOT NULL COLLATE utf8_unicode_ci, content LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX sponsor_lookup_unique_idx (locale, object_id, field), INDEX IDX_4C84E738232D562B (object_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sponsor_translations ADD CONSTRAINT FK_4C84E738232D562B FOREIGN KEY (object_id) REFERENCES sponsors (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sponsors ADD slug VARCHAR(255) NOT NULL COLLATE utf8_general_ci, ADD about LONGTEXT DEFAULT NULL COLLATE utf8_general_ci, ADD on_main TINYINT(1) NOT NULL');
    }
}
