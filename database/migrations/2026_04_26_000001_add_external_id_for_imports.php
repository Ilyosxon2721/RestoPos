<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('external_source', 32)->nullable()->after('uuid');
            $table->string('external_id', 128)->nullable()->after('external_source');
            $table->unique(['organization_id', 'external_source', 'external_id'], 'products_external_unique');
        });

        Schema::table('ingredients', function (Blueprint $table): void {
            $table->string('external_source', 32)->nullable()->after('organization_id');
            $table->string('external_id', 128)->nullable()->after('external_source');
            $table->unique(['organization_id', 'external_source', 'external_id'], 'ingredients_external_unique');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->string('external_source', 32)->nullable()->after('organization_id');
            $table->string('external_id', 128)->nullable()->after('external_source');
            $table->unique(['organization_id', 'external_source', 'external_id'], 'categories_external_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique('products_external_unique');
            $table->dropColumn(['external_source', 'external_id']);
        });

        Schema::table('ingredients', function (Blueprint $table): void {
            $table->dropUnique('ingredients_external_unique');
            $table->dropColumn(['external_source', 'external_id']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropUnique('categories_external_unique');
            $table->dropColumn(['external_source', 'external_id']);
        });
    }
};
