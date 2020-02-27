<?php

namespace App\Tests;

use App\ErrorsLoader;
use App\Validators\AddressValidators\AddressValidator;
use App\Validators\AddressValidators\AddressValidatorDomestic;
use App\Validators\AddressValidators\AddressValidatorInternational;
use App\Validators\AddressValidators\Modules\CountryValidator;
use App\Validators\AddressValidators\Modules\PhoneValidator;
use App\Validators\AddressValidators\Modules\StateValidator;
use App\Validators\AddressValidators\Modules\StreetValidator;
use App\Validators\AddressValidators\Modules\ZipCodeValidator;
use App\Validators\AddressValidators\ShipmentType;
use App\Validators\UserValidators\NameSurnameValidator;
use PHPUnit\Framework\TestCase;

class AddressValidatorTest extends TestCase
{
    public function test_valid_domestic_address()
    {
        $ship_to_address = [
            "name" => "John",
            "surname" => "Doe",
            "street" => "Palm street 25-7",
            "state" => "California",
            "zip" => "60744",
            "country" => "US",
            "phone" => "+1 123 123 123"
        ];

        $domesticAddressValidator = new AddressValidatorDomestic(new NameSurnameValidator(), new StreetValidator(), new StateValidator(), new ZipCodeValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());
        $internationalAddressValidator = new AddressValidatorInternational(new NameSurnameValidator(), new StreetValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $addressValidator = new AddressValidator(new ShipmentType(), $domesticAddressValidator, $internationalAddressValidator);

        $valid = $addressValidator->validate($ship_to_address);

        $this->assertTrue($valid);
    }

    public function test_valid_international_address()
    {
        $ship_to_address = [
            "name" => "John",
            "surname" => "Doe",
            "street" => "Palm street 25-7",
            "country" => "Latvia",
            "phone" => "+1 123 123 123"
        ];

        $domesticAddressValidator = new AddressValidatorDomestic(new NameSurnameValidator(), new StreetValidator(), new StateValidator(), new ZipCodeValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());
        $internationalAddressValidator = new AddressValidatorInternational(new NameSurnameValidator(), new StreetValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $addressValidator = new AddressValidator(new ShipmentType(), $domesticAddressValidator, $internationalAddressValidator);

        $valid = $addressValidator->validate($ship_to_address);

        $this->assertTrue($valid);
    }

    public function test_no_country_key_set()
    {
        $ship_to_address = [
            "name" => "John",
            "surname" => "Doe",
            "street" => "Palm street 25-7",
            "XXXXXXX" => "Latvia",
            "phone" => "+1 123 123 123"
        ];

        $domesticAddressValidator = new AddressValidatorDomestic(new NameSurnameValidator(), new StreetValidator(), new StateValidator(), new ZipCodeValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());
        $internationalAddressValidator = new AddressValidatorInternational(new NameSurnameValidator(), new StreetValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $addressValidator = new AddressValidator(new ShipmentType(), $domesticAddressValidator, $internationalAddressValidator);

        $valid = $addressValidator->validate($ship_to_address);

        $this->assertFalse($valid);

        $errors = $addressValidator->getErrors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey("country", $errors);
        $this->assertEquals($errors["country"], "country key not set");
    }

    public function test_invalid_country()
    {
        $ship_to_address = [
            "name" => "John",
            "surname" => "Doe",
            "street" => "Palm street 25-7",
            "country" => "XXXXXX",
            "phone" => "+1 123 123 123"
        ];

        $domesticAddressValidator = new AddressValidatorDomestic(new NameSurnameValidator(), new StreetValidator(), new StateValidator(), new ZipCodeValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());
        $internationalAddressValidator = new AddressValidatorInternational(new NameSurnameValidator(), new StreetValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $addressValidator = new AddressValidator(new ShipmentType(), $domesticAddressValidator, $internationalAddressValidator);

        $valid = $addressValidator->validate($ship_to_address);

        $this->assertFalse($valid);

        $errors = $addressValidator->getErrors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey("country", $errors);
        $this->assertEquals($errors["country"][0], "invalid country");
    }
}
