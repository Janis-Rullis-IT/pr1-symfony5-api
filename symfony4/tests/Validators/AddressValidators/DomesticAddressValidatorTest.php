<?php

namespace App\Tests;

use App\Entity\Order;
use App\ErrorsLoader;
use App\Validators\AddressValidators\AddressValidatorDomestic;
use App\Validators\AddressValidators\Modules\CountryValidator;
use App\Validators\AddressValidators\Modules\PhoneValidator;
use App\Validators\AddressValidators\Modules\StateValidator;
use App\Validators\AddressValidators\Modules\StreetValidator;
use App\Validators\AddressValidators\Modules\ZipCodeValidator;
use App\Validators\UserValidators\NameSurnameValidator;
use PHPUnit\Framework\TestCase;

class DomesticAddressValidatorTest extends TestCase
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

        $valid = $domesticAddressValidator->validate($ship_to_address);

        $this->assertTrue($valid);

        $errors = $domesticAddressValidator->getErrors();

        $this->assertEmpty($errors);
    }

    public function test_name_not_set()
    {
        $ship_to_address = [
            "XXXX" => "John",
            "surname" => "Doe",
            "street" => "Palm street 25-7",
            "state" => "California",
            "zip" => "60744",
            "country" => "US",
            "phone" => "+1 123 123 123"
        ];

        $domesticAddressValidator = new AddressValidatorDomestic(new NameSurnameValidator(), new StreetValidator(), new StateValidator(), new ZipCodeValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $valid = $domesticAddressValidator->validate($ship_to_address);

        $this->assertFalse($valid);

        $errors = $domesticAddressValidator->getErrors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey(Order::OWNER_NAME, $errors);
        $this->assertEquals($errors[Order::OWNER_NAME][0], "name key not set");
    }

    public function test_surname_not_set()
    {
        $ship_to_address = [
            "name" => "John",
            "xxxxxxx" => "Doe",
            "street" => "Palm street 25-7",
            "state" => "California",
            "zip" => "60744",
            "country" => "US",
            "phone" => "+1 123 123 123"
        ];

        $domesticAddressValidator = new AddressValidatorDomestic(new NameSurnameValidator(), new StreetValidator(), new StateValidator(), new ZipCodeValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $valid = $domesticAddressValidator->validate($ship_to_address);

        $this->assertFalse($valid);

        $errors = $domesticAddressValidator->getErrors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey(Order::OWNER_SURNAME, $errors);
        $this->assertEquals($errors[Order::OWNER_SURNAME][0], "surname key not set");
    }

    public function test_street_not_set()
    {
        $ship_to_address = [
            "name" => "John",
            "surname" => "Doe",
            "xxxxxx" => "Palm street 25-7",
            "state" => "California",
            "zip" => "60744",
            "country" => "US",
            "phone" => "+1 123 123 123"
        ];

        $domesticAddressValidator = new AddressValidatorDomestic(new NameSurnameValidator(), new StreetValidator(), new StateValidator(), new ZipCodeValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $valid = $domesticAddressValidator->validate($ship_to_address);

        $this->assertFalse($valid);

        $errors = $domesticAddressValidator->getErrors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey(Order::STREET, $errors);
        $this->assertEquals($errors[Order::STREET][0], "street key not set");
    }

    public function test_state_not_set()
    {
        $ship_to_address = [
            "name" => "John",
            "surname" => "Doe",
            "street" => "Palm street 25-7",
            "xxxxx" => "California",
            "zip" => "60744",
            "country" => "US",
            "phone" => "+1 123 123 123"
        ];

        $domesticAddressValidator = new AddressValidatorDomestic(new NameSurnameValidator(), new StreetValidator(), new StateValidator(), new ZipCodeValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $valid = $domesticAddressValidator->validate($ship_to_address);

        $this->assertFalse($valid);

        $errors = $domesticAddressValidator->getErrors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey(Order::STATE, $errors);
        $this->assertEquals($errors[Order::STATE][0], "state key not set");
    }

    public function test_zip_not_set()
    {
        $ship_to_address = [
            "name" => "John",
            "surname" => "Doe",
            "street" => "Palm street 25-7",
            "state" => "California",
            "xxx" => "60744",
            "country" => "US",
            "phone" => "+1 123 123 123"
        ];

        $domesticAddressValidator = new AddressValidatorDomestic(new NameSurnameValidator(), new StreetValidator(), new StateValidator(), new ZipCodeValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $valid = $domesticAddressValidator->validate($ship_to_address);

        $this->assertFalse($valid);

        $errors = $domesticAddressValidator->getErrors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey(Order::ZIP, $errors);
        $this->assertEquals($errors[Order::ZIP][0], "zip code key not set");
    }

    public function test_country_not_set()
    {
        $ship_to_address = [
            "name" => "John",
            "surname" => "Doe",
            "street" => "Palm street 25-7",
            "state" => "California",
            "zip" => "60744",
            "xxxxxxx" => "US",
            "phone" => "+1 123 123 123"
        ];

        $domesticAddressValidator = new AddressValidatorDomestic(new NameSurnameValidator(), new StreetValidator(), new StateValidator(), new ZipCodeValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $valid = $domesticAddressValidator->validate($ship_to_address);

        $this->assertFalse($valid);

        $errors = $domesticAddressValidator->getErrors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey(Order::COUNTRY, $errors);
        $this->assertEquals($errors[Order::COUNTRY][0], "country key not set");
    }

    public function test_phone_not_set()
    {
        $ship_to_address = [
            "name" => "John",
            "surname" => "Doe",
            "street" => "Palm street 25-7",
            "state" => "California",
            "zip" => "60744",
            "country" => "US",
            "xxxxx" => "+1 123 123 123"
        ];

        $domesticAddressValidator = new AddressValidatorDomestic(new NameSurnameValidator(), new StreetValidator(), new StateValidator(), new ZipCodeValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());

        $valid = $domesticAddressValidator->validate($ship_to_address);

        $this->assertFalse($valid);

        $errors = $domesticAddressValidator->getErrors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey(Order::PHONE, $errors);
        $this->assertEquals($errors[Order::PHONE][0], "phone key not set");
    }
}
