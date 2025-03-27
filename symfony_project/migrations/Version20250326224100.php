<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250326224100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add User entity and default users';
    }

    public function up(Schema $schema): void
    {
        // Create user table
        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL,
            username VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            UNIQUE INDEX UNIQ_8D93D649F85E0677 (username),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add user_id to reservation table
        $this->addSql('ALTER TABLE reservation ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_42C84955A76ED395 ON reservation (user_id)');

        // Insert default users (passwords will be hashed in DataFixtures)
        $this->addSql("INSERT INTO `user` (username, roles, password) VALUES 
            ('admin', '[\"ROLE_ADMIN\"]', '\$2y\$13\$ESVwrL3ZfCMgwmxrGGxQd.ulhxVcUm3J5PP8ZfB2RPHZwkAQJju4e'), -- admin
            ('user', '[\"ROLE_USER\"]', '\$2y\$13\$vd9PTtZZrNcwsxgYGBa0UOEhYm.3ySwYFEGcluXyZt.AbXGBXoVEi')  -- user
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A76ED395');
        $this->addSql('DROP INDEX IDX_42C84955A76ED395 ON reservation');
        $this->addSql('ALTER TABLE reservation DROP user_id');
        $this->addSql('DROP TABLE `user`');
    }
}
