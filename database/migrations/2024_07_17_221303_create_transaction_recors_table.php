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
        Schema::create('transaction_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('wallet_id')->constrained();
            $table->string('transaction_reference')->unique();
            $table->enum('transaction_type', ['credit', 'debit'])->default('debit');
            $table->string('transaction_slug')->nullable();
            $table->enum('transaction_status', ['successful', 'failed', 'pending', 'processing'])->default('pending');
            $table->string('transaction_amount')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_recors');
    }
};
