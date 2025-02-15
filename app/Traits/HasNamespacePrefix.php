<?php

namespace App\Traits;

trait HasNamespacePrefix
{
	protected function getPrefix(): string
	{
		// The prefix should be defined in the namespace which should have the following format:
		// 'App\GameApps\{previx}\[\Components | \Services]
		$namespace = explode('\\', get_class($this));
		$prefix = strtolower($namespace[2]);
		return $prefix;
	}
}
