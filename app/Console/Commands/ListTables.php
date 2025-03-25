<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class ListTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:tables {pattern?} {--h : Display help information}
                          {--show-ignored : Include tables that would normally be ignored}
                          {--with-data : Show only tables containing data (rows > 0)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display database table names with optional wildcard filtering';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if help option was provided
        if ($this->option('h')) {
            $this->displayHelp();
            return;
        }

        $pattern = $this->argument('pattern');
        $showIgnored = $this->option('show-ignored');
        $withData = $this->option('with-data');
        $tables = [];

        // Get all tables using a database-agnostic approach
        $connection = DB::connection();
        $databaseName = $connection->getDatabaseName();

        switch ($connection->getDriverName()) {
            case 'sqlite':
                // For SQLite
                $tables = $connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';");
                $tables = array_map(function ($table) {
                    return $table->name;
                }, $tables);
                break;

            case 'mysql':
                // For MySQL
                $tables = $connection->select('SHOW TABLES');
                $tables = array_map(function ($table) {
                    return reset($table);
                }, $tables);
                break;

            case 'pgsql':
                // For PostgreSQL
                $tables = $connection->select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema'");
                $tables = array_map(function ($table) {
                    return $table->tablename;
                }, $tables);
                break;

            default:
                // Fallback for other drivers
                $tables = Schema::getTables();
                break;
        }

        // Filter out ignored tables if not showing ignored
        if (!$showIgnored) {
            $tables = $this->filterIgnoredTables($tables);
        }

        // Apply wildcard filtering if a pattern is provided
        if ($pattern) {
            $tables = array_filter($tables, function ($tableName) use ($pattern) {
                // Convert SQL-like wildcards (% and _) to shell-style wildcards (* and ?)
                $shellPattern = str_replace('%', '*', $pattern);
                $shellPattern = str_replace('_', '?', $shellPattern);

                return fnmatch($shellPattern, $tableName);
            });
        }

        if (empty($tables)) {
            $this->info('No tables found' . ($pattern ? " matching pattern '$pattern'" : ''));
            return;
        }

        // Display results with enhanced information if no pattern was specified
        $this->info('Database Tables:');

        // Show detailed information for each table
        $tableData = [];
        foreach ($tables as $tableName) {
            // Get column count
            $columns = Schema::getColumnListing($tableName);
            $columnCount = count($columns);

            // Get row count
            $rowCount = DB::table($tableName)->count();

            // Skip tables with no rows if --with-data option is used
            if ($withData && $rowCount == 0) {
                continue;
            }

            $tableInfo = [
                'name' => $tableName,
                'rows' => $rowCount,
                'columns' => $columnCount
            ];

            $tableData[] = $tableInfo;
        }

        if (empty($tableData)) {
            $this->info('No tables with data found' . ($pattern ? " matching pattern '$pattern'" : ''));
            return;
        }

        if (!$pattern) {
            $this->table(['Table Name', 'Rows', 'Columns'], $tableData);
        } else {
            // If a pattern was specified, we still want to show row count for context
            $patternTableData = array_map(function ($table) {
                return ['name' => $table['name'], 'rows' => $table['rows']];
            }, $tableData);

            $this->table(['Table Name', 'Rows'], $patternTableData);
        }

        $this->info(count($tableData) . ' table(s) found');

        if ($withData) {
            $this->line('Note: Only showing tables with at least 1 row (--with-data filter active)');
        }

        if (!$showIgnored) {
            $this->line('Note: Some tables might be hidden due to ignore settings in config/tables.php');
            $this->line('      Use --show-ignored to see all tables');
        }
    }

    /**
     * Filter out tables that should be ignored according to config
     *
     * @param array $tables List of table names to filter
     * @return array Filtered list of table names
     */
    private function filterIgnoredTables(array $tables)
    {
        // Get ignore config
        $ignoredTables = Config::get('tables.ignore', []);
        $ignoredPatterns = Config::get('tables.ignore_patterns', []);

        // Filter out exact table name matches
        $filteredTables = array_filter($tables, function ($tableName) use ($ignoredTables) {
            return !in_array($tableName, $ignoredTables);
        });

        // Filter out pattern matches
        return array_filter($filteredTables, function ($tableName) use ($ignoredPatterns) {
            foreach ($ignoredPatterns as $pattern) {
                // Convert SQL-like wildcards to shell-style wildcards if present
                $pattern = str_replace('%', '*', $pattern);
                $pattern = str_replace('_', '?', $pattern);

                if (fnmatch($pattern, $tableName)) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Display detailed help information for the command
     */
    private function displayHelp()
    {
        $this->info('Database Tables Command Help');
        $this->line('============================');
        $this->line('');
        $this->line('This command displays database tables with optional filtering capabilities.');
        $this->line('');

        $this->info('Basic Usage:');
        $this->line('  php artisan db:tables                Display all database tables with detailed info');
        $this->line('  php artisan db:tables <pattern>      Display tables matching the pattern');
        $this->line('  php artisan db:tables --h            Display this help information');
        $this->line('  php artisan db:tables --show-ignored Show all tables including ignored ones');
        $this->line('  php artisan db:tables --with-data    Show only tables containing data (rows > 0)');
        $this->line('');

        $this->info('Pattern Examples:');
        $this->line('  php artisan db:tables users          Find tables named "users"');
        $this->line('  php artisan db:tables user*          Find tables starting with "user"');
        $this->line('  php artisan db:tables *_log          Find tables ending with "_log"');
        $this->line('  php artisan db:tables *user*         Find tables containing "user" anywhere');
        $this->line('  php artisan db:tables %user%         Same as above (SQL wildcards also supported)');
        $this->line('  php artisan db:tables user?          Find tables like "user1", "userA", etc.');
        $this->line('');

        $this->info('Filtering Options:');
        $this->line('  --with-data                          Show only tables that contain at least one row');
        $this->line('  --show-ignored                       Include tables that are normally ignored');
        $this->line('');

        $this->info('Configuration:');
        $this->line('  Tables to be ignored are configured in config/tables.php');
        $this->line('  You can specify exact table names or patterns to ignore');
        $this->line('');

        $this->info('Display Behavior:');
        $this->line('  - Without a pattern: Shows detailed information (columns, rows)');
        $this->line('  - With a pattern: Shows table names and row counts for better context');
        $this->line('');

        $this->line('The command supports both SQL wildcards (% and _) and shell-style wildcards (* and ?).');
    }
}
