<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230402040938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'replace ip and agent by extra fields';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE connection ADD extra_fields JSON DEFAULT NULL, DROP ip, DROP agent');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE connection ADD ip VARCHAR(255) DEFAULT NULL, ADD agent VARCHAR(255) DEFAULT NULL, DROP extra_fields');
    }
}
