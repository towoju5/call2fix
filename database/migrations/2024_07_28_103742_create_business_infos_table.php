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
        Schema::dropIfExists('business_infos');
        Schema::create('business_infos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id')->constrained()->cascadeOnDelete();
            $table->string('businessName')->nullable();
            $table->string('cacNumber')->nullable();
            $table->string('officeAddress')->nullable();
            $table->json('businessCategory')->nullable();
            $table->string('businessDescription')->nullable();
            $table->string('businessIdType')->nullable();
            $table->string('businessIdNumber')->nullable();
            $table->string('businessIdImage')->nullable();
            $table->json('businessBankInfo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_infos');
    }
};
