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
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE users_balance');
    }
}
