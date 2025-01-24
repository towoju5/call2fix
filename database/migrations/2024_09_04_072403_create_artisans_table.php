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
        Schema::create('artisans', function (Blueprint $table) {
            $table->id();
            $table->string('artisan_id')->constrained('users');
            $table->string('service_provider_id')->constrained('users');
            $table->string("first_name")->nullable();
            $table->string("last_name")->nullable();
            $table->string("email")->nullable();
            $table->string("phone")->nullable();
            $table->string("trade")->nullable();
            $table->json("location")->nullable()->comment('A location from the list of locations the service provider offers services');
            $table->enum("id_type", ['national_id','drivers_license','passport','voters_card'])->default('national_id')->nullable();
            $table->string("id_image", 5000)->nullable();
            $table->string("trade_certificate", 5000)->nullable();
            $table->enum("payment_plan", ['percentage','fixed'])->default('percentage')->nullable();
            $table->string("payment_amount")->default(0)->nullable();
            $table->string("bank_code")->nullable();
            $table->string("account_number")->nullable();
            $table->string("account_name")->nullable();
            $table->json("artisan_category")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artisans');
    }
};
