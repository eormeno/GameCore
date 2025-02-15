<?php

namespace App\GameApps\gtn\Services;

use App\Models\GameService;

class ClueService extends GameService
{
    protected $table = null;

    public function getClues(GtnService $gtnService = null): array
    {
        if ($gtnService === null) {
            $gtnService = $this->getService('gtn-service');
        }
        $ret = [];
        $number = $gtnService->random_number;
        $min = $gtnService->min_number;
        $max = $gtnService->max_number;

        $minIterations = $this->getMinimalIterations($number, $min, $max);
        $ret['clue.iterations'] = ['data' => $minIterations];

        if ($this->isPrime($number)) {
            $ret['clue.prime'] = ['data' => 'is-prime'];
            return $ret;
        }
        //$multiples = $this->getMultiples($number);
        $factors = implode(', ', $this->getPrimeFactors($number));
        $ret['clue.multiples'] = ['data' => $factors];

        return $ret;
    }

    public function findRandomNumber(GtnService $gtnService): int
    {
        $min = $gtnService->min_number;
        $max = $gtnService->max_number;
        do {
            $number = $this->getRandomNumber($min, $max);
        } while ($this->getMinimalIterations($number, $min, $max) > 6);
        return $number;
    }

    private function getRandomNumber(int $min, int $max): int
    {
        if (env('APP_ENV') === 'testing') {
            return 512;
        }
        return random_int($min, $max);
    }

    public function getMinimalIterations(int $number, int $min, int $max): int
    {
        return $this->binarySearchIterations($min, $max, $number);
    }

    private function binarySearchIterations(int $min, int $max, int $number): int
    {
        $start = $min;
        $end = $max;
        $iterations = 0;
        while ($start <= $end) {
            $iterations++;
            $middle = intdiv($start + $end, 2);
            if ($number == $middle) {
                return $iterations;
            } elseif ($number < $middle) {
                $end = $middle - 1;
            } else {
                $start = $middle + 1;
            }
        }
        return $iterations;
    }

    private function isPrime(int $number): bool
    {
        if ($number <= 1) {
            return false;
        }
        for ($i = 2; $i <= sqrt($number); $i++) {
            if ($number % $i == 0) {
                return false;
            }
        }
        return true;
    }

    public function getPrimeFactors(int $number): array
    {
        $multiples = [];
        for ($i = 2; $i < $number; $i++) {
            if ($number % $i == 0) {
                if ($this->isPrime($i)) {
                    $multiples[] = $i;
                }
            }
        }
        return $multiples;
    }
}
