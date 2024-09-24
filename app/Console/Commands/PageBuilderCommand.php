<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PageBuilderCommand extends Command
{
    // The name and signature of the console command.
    protected $signature = 'pagebuilder:command {action} {name?}';

    // The console command description.
    protected $description = 'General command for page builder actions';

    // Create a new command instance.
    public function __construct()
    {
        parent::__construct();
    }

    // Execute the console command.
    public function handle()
    {
        $action = $this->argument('action');
        $name = $this->argument('name');

        switch ($action) {
            case 'create-theme':
                $this->createTheme($name);
                break;
            // Add more cases for other actions
            default:
                $this->error("Action '{$action}' is not defined.");
                break;
        }
    }

    // Method to create a theme
    protected function createTheme($name)
    {
        if (!$name) {
            $this->error('Theme name is required.');
            return;
        }

        $themePath = base_path('themes/' . $name); // Change to base_path

        if (File::exists($themePath)) {
            $this->error("Theme '{$name}' already exists.");
            return;
        }

        // Create the theme directory
        File::makeDirectory($themePath, 0755, true);

        // Optionally, create some default files in the theme directory
        File::put($themePath . '/index.html', '<h1>Welcome to ' . $name . ' theme</h1>');

        $this->info("Theme '{$name}' created successfully at {$themePath}.");
    }
}