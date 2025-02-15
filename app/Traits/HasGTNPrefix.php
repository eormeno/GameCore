<?php

namespace App\Traits;

trait HasGTNPrefix
{
	protected function getPrefix(): string
	{
		return 'gtn';
	}
}
