<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250802135300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE trade_reference');
        $this->addSql('ALTER TABLE admin_options DROP FOREIGN KEY FK_1409780E4256224E');
        $this->addSql('DROP INDEX UNIQ_1409780E4256224E ON admin_options');
        $this->addSql('ALTER TABLE admin_options DROP unsolicited_consultant_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE trade_reference (trade_id INT NOT NULL, reference_id INT NOT NULL, INDEX IDX_89F5256E1645DEA9 (reference_id), INDEX IDX_89F5256EC2D9760 (trade_id), PRIMARY KEY(trade_id, reference_id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE admin_options ADD unsolicited_consultant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admin_options ADD CONSTRAINT FK_1409780E4256224E FOREIGN KEY (unsolicited_consultant_id) REFERENCES employee (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1409780E4256224E ON admin_options (unsolicited_consultant_id)');
    }
}
