<?php

namespace App\Models\Prefab\Parsers;

use InvalidArgumentException;

class GameObjectNameParser
{
	public function parse(string $childName): ParsingResult
	{
		$this->validateInput($childName);
		$parts = $this->splitName($childName);
		return new ParsingResult($parts);
	}

	private function validateInput(string $childName): void
	{
		$childName = trim($childName);
		if (empty($childName)) {
			throw new InvalidArgumentException('GameObject name cannot be empty.');
		}
	}

	private function splitName(string $childName): array
	{
		$parts = explode(':', $childName);
		if (count($parts) > 2) {
			throw new InvalidArgumentException('GameObject name format is invalid.');
		}
		if (empty($parts[0]) || (count($parts) === 2 && empty($parts[1]))) {
			throw new InvalidArgumentException('GameObject name format is invalid.');
		}
		return $parts;
	}
}
