<?php

namespace App\Services;

use App\Models\Game;
use App\Events\GameEvent;
use App\Contracts\IRenderer;
use App\Models\GameObject\GameObject;

class RendererService implements IRenderer
{

	public function render(Game $game, array $eventInfo): array
	{
		$currentTimestamp = microtime(true);
		event(new GameEvent($game, $eventInfo));
		$result = $this->buildViews($game, $eventInfo);
		$result['elapsed'] = $this->calculateElapsed($currentTimestamp);
		return $result;
	}

	private function buildViews(Game $game, array $event): array
	{
		$isJsonClient = $game->gameApp->client == 'webgl';
		$rootGameObject = $game->gameObject;
		$rendered = $event['rendered'];
		$response = [
			'elapsed' => 0,
		];

		if (!$isJsonClient) {
			$response['root'] = $rootGameObject->id;
		}

		$activeGameObjects = GameObject::activesOfGame($game)->get();
		$views = $this->resolveActiveGameObjectsViews($activeGameObjects, $game, $rendered);

		foreach ($views as $id => $view) {
			$response[$id] = $view;
		}

		$activeIds = $this->resolveActiveGOIds($activeGameObjects, $rootGameObject->id);
		$deactiveIds = $this->resolveDeactives($rendered, $activeIds);

		if (!empty($deactiveIds)) {
			$response['deactives'] = $deactiveIds;
		}

		return $response;
	}

	private function resolveActiveGOIds($activeGameObjects, $rootId): array
	{
		return collect($activeGameObjects)
			->pluck('id')
			->reject(fn($id) => $id === $rootId)
			->values()
			->toArray();
	}

	private function resolveDeactives(array $rendered, array $actives): array
	{
		$deactives = [];
		if (empty($rendered)) {
			return $deactives;
		}
		foreach ($rendered as $id => $version) {
			if (!in_array($id, $actives)) {
				$deactives[] = (string) $id;
			}
		}
		return $deactives;
	}

	private function resolveActiveGameObjectsViews($activeGameObjects, Game $game, array $rendered): array
	{
		$views = [];
		foreach ($activeGameObjects as $gameObject) {
			$view = $gameObject->view();
			if ($this->shouldSkipRendering($gameObject, $rendered)) {
				continue;
			}
			$views[$gameObject->id] = $view;
		}
		return $views;
	}

	private function shouldSkipRendering(GameObject $gameObject, array $rendered): bool
	{
		return empty($gameObject->view()) ||
			(isset($rendered[$gameObject->id]) && $rendered[$gameObject->id] == $gameObject->version);
	}

	private function calculateElapsed($start): int
	{
		return ceil((microtime(true) - $start) * 1000);
	}

}
