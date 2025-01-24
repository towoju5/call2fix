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
        Schema::create('submitted_quotes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider_id')->constrained('users')->onDelete('cascade');
            $table->string('artisan_id')->constrained('users')->onDelete('cascade');
            $table->string("request_id")->constrained('service_requests')->onDelete('cascade');
            $table->string("workmanship")->nullable();
            $table->json("items")->nullable();
            $table->string("sla_duration")->nullable();
            $table->string("sla_start_date")->nullable();
            $table->string("summary_note")->nullable();
            $table->string("administrative_fee")->nullable();
            $table->string("service_vat")->nullable();
            $table->string("total_charges")->nullable();
            $table->json("attachments")->nullable();
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
        Schema::dropIfExists('submitted_quotes');
    }
};
