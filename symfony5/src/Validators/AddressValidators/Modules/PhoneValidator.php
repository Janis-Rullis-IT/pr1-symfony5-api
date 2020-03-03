<?php


namespace App\Validators\AddressValidators\Modules;


class PhoneValidator
{
    public function validate($str): bool
    {
        if (preg_match('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/', $str))
            return (true);
        return (false);
    }
}