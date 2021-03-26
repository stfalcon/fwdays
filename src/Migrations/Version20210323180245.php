<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210323180245 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'add ticket costs indexes and certificate';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX IDX_3D5054F28CDE5729 ON event__ticketsCost (type)');
        $this->addSql('CREATE INDEX IDX_3D5054F2DB578C16 ON event__ticketsCost (tickets_run_out)');
        $this->addSql('CREATE INDEX IDX_3D5054F2845CBB3E ON event__ticketsCost (end_date)');
        $this->addSql('CREATE INDEX IDX_3D5054F250F9BB84 ON event__ticketsCost (enabled)');
        $this->addSql('CREATE INDEX IDX_3D5054F27AB0E859 ON event__ticketsCost (visible)');
        $this->addSql('ALTER TABLE event__ticket_benefits ADD certificate VARCHAR(255) DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event__ticket_benefits DROP certificate, DROP updated_at');
        $this->addSql('DROP INDEX IDX_3D5054F28CDE5729 ON event__ticketsCost');
        $this->addSql('DROP INDEX IDX_3D5054F2DB578C16 ON event__ticketsCost');
        $this->addSql('DROP INDEX IDX_3D5054F2845CBB3E ON event__ticketsCost');
        $this->addSql('DROP INDEX IDX_3D5054F250F9BB84 ON event__ticketsCost');
        $this->addSql('DROP INDEX IDX_3D5054F27AB0E859 ON event__ticketsCost');
    }
}
