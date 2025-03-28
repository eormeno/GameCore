<?php

namespace App\Console\Support;

use Illuminate\Console\Command;

class DatabaseTablesCommandHelp
{
    /**
     * @var Command
     */
    protected $command;

    /**
     * @param Command $command
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * Display the full help information
     */
    public function display(): void
    {
        $this->displayHeader();
        $this->displayBasicUsage();
        $this->displayPatternExamples();
        $this->displayFilteringOptions();
        $this->displayConfiguration();
        $this->displayBehavior();
    }

    /**
     * Display help header
     */
    protected function displayHeader(): void
    {
        $this->command->info('Database Tables Command Help');
        $this->command->line('============================');
        $this->command->line('');
        $this->command->line('This command displays database tables with optional filtering capabilities.');
        $this->command->line('');
    }

    /**
     * Display basic usage information
     */
    protected function displayBasicUsage(): void
    {
        $this->command->info('Basic Usage:');
        $this->command->line('  php artisan db:tables                Display all database tables with detailed info');
        $this->command->line('  php artisan db:tables <pattern>      Display tables matching the pattern');
        $this->command->line('  php artisan db:tables --h            Display this help information');
        $this->command->line('  php artisan db:tables --show-ignored Show all tables including ignored ones');
        $this->command->line('  php artisan db:tables --with-data    Show only tables containing data (rows > 0)');
        $this->command->line('  php artisan db:tables --json         Output results in JSON format');
        $this->command->line('  php artisan db:tables --pretty       Pretty print JSON output');
        $this->command->line('');
    }

    /**
     * Display pattern examples
     */
    protected function displayPatternExamples(): void
    {
        $this->command->info('Pattern Examples:');
        $this->command->line('  php artisan db:tables users          Find tables named "users"');
        $this->command->line('  php artisan db:tables user*          Find tables starting with "user"');
        $this->command->line('  php artisan db:tables *_log          Find tables ending with "_log"');
        $this->command->line('  php artisan db:tables *user*         Find tables containing "user" anywhere');
        $this->command->line('  php artisan db:tables %user%         Same as above (SQL wildcards also supported)');
        $this->command->line('  php artisan db:tables user?          Find tables like "user1", "userA", etc.');
        $this->command->line('');
    }

    /**
     * Display filtering options
     */
    protected function displayFilteringOptions(): void
    {
        $this->command->info('Filtering Options:');
        $this->command->line('  --with-data                          Show only tables that contain at least one row');
        $this->command->line('  --show-ignored                       Include tables that are normally ignored');
        $this->command->line('');
    }

    /**
     * Display configuration information
     */
    protected function displayConfiguration(): void
    {
        $this->command->info('Configuration:');
        $this->command->line('  Tables to be ignored are configured in config/tables.php');
        $this->command->line('  You can specify exact table names or patterns to ignore');
        $this->command->line('');
    }

    /**
     * Display behavior notes
     */
    protected function displayBehavior(): void
    {
        $this->command->info('Display Behavior:');
        $this->command->line('  - Without a pattern: Shows detailed information (columns, rows)');
        $this->command->line('  - With a pattern: Shows table names and row counts for better context');
        $this->command->line('');

        $this->command->line('The command supports both SQL wildcards (% and _) and shell-style wildcards (* and ?).');
    }
}
