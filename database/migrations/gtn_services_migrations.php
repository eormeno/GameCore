<?php

use App\Models\GameService;
use App\Contracts\IPersistent;
use App\Utils\ReflectionUtils;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// TODO Unificar implementación con ComponentsMigration.php
return new class extends Migration {

    public function up(): void
    {
        $tables = 0;
        ReflectionUtils::findClassesInPath($this->namespace(), function ($class) use (&$tables) {
            $table_name = $class->getMethod('getTable')->invoke(new $class->name);
            $config = $class->getMethod('config')->invoke(null);
            Schema::create($table_name, function (Blueprint $table) use ($config, &$tables) {
                $table->id(); // PK and FK to components
                $table->foreign('id')->references('id')->on('game_services')->onDelete('cascade');
                foreach ($config as $column => $type) {
                    $macro = $type[0];
                    $default = $type[1];
                    $table->$macro($column)->default($default)->nullable();
                }
                $tables++;
            });
        }, GameService::class, IPersistent::class);

        print "Created {$tables} tables found in {$this->namespace()}\n";
    }

    public function down(): void
    {
        ReflectionUtils::findClassesInPath($this->namespace(), function ($class) {
            $table_name = $class->getMethod('getTable')->invoke(new $class->name);
            Schema::dropIfExists($table_name);
        }, GameService::class, IPersistent::class);
    }

    private function namespace(): string
    {
        $name = basename(__FILE__, '.php');
        // extract the words between the underscores
        $words = explode('_', $name);
        // remove the last word
        array_pop($words);
		$this->pascalize($words, ['common', 'components', 'services']);
        // join the words with a '/'
        $namespace = implode('/', $words);
        return "GameApps/$namespace";
    }

	private function pascalize(array &$words, array $toPascalize = []): void
	{
		foreach ($words as &$word) {
			$lower = strtolower($word);
			if (in_array($lower, $toPascalize)) {
				$word = ucfirst($word);
			}
		}
	}
};
