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
        Schema::dropIfExists('properties');
        Schema::create('properties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id')->constrained()->onDelete('cascade');
            $table->string('property_address');
            $table->string('property_type');
            $table->string('property_nearest_landmark');
            $table->string('property_name');
            $table->string('porperty_longitude');
            $table->string('porperty_latitude');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
