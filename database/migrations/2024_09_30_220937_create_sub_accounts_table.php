<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add required fields to the users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('parent_account_id')->nullable(); // Main account for sub-accounts
            $table->string('main_account_role');
            $table->enum('sub_account_type', ['normal', 'department'])->default('normal'); // For sub-accounts
            $table->boolean('can_hold_wallet')->default(true);
            $table->foreign('parent_account_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Create the sub_accounts table to handle sub-account mapping
        Schema::create('sub_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('main_account_id'); // Refers to the main account (corporate or private)
            $table->string('sub_account_id'); // Refers to the sub-account (users)
            $table->enum('status', ['active', 'inactive'])->default('active'); // To track sub-account status
            $table->timestamps();
            $table->foreign('main_account_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sub_account_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the sub_accounts table
        Schema::dropIfExists('sub_accounts');

        // Remove added columns from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'parent_account_id',
                'main_account_role',
                'sub_account_type',
                'can_hold_wallet',
            ]);
        });
    }
};
