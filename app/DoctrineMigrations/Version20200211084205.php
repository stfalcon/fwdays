<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200211084205 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE wayforpay_logs DROP FOREIGN KEY FK_B473FBBB4C3A3BB');
        $this->addSql('ALTER TABLE wayforpay_logs ADD CONSTRAINT FK_B473FBBB4C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ext_log_entries (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, logged_at DATETIME NOT NULL, object_id VARCHAR(32) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, object_class VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, version INT NOT NULL, data LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci` COMMENT \'(DC2Type:array)\', username VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, INDEX log_date_lookup_idx (logged_at), INDEX log_class_lookup_idx (object_class), INDEX log_user_lookup_idx (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE wayforpay_logs DROP FOREIGN KEY FK_B473FBBB4C3A3BB');
        $this->addSql('ALTER TABLE wayforpay_logs ADD CONSTRAINT FK_B473FBBB4C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id)');
    }
}
