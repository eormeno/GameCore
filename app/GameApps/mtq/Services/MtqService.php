<?php

namespace App\GameApps\mtq\Services;

use App\Models\GameService;
use App\Contracts\IPersistent;

class MtqService extends GameService implements IPersistent
{
	public const TABLE = 'mtq_services';

	public static function config(): array
	{
		return [
			'questions' => ['string', ''],
			'current_question' => ['integer', 0],
			'current_score' => ['integer', 0],
			'finished' => ['boolean', false],
		];
	}
}
