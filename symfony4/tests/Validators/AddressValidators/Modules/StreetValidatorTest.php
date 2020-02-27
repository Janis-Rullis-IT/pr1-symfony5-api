<?php

namespace App\Tests;

use App\Validators\AddressValidators\Modules\StreetValidator;
use PHPUnit\Framework\TestCase;

class StreetValidatorTest extends TestCase
{
    public function test_valid_street()
    {
        $streetValidator = new StreetValidator();
        $valid = $streetValidator->validate("Palm street 25-7");
        $this->assertEquals($valid, 1);
    }

    public function test_invalid_street()
    {
        $streetValidator = new StreetValidator();
        $valid = $streetValidator->validate("Palm street 25-7+++++++");
        $this->assertEquals($valid, 0);
    }
}
