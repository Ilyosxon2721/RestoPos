<?php

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
        // Единицы измерения
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('short_name', 10);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Цехи производства
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->unsignedBigInteger('printer_id')->nullable();
            $table->string('color', 7)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Категории меню
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('image', 500)->nullable();
            $table->string('color', 7)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->time('available_from')->nullable();
            $table->time('available_to')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
            $table->index(['organization_id', 'parent_id']);
            $table->index('sort_order');
        });

        // Товары/Блюда
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('workshop_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['dish', 'drink', 'product', 'service', 'semi_finished'])->default('dish');
            $table->string('name');
            $table->string('name_uz')->nullable();
            $table->string('name_en')->nullable();
            $table->string('slug')->nullable();
            $table->string('sku', 100)->nullable();
            $table->string('barcode', 100)->nullable();
            $table->text('description')->nullable();
            $table->text('description_uz')->nullable();
            $table->text('description_en')->nullable();
            $table->string('image', 500)->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->integer('calories')->nullable();
            $table->decimal('proteins', 8, 2)->nullable();
            $table->decimal('fats', 8, 2)->nullable();
            $table->decimal('carbohydrates', 8, 2)->nullable();
            $table->decimal('weight', 10, 3)->nullable();
            $table->integer('cooking_time')->nullable(); // в минутах
            $table->boolean('is_weighable')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_available')->default(true);
            $table->boolean('in_stop_list')->default(false);
            $table->string('stop_list_reason')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'category_id']);
            $table->index('barcode');
            $table->index('sku');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('products', function (Blueprint $table) {
                $table->fullText(['name', 'description']);
            });
        }

        // Цены по филиалам (если отличаются)
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('workshops');
        Schema::dropIfExists('units');
    }
};
