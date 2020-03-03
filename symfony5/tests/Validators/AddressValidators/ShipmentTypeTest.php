<?php

namespace App\Tests;

use App\Validators\AddressValidators\ShipmentType;
use PHPUnit\Framework\TestCase;

class ShipmentTypeTest extends TestCase
{
    public function test_is_domestic()
    {
        $ship_to_address = [
            "name" => "John",
            "surname" => "Doe",
            "street" => "Palm street 25-7",
            "state" => "California",
            "ZIP" => "60744",
            "country" => "US",
            "phone" => "+1 123 123 123"
        ];
        $shipmentType = new ShipmentType();

        $type = $shipmentType->getType($ship_to_address);
        $this->assertEquals($type, "domestic");
    }

    public function test_is_international()
    {
        $ship_to_address = [
            "name" => "John",
            "surname" => "Doe",
            "street" => "pirma iela 20-7",
            "state" => "Adazu novads",
            "ZIP" => "60744",
            "country" => "Latvia",
            "phone" => "+371 28 222 222"
        ];
        $shipmentType = new ShipmentType();

        $type = $shipmentType->getType($ship_to_address);
        $this->assertEquals($type, "international");
    }

    public function test_country_not_set()
    {
        $ship_to_address = [
            "name" => "John",
            "surname" => "Doe",
            "street" => "pirma iela 20-7",
            "state" => "Adazu novads",
            "ZIP" => "60744",
            "xxxxxxx" => "Latvia",
            "phone" => "+371 28 222 222"
        ];

        $shipmentType = new ShipmentType();
        $type = $shipmentType->getType($ship_to_address);
        $this->assertEquals($type, null);

        $errors = $shipmentType->getErrors();
        $this->assertIsArray($errors);
        $this->assertArrayHasKey("country", $errors);
        $this->assertEquals("country key not set", $errors["country"]);
    }
}
