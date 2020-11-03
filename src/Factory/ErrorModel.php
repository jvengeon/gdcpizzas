<?php


namespace App\Factory;


class ErrorModel
{
    public function createError(string $message): \App\Model\Error
    {
        return new \App\Model\Error($message);
    }
}