<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170504091342 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE users_balance (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, payment_id INT DEFAULT NULL, created_at DATETIME NOT NULL, operation_amount NUMERIC(10, 2) NOT NULL, balance NUMERIC(10, 2) NOT NULL, description LONGTEXT, INDEX IDX_5CF46B34A76ED395 (user_id), INDEX IDX_5CF46B344C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE users_balance ADD CONSTRAINT FK_5CF46B34A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users_balance ADD CONSTRAINT FK_5CF46B344C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE SET NULL');
        $this->addSql('DROP TABLE ext_log_entries');
        $this->addSql('DROP TABLE ext_translations');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ext_log_entries (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL COLLATE utf8_general_ci, logged_at DATETIME NOT NULL, object_id VARCHAR(32) DEFAULT NULL COLLATE utf8_general_ci, object_class VARCHAR(255) NOT NULL COLLATE utf8_general_ci, version INT NOT NULL, data LONGTEXT DEFAULT NULL COLLATE utf8_general_ci COMMENT \'(DC2Type:array)\', username VARCHAR(255) DEFAULT NULL COLLATE utf8_general_ci, INDEX log_class_lookup_idx (object_class), INDEX log_date_lookup_idx (logged_at), INDEX log_user_lookup_idx (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ext_translations (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL COLLATE utf8_general_ci, object_class VARCHAR(255) NOT NULL COLLATE utf8_general_ci, field VARCHAR(32) NOT NULL COLLATE utf8_general_ci, foreign_key VARCHAR(64) NOT NULL COLLATE utf8_general_ci, content LONGTEXT DEFAULT NULL COLLATE utf8_general_ci, UNIQUE INDEX lookup_unique_idx (locale, object_class, foreign_key, field), INDEX translations_lookup_idx (locale, object_class, foreign_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE users_balance');
    }
}
