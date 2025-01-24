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
        Schema::dropIfExists('service_requests');
        Schema::create('service_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id')->constrained();
            $table->string('department_id')->nullable();
            $table->string('property_id')->constrained();
            $table->string('service_category_id')->nullable()->constrained();
            $table->string('service_id')->nullable()->constrained();
            $table->string('problem_title');
            $table->text('problem_description');
            $table->string('inspection_time');
            $table->date('inspection_date');
            $table->json('problem_images');
            $table->boolean('use_featured_providers')->default(false);
            $table->json('featured_providers_id')->nullable();
            $table->string('approved_providers_id')->nullable();
            $table->string('approved_artisan_id')->nullable();
            $table->boolean('assesment_fee_paid')->default(false);
            $table->enum('request_status', ["Draft","Pending","Processing","Bidding In Progress","Quote Accepted","Awaiting Payment","Payment Confirmed","On Hold","Work In Progress","Cancelled","Completed","Overdue","Closed","Rejected", "Rework issued"])->default('Pending');
            $table->timestamps();
            $table->softDeletes();
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
