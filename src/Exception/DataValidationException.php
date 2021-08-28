<?php

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

class DataValidationException extends \Exception
{
    function __construct(ConstraintViolationListInterface $errors, int $code = 0, Throwable $previous = null)
    {
        $errorsString = [];

        foreach ($errors as $error) {
            $errorsString[] = $error->getMessage();
        }

        parent::__construct(implode(' ', $errorsString), $code, $previous);
    }
}
