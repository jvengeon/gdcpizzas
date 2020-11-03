<?php


namespace App\Factory;


class Error
{
    public function createError(string $message): \App\Model\Error
    {
        return new \App\Model\Error($message);
    }
}