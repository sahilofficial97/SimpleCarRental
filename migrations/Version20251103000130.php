<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251103000130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create reservation table with user and car relations, dates, total price, and createdAt';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('reservation')) {
            return;
        }

        $table = $schema->createTable('reservation');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('car_id', 'integer', ['notnull' => true]);
        $table->addColumn('start_date', 'date_immutable', ['notnull' => true]);
        $table->addColumn('end_date', 'date_immutable', ['notnull' => true]);
        $table->addColumn('total_price', 'decimal', ['precision' => 10, 'scale' => 2, 'notnull' => true]);
        $table->addColumn('created_at', 'datetime_immutable', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_id'], 'idx_reservation_user');
        $table->addIndex(['car_id'], 'idx_reservation_car');

        // Add foreign keys if supported in this migration style
        if (method_exists($table, 'addForeignKeyConstraint')) {
            $table->addForeignKeyConstraint('user', ['user_id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_reservation_user');
            $table->addForeignKeyConstraint('car', ['car_id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_reservation_car');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('reservation')) {
            $schema->dropTable('reservation');
        }
    }
}
