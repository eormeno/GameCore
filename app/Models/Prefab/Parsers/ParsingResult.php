<?php

namespace App\Models\Prefab\Parsers;

class ParsingResult {
	public bool $isPrefab;
	public string $name;
	public ?string $prefab;

	public function __construct(array $parts) {
		$this->isPrefab = count($parts) === 2;
		$this->name = $parts[0];
		$this->prefab =  $parts[1] ?? null;
	}
}
