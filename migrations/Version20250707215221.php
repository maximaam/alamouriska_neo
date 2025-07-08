<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250707215221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_comment ADD wall_id INT DEFAULT NULL, CHANGE post_id post_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_comment ADD CONSTRAINT FK_CC794C66C33923F1 FOREIGN KEY (wall_id) REFERENCES wall (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CC794C66C33923F1 ON user_comment (wall_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_like ADD wall_id INT DEFAULT NULL, CHANGE post_id post_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_like ADD CONSTRAINT FK_D6E20C7AC33923F1 FOREIGN KEY (wall_id) REFERENCES wall (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D6E20C7AC33923F1 ON user_like (wall_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_like DROP FOREIGN KEY FK_D6E20C7AC33923F1
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_D6E20C7AC33923F1 ON user_like
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_like DROP wall_id, CHANGE post_id post_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_comment DROP FOREIGN KEY FK_CC794C66C33923F1
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_CC794C66C33923F1 ON user_comment
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_comment DROP wall_id, CHANGE post_id post_id INT NOT NULL
        SQL);
    }
}
