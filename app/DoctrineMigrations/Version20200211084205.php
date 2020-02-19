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

        $this->addSql('ALTER TABLE wayforpay_logs DROP FOREIGN KEY FK_B473FBBB4C3A3BB');
        $this->addSql('ALTER TABLE wayforpay_logs ADD CONSTRAINT FK_B473FBBB4C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id)');
    }
}
