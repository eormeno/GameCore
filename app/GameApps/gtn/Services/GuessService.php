<?php

namespace App\GameApps\gtn\Services;

use App\Models\GameService;

class GuessService extends GameService
{
    protected $table = null;

    public function guess($number): array
    {
        $ret = [];
        $gtnService = $this->getService('gtn-service');
        $this->checkNumberIsCheat($number, $gtnService, $ret);
        $this->checkNumberIsGuessed($number, $gtnService, $ret);
        $this->checkNumberIsLowerThanRandomNumber($number, $gtnService, $ret);
        $this->checkNumberIsGreaterThanRandomNumber($number, $gtnService, $ret);
        return $ret;
    }

    private function checkNumberIsCheat($number, GtnService $gtnService, array &$ret): void
    {
        if ($number == $gtnService->cheat_number) {
            $ret['guess_result.cheat'] = [$gtnService->random_number];
            $gtnService->cheat();
        }
    }

    private function checkNumberIsGuessed($number, GtnService $gtnService, array &$ret)
    {
        if ($number == $gtnService->random_number) {
            $ret['guess_result.success'] = [$gtnService->user->name];
            $gtnService->endGame();
        }
    }

    private function checkNoEnoughAttempts(GtnService $gtnService, array &$ret)
    {
        if ($gtnService->remaining_attempts == 0) {
            $ret['guess_result.game_over'] = [$gtnService->random_number];
            $gtnService->endGame();
        }
    }

    protected function checkNumberIsLowerThanRandomNumber($number, GtnService $gtnService, array &$ret)
    {
        if ($number === $gtnService->cheat_number || $gtnService->finished) {
            return;
        }
        if ($number < $gtnService->random_number) {
            if ($number < $gtnService->min_number) {
                $ret['guess_result.out_of_range'] = [$number, $gtnService->min_number, $gtnService->max_number];
                return;
            }
            $gtnService->decreaseRemainingAttempts();
            $gtnService->setLastNumber($number);
            $this->checkNoEnoughAttempts($gtnService, $ret);
            if ($gtnService->finished) {
                return;
            }
            $ret['guess_result.greater'] = [$number];
        }
    }

    protected function checkNumberIsGreaterThanRandomNumber($number, GtnService $gtnService, array &$ret)
    {
        if ($number === $gtnService->cheat_number || $gtnService->finished) {
            return;
        }
        if ($number > $gtnService->random_number) {
            if ($number > $gtnService->max_number) {
                $ret['guess_result.out_of_range'] = [$number, $gtnService->min_number, $gtnService->max_number];
                return;
            }
            $gtnService->decreaseRemainingAttempts();
            $gtnService->setLastNumber($number);
            $this->checkNoEnoughAttempts($gtnService, $ret);
            if ($gtnService->finished) {
                return;
            }
            $ret['guess_result.lower'] = [$number];
        }
    }
}
