<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625184731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_5A8A6C8D9446177D ON post
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD title VARCHAR(255) NOT NULL, ADD title_arabic VARCHAR(255) NOT NULL, DROP title_latin, DROP title_arab
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_5A8A6C8D2B36786B ON post (title)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_5A8A6C8D9794B4FE ON post (title_arabic)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_5A8A6C8D2B36786B ON post
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_5A8A6C8D9794B4FE ON post
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD title_latin VARCHAR(255) NOT NULL, ADD title_arab VARCHAR(255) NOT NULL, DROP title, DROP title_arabic
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_5A8A6C8D9446177D ON post (title_latin)
        SQL);
    }
}
