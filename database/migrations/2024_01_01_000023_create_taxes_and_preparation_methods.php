<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Налоги
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->decimal('rate', 5, 2)->default(0);
            $table->enum('type', ['vat', 'turnover', 'none'])->default('vat');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
        });

        // Методы приготовления (жарка, варка, тушение, ...)
        Schema::create('preparation_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->decimal('default_loss_percent', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('tax_id')->nullable()->after('parent_id')
                ->constrained('taxes')->nullOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('tax_id')->nullable()->after('unit_id')
                ->constrained('taxes')->nullOnDelete();
            $table->boolean('excluded_from_discounts')->default(false)->after('is_weighable');
        });

        Schema::table('tech_cards', function (Blueprint $table) {
            $table->foreignId('output_unit_id')->nullable()->after('output_quantity')
                ->constrained('units')->nullOnDelete();
        });

        Schema::table('tech_card_items', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('semi_finished_id')
                ->constrained('units')->nullOnDelete();
            $table->foreignId('preparation_method_id')->nullable()->after('unit_id')
                ->constrained('preparation_methods')->nullOnDelete();
        });

        // Add is_required to product_modifier_groups pivot (Product model expects it).
        if (!Schema::hasColumn('product_modifier_groups', 'is_required')) {
            Schema::table('product_modifier_groups', function (Blueprint $table) {
                $table->boolean('is_required')->default(false)->after('modifier_group_id');
            });
        }

        // Backfill uuid on units (Unit model uses HasUuid trait but the column was missing).
        if (!Schema::hasColumn('units', 'uuid')) {
            Schema::table('units', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            });
            \Illuminate\Support\Facades\DB::table('units')->whereNull('uuid')->orderBy('id')
                ->each(function ($row) {
                    \Illuminate\Support\Facades\DB::table('units')
                        ->where('id', $row->id)
                        ->update(['uuid' => (string) \Illuminate\Support\Str::uuid()]);
                });
        }
    }

    public function down(): void
    {
        Schema::table('tech_card_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('preparation_method_id');
            $table->dropConstrainedForeignId('unit_id');
        });

        Schema::table('tech_cards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('output_unit_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tax_id');
            $table->dropColumn('excluded_from_discounts');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tax_id');
        });

        Schema::dropIfExists('preparation_methods');
        Schema::dropIfExists('taxes');
    }
};
