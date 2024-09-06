<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class GenerateCrudPermissions extends Command
{
    // The name and signature of the console command
    protected $signature = 'permissions:generate-crud';

    // The console command description
    protected $description = 'Automatically generate CRUD permissions for models in App\Models and Modules\**\App\Models directories';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Directories to scan for models
        $mainModelsDirectory = app_path('Models');
        $modulesDirectory = base_path('Modules');

        // Scan the App\Models directory
        $this->scanDirectoryForModels($mainModelsDirectory);

        // Scan the Modules/**/App/Models directory
        if (is_dir($modulesDirectory)) {
            $moduleDirectories = File::directories($modulesDirectory);
            foreach ($moduleDirectories as $moduleDir) {
                $moduleModelsDir = $moduleDir . '/App/Models';
                if (is_dir($moduleModelsDir)) {
                    $this->scanDirectoryForModels($moduleModelsDir);
                }
            }
        }

        $this->info('CRUD permissions generated for all models!');
    }

    protected function scanDirectoryForModels($directory)
    {
        // Recursively scan the directory for PHP model files
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            // Get the class name of the model
            $namespace = $this->getNamespaceFromFile($file->getPathname());

            // If the namespace is valid and the class exists
            if (class_exists($namespace)) {
                $model = class_basename($namespace);
                $this->generateCrudPermissions($model);
            }
        }
    }

    protected function getNamespaceFromFile($file)
    {
        $content = file_get_contents($file);
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = $matches[1];
            $class = pathinfo($file, PATHINFO_FILENAME);
            return $namespace . '\\' . $class;
        }

        return null;
    }

    protected function generateCrudPermissions($model)
    {
        // Define the CRUD actions
        $actions = ['create', 'read', 'update', 'delete'];

        foreach ($actions as $action) {
            $permissionName = "{$action} {$model}";
            
            // Check if the permission already exists to avoid duplicates
            if (!Permission::where('name', $permissionName)->exists()) {
                Permission::create(['name' => $permissionName]);
                $this->info("Created permission: {$permissionName}");
            }
        }
    }
}
