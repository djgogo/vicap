<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250901190442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE portfolio (id INT AUTO_INCREMENT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, name VARCHAR(255) NOT NULL, client VARCHAR(255) NOT NULL, features VARCHAR(2048) DEFAULT NULL, technologies VARCHAR(2048) DEFAULT NULL, description LONGTEXT DEFAULT NULL, portfolio_category_id INT DEFAULT NULL, INDEX IDX_A9ED1062AC7FAB36 (portfolio_category_id), INDEX name_idx (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE portfolio_category (id INT AUTO_INCREMENT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, INDEX name_idx (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE portfolio ADD CONSTRAINT FK_A9ED1062AC7FAB36 FOREIGN KEY (portfolio_category_id) REFERENCES portfolio_category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE portfolio DROP FOREIGN KEY FK_A9ED1062AC7FAB36');
        $this->addSql('DROP TABLE portfolio');
        $this->addSql('DROP TABLE portfolio_category');
    }
}
