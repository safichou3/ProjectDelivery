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
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dish ADD CONSTRAINT FK_957D8CB8150A48F1 FOREIGN KEY (chef_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_957D8CB8150A48F1 ON dish (chef_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dish DROP FOREIGN KEY FK_957D8CB8150A48F1');
        $this->addSql('DROP INDEX IDX_957D8CB8150A48F1 ON dish');
    }
}
