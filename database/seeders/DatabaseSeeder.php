<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            AdminSeeder::class,
            RolePermissionSeeder::class,
            PlanSeeder::class,
            UserSeeder::class,
            SettingsSeeder::class,
        ]);

        // restore old database
        $sql = File::get(storage_path('app/_fb.sql'));

        // Execute the SQL commands
        try {
            DB::unprepared($sql);
        } catch (\Exception $e) {
            Log::error("Unable to restore database: _fb: ", ['error' => 'Error while restoring the database: ' . $e->getMessage()]);
        }
    }
}
