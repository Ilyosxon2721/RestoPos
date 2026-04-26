<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addExternalColumns('products', 'uuid');
        $this->addExternalColumns('ingredients', 'organization_id');
        $this->addExternalColumns('categories', 'organization_id');

        $this->addUniqueIndex('products', 'products_external_unique');
        $this->addUniqueIndex('ingredients', 'ingredients_external_unique');
        $this->addUniqueIndex('categories', 'categories_external_unique');
    }

    public function down(): void
    {
        foreach (['products', 'ingredients', 'categories'] as $table) {
            $indexName = $table . '_external_unique';
            if ($this->indexExists($table, $indexName)) {
                Schema::table($table, fn (Blueprint $t) => $t->dropUnique($indexName));
            }

            $columns = array_filter(
                ['external_source', 'external_id'],
                static fn (string $c) => Schema::hasColumn($table, $c),
            );

            if ($columns !== []) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn($columns));
            }
        }
    }

    private function addExternalColumns(string $table, string $afterColumn): void
    {
        Schema::table($table, function (Blueprint $t) use ($table, $afterColumn): void {
            if (!Schema::hasColumn($table, 'external_source')) {
                $t->string('external_source', 32)->nullable()->after($afterColumn);
            }
            if (!Schema::hasColumn($table, 'external_id')) {
                $afterFor = Schema::hasColumn($table, 'external_source') ? 'external_source' : $afterColumn;
                $t->string('external_id', 128)->nullable()->after($afterFor);
            }
        });
    }

    private function addUniqueIndex(string $table, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($indexName): void {
            $t->unique(['organization_id', 'external_source', 'external_id'], $indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $database = DB::connection()->getDatabaseName();
        $result = DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName],
        );

        return $result !== null && (int) $result->c > 0;
    }
};
