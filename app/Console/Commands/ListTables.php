<?php

namespace App\Console\Commands;

use App\Console\Support\DatabaseTablesCommandHelp;
use App\Services\DatabaseTable\Contracts\DatabaseTableServiceInterface;
use Illuminate\Console\Command;

class ListTables extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'db:tables
				{pattern?}
				{--h : Display help information}
				{--show-ignored : Include tables that would normally be ignored}
				{--with-data : Show only tables containing data (rows > 0)}
				{--json : Output in JSON format}
				{--pretty : Pretty print JSON output}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Display database table names with optional wildcard filtering';

	/**
	 * @var DatabaseTableServiceInterface
	 */
	protected $databaseTableService;

	/**
	 * @param DatabaseTableServiceInterface $databaseTableService
	 */
	public function __construct(DatabaseTableServiceInterface $databaseTableService)
	{
		parent::__construct();
		$this->databaseTableService = $databaseTableService;
	}

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
		$json = $this->option('json');
		$pretty = $this->option('pretty');

		if ($json) {
			$this->output->writeln($this->databaseTableService->getTablesJson($showIgnored, $withData, $pattern, $pretty));
			return;
		}

		$tables = $this->databaseTableService->getTables($showIgnored, $withData, $pattern);

		if (empty($tables)) {
			$this->info('No tables found' . ($pattern ? " matching pattern '$pattern'" : ''));
			return;
		}

		// Display results with enhanced information
		$this->info('Database Tables:');

		// Format table data for display
		$tableData = [];
		foreach ($tables as $table) {
			if (!$pattern) {
				$tableData[] = [
					'name' => $table['name'],
					'rows' => $table['rows'],
					'columns' => $table['columns']
				];
			} else {
				$tableData[] = [
					'name' => $table['name'],
					'rows' => $table['rows']
				];
			}
		}

		// Display table data
		if (!$pattern) {
			$this->table(['Table Name', 'Rows', 'Columns'], $tableData);
		} else {
			$this->table(['Table Name', 'Rows'], $tableData);
		}

		$this->info(count($tables) . ' table(s) found');

		if ($withData) {
			$this->line('Note: Only showing tables with at least 1 row (--with-data filter active)');
		}

		if (!$showIgnored) {
			$this->line('Note: Some tables might be hidden due to ignore settings in config/tables.php');
			$this->line('      Use --show-ignored to see all tables');
		}
	}

	/**
	 * Display detailed help information for the command
	 */
	private function displayHelp()
	{
		$helpDisplay = new DatabaseTablesCommandHelp($this);
		$helpDisplay->display();
	}
}
