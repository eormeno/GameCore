<?php

namespace App\Exceptions;

use Exception;

class GameAlreadyStartedException extends Exception
{
    public function __construct(string $message = "Cannot join a running game when late join is disabled", int $code = 422, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        return response()->json([
            'error' => 'Game already started',
            'message' => $this->getMessage()
        ], $this->getCode());
    }
}