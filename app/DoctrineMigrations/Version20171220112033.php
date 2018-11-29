<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171220112033 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event_speakers_candidate (speaker_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_BB90FF25D04A0F27 (speaker_id), INDEX IDX_BB90FF2571F7E88B (event_id), PRIMARY KEY(speaker_id, event_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event__ticketsCost (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, count INT DEFAULT NULL, amount NUMERIC(10, 2) NOT NULL, alt_amount NUMERIC(10, 2) NOT NULL, sold_count INT DEFAULT NULL, enabled TINYINT(1) DEFAULT \'1\' NOT NULL, unlimited TINYINT(1) DEFAULT \'0\' NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_3D5054F271F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_wants_visit_event (user_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_D23C106DA76ED395 (user_id), INDEX IDX_D23C106D71F7E88B (event_id), PRIMARY KEY(user_id, event_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_speakers_candidate ADD CONSTRAINT FK_BB90FF25D04A0F27 FOREIGN KEY (speaker_id) REFERENCES event__speakers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_speakers_candidate ADD CONSTRAINT FK_BB90FF2571F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event__ticketsCost ADD CONSTRAINT FK_3D5054F271F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_wants_visit_event ADD CONSTRAINT FK_D23C106DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE user_wants_visit_event ADD CONSTRAINT FK_D23C106D71F7E88B FOREIGN KEY (event_id) REFERENCES event__events (id)');
        $this->addSql('ALTER TABLE event__speakers ADD sort_order INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE payments ADD refunded_amount NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE event__tickets ADD ticket_cost_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event__tickets ADD CONSTRAINT FK_66E295591173DC2D FOREIGN KEY (ticket_cost_id) REFERENCES event__ticketsCost (id)');
        $this->addSql('CREATE INDEX IDX_66E295591173DC2D ON event__tickets (ticket_cost_id)');
        $this->addSql('ALTER TABLE reviews_users_likes DROP FOREIGN KEY FK_8009513FA76ED395');
        $this->addSql('ALTER TABLE reviews_users_likes ADD CONSTRAINT FK_8009513FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event__mails ADD wants_visit_event TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE event__events ADD approximateDate VARCHAR(255) DEFAULT NULL, ADD useApproximateDate TINYINT(1) DEFAULT NULL, ADD dateEnd DATETIME DEFAULT NULL, ADD wantsToVisitCount INT DEFAULT NULL, ADD smallEvent TINYINT(1) NOT NULL, ADD background_color VARCHAR(7) DEFAULT \'#4e4e84\' NOT NULL, ADD meta_description VARCHAR(255) DEFAULT NULL, CHANGE cost cost NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE event__promo_code ADD usedCount INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE event__pages ADD text_new LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE sponsors_category ADD is_wide_container TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE users ADD name VARCHAR(255) NOT NULL, ADD surname VARCHAR(255) NOT NULL, ADD phone VARCHAR(20) DEFAULT NULL, ADD email_exists VARCHAR(255) DEFAULT \'1\', ADD facebook_id VARCHAR(255) DEFAULT NULL, ADD google_id VARCHAR(255) DEFAULT NULL, CHANGE fullname fullname VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event__tickets DROP FOREIGN KEY FK_66E295591173DC2D');
        $this->addSql('CREATE TABLE ext_log_entries (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL COLLATE utf8_general_ci, logged_at DATETIME NOT NULL, object_id VARCHAR(32) DEFAULT NULL COLLATE utf8_general_ci, object_class VARCHAR(255) NOT NULL COLLATE utf8_general_ci, version INT NOT NULL, data LONGTEXT DEFAULT NULL COLLATE utf8_general_ci COMMENT \'(DC2Type:array)\', username VARCHAR(255) DEFAULT NULL COLLATE utf8_general_ci, INDEX log_class_lookup_idx (object_class), INDEX log_date_lookup_idx (logged_at), INDEX log_user_lookup_idx (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ext_translations (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL COLLATE utf8_general_ci, object_class VARCHAR(255) NOT NULL COLLATE utf8_general_ci, field VARCHAR(32) NOT NULL COLLATE utf8_general_ci, foreign_key VARCHAR(64) NOT NULL COLLATE utf8_general_ci, content LONGTEXT DEFAULT NULL COLLATE utf8_general_ci, UNIQUE INDEX lookup_unique_idx (locale, object_class, foreign_key, field), INDEX translations_lookup_idx (locale, object_class, foreign_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE event_speakers_candidate');
        $this->addSql('DROP TABLE event__ticketsCost');
        $this->addSql('DROP TABLE user_wants_visit_event');
        $this->addSql('ALTER TABLE event__events DROP approximateDate, DROP useApproximateDate, DROP dateEnd, DROP wantsToVisitCount, DROP smallEvent, DROP background_color, DROP meta_description, CHANGE cost cost NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE event__mails DROP wants_visit_event');
        $this->addSql('ALTER TABLE event__pages DROP text_new');
        $this->addSql('ALTER TABLE event__promo_code DROP usedCount');
        $this->addSql('ALTER TABLE event__speakers DROP sort_order');
        $this->addSql('DROP INDEX IDX_66E295591173DC2D ON event__tickets');
        $this->addSql('ALTER TABLE event__tickets DROP ticket_cost_id');
        $this->addSql('ALTER TABLE payments DROP refunded_amount');
        $this->addSql('ALTER TABLE reviews_users_likes DROP FOREIGN KEY FK_8009513FA76ED395');
        $this->addSql('ALTER TABLE reviews_users_likes ADD CONSTRAINT FK_8009513FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE sponsors_category DROP is_wide_container');
        $this->addSql('ALTER TABLE users DROP name, DROP surname, DROP phone, DROP email_exists, DROP facebook_id, DROP google_id, CHANGE fullname fullname VARCHAR(255) NOT NULL COLLATE utf8_general_ci');
    }
}
