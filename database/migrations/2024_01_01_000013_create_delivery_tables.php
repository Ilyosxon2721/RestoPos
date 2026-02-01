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
        // Зоны доставки
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->json('polygon'); // координаты полигона
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('min_order_amount', 12, 2)->default(0);
            $table->decimal('free_delivery_from', 12, 2)->nullable();
            $table->integer('estimated_time')->nullable(); // минуты
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Курьеры
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('phone', 50);

            $table->enum('vehicle_type', ['foot', 'bicycle', 'motorcycle', 'car'])->default('car');
            $table->string('vehicle_number', 50)->nullable();

            $table->enum('status', ['offline', 'available', 'busy'])->default('offline');
            $table->decimal('current_location_lat', 10, 8)->nullable();
            $table->decimal('current_location_lng', 11, 8)->nullable();
            $table->timestamp('last_location_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Заказы на доставку
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('delivery_zone_id')->nullable()->constrained()->nullOnDelete();

            $table->text('address');
            $table->string('address_details')->nullable(); // квартира, подъезд, этаж
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 50);

            $table->decimal('delivery_fee', 12, 2)->default(0);

            $table->timestamp('scheduled_at')->nullable(); // запланированное время доставки
            $table->timestamp('estimated_delivery_at')->nullable();

            $table->enum('status', ['pending', 'assigned', 'picked_up', 'in_transit', 'delivered', 'failed'])->default('pending');

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->text('delivery_notes')->nullable();
            $table->string('failure_reason')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index(['courier_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
        Schema::dropIfExists('couriers');
        Schema::dropIfExists('delivery_zones');
    }
};
