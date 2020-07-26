<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200725140629 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event__ticket_benefits (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, type VARCHAR(20) NOT NULL, benefits LONGTEXT NOT NULL, INDEX IDX_48F8B15B71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket_benefit_translations (id INT AUTO_INCREMENT NOT NULL, object_id INT DEFAULT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, INDEX IDX_81F8A342232D562B (object_id), UNIQUE INDEX benefit_lookup_unique_idx (locale, object_id, field), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event__ticket_benefits ADD CONSTRAINT FK_48F8B15B71F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id)');
        $this->addSql('ALTER TABLE ticket_benefit_translations ADD CONSTRAINT FK_81F8A342232D562B FOREIGN KEY (object_id) REFERENCES event__ticket_benefits (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event__ticketsCost ADD type VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ticket_benefit_translations DROP FOREIGN KEY FK_81F8A342232D562B');
        $this->addSql('DROP TABLE event__ticket_benefits');
        $this->addSql('DROP TABLE ticket_benefit_translations');
        $this->addSql('ALTER TABLE event__ticketsCost DROP type');
    }
}
