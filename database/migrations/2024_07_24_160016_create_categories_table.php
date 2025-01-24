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
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('parent_category')->nullable();
            $table->string('category_name');
            $table->string('category_slug')->unique();
            $table->string('category_image')->nullable();
            $table->string('category_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
