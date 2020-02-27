<?php

namespace App\Validators\UserValidators;

class NameSurnameValidator
{
    public function validate($str): bool
    {
        if (preg_match('/^[a-z ,.\'-]+$/i', $str))
            return (true);
        return (false);
    }
}