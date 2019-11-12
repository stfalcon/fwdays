<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191021080818 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lexik_translation_file CHANGE domain domain VARCHAR(191) NOT NULL, CHANGE locale locale VARCHAR(191) NOT NULL, CHANGE extention extention VARCHAR(191) NOT NULL, CHANGE path path VARCHAR(191) NOT NULL, CHANGE hash hash VARCHAR(191) NOT NULL');
        $this->addSql('ALTER TABLE lexik_trans_unit CHANGE key_name key_name VARCHAR(191) NOT NULL, CHANGE domain domain VARCHAR(191) NOT NULL');
        $this->addSql('ALTER TABLE lexik_trans_unit_translations CHANGE locale locale VARCHAR(191) NOT NULL');
        $this->addSql('ALTER TABLE event__mails CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lexik_trans_unit CHANGE key_name key_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE domain domain VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE lexik_trans_unit_translations CHANGE locale locale VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE lexik_translation_file CHANGE domain domain VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE locale locale VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, CHANGE extention extention VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, CHANGE path path VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE hash hash VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE event__mails CHANGE title title VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL');
    }
}
