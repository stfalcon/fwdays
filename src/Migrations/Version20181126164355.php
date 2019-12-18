<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181126164355 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event_block (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, text LONGTEXT DEFAULT NULL, visible TINYINT(1) NOT NULL, INDEX IDX_C200260E71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE block_translations (id INT AUTO_INCREMENT NOT NULL, object_id INT DEFAULT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, INDEX IDX_D76CDC8E232D562B (object_id), UNIQUE INDEX block_lookup_unique_idx (locale, object_id, field), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_block ADD CONSTRAINT FK_C200260E71F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE block_translations ADD CONSTRAINT FK_D76CDC8E232D562B FOREIGN KEY (object_id) REFERENCES event_block (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE block_translations DROP FOREIGN KEY FK_D76CDC8E232D562B');
        $this->addSql('DROP TABLE event_block');
        $this->addSql('DROP TABLE block_translations');
    }
}
