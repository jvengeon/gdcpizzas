<?php


namespace App\Model;


class Error
{
    public $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}