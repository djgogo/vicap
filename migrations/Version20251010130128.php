<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251010130128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE media (id INT AUTO_INCREMENT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, file_name VARCHAR(255) DEFAULT NULL, file_path VARCHAR(255) DEFAULT NULL, file_type VARCHAR(255) DEFAULT NULL, file_size INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE blog CHANGE author_id author_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE media');
        $this->addSql('ALTER TABLE blog CHANGE author_id author_id INT NOT NULL');
    }
}
