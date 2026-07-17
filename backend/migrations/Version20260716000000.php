<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260716000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create cats table';
    }

    public function up(Schema $schema): void
    {
        $cats = $schema->createTable('cats');
        $cats->addColumn('id', 'integer', ['autoincrement' => true]);
        $cats->addColumn('user_id', 'integer');
        $cats->addColumn('name', 'string', ['length' => 100]);
        $cats->addColumn('lang', 'string', ['length' => 50]);
        $cats->addColumn('created_at', 'datetime');
        $cats->addColumn('updated_at', 'datetime');

        $cats->setPrimaryKey(['id']);
        $cats->addIndex(['user_id'], 'idx_cats_user_id');
        $cats->addForeignKeyConstraint(
            'users',
            ['user_id'],
            ['id'],
            ['onDelete' => 'CASCADE'],
            'fk_cats_user_id'
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('cats');
    }
}
