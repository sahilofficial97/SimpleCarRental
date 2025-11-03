<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251029000220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add price_per_day column to car table';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('car')) {
            return;
        }

        $table = $schema->getTable('car');
        if (!$table->hasColumn('price_per_day')) {
            $table->addColumn('price_per_day', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'notnull' => true,
                'default' => '0.00',
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('car')) {
            $table = $schema->getTable('car');
            if ($table->hasColumn('price_per_day')) {
                $table->dropColumn('price_per_day');
            }
        }
    }
}
