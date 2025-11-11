<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251105000150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_paid boolean column to reservation table';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('reservation')) {
            $table = $schema->getTable('reservation');
            if (!$table->hasColumn('is_paid')) {
                $table->addColumn('is_paid', 'boolean', ['notnull' => true, 'default' => false]);
            }
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('reservation')) {
            $table = $schema->getTable('reservation');
            if ($table->hasColumn('is_paid')) {
                $table->dropColumn('is_paid');
            }
        }
    }
}
