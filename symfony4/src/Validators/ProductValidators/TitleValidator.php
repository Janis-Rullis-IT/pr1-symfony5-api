<?php


namespace App\Validators\ProductValidators;


class TitleValidator
{
    public function validate($str): bool
    {
        if (preg_match('/^[A-Za-z0-9\-\s]+$/', $str))
            return (true);
        return (false);
    }
}