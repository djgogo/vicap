<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250902194159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE portfolio_portfolio_category (portfolio_id INT NOT NULL, portfolio_category_id INT NOT NULL, INDEX IDX_A6F563E1B96B5643 (portfolio_id), INDEX IDX_A6F563E1AC7FAB36 (portfolio_category_id), PRIMARY KEY(portfolio_id, portfolio_category_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE portfolio_portfolio_category ADD CONSTRAINT FK_A6F563E1B96B5643 FOREIGN KEY (portfolio_id) REFERENCES portfolio (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE portfolio_portfolio_category ADD CONSTRAINT FK_A6F563E1AC7FAB36 FOREIGN KEY (portfolio_category_id) REFERENCES portfolio_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE portfolio DROP FOREIGN KEY FK_A9ED1062AC7FAB36');
        $this->addSql('DROP INDEX IDX_A9ED1062AC7FAB36 ON portfolio');
        $this->addSql('ALTER TABLE portfolio DROP portfolio_category_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE portfolio_portfolio_category DROP FOREIGN KEY FK_A6F563E1B96B5643');
        $this->addSql('ALTER TABLE portfolio_portfolio_category DROP FOREIGN KEY FK_A6F563E1AC7FAB36');
        $this->addSql('DROP TABLE portfolio_portfolio_category');
        $this->addSql('ALTER TABLE portfolio ADD portfolio_category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE portfolio ADD CONSTRAINT FK_A9ED1062AC7FAB36 FOREIGN KEY (portfolio_category_id) REFERENCES portfolio_category (id)');
        $this->addSql('CREATE INDEX IDX_A9ED1062AC7FAB36 ON portfolio (portfolio_category_id)');
    }
}
