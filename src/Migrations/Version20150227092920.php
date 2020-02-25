<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150227092920 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE payments ADD base_amount NUMERIC(10, 2) NOT NULL, ADD fwdays_amount NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD referral_code VARCHAR(50) DEFAULT NULL, ADD balance NUMERIC(10, 2) DEFAULT \'0\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE payments DROP base_amount, DROP fwdays_amount');
        $this->addSql('ALTER TABLE users DROP referral_code, DROP balance');
    }
}
