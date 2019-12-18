<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190422132317 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event_news_translations DROP FOREIGN KEY FK_B564E951232D562B');
        $this->addSql('ALTER TABLE news_translations DROP FOREIGN KEY FK_20FDB330232D562B');
        $this->addSql('DROP TABLE event__news');
        $this->addSql('DROP TABLE event_news_translations');
        $this->addSql('DROP TABLE news');
        $this->addSql('DROP TABLE news_translations');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event__news (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, slug VARCHAR(255) NOT NULL COLLATE utf8_general_ci, title VARCHAR(255) NOT NULL COLLATE utf8_general_ci, preview LONGTEXT NOT NULL COLLATE utf8_general_ci, text LONGTEXT NOT NULL COLLATE utf8_general_ci, created_at DATETIME NOT NULL, meta_keywords VARCHAR(255) DEFAULT NULL COLLATE utf8_general_ci, meta_description VARCHAR(255) DEFAULT NULL COLLATE utf8_general_ci, INDEX IDX_1ED586771F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_news_translations (id INT AUTO_INCREMENT NOT NULL, object_id INT DEFAULT NULL, locale VARCHAR(8) NOT NULL COLLATE utf8_unicode_ci, field VARCHAR(32) NOT NULL COLLATE utf8_unicode_ci, content LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX event_news_lookup_unique_idx (locale, object_id, field), INDEX IDX_B564E951232D562B (object_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE news (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(255) NOT NULL COLLATE utf8_general_ci, title VARCHAR(255) NOT NULL COLLATE utf8_general_ci, preview LONGTEXT NOT NULL COLLATE utf8_general_ci, text LONGTEXT NOT NULL COLLATE utf8_general_ci, created_at DATETIME NOT NULL, meta_keywords VARCHAR(255) DEFAULT NULL COLLATE utf8_general_ci, meta_description VARCHAR(255) DEFAULT NULL COLLATE utf8_general_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE news_translations (id INT AUTO_INCREMENT NOT NULL, object_id INT DEFAULT NULL, locale VARCHAR(8) NOT NULL COLLATE utf8_unicode_ci, field VARCHAR(32) NOT NULL COLLATE utf8_unicode_ci, content LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX news_lookup_unique_idx (locale, object_id, field), INDEX IDX_20FDB330232D562B (object_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event__news ADD CONSTRAINT FK_1ED586771F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id)');
        $this->addSql('ALTER TABLE event_news_translations ADD CONSTRAINT FK_B564E951232D562B FOREIGN KEY (object_id) REFERENCES event__news (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE news_translations ADD CONSTRAINT FK_20FDB330232D562B FOREIGN KEY (object_id) REFERENCES news (id) ON DELETE CASCADE');
    }
}
