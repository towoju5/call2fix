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
        // Schema::create('plans', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->integer('price');
        //     $table->integer('service_category_limit')->nullable(); // null for unlimited
        //     $table->integer('artisan_limit')->nullable();
        //     $table->integer('product_category_limit')->nullable();
        //     $table->integer('product_limit')->nullable();
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};