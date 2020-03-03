<?php

namespace App\Validators\AddressValidators\Modules;

class ZipCodeValidator
{
    public function validate($str): bool
    {
        if (preg_match('/^[0-9]{5}(?:-[0-9]{4})?$/', $str))
            return (true);
        return (false);
    }
}