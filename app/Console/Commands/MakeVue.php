<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeVue extends Command
{
    protected $signature = 'make:vue {name} {--type=component} {--i}';
    protected $description = 'Scaffold a new Vue component, page, layout, or composable.';

    public function handle()
    {
        $name = $this->argument('name');
        $type = strtolower($this->option('type'));
        $withIndex = $this->option('i');
        $basePath = base_path('resources/js');
        
        $directories = [
            'component' => 'components',
            'page' => 'pages',
            'layout' => 'layouts',
            'composable' => 'composables',
        ];

        if (!array_key_exists($type, $directories)) {
            $this->components->error("Invalid type. Allowed types: component, page, layout, composable.");
            return;
        }

        $dir = $directories[$type];
        $componentName = class_basename($name);
        
        $stubPath = base_path("stubs/vue/{$type}.stub");
        if (!File::exists($stubPath)) {
            $this->components->error("Stub file missing at: {$stubPath}");
            return;
        }

        $content = File::get($stubPath);
        $content = str_replace('{{ componentName }}', Str::studly($componentName), $content);
        
        if ($type === 'composable') {
            $filePath = "$basePath/$dir/$name.ts";
        } 
        
        elseif ($type === 'component' && $withIndex) {
            $folderPath = "$basePath/components/ui/$componentName";
            $filePath = "$folderPath/$componentName.vue";
            $indexPath = "$folderPath/index.ts";
            
            File::ensureDirectoryExists($folderPath);
            
            $indexContent = "export { default as $componentName } from './$componentName.vue';\n";
            if (!File::exists($indexPath)) {
                File::put($indexPath, $indexContent);
                $relativeIndexPath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $indexPath);
                $this->components->info("Index file [$relativeIndexPath] created successfully.");
            }
        } 

        else {
            $filePath = "$basePath/$dir/$name.vue";
        }

        File::ensureDirectoryExists(dirname($filePath));

        $relativeFilePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $filePath);

        if (File::exists($filePath)) {
            $this->components->error(ucfirst($type) . " [$relativeFilePath] already exists.");
            return;
        }

        File::put($filePath, $content);
        
        $this->components->info(ucfirst($type) . " [$relativeFilePath] created successfully.");
    }
}