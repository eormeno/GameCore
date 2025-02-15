<?php

namespace App\GameApps\mtq\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class MenuStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$menuView = $this->gameObject->findChild('menu_view');
		if ($menuView) {
			$menuView->activate();
		}
	}

	public function onExit(): void
	{
		$menuView = $this->gameObject->findChild('menu_view');
		if ($menuView) {
			$menuView->deactivate();
		}
	}

	public function onStartEvent(): string|null
	{
		return 'playing';
	}

}
