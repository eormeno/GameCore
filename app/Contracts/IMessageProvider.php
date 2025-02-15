<?php
namespace App\Contracts;

interface IMessageProvider
{
    public function getMessages(array $parameters): array;
}
