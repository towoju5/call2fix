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
        Schema::create('request_quotes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('artisan_id')->constrained('users')->onDelete('cascade');
            $table->foreignId("request_id")->constrained('requests')->onDelete('cascade');
            $table->string("workmanship");
            $table->json("items");
            $table->string("sla_duration");
            $table->string("sla_start_date");
            $table->string("attachments");
            $table->string("summary_note");
            $table->string("administrative_fee");
            $table->string("service_vat");
            $table->enum('quote_status', ['pending', 'accepted', 'rejected', 'cancelled', 'reported', 'closed_by_admin'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_quotes');
    }
};
