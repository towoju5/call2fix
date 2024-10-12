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
            $table->id();
            $table->string('user_id')->constrained()->onDelete('cascade');
            $table->string('currency');
            $table->string('role');
            $table->decimal('balance', 20, 2)->default(0);
            $table->json('meta')->nullable();
            $table->string('title')->nullable(); // Add the wallet title field
            $table->timestamps();

            $table->unique(['user_id', 'currency', 'role']);
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
