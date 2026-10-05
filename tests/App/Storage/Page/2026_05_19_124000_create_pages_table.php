<?php

declare(strict_types=1);

namespace Test\App\Storage\Page;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new class() extends Migration {
    /**
     * Прокатить миграцию
     */
    public function up(Builder $schema): void
    {
        $schema->create('pages', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('title');

            $table->foreignId('page_id')->nullable()->constrained('pages');
            $table->string('description')->nullable();
            $table->jsonb('layout')->nullable();

            $table->timestamp('early_dated_at')->nullable();
            $table->timestamp('late_dated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Прокатить миграцию
     */
    public function down(Builder $schema): void
    {
        $schema->dropIfExists('pages');
    }
};
