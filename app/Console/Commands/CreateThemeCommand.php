<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateThemeCommand extends Command
{
    // The name and signature of the console command.
    protected $signature = 'pagebuilder:create-theme {name}';

    // The console command description.
    protected $description = 'Create a new theme for the page builder';

    // Create a new command instance.
    public function __construct()
    {
        parent::__construct();
    }

    // Execute the console command.
    public function handle()
    {
        $name = $this->argument('name');
        // Add your logic to create a theme here
        $this->info("Theme '{$name}' created successfully.");
    }
}