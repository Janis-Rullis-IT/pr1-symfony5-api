<?php

namespace App\Tests;

use App\ErrorsLoader;
use App\Validators\AddressValidators\AddressValidatorInternational;
use App\Validators\AddressValidators\Modules\CountryValidator;
use App\Validators\AddressValidators\Modules\PhoneValidator;
use App\Validators\AddressValidators\Modules\StreetValidator;
use App\Validators\UserValidators\NameSurnameValidator;
use PHPUnit\Framework\TestCase;

class InternationalAddressValidatorTest extends TestCase
{
    public function test_valid_international_address()
    {
        $ship_to_address = [
            "name" => "John",
            "surname" => "Doe",
            "street" => "Palm street 25-7",
            "country" => "Latvia",
            "phone" => "+1 123 123 123"
        ];

        $internationalAddressValidator = new AddressValidatorInternational(new NameSurnameValidator(), new StreetValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $valid = $internationalAddressValidator->validate($ship_to_address);

        $this->assertTrue($valid);

        $errors = $internationalAddressValidator->getErrors();

        $this->assertEmpty($errors);
    }

    public function test_name_not_set()
    {
        $ship_to_address = [
            "XXXX" => "John",
            "surname" => "Doe",
            "street" => "Palm street 25-7",
            "country" => "Latvia",
            "phone" => "+1 123 123 123"
        ];

        $internationalAddressValidator = new AddressValidatorInternational(new NameSurnameValidator(), new StreetValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $valid = $internationalAddressValidator->validate($ship_to_address);

        $this->assertFalse($valid);

        $errors = $internationalAddressValidator->getErrors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey("name", $errors);
        $this->assertEquals($errors["name"][0], "name key not set");
    }

    public function test_surname_not_set()
    {
        $ship_to_address = [
            "name" => "John",
            "xxxxxxx" => "Doe",
            "street" => "Palm street 25-7",
            "country" => "Latvia",
            "phone" => "+1 123 123 123"
        ];

        $internationalAddressValidator = new AddressValidatorInternational(new NameSurnameValidator(), new StreetValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $valid = $internationalAddressValidator->validate($ship_to_address);

        $this->assertFalse($valid);

        $errors = $internationalAddressValidator->getErrors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey("surname", $errors);
        $this->assertEquals($errors["surname"][0], "surname key not set");
    }

    public function test_street_not_set()
    {
        $ship_to_address = [
            "name" => "John",
            "surname" => "Doe",
            "xxxxxx" => "Palm street 25-7",
            "country" => "Latvia",
            "phone" => "+1 123 123 123"
        ];

        $internationalAddressValidator = new AddressValidatorInternational(new NameSurnameValidator(), new StreetValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $valid = $internationalAddressValidator->validate($ship_to_address);

        $this->assertFalse($valid);

        $errors = $internationalAddressValidator->getErrors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey("street", $errors);
        $this->assertEquals($errors["street"][0], "street key not set");
    }

    public function test_country_not_set()
    {
        $ship_to_address = [
            "name" => "John",
            "surname" => "Doe",
            "street" => "Palm street 25-7",
            "xxxxxxx" => "Latvia",
            "phone" => "+1 123 123 123"
        ];

        $internationalAddressValidator = new AddressValidatorInternational(new NameSurnameValidator(), new StreetValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $valid = $internationalAddressValidator->validate($ship_to_address);

        $this->assertFalse($valid);

        $errors = $internationalAddressValidator->getErrors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey("country", $errors);
        $this->assertEquals($errors["country"][0], "country key not set");
    }

    public function test_phone_not_set()
    {
        $ship_to_address = [
            "name" => "John",
            "surname" => "Doe",
            "street" => "Palm street 25-7",
            "country" => "Latvia",
            "xxxxx" => "+1 123 123 123"
        ];

        $internationalAddressValidator = new AddressValidatorInternational(new NameSurnameValidator(), new StreetValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $valid = $internationalAddressValidator->validate($ship_to_address);

        $this->assertFalse($valid);

        $errors = $internationalAddressValidator->getErrors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey("phone", $errors);
        $this->assertEquals($errors["phone"][0], "phone key not set");
    }
}
