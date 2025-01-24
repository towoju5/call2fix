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
        Schema::dropIfExists('wallet_transactions');
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdrawal']); // Type of transaction
            $table->string('amount'); // Amount involved
            $table->string('balance_before', ); // Balance before the transaction
            $table->string('balance_after', ); // Balance after the transaction
            $table->integer('decimal_places')->default(2); // Decimal places for the amount
            $table->text('meta')->nullable(); // Extra metadata (JSON format)
            $table->string('description')->nullable(); // Description for the transaction
            $table->timestamps();
            $table->softDeletes();
            $table->string('_account_type')->default('general');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
