<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Добавляем поля для авторизации клиентов
        Schema::table('customers', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
            $table->string('verification_code', 6)->nullable()->after('password');
            $table->timestamp('verification_code_sent_at')->nullable()->after('verification_code');
            $table->boolean('is_registered')->default(false)->after('verification_code_sent_at');
            $table->string('avatar')->nullable()->after('is_registered');
            $table->rememberToken()->after('avatar');
        });

        // Адреса клиентов
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('label', 50)->default('Дом'); // Дом, Работа, Другой
            $table->text('address');
            $table->string('apartment', 50)->nullable();
            $table->string('entrance', 20)->nullable();
            $table->string('floor', 20)->nullable();
            $table->string('intercom', 50)->nullable();
            $table->text('comment')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('customer_id');
        });

        // Избранные продукты
        Schema::create('customer_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['customer_id', 'product_id']);
        });

        // Баннеры магазина
        Schema::create('store_banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image'); // URL баннера
            $table->string('link')->nullable(); // Ссылка при клике
            $table->string('link_type', 30)->default('none'); // none, product, category, url
            $table->unsignedBigInteger('link_id')->nullable(); // ID продукта/категории
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
        });

        // Настройки магазина
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('store_name')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('primary_color', 7)->default('#10b981');
            $table->string('currency', 10)->default('сум');
            $table->unsignedBigInteger('drink_of_day_product_id')->nullable();
            $table->boolean('delivery_enabled')->default(true);
            $table->boolean('pickup_enabled')->default(true);
            $table->decimal('min_order_amount', 12, 2)->default(0);
            $table->string('phone', 50)->nullable();
            $table->string('instagram', 100)->nullable();
            $table->string('telegram', 100)->nullable();
            $table->string('working_hours_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
        Schema::dropIfExists('store_banners');
        Schema::dropIfExists('customer_favorites');
        Schema::dropIfExists('customer_addresses');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'password', 'verification_code', 'verification_code_sent_at',
                'is_registered', 'avatar', 'remember_token',
            ]);
        });
    }
};
