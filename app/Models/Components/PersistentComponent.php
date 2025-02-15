<?php

namespace App\Models\Components;

use App\Contracts\IPersistent;

abstract class PersistentComponent extends Component implements IPersistent
{
    public function getTable(): string
    {
		$tableName = parent::getTable();
		if (strpos($tableName, $this->getPrefix()) === 0) {
			return $tableName;
		}
		if ($this->getPrefix() === '') {
			return $tableName;
		}
		return $this->getPrefix() . '_' . $tableName;
    }

	protected function getPrefix(): string {
		return '';
	}

	public static function config(): array
	{
		return [];
	}

    public function getFillable(): array
    {
        $config = [];
        // if current class has a config method, then call it
        if (method_exists($this, 'config')) {
            $config = $this->config();
        }
        return array_merge(array_keys($config), ['id']);
    }

	public function getCast(): array
	{
		$config = [];
		// if current class has a config method, then call it
		if (method_exists($this, 'config')) {
			$config = $this->config();
		}
		$cast = [];
		foreach ($config as $key => $value) {
			if ($value[0] === 'json') {
				$cast[$key] = 'array';
			}
		}
		return $cast;
	}
}
