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
        Schema::dropIfExists('deposits');
        Schema::create('deposits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id')->constrained('users');
            $table->string('amount');
            $table->string('reference');
            $table->string('payment_method')->comment('bank_transfer or credit_card');
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
