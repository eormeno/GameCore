<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventRequestFilter extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [];
	}

	public function eventInfo(): array
	{
		$validated = $this->all();
		$event_name = $validated['event'] ?? null;
		$eventSource = $validated['source'] ?? null;
		unset($validated['event']);
		unset($validated['_token']);
		unset($validated['_method']);
		unset($validated['submit']);
		$event = [
			'event' => $event_name,
			'source' => $eventSource,
			'data' => $validated['data'],
			'destination' => $validated['destination'] ?? null,
			'rendered' => $validated['rendered'] ?? [],
		];
		return $event;
	}
}
