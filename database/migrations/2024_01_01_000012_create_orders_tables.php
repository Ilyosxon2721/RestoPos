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
        // Заказы
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cash_shift_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('waiter_id')->nullable();

            $table->string('order_number', 50);
            $table->enum('type', ['dine_in', 'takeaway', 'delivery', 'preorder'])->default('dine_in');
            $table->enum('source', ['pos', 'website', 'app', 'aggregator', 'phone', 'qr'])->default('pos');

            $table->integer('guests_count')->default(1);

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->string('discount_reason')->nullable();
            $table->decimal('service_charge', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);

            $table->enum('status', ['new', 'accepted', 'preparing', 'ready', 'served', 'completed', 'cancelled'])->default('new');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');

            $table->text('notes')->nullable();

            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            $table->foreign('waiter_id')->references('id')->on('employees')->nullOnDelete();
            $table->index(['branch_id', 'created_at']);
            $table->index('status');
            $table->index('order_number');
            $table->index(['table_id', 'status']);
        });

        // Позиции заказа
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();

            $table->string('name'); // сохраняем на момент заказа
            $table->decimal('quantity', 10, 3)->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2);
            $table->decimal('cost_price', 12, 2)->default(0);

            $table->integer('course')->default(1); // номер курса подачи
            $table->enum('status', ['pending', 'sent', 'preparing', 'ready', 'served', 'cancelled'])->default('pending');

            $table->timestamp('sent_to_kitchen_at')->nullable();
            $table->timestamp('ready_at')->nullable();

            $table->text('comment')->nullable();
            $table->string('cancelled_reason')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();

            $table->timestamps();

            $table->foreign('cancelled_by')->references('id')->on('users');
            $table->index('order_id');
            $table->index('status');
        });

        // Модификаторы позиции заказа
        Schema::create('order_item_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('modifier_id')->constrained();
            $table->string('name');
            $table->decimal('price_adjustment', 12, 2)->default(0);
            $table->integer('quantity')->default(1);
        });

        // Платежи
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained();
            $table->foreignId('cash_shift_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained();

            $table->decimal('amount', 12, 2);
            $table->decimal('change_amount', 12, 2)->default(0); // сдача для наличных

            $table->enum('status', ['pending', 'completed', 'refunded', 'cancelled'])->default('pending');

            $table->string('transaction_id')->nullable(); // ID транзакции из платёжной системы
            $table->json('payment_data')->nullable(); // дополнительные данные от платёжной системы

            $table->timestamp('paid_at')->useCurrent();

            $table->index('order_id');
            $table->index('transaction_id');
        });

        // Чеки (фискальные)
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', ['sale', 'refund', 'precheck']);
            $table->string('number', 100)->nullable();
            $table->string('fiscal_number', 100)->nullable();
            $table->string('fiscal_sign')->nullable();

            $table->decimal('amount', 12, 2);

            $table->enum('status', ['pending', 'printed', 'sent', 'error'])->default('pending');
            $table->text('error_message')->nullable();

            $table->json('receipt_data')->nullable();

            $table->timestamp('printed_at')->nullable();
            $table->timestamps();

            $table->index('fiscal_number');
        });

        // Добавляем foreign key для bonus_transactions на orders
        Schema::table('bonus_transactions', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bonus_transactions', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::dropIfExists('receipts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_item_modifiers');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
