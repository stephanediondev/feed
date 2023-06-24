<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230624052414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Delete action_item unread (12)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM action_item WHERE action_id = 12;');
    }

    public function down(Schema $schema): void
    {
    }
}
