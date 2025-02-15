<?php

namespace App\Contracts;

interface IStateContext
{
    public function request(array $event);

}
