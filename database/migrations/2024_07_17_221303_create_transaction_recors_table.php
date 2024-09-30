<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('transaction_records');
        Schema::create('transaction_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id');
            $table->string('wallet_id');
            $table->string('transaction_reference')->unique();
            $table->enum('transaction_type', ['credit', 'debit'])->default('debit');
            $table->string('transaction_slug')->nullable();
            $table->enum('transaction_status', ['successful', 'failed', 'pending', 'processing'])->default('pending');
            $table->string('transaction_amount')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users');
            // $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
        });    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_records');
    }
};
