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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('property_id')->constrained();
            $table->foreignId('service_category_id')->nullable()->constrained();
            $table->foreignId('service_id')->nullable()->constrained();
            $table->string('problem_title');
            $table->text('problem_description');
            $table->time('inspection_time');
            $table->date('inspection_date');
            $table->json('problem_images');
            $table->boolean('use_featured_providers')->default(false);
            $table->json('featured_providers_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
