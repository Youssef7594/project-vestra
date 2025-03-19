<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250205152756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE followers ADD CONSTRAINT FK_8408FDA7AC24F853 FOREIGN KEY (follower_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE followers ADD CONSTRAINT FK_8408FDA7D956F010 FOREIGN KEY (followed_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_8408FDA7AC24F853 ON followers (follower_id)');
        $this->addSql('CREATE INDEX IDX_8408FDA7D956F010 ON followers (followed_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE followers DROP FOREIGN KEY FK_8408FDA7AC24F853');
        $this->addSql('ALTER TABLE followers DROP FOREIGN KEY FK_8408FDA7D956F010');
        $this->addSql('DROP INDEX IDX_8408FDA7AC24F853 ON followers');
        $this->addSql('DROP INDEX IDX_8408FDA7D956F010 ON followers');
    }
}
