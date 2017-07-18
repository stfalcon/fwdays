<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170726144253 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE promo_code_translations (id INT AUTO_INCREMENT NOT NULL, object_id INT DEFAULT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, INDEX IDX_89D0C96F232D562B (object_id), UNIQUE INDEX promo_code_lookup_unique_idx (locale, object_id, field), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE promo_code_translations ADD CONSTRAINT FK_89D0C96F232D562B FOREIGN KEY (object_id) REFERENCES event__promo_code (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event__news ADD meta_keywords VARCHAR(255) DEFAULT NULL, ADD meta_description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE news ADD meta_keywords VARCHAR(255) DEFAULT NULL, ADD meta_description VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE promo_code_translations');
        $this->addSql('ALTER TABLE event__news DROP meta_keywords, DROP meta_description');
        $this->addSql('ALTER TABLE news DROP meta_keywords, DROP meta_description');
    }
}
