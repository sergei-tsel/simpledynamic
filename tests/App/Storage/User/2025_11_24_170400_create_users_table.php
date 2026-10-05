<?php

declare(strict_types=1);

namespace Test\App\Storage\User;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new class() extends Migration {
    /**
     * Прокатить миграцию
     */
    public function up(Builder $schema): void
    {
        $schema->create('users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    /**
     * Откатить миграцию
     */
    public function down(Builder $schema): void
    {
        $schema->dropIfExists('users');
    }
};
