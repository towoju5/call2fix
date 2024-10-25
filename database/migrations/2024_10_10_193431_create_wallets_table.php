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
        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id')->constrained()->onDelete('cascade');
            $table->string('currency')->nullable();
            $table->string('_account_type')->nullable();
            $table->string('balance')->default(0);
            $table->json('meta')->nullable();
            $table->string('title')->nullable(); // Add the wallet title field
            $table->timestamps();

            $table->unique(['user_id', 'currency', '_account_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
