<?php

namespace App\GameApps\gtn\Services;

use App\Models\User;
use App\Models\GameService;
use App\Contracts\IPersistent;

class GtnService extends GameService implements IPersistent
{
    public const TABLE = 'gtn_services';

    protected $appends = ['remaining_message', 'user', 'remaining_free_attempts'];
    protected $casts = [
        'finished' => 'boolean',
    ];

    public static function config(): array
    {
        return [
            'cheat_number' => ['integer', 55555],
            'min_number' => ['integer', 1],
            'max_number' => ['integer', 1024],
            'max_attempts' => ['integer', 10],
            'last_number' => ['integer', 0],
            'remaining_attempts' => ['integer', 10],
            'random_number' => ['integer', null],
            'score' => ['integer', 0],
            'times_played' => ['integer', 0],
            'finished' => ['boolean', false],
        ];
    }

    public function getRemainingMessageAttribute(): array
    {
        $key = "remaining_message";
        $remaining = $this->remaining_attempts;
        $finished = $this->finished;
        $message = match (true) {
            $finished => ["$key.finished" => ['remaining_attempts' => $remaining]],
            $remaining === 1 => ["$key.last" => ['remaining_attempts' => $remaining]],
            $remaining === $this->max_attempts => ["$key.starting" => ['remaining_attempts' => $remaining]],
            $remaining <= $this->max_attempts / 2 => ["$key.half" => ['remaining_attempts' => $remaining]],
            default => ["$key.remaining" => ['remaining_attempts' => $remaining]],
        };
        return $message;
    }

    public function getUserAttribute(): User
    {
        return auth()->user()->first();
    }


    public function getTable(): string
    {
        return self::TABLE;
    }

    public function getFillable(): array
    {
        return array_merge(array_keys(self::config()), ['id']);
    }

    public function startGame()
    {
        $clueService = $this->getService('clue-service');
        $random_number = $clueService->findRandomNumber($this);
        $this->times_played++;
        $this->remaining_attempts = $this->max_attempts;
        $this->random_number = $random_number;
        $this->last_number = 0;
        $this->finished = false;
        $this->save();
    }

    public function calculateScore(): int
    {
        return $this->remaining_attempts * 100;
    }

    public function totalScore(): int
    {
        return $this->score;
    }

    public function endGame()
    {
        $this->finished = true;
        $this->score = $this->calculateScore() + $this->score;
        $this->save();
    }

    public function cheat()
    {
        $this->remaining_attempts = 1;
        $this->save();
    }

    public function decreaseRemainingAttempts()
    {
        $this->remaining_attempts--;
        $this->save();
    }

    public function setLastNumber(int $value)
    {
        $this->last_number = $value;
        $this->save();
    }

    public function getRemainingFreeAttemptsAttribute(): int
    {
        return ($this->max_attempts - $this->remaining_attempts) + 1;
    }
}
