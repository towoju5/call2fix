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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('duration_type')->nullable();
            $table->string('lease_duration')->nullable();
            $table->string('lease_rate')->nullable();
            $table->string('lease_notes')->nullable();
        });
    }
};
