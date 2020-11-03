<?php


namespace App\Factory;


use App\Model\Error;

class ErrorModel
{
    public function createError(string $message): Error
    {
        return new Error($message);
    }
}