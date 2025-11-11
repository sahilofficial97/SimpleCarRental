<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251106000160 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add payment_reference (nullable string) to reservation table';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('reservation')) {
            $table = $schema->getTable('reservation');
            if (!$table->hasColumn('payment_reference')) {
                $table->addColumn('payment_reference', 'string', [
                    'length' => 255,
                    'notnull' => false,
                    'default' => null,
                ]);
            }
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('reservation')) {
            $table = $schema->getTable('reservation');
            if ($table->hasColumn('payment_reference')) {
                $table->dropColumn('payment_reference');
            }
        }
    }
}
