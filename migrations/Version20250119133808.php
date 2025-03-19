<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250119133808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE projects ADD images JSON DEFAULT NULL, ADD slug VARCHAR(255) NOT NULL, DROP image');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C93B3A4989D9B62 ON projects (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_5C93B3A4989D9B62 ON projects');
        $this->addSql('ALTER TABLE projects ADD image VARCHAR(255) DEFAULT NULL, DROP images, DROP slug');
    }
}
