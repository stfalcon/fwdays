<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140324104524 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("CREATE TABLE event__promo_code (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, discountAmount INT NOT NULL, code VARCHAR(255) NOT NULL, endDate DATETIME NOT NULL, INDEX IDX_F54EB06671F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE event__promo_code ADD CONSTRAINT FK_F54EB06671F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE event__tickets ADD promo_code_id INT DEFAULT NULL, ADD amount NUMERIC(10, 2) NOT NULL, ADD amount_without_discount NUMERIC(10, 2) NOT NULL, ADD has_discount TINYINT(1) NOT NULL");
        $this->addSql("ALTER TABLE event__tickets ADD CONSTRAINT FK_66E295592FAE4625 FOREIGN KEY (promo_code_id) REFERENCES event__promo_code (id) ON DELETE SET NULL");
        $this->addSql("CREATE INDEX IDX_66E295592FAE4625 ON event__tickets (promo_code_id)");
        $statament = $this->connection->prepare('SELECT * FROM payments');
        $statament->execute();
        while ($row = $statament->fetch()) {
            $this->addSql('UPDATE event__tickets SET amount = ' . $row['amount'] .
                ', amount_without_discount = ' . $row['amount_without_discount'] .
                ', has_discount = ' . $row['has_discount'] .
                ' WHERE payment_id = ' . $row['id']);
        }

        $this->addSql("ALTER TABLE payments DROP has_discount, DROP amount_without_discount");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE event__tickets DROP FOREIGN KEY FK_66E295592FAE4625");
        $this->addSql("DROP TABLE event__promo_code");
        $this->addSql("DROP INDEX IDX_66E295592FAE4625 ON event__tickets");
        $this->addSql("ALTER TABLE event__tickets DROP promo_code_id, DROP amount, DROP amount_without_discount, DROP has_discount");
        $this->addSql("ALTER TABLE payments ADD has_discount TINYINT(1) NOT NULL, ADD amount_without_discount NUMERIC(10, 2) NOT NULL");
    }
}
