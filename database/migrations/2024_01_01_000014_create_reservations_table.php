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
        // Бронирования
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            $table->string('guest_name');
            $table->string('guest_phone', 50);
            $table->string('guest_email')->nullable();
            $table->integer('guests_count');

            $table->date('reservation_date');
            $table->time('reservation_time');
            $table->integer('duration_minutes')->default(120);

            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->boolean('deposit_paid')->default(false);

            $table->enum('status', ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'])->default('pending');

            $table->enum('source', ['phone', 'website', 'app', 'walk_in'])->default('phone');

            $table->text('special_requests')->nullable();
            $table->text('internal_notes')->nullable();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();

            $table->boolean('reminder_sent')->default(false);

            $table->timestamps();

            $table->index(['branch_id', 'reservation_date']);
            $table->index('status');
            $table->index('guest_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
