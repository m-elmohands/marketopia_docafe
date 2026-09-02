<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'sort_order']);
        });

        Schema::create('product_stage_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_stage_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10)->index();
            $table->string('title');
            $table->text('description')->nullable();

            $table->unique(['product_stage_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stage_translations');
        Schema::dropIfExists('product_stages');
    }
};
