<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181105145541 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE wayforpay_logs (id INT AUTO_INCREMENT NOT NULL, payment_id INT DEFAULT NULL, date DATETIME NOT NULL, status VARCHAR(255) NOT NULL, responseData LONGTEXT NOT NULL, fwdaysResponse LONGTEXT NOT NULL, INDEX IDX_B473FBBB4C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE wayforpay_logs ADD CONSTRAINT FK_B473FBBB4C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE wayforpay_logs');
    }
}
