<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210322152252 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX IDX_2FE319664B1EFC02 ON event__events (active)');
        $this->addSql('CREATE INDEX IDX_2FE319661F1D2E19 ON event__events (receive_payments)');
        $this->addSql('CREATE INDEX IDX_2FE31966AA9E377A ON event__events (date)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX IDX_2FE319664B1EFC02 ON event__events');
        $this->addSql('DROP INDEX IDX_2FE319661F1D2E19 ON event__events');
        $this->addSql('DROP INDEX IDX_2FE31966AA9E377A ON event__events');
    }
}
