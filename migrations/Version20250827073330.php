<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250827073330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE menu DROP COLUMN IF EXISTS price');
    }


    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE menu ADD price NUMERIC(10,2) NOT NULL');
    }
}
