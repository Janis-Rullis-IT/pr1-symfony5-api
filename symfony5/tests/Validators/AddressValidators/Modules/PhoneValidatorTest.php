<?php

namespace App\Tests;

use App\Validators\AddressValidators\Modules\PhoneValidator;
use PHPUnit\Framework\TestCase;

class PhoneValidatorTest extends TestCase
{
    public function test_valid_phone()
    {
        $phoneValidator = new PhoneValidator();
        $phone = "+371 28 888 888";
        $valid = $phoneValidator->validate($phone);
        $this->assertTrue($valid);
    }

    public function test_invalid_phone()
    {
        $phoneValidator = new PhoneValidator();
        $phone = "+371 28 * 888 888";
        $valid = $phoneValidator->validate($phone);
        $this->assertFalse($valid);
    }
}
