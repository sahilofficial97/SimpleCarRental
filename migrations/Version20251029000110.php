<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251029000110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create car table with brand_name, seat_amount, color, and type';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('car')) {
            return;
        }

        $table = $schema->createTable('car');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('brand_name', 'string', ['length' => 120, 'notnull' => true]);
        $table->addColumn('seat_amount', 'integer', ['notnull' => true]);
        $table->addColumn('color', 'string', ['length' => 50, 'notnull' => true]);
        $table->addColumn('type', 'string', ['length' => 20, 'notnull' => true]);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('car')) {
            $schema->dropTable('car');
        }
    }
}
