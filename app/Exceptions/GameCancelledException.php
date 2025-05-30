<?php

namespace App\Exceptions;

use Exception;

class GameCancelledException extends Exception
{
    public function __construct(string $message = "Game has been cancelled", int $code = 422, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        return response()->json([
            'error' => [
                'type' => 'GameCancelledException',
                'message' => $this->getMessage(),
                'code' => $this->getCode()
            ]
        ], $this->getCode());
    }
}
