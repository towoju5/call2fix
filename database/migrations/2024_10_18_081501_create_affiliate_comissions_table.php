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
        Schema::create('affiliate_comissions', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('task_id');
            $table->string('commission');
            $table->string('start_date');
            $table->string('completion_date');
            $table->string('_account_type');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_comissions');
    }
};
