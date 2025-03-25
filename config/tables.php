<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Tables to Ignore
	|--------------------------------------------------------------------------
	|
	| This array contains the tables that should be ignored when listing tables
	| with the db:tables command. You can use plain table names or patterns.
	|
	*/
	'ignore' => [
		'migrations',				// Ignore migrations table
		'jobs',						// Ignore jobs table
		'failed_jobs',				// Ignore failed jobs table
		'job_batches',				// Ignore job batches table
		'password_resets',			// Ignore password resets table
		'sessions',					// Ignore sessions table
		'cache',					// Ignore cache table
		'cache_locks',				// Ignore cache locks table
		'password_reset_tokens',	// Ignore password reset tokens table
		'personal_access_tokens',	// Ignore personal access tokens table
		'users',					// Ignore users table
		// Add more tables to ignore as needed
	],

	/*
	|--------------------------------------------------------------------------
	| Ignore Patterns
	|--------------------------------------------------------------------------
	|
	| These patterns will be used to ignore tables that match them.
	| You can use * and ? wildcards.
	|
	*/
	'ignore_patterns' => [
		'temp_*',					// Ignore all tables starting with temp_
		'*_log',					// Ignore all tables ending with _log
		// Add more patterns as needed
	],
];
