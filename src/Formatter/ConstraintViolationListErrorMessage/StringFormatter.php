<?php


namespace App\Formatter\ConstraintViolationListErrorMessage;


use App\Formatter\ConstraintViolationListErrorMessageInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class StringFormatter implements ConstraintViolationListErrorMessageInterface
{
    public function format(ConstraintViolationListInterface $constraintViolationList)
    {
        //json_encode directly from $constraintViolationList return nothing. Generate list of error messages
        $errorsList = array_map(
            function ($error) {
                return $error->getMessage();
            },
            iterator_to_array($constraintViolationList)
        );

        return implode($errorsList, ', ');
    }
}