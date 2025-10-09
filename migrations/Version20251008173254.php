<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251008173254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blog (id INT AUTO_INCREMENT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, title VARCHAR(255) NOT NULL, author VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, INDEX title_idx (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE blog_blog_category (blog_id INT NOT NULL, blog_category_id INT NOT NULL, INDEX IDX_197F78ADAE07E97 (blog_id), INDEX IDX_197F78ACB76011C (blog_category_id), PRIMARY KEY(blog_id, blog_category_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE blog_category (id INT AUTO_INCREMENT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, name VARCHAR(255) NOT NULL, INDEX name_idx (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE blog_blog_category ADD CONSTRAINT FK_197F78ADAE07E97 FOREIGN KEY (blog_id) REFERENCES blog (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_blog_category ADD CONSTRAINT FK_197F78ACB76011C FOREIGN KEY (blog_category_id) REFERENCES blog_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_options CHANGE admin_email admin_email VARCHAR(255) NOT NULL, CHANGE sales_email sales_email VARCHAR(255) DEFAULT NULL, CHANGE company_name company_name VARCHAR(255) DEFAULT NULL, CHANGE company_address company_address VARCHAR(1024) DEFAULT NULL, CHANGE company_phone company_phone VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE countries CHANGE name name VARCHAR(128) NOT NULL, CHANGE iso_code iso_code VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE email_templates CHANGE name name VARCHAR(255) NOT NULL, CHANGE locale locale VARCHAR(20) NOT NULL, CHANGE subject subject VARCHAR(1024) NOT NULL, CHANGE content content LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE notifications CHANGE message_key message_key VARCHAR(255) NOT NULL, CHANGE type type VARCHAR(50) DEFAULT NULL, CHANGE url url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE password_reset_code CHANGE code code VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE portfolio CHANGE name name VARCHAR(255) NOT NULL, CHANGE client client VARCHAR(255) NOT NULL, CHANGE features features VARCHAR(2048) DEFAULT NULL, CHANGE technologies technologies VARCHAR(2048) DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE website_url website_url VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE portfolio_category CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE postal_code CHANGE city city VARCHAR(255) NOT NULL, CHANGE state state VARCHAR(255) NOT NULL, CHANGE state_code state_code VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE reference CHANGE name name VARCHAR(255) NOT NULL, CHANGE logo logo VARCHAR(255) DEFAULT NULL, CHANGE url url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE registration_codes CHANGE code code VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE term_templates CHANGE name name VARCHAR(255) NOT NULL, CHANGE locale locale VARCHAR(20) NOT NULL, CHANGE content content LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(128) NOT NULL, CHANGE password password VARCHAR(255) NOT NULL, CHANGE first_name first_name VARCHAR(254) DEFAULT NULL, CHANGE last_name last_name VARCHAR(254) DEFAULT NULL, CHANGE about about LONGTEXT DEFAULT NULL, CHANGE company company VARCHAR(254) DEFAULT NULL, CHANGE photo photo VARCHAR(255) DEFAULT NULL, CHANGE phone phone VARCHAR(32) DEFAULT NULL, CHANGE city city VARCHAR(255) DEFAULT NULL, CHANGE address address VARCHAR(512) DEFAULT NULL, CHANGE zip zip VARCHAR(12) DEFAULT NULL, CHANGE website_url website_url VARCHAR(255) DEFAULT NULL, CHANGE instagram_url instagram_url VARCHAR(255) DEFAULT NULL, CHANGE facebook_url facebook_url VARCHAR(255) DEFAULT NULL, CHANGE x_url x_url VARCHAR(255) DEFAULT NULL, CHANGE job_designation job_designation LONGTEXT DEFAULT NULL, CHANGE locale locale LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_log CHANGE action action VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user_options CHANGE name name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blog_blog_category DROP FOREIGN KEY FK_197F78ADAE07E97');
        $this->addSql('ALTER TABLE blog_blog_category DROP FOREIGN KEY FK_197F78ACB76011C');
        $this->addSql('DROP TABLE blog');
        $this->addSql('DROP TABLE blog_blog_category');
        $this->addSql('DROP TABLE blog_category');
        $this->addSql('ALTER TABLE portfolio_category CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`');
        $this->addSql('ALTER TABLE registration_codes CHANGE code code VARCHAR(64) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE countries CHANGE name name VARCHAR(128) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE iso_code iso_code VARCHAR(2) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE user_log CHANGE action action VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE notifications CHANGE message_key message_key VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE type type VARCHAR(50) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE url url VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE password_reset_code CHANGE code code VARCHAR(64) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE portfolio CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`, CHANGE client client VARCHAR(255) NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`, CHANGE website_url website_url VARCHAR(255) NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`, CHANGE features features VARCHAR(2048) DEFAULT NULL COLLATE `utf8mb4_uca1400_ai_ci`, CHANGE technologies technologies VARCHAR(2048) DEFAULT NULL COLLATE `utf8mb4_uca1400_ai_ci`, CHANGE description description LONGTEXT DEFAULT NULL COLLATE `utf8mb4_uca1400_ai_ci`, CHANGE image image VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_uca1400_ai_ci`');
        $this->addSql('ALTER TABLE email_templates CHANGE name name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE locale locale VARCHAR(20) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE subject subject VARCHAR(1024) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE content content LONGTEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE term_templates CHANGE name name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE locale locale VARCHAR(20) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE content content LONGTEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(128) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE password password VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE locale locale LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE first_name first_name VARCHAR(254) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE last_name last_name VARCHAR(254) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE about about LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE company company VARCHAR(254) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE photo photo VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE website_url website_url VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE instagram_url instagram_url VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE facebook_url facebook_url VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE x_url x_url VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE phone phone VARCHAR(32) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE job_designation job_designation LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE city city VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE address address VARCHAR(512) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE zip zip VARCHAR(12) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE reference CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`, CHANGE logo logo VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_uca1400_ai_ci`, CHANGE url url VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_uca1400_ai_ci`');
        $this->addSql('ALTER TABLE admin_options CHANGE admin_email admin_email VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE sales_email sales_email VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE company_name company_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE company_address company_address VARCHAR(1024) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE company_phone company_phone VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('ALTER TABLE postal_code CHANGE city city VARCHAR(255) NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`, CHANGE state state VARCHAR(255) NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`, CHANGE state_code state_code VARCHAR(50) NOT NULL COLLATE `utf8mb4_uca1400_ai_ci`');
        $this->addSql('ALTER TABLE user_options CHANGE name name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`');
    }
}
