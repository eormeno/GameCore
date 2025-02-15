<?php

namespace App\Models\GameObject;

abstract class ViewStateContextBase extends StateContextBase
{
	public function view()
	{
		$mergedViews = [];
		$this->componentsIterator(function ($component) use (&$mergedViews) {
			$view = $component->view();
			if ($view !== null) {
				if (is_array($view)) {
					$version = $component->gameObject->version;
					if ($version !== null && $version > 0) {
						$view['version'] = $version;
					}
					$mergedViews = array_merge($mergedViews, $view);
				} else {
					// TODO This is a very naive approach, it should be improved
					$mergedViews = $view;
				}
			}
		});
		return $mergedViews ?? null;
	}
}
