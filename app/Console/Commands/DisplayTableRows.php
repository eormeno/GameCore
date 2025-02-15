<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DisplayTableRows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:show {table} {columns?*} {--limit=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display all rows of a table';

    /**
     * Given a table name, display all its rows
     */
    public function handle()
    {
        $tableName = $this->argument('table');
        $columnsToDisplay = $this->argument('columns');
        $limit = $this->option('limit');
        if (!$tableName) {
            $tableName = $this->ask('What table do you want to display?');
        }
        if (!$tableName || !Schema::hasTable($tableName)) {
            $this->error("Table '$tableName' does not exist.");
            return;
        }

        $columns = Schema::getColumnListing($tableName);
        if (empty($columnsToDisplay)) {
            $columnsToDisplay = $columns;
        }
        $columns = array_intersect($columns, $columnsToDisplay);
        $rows = DB::table($tableName)->limit($limit)->get()->map(function ($row) use ($columns) {
            $arr = (array) $row;
            return array_filter($arr, function ($key) use ($columns) {
                return in_array($key, $columns);
            }, ARRAY_FILTER_USE_KEY);
        })->toArray();

        // Display the table name in the title
        $this->info("Table: $tableName");
        $this->table($columns, $rows, 'box');

    }
}
