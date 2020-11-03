<?php


namespace App\Formatter;


use Symfony\Component\Validator\ConstraintViolationListInterface;

interface ConstraintViolationListErrorMessageInterface
{
    public function format(ConstraintViolationListInterface $constraintViolationList);
}