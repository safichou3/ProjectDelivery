<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250831040019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_approval DROP FOREIGN KEY FK_8240922D150A48F1');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744B83297E7');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('DROP TABLE admin_approval');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE notification');
        $this->addSql('ALTER TABLE chef_profile DROP FOREIGN KEY FK_4684ACEBA76ED395');
        $this->addSql('ALTER TABLE chef_profile ADD CONSTRAINT FK_4684ACEBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chef_schedule DROP FOREIGN KEY FK_FD98E49C150A48F1');
        $this->addSql('ALTER TABLE chef_schedule ADD CONSTRAINT FK_FD98E49C150A48F1 FOREIGN KEY (chef_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dish DROP FOREIGN KEY FK_957D8CB8150A48F1');
        $this->addSql('ALTER TABLE dish DROP FOREIGN KEY FK_957D8CB8CCD7E912');
        $this->addSql('ALTER TABLE dish ADD CONSTRAINT FK_957D8CB8150A48F1 FOREIGN KEY (chef_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dish ADD CONSTRAINT FK_957D8CB8CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favorite_dish DROP FOREIGN KEY FK_7B7B9587A76ED395');
        $this->addSql('ALTER TABLE favorite_dish DROP FOREIGN KEY FK_7B7B9587148EB0CB');
        $this->addSql('ALTER TABLE favorite_dish ADD CONSTRAINT FK_7B7B9587A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favorite_dish ADD CONSTRAINT FK_7B7B9587148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A93150A48F1');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A93150A48F1 FOREIGN KEY (chef_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DB83297E7');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495519EB6921');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955148EB0CB');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495519EB6921 FOREIGN KEY (client_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6150A48F1');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C619EB6921');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6150A48F1 FOREIGN KEY (chef_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C619EB6921 FOREIGN KEY (client_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE support_message DROP FOREIGN KEY FK_B883883A76ED395');
        $this->addSql('ALTER TABLE support_message ADD CONSTRAINT FK_B883883A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin_approval (id INT AUTO_INCREMENT NOT NULL, chef_id INT NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, admin_comment LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_8240922D150A48F1 (chef_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, reservation_id INT NOT NULL, total_amount NUMERIC(10, 2) NOT NULL, platform_fee NUMERIC(10, 2) NOT NULL, amount_to_chef NUMERIC(10, 2) NOT NULL, is_paid_to_chef TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_90651744B83297E7 (reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, message LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE admin_approval ADD CONSTRAINT FK_8240922D150A48F1 FOREIGN KEY (chef_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chef_profile DROP FOREIGN KEY FK_4684ACEBA76ED395');
        $this->addSql('ALTER TABLE chef_profile ADD CONSTRAINT FK_4684ACEBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chef_schedule DROP FOREIGN KEY FK_FD98E49C150A48F1');
        $this->addSql('ALTER TABLE chef_schedule ADD CONSTRAINT FK_FD98E49C150A48F1 FOREIGN KEY (chef_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dish DROP FOREIGN KEY FK_957D8CB8CCD7E912');
        $this->addSql('ALTER TABLE dish DROP FOREIGN KEY FK_957D8CB8150A48F1');
        $this->addSql('ALTER TABLE dish ADD CONSTRAINT FK_957D8CB8CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('ALTER TABLE dish ADD CONSTRAINT FK_957D8CB8150A48F1 FOREIGN KEY (chef_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE favorite_dish DROP FOREIGN KEY FK_7B7B9587A76ED395');
        $this->addSql('ALTER TABLE favorite_dish DROP FOREIGN KEY FK_7B7B9587148EB0CB');
        $this->addSql('ALTER TABLE favorite_dish ADD CONSTRAINT FK_7B7B9587A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE favorite_dish ADD CONSTRAINT FK_7B7B9587148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id)');
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A93150A48F1');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A93150A48F1 FOREIGN KEY (chef_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DB83297E7');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495519EB6921');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955148EB0CB');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495519EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id)');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C619EB6921');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6150A48F1');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C619EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6150A48F1 FOREIGN KEY (chef_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE support_message DROP FOREIGN KEY FK_B883883A76ED395');
        $this->addSql('ALTER TABLE support_message ADD CONSTRAINT FK_B883883A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }
}
