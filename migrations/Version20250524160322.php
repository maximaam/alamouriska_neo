<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250524160322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP FOREIGN KEY FK_8D93D64986383B10
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_8D93D64986383B10 ON user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD avatar_name VARCHAR(255) DEFAULT NULL, ADD avatar_original_name VARCHAR(255) DEFAULT NULL, ADD avatar_mime_type VARCHAR(255) DEFAULT NULL, ADD avatar_dimensions LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', CHANGE avatar_id avatar_size INT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `user` DROP avatar_name, DROP avatar_original_name, DROP avatar_mime_type, DROP avatar_dimensions, CHANGE avatar_size avatar_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `user` ADD CONSTRAINT FK_8D93D64986383B10 FOREIGN KEY (avatar_id) REFERENCES image (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8D93D64986383B10 ON `user` (avatar_id)
        SQL);
    }
}
