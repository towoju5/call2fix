<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('kwik_deliveries');
        Schema::create('kwik_deliveries', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->decimal('estimate', 10, 2)->nullable()->comment('Shipping cost estimate');
            $table->decimal('billed', 10, 2)->nullable()->comment('Total charged to the user, including product fee');
            $table->uuid('seller_id')->nullable()->constrained('users')->onDelete('set null')->comment('Supplier ID');
            $table->uuid('customer_id')->nullable()->constrained('users')->onDelete('set null')->comment('Customer ID');
            $table->uuid('order_id')->nullable()->constrained('orders')->onDelete('cascade')->comment('Associated order ID');
            $table->string('kwik_delivery_id')->unique()->comment('Unique delivery ID from Kwik');
            $table->json('metadata')->nullable()->comment('Additional metadata for the delivery');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kwik_deliveries');
    }
};