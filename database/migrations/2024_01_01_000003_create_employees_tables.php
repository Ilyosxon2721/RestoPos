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
        // Сотрудники (расширение пользователей)
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('position', 100)->nullable();
            $table->date('hire_date')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('passport_series', 20)->nullable();
            $table->string('passport_number', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_phone', 50)->nullable();
            $table->enum('salary_type', ['hourly', 'monthly', 'percent', 'mixed'])->default('monthly');
            $table->decimal('hourly_rate', 12, 2)->default(0);
            $table->decimal('monthly_salary', 12, 2)->default(0);
            $table->decimal('sales_percent', 5, 2)->default(0);
            $table->timestamps();

            $table->index('branch_id');
        });

        // Смены сотрудников
        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->timestamp('clock_in');
            $table->timestamp('clock_out')->nullable();
            $table->integer('break_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'clock_in']);
        });

        // Начисления ЗП
        Schema::create('salary_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('hours_worked', 8, 2)->default(0);
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('sales_bonus', 12, 2)->default(0);
            $table->decimal('tips', 12, 2)->default(0);
            $table->decimal('bonuses', 12, 2)->default(0);
            $table->decimal('penalties', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['period_start', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_calculations');
        Schema::dropIfExists('employee_shifts');
        Schema::dropIfExists('employees');
    }
};
