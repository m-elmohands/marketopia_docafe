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
        Schema::create('payment_intentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->string('special_reference')->unique();
            $table->string('paymob_intention_id')->nullable()->unique();
            $table->string('status')->default('pending')->index();
            $table->unsignedBigInteger('amount_cents');
            $table->char('currency', 3);
            $table->json('payment_methods')->nullable();
            $table->boolean('is_live')->default(false)->index();
            $table->json('provider_data')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_intention_id')->constrained()->restrictOnDelete();
            $table->string('paymob_transaction_id')->unique();
            $table->string('paymob_order_id')->nullable()->index();
            $table->string('parent_transaction_id')->nullable()->index();
            $table->string('integration_id')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->unsignedBigInteger('amount_cents');
            $table->char('currency', 3);
            $table->string('source_type')->nullable();
            $table->string('source_subtype')->nullable();
            $table->string('masked_pan', 32)->nullable();
            $table->boolean('is_success')->default(false);
            $table->boolean('is_pending')->default(true);
            $table->boolean('is_authorized')->default(false);
            $table->boolean('is_captured')->default(false);
            $table->boolean('is_voided')->default(false);
            $table->boolean('is_refunded')->default(false);
            $table->boolean('is_3d_secure')->default(false);
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->json('provider_data')->nullable();
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_transaction_id')->constrained()->restrictOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('paymob_transaction_id')->nullable()->unique();
            $table->string('status')->default('pending')->index();
            $table->unsignedBigInteger('amount_cents');
            $table->char('currency', 3);
            $table->text('reason')->nullable();
            $table->json('provider_data')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('fingerprint', 64)->unique();
            $table->string('event_type')->index();
            $table->string('paymob_transaction_id')->nullable()->index();
            $table->boolean('hmac_verified')->default(false)->index();
            $table->string('status')->default('pending')->index();
            $table->unsignedSmallInteger('processing_attempts')->default(0);
            $table->json('payload');
            $table->text('processing_error')->nullable();
            $table->timestamp('received_at')->index();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_webhooks');
        Schema::dropIfExists('payment_refunds');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payment_intentions');
    }
};
