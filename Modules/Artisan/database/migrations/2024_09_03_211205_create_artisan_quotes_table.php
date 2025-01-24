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
        Schema::create('artisan_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('artisan_id')->constrained();
            $table->string('request_id')->constrained();
            $table->string('service_provider_id')->constrained();
            $table->text('workmanship');
            $table->json('items');
            $table->integer('sla_duration');
            $table->date('sla_start_date');
            $table->json('attachments')->nullable();
            $table->text('summary_note')->nullable();
            $table->decimal('administrative_fee', 10, 2);
            $table->decimal('service_vat', 10, 2);
            $table->string('request_status')->default('pending_review');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artisan_quotes');
    }
};
