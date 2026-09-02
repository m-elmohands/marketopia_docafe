<?php

use App\Enums\ArticleStatus;
use App\Enums\CatalogStatus;
use App\Enums\Currency;
use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentProcessingStatus;
use App\Enums\PaymentStatus;
use App\Enums\SettingType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<string, array<string, class-string<BackedEnum>>> */
    private array $constraints = [
        'products' => ['status' => CatalogStatus::class, 'currency' => Currency::class],
        'categories' => ['status' => CatalogStatus::class],
        'articles' => ['status' => ArticleStatus::class],
        'settings' => ['type' => SettingType::class],
        'orders' => [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'fulfillment_status' => FulfillmentStatus::class,
            'currency' => Currency::class,
        ],
        'payment_transactions' => ['status' => PaymentProcessingStatus::class, 'currency' => Currency::class],
        'payment_refunds' => ['status' => PaymentProcessingStatus::class, 'currency' => Currency::class],
    ];

    public function up(): void
    {
        foreach ($this->constraints as $table => $columns) {
            foreach ($columns as $column => $enum) {
                $this->addConstraint($table, $column, $enum);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->constraints as $table => $columns) {
            foreach (array_keys($columns) as $column) {
                $this->dropConstraint($table, $column);
            }
        }
    }

    /** @param class-string<BackedEnum> $enum */
    private function addConstraint(string $table, string $column, string $enum): void
    {
        $name = "{$table}_{$column}_allowed";
        $values = collect($enum::cases())
            ->map(fn (BackedEnum $case): string => DB::getPdo()->quote($case->value))
            ->implode(', ');

        if (DB::getDriverName() === 'sqlite') {
            foreach (['insert' => 'NEW', 'update' => 'NEW'] as $operation => $reference) {
                DB::statement("CREATE TRIGGER {$name}_{$operation} BEFORE ".strtoupper($operation)." ON {$table} WHEN {$reference}.{$column} NOT IN ({$values}) BEGIN SELECT RAISE(ABORT, '{$name}'); END");
            }

            return;
        }

        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$column} IN ({$values}))");
    }

    private function dropConstraint(string $table, string $column): void
    {
        $name = "{$table}_{$column}_allowed";

        if (DB::getDriverName() === 'sqlite') {
            DB::statement("DROP TRIGGER IF EXISTS {$name}_insert");
            DB::statement("DROP TRIGGER IF EXISTS {$name}_update");

            return;
        }

        $dropKeyword = DB::getDriverName() === 'mysql' ? 'CHECK' : 'CONSTRAINT';
        DB::statement("ALTER TABLE {$table} DROP {$dropKeyword} {$name}");
    }
};
