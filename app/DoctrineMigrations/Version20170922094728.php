<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170922094728 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reviews_users_likes DROP FOREIGN KEY FK_8009513FA76ED395');
        $this->addSql('ALTER TABLE reviews_users_likes ADD CONSTRAINT FK_8009513FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event__events ADD approximateDate VARCHAR(255) DEFAULT NULL, ADD useApproximateDate TINYINT(1) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reviews_users_likes DROP FOREIGN KEY FK_8009513FA76ED395');
        $this->addSql('ALTER TABLE reviews_users_likes ADD CONSTRAINT FK_8009513FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE event__events DROP approximateDate, DROP useApproximateDate');

    }
}
