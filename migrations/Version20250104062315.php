<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250104062315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'new table member_passkey';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE member_passkey (id INT UNSIGNED AUTO_INCREMENT NOT NULL, member_id INT UNSIGNED NOT NULL, title VARCHAR(255) NOT NULL, credential_id VARCHAR(255) NOT NULL, public_key TEXT NOT NULL, last_time_active DATETIME DEFAULT NULL, date_created DATETIME NOT NULL, INDEX member_id (member_id), INDEX credential_id (credential_id), INDEX date_created (date_created), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE member_passkey ADD CONSTRAINT FK_BF7183E87597D3FE FOREIGN KEY (member_id) REFERENCES `member` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE member_passkey DROP FOREIGN KEY FK_BF7183E87597D3FE');
        $this->addSql('DROP TABLE member_passkey');
    }
}
