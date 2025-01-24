<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    protected $column_name = '_account_type';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get the list of all tables in the current database
        $tables = DB::select('SHOW TABLES');
        $database = env('DB_DATABASE');

        // Loop through each table and add the column
        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_' . $database};

            // Modify the table to add a new column
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Check if the column already exists before adding it
                if (!Schema::hasColumn($tableName, $this->column_name)) {
                    $table->string($this->column_name)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Get the list of all tables in the current database
        $tables = DB::select('SHOW TABLES');
        $database = env('DB_DATABASE'); // Fetch the database name from the .env file

        // Loop through each table and remove the column
        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_' . $database};

            // Modify the table to drop the new column
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, $this->column_name)) {
                    $table->dropColumn($this->column_name);
                }
            });
        }
    }
};
