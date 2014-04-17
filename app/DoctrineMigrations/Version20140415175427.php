<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140415175427 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE reviews_users_likes (review_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_8009513F3E2E969B (review_id), INDEX IDX_8009513FA76ED395 (user_id), PRIMARY KEY(review_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE reviews_users_likes ADD CONSTRAINT FK_8009513F3E2E969B FOREIGN KEY (review_id) REFERENCES event__reviews (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE reviews_users_likes ADD CONSTRAINT FK_8009513FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE reviews_users_likes");
    }
}
