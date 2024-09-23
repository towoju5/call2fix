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
        Schema::create('artisan_can_submit_quote', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('artisan_id');
            $table->string('request_id');
            $table->string('service_provider_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['artisan_id','request_id'],'service_provider_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
