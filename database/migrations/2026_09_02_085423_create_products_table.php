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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('status')->default('draft')->index();
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('compare_at_price')->nullable();
            $table->unsignedBigInteger('cost_price')->nullable();
            $table->char('currency', 3)->default('USD');
            $table->boolean('track_inventory')->default(true);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->nullable();
            $table->unsignedInteger('weight_grams')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->json('metadata')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10)->index();
            $table->string('name');
            $table->string('slug');
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            $table->unique(['product_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('products');
    }
};
