<?php

namespace App\Exceptions;

use Exception;

class GameFullException extends Exception
{
    public function __construct(string $message = "Game has reached maximum capacity", int $code = 422, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        return response()->json([
            'error' => 'Game full',
            'message' => $this->getMessage()
        ], $this->getCode());
    }
}