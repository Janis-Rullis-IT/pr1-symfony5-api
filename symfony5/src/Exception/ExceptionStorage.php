<?php

namespace App\Exception;

class ExceptionStorage extends \Exception
{
    private $errors = [];

    public function __construct(array $errors, int $code = 0)
    {
        $this->errors = $errors;
        $this->code = $code;
        parent::__construct('', $code, null);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
