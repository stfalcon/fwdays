<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150227122017 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE users ADD user_ref_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E944E55A94 FOREIGN KEY (user_ref_id) REFERENCES users (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E944E55A94 ON users (user_ref_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E944E55A94');
        $this->addSql('DROP INDEX UNIQ_1483A5E944E55A94 ON users');
        $this->addSql('ALTER TABLE users DROP user_ref_id');
    }
}
