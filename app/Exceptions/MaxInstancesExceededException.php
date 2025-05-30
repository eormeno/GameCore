<?php

namespace App\Exceptions;

use Exception;

class MaxInstancesExceededException extends Exception
{
    public function __construct(string $message = "User has reached maximum game instances", int $code = 422, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        return response()->json([
            'error' => 'Max instances exceeded',
            'message' => $this->getMessage()
        ], $this->getCode());
    }
}