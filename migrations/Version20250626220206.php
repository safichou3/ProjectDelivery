<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250626220206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chef_schedule (id INT AUTO_INCREMENT NOT NULL, chef_id INT NOT NULL, day_of_week VARCHAR(255) NOT NULL, opening_time TIME NOT NULL, closing_time TIME NOT NULL, is_closed TINYINT(1) NOT NULL, exception_date DATE DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_FD98E49C150A48F1 (chef_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chef_schedule ADD CONSTRAINT FK_FD98E49C150A48F1 FOREIGN KEY (chef_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chef_schedule DROP FOREIGN KEY FK_FD98E49C150A48F1');
        $this->addSql('DROP TABLE chef_schedule');
    }
}
