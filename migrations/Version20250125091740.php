<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250125091740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE projects CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE users ADD background_image VARCHAR(255) DEFAULT NULL, ADD who_am_i LONGTEXT DEFAULT NULL, ADD experience LONGTEXT DEFAULT NULL, ADD qualities LONGTEXT DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users DROP background_image, DROP who_am_i, DROP experience, DROP qualities, CHANGE profile_picture profile_picture VARCHAR(512) DEFAULT NULL');
        $this->addSql('ALTER TABLE projects CHANGE user_id user_id INT DEFAULT NULL');
    }
}
