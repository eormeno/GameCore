<?php

namespace App\Exceptions;

use Exception;

class PlayerBannedException extends Exception
{
    public function __construct(string $message = "Player is banned from this game", int $code = 403, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        return response()->json([
            'error' => 'Player banned',
            'message' => $this->getMessage()
        ], $this->getCode());
    }
}