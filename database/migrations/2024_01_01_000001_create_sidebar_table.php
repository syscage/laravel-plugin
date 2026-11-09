<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sidebar', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->nullable()->constrained('sidebar')->cascadeOnDelete();
            $table->string('plugin')->nullable()->index();
            $table->string('label');
            $table->string('href')->nullable();
            $table->string('icon')->nullable();
            $table->string('icon_provider')->nullable(); // e.g: tabler, lucide etc...
            $table->string('position')->default('top');
            $table->json('permissions')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sidebar');
    }
};
