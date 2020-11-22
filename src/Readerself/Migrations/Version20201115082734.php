<?php

declare(strict_types=1);

namespace Readerself\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201115082734 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('RENAME TABLE action_feed_member TO action_feed;');

        $this->addSql('DROP INDEX action_id_feed_id_member_id ON action_feed;');
        $this->addSql('ALTER TABLE action_author ADD member_id INT UNSIGNED DEFAULT NULL;');
        $this->addSql('ALTER TABLE action_author ADD CONSTRAINT FK_7F9FE9A37597D3FE FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE;');
        $this->addSql('CREATE INDEX member_id ON action_author (member_id);');

        $this->addSql('DROP INDEX action_id_category_id ON action_category;');
        $this->addSql('ALTER TABLE action_category ADD member_id INT UNSIGNED DEFAULT NULL;');
        $this->addSql('ALTER TABLE action_category ADD CONSTRAINT FK_D19B69A77597D3FE FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE;');
        $this->addSql('CREATE INDEX member_id ON action_category (member_id);');

        $this->addSql('DROP INDEX action_id_feed_id_member_id ON action_feed;');
        $this->addSql('ALTER TABLE action_feed CHANGE member_id member_id INT UNSIGNED DEFAULT NULL;');

        $this->addSql('DROP INDEX action_id_item_id ON action_item;');
        $this->addSql('ALTER TABLE action_item ADD member_id INT UNSIGNED DEFAULT NULL;');
        $this->addSql('ALTER TABLE action_item ADD CONSTRAINT FK_69FA9E107597D3FE FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE;');
        $this->addSql('CREATE INDEX member_id ON action_item (member_id);');

        $this->addSql('INSERT INTO action_author (action_id, author_id, member_id, date_created) SELECT action_id, author_id, member_id, date_created FROM action_author_member;');
        $this->addSql('INSERT INTO action_category (action_id, category_id, member_id, date_created) SELECT action_id, category_id, member_id, date_created FROM action_category_member;');
        $this->addSql('INSERT INTO action_item (action_id, item_id, member_id, date_created) SELECT action_id, item_id, member_id, date_created FROM action_item_member;');

        $this->addSql('DROP TABLE action_author_member;');
        $this->addSql('DROP TABLE action_category_member;');
        $this->addSql('DROP TABLE action_item_member;');
    }

    public function down(Schema $schema) : void
    {
    }
}
