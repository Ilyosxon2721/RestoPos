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
        // Склады
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->enum('type', ['main', 'kitchen', 'bar', 'freezer'])->default('main');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Поставщики
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('inn', 20)->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->integer('payment_terms')->default(0); // дни отсрочки
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Поставки (приходные накладные)
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->string('number', 50)->nullable();
            $table->string('document_number', 100)->nullable();
            $table->date('document_date')->nullable();
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->enum('status', ['draft', 'pending', 'received', 'cancelled'])->default('draft');
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['warehouse_id', 'created_at']);
        });

        // Позиции поставки
        Schema::create('supply_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->decimal('price', 12, 4);
            $table->decimal('total', 14, 2)->storedAs('quantity * price');
            $table->date('expiry_date')->nullable();
            $table->string('batch_number', 100)->nullable();
        });

        // Остатки на складе
        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 3)->default(0);
            $table->decimal('reserved_quantity', 12, 3)->default(0);
            $table->decimal('average_cost', 12, 4)->default(0);
            $table->timestamp('last_supply_date')->nullable();
            $table->decimal('last_supply_price', 12, 4)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['warehouse_id', 'ingredient_id']);
        });

        // Партии (для FIFO учёта)
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supply_item_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('initial_quantity', 12, 3);
            $table->decimal('remaining_quantity', 12, 3);
            $table->decimal('cost_price', 12, 4);
            $table->date('expiry_date')->nullable();
            $table->string('batch_number', 100)->nullable();
            $table->timestamps();

            $table->index(['warehouse_id', 'ingredient_id']);
            $table->index('expiry_date');
        });

        // Движение товаров
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->enum('type', ['supply', 'sale', 'write_off', 'transfer_in', 'transfer_out', 'production', 'inventory', 'return']);
            $table->decimal('quantity', 12, 3); // положительное или отрицательное
            $table->decimal('cost_price', 12, 4)->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['warehouse_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_batches');
        Schema::dropIfExists('stock');
        Schema::dropIfExists('supply_items');
        Schema::dropIfExists('supplies');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('warehouses');
    }
};
