<?php

namespace App\Tests;

use App\Validators\AddressValidators\Modules\CountryValidator;
use PHPUnit\Framework\TestCase;

class CountryValidatorTest extends TestCase
{
    public function test_valid_country_code()
    {
        $countryValidator = new CountryValidator();
        $code = "LV";
        $valid = $countryValidator->validateAlphabetic($code);
        $this->assertTrue($valid);
    }

    public function test_invalid_country_code()
    {
        $countryValidator = new CountryValidator();
        $code = "XX";
        $valid = $countryValidator->validateAlphabetic($code);
        $this->assertFalse($valid);
    }

    public function test_valid_country_code3()
    {
        $countryValidator = new CountryValidator();
        $code = "LVA";
        $valid = $countryValidator->validateAlphabetic($code);
        $this->assertTrue($valid);
    }

    public function test_valid_country_name()
    {
        $countryValidator = new CountryValidator();
        $code = "Latvia";
        $valid = $countryValidator->validateAlphabetic($code);
        $this->assertTrue($valid);
    }

    public function test_valid_country_number()
    {
        $countryValidator = new CountryValidator();
        $code = "428";
        $valid = $countryValidator->validateNumeric($code);
        $this->assertTrue($valid);
    }
}
