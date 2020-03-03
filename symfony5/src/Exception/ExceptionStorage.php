<?php


namespace App\Exception;


use Throwable;

class ExceptionStorage extends \Exception
{
    private $errors = [];

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct("", 0, null);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}