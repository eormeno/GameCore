<?php
namespace App\Contracts;

interface IServiceProvider
{
    public function __get($name);

    public function __set($name, $value);
}
