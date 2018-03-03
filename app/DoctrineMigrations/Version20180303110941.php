<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180303110941 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event__tickets DROP FOREIGN KEY FK_66E295591173DC2D');
        $this->addSql('ALTER TABLE event__tickets ADD CONSTRAINT FK_66E295591173DC2D FOREIGN KEY (ticket_cost_id) REFERENCES event__ticketsCost (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E944E55A94');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E944E55A94 FOREIGN KEY (user_ref_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event__tickets DROP FOREIGN KEY FK_66E295591173DC2D');
        $this->addSql('ALTER TABLE event__tickets ADD CONSTRAINT FK_66E295591173DC2D FOREIGN KEY (ticket_cost_id) REFERENCES event__ticketsCost (id)');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E944E55A94');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E944E55A94 FOREIGN KEY (user_ref_id) REFERENCES users (id)');
    }
}
