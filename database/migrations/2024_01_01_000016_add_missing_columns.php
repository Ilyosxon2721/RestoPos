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
        // Добавляем недостающие поля в customer_groups
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->boolean('is_active')->default(true)->after('color');
        });

        // Добавляем uuid и organization_id в warehouses
        Schema::table('warehouses', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->foreignId('organization_id')->nullable()->after('id')
                ->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_active']);
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn(['uuid', 'organization_id']);
        });
    }
};
