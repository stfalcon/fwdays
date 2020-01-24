<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200121081635 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event__promo_code ADD created_by INT DEFAULT NULL, ADD description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event__promo_code ADD CONSTRAINT FK_F54EB066DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_F54EB066DE12AB56 ON event__promo_code (created_by)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event__promo_code DROP FOREIGN KEY FK_F54EB066DE12AB56');
        $this->addSql('DROP INDEX IDX_F54EB066DE12AB56 ON event__promo_code');
        $this->addSql('ALTER TABLE event__promo_code DROP created_by, DROP description');
    }
}
