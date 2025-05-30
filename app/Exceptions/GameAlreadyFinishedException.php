<?php

namespace App\Exceptions;

use Exception;

class GameAlreadyFinishedException extends Exception
{
    public function __construct(string $message = "Game has already finished", int $code = 422, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        return response()->json([
            'error' => [
                'type' => 'GameAlreadyFinishedException',
                'message' => $this->getMessage(),
                'code' => $this->getCode()
            ]
        ], $this->getCode());
    }
}