<?php

namespace App\Tests;

use App\Validators\AddressValidators\Modules\ZipCodeValidator;
use PHPUnit\Framework\TestCase;

class ZipCodeValidatorTest extends TestCase
{
    public function test_valid_zipcode_4()
    {
        $zipCodeValidator = new ZipCodeValidator();
        $valid = $zipCodeValidator->validate("12345");
        $this->assertTrue( $valid);
    }

    public function test_valid_zipcode_9()
    {
        $zipCodeValidator = new ZipCodeValidator();
        $valid = $zipCodeValidator->validate("12345-6789");
        $this->assertTrue( $valid);
    }

    public function test_invalid_zipcode()
    {
        $zipCodeValidator = new ZipCodeValidator();
        $valid = $zipCodeValidator->validate("123");
        $this->assertFalse( $valid);
    }
}
