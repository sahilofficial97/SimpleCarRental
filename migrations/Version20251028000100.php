<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251028000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user table with email unique, roles json, and password hash';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('user')) {
            return;
        }

        $table = $schema->createTable('user');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('email', 'string', ['length' => 180, 'notnull' => true]);
        $table->addColumn('roles', 'json', ['notnull' => true]);
        $table->addColumn('password', 'string', ['length' => 255, 'notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['email'], 'uniq_user_email');
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('user')) {
            $schema->dropTable('user');
        }
    }
}
