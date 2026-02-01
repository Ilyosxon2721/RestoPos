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
        // Ингредиенты
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreignId('unit_id')->constrained();
            $table->string('name');
            $table->string('sku', 100)->nullable();
            $table->string('barcode', 100)->nullable();
            $table->decimal('min_stock', 12, 3)->default(0);
            $table->decimal('current_cost', 12, 4)->default(0);
            $table->decimal('loss_percent', 5, 2)->default(0); // % потерь при обработке
            $table->integer('shelf_life_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
            $table->index('barcode');
        });

        // Технологические карты
        Schema::create('tech_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('output_quantity', 10, 3)->default(1);
            $table->text('description')->nullable();
            $table->text('cooking_instructions')->nullable();
            $table->integer('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('product_id');
        });

        // Состав техкарты
        Schema::create('tech_card_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tech_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('semi_finished_id')->nullable(); // ссылка на полуфабрикат (product)
            $table->decimal('quantity', 12, 4);
            $table->decimal('loss_percent', 5, 2)->default(0);
            $table->decimal('gross_quantity', 12, 4)->storedAs('quantity * (1 + loss_percent / 100)');
            $table->integer('sort_order')->default(0);

            $table->foreign('semi_finished_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index('tech_card_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tech_card_items');
        Schema::dropIfExists('tech_cards');
        Schema::dropIfExists('ingredients');
    }
};
