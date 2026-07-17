<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260708000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create users table';
    }

    public function up(Schema $schema): void
    {
        $users = $schema->createTable('users');
        $users->addColumn('id', 'integer', ['autoincrement' => true]);
        $users->addColumn('email', 'string', ['length' => 255]);
        $users->addColumn('password', 'string', ['length' => 255]);
        $users->addColumn('remember_token', 'string', ['length' => 255, 'notnull' => false]);
        $users->addColumn('role_id', 'integer', ['default' => 1]);
        $users->addColumn('created_at', 'datetime');
        $users->addColumn('updated_at', 'datetime');
        $users->setPrimaryKey(['id']);
        $users->addUniqueIndex(['email'], 'uniq_users_email');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('users');
    }
}
