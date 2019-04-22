<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190419062426 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event__events_sponsors DROP FOREIGN KEY FK_3CCEC92812F7FB51');
        $this->addSql('ALTER TABLE event__events_sponsors ADD CONSTRAINT FK_3CCEC92812F7FB51 FOREIGN KEY (sponsor_id) REFERENCES sponsors (id)');
        $this->addSql('ALTER TABLE event__ticketsCost DROP FOREIGN KEY FK_3D5054F271F7E88B');
        $this->addSql('ALTER TABLE event__ticketsCost ADD CONSTRAINT FK_3D5054F271F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event__events_sponsors DROP FOREIGN KEY FK_3CCEC92812F7FB51');
        $this->addSql('ALTER TABLE event__events_sponsors ADD CONSTRAINT FK_3CCEC92812F7FB51 FOREIGN KEY (sponsor_id) REFERENCES sponsors (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event__ticketsCost DROP FOREIGN KEY FK_3D5054F271F7E88B');
        $this->addSql('ALTER TABLE event__ticketsCost ADD CONSTRAINT FK_3D5054F271F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON DELETE CASCADE');
    }
}
