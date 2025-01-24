<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::dropIfExists('wallets');
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('currency')->nullable();
            $table->string('balance')->default(0);
            $table->json('meta')->nullable();
            $table->string('title')->nullable();
            $table->boolean('is_department')->default(0);
            $table->string('department_id')->nullable();
            $table->string('role')->default('general');
            $table->timestamps();
            $table->softDeletes();
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
