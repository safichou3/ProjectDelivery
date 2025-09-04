<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250821023129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955CCD7E912');
        $this->addSql('DROP INDEX IDX_42C84955CCD7E912 ON reservation');
        $this->addSql('ALTER TABLE reservation ADD payment_type VARCHAR(255) NOT NULL, ADD quantity INT NOT NULL, DROP pickup_time, CHANGE menu_id dish_id INT NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id)');
        $this->addSql('CREATE INDEX IDX_42C84955148EB0CB ON reservation (dish_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955148EB0CB');
        $this->addSql('DROP INDEX IDX_42C84955148EB0CB ON reservation');
        $this->addSql('ALTER TABLE reservation ADD menu_id INT NOT NULL, ADD pickup_time TIME NOT NULL, DROP dish_id, DROP payment_type, DROP quantity');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('CREATE INDEX IDX_42C84955CCD7E912 ON reservation (menu_id)');
    }
}
