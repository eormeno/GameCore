<?php

namespace App\Contracts;

interface IPersistent
{
    public const TABLE = 'services';

    public static function config(): array;
}
