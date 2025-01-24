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
        Schema::create('rework_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('service_request_id');
            $table->string('user_id');
            $table->text('message');
            $table->json('images')->nullable();
            $table->string('_account_type');
            $table->boolean('is_read')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rework_messages');
    }
};
