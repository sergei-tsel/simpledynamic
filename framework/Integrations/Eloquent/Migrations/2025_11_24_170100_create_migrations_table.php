<?php

declare(strict_types=1);

namespace Simpledynamic\Integrations\Eloquent\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('migrations', function (Blueprint $table): void {
            $table->id();
            $table->string('migration');
            $table->integer('batch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('migrations');
    }
};
