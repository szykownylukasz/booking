<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240326191500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add default settings for spots and price';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO settings (`key`, `value`, `updated_at`) VALUES (?, ?, NOW())', [
            'default_total_spots',
            '10' // default total spots
        ]);

        $this->addSql('INSERT INTO settings (`key`, `value`, `updated_at`) VALUES (?, ?, NOW())', [
            'daily_price',
            '100.00' // default daily price
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM settings WHERE `key` IN (?, ?)', [
            'default_total_spots',
            'daily_price'
        ]);
    }
}
