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
        // Группы клиентов
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('bonus_earn_percent', 5, 2)->default(0);
            $table->decimal('min_spent_to_join', 14, 2)->default(0);
            $table->string('color', 7)->nullable();
            $table->timestamps();
        });

        // Клиенты
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('loyalty_card_number', 100)->nullable();
            $table->decimal('bonus_balance', 12, 2)->default(0);
            $table->decimal('total_spent', 14, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->foreignId('customer_group_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->timestamp('last_visit_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'phone']);
            $table->index('loyalty_card_number');
            $table->index('email');
        });

        // Бонусные транзакции
        Schema::create('bonus_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->enum('type', ['earn', 'spend', 'adjust', 'expire']);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('description')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
        });

        // Акции
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['discount', 'bonus_multiply', 'gift', 'combo', 'happy_hour', 'buy_x_get_y']);
            $table->enum('discount_type', ['percent', 'fixed'])->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->json('conditions')->nullable(); // условия срабатывания
            $table->decimal('min_order_amount', 12, 2)->nullable();
            $table->decimal('max_discount_amount', 12, 2)->nullable();
            $table->enum('applicable_to', ['all', 'categories', 'products'])->default('all');
            $table->json('applicable_ids')->nullable(); // IDs категорий или товаров
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->json('active_days')->nullable(); // дни недели
            $table->time('active_hours_from')->nullable();
            $table->time('active_hours_to')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->integer('usage_limit_per_customer')->nullable();
            $table->string('promo_code', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'start_date', 'end_date']);
            $table->index('promo_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('bonus_transactions');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('customer_groups');
    }
};
