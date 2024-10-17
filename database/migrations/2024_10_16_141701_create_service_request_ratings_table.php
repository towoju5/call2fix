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
        Schema::create('service_request_ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('service_request_id')->constrained()->onDelete('cascade');
            $table->string('user_id')->constrained()->onDelete('cascade');
            $table->string('_account_type');
            $table->integer('work_quality')->nullable();
            $table->integer('timeliness')->nullable();
            $table->integer('communication')->nullable();
            $table->integer('professionalism')->nullable();
            $table->integer('cleanliness')->nullable();
            $table->integer('pricing_transparency')->nullable();
            $table->integer('tools_quality')->nullable();
            $table->integer('issue_handling')->nullable();
            $table->integer('safety_adherence')->nullable();
            $table->integer('overall_satisfaction')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_request_ratings');
    }
};
