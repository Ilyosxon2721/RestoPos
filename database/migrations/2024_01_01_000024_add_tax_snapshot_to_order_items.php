<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('tax_id')->nullable()->after('total_price')
                ->constrained('taxes')->nullOnDelete();
            $table->decimal('tax_rate', 5, 2)->default(0)->after('tax_id');
            $table->string('tax_type', 16)->default('none')->after('tax_rate');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_type');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tax_id');
            $table->dropColumn(['tax_rate', 'tax_type', 'tax_amount']);
        });
    }
};
