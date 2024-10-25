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
        Schema::dropIfExists('department_user');
        Schema::create('department_user', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->constrained()->onDelete('cascade');
            $table->string('department_id')->constrained()->onDelete('cascade');
            $table->string('_account_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_user');
    }
};
