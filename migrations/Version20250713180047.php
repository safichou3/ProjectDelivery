<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250713180047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dish ADD chef_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_957D8CB8150A48F1 ON dish (chef_id)');
        $this->addSql('ALTER TABLE dish ADD CONSTRAINT FK_957D8CB8150A48F1 FOREIGN KEY (chef_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dish DROP FOREIGN KEY FK_957D8CB8150A48F1');
        $this->addSql('DROP INDEX IDX_957D8CB8150A48F1 ON dish');
        $this->addSql('ALTER TABLE dish DROP chef_id');
    }
}
