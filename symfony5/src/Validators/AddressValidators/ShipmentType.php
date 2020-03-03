<?php


namespace App\Validators\AddressValidators;

use App\Entity\Order;

class ShipmentType
{
    const DOMESTIC_CODE = "US";
    const DOMESTIC_CODE3 = "USA";
    const DOMESTIC_NAME = "United States of America";
    const NO_COUNTRY_SET = "country key not set";

    private $errors = [];

    public function getType(array $ship_to_address): ?string
    {
        if (!array_key_exists(Order::COUNTRY, $ship_to_address))
        {
            $this->errors[Order::COUNTRY] = ShipmentType::NO_COUNTRY_SET;
            return (null);
        }

        if ($ship_to_address[Order::COUNTRY] === self::DOMESTIC_CODE
            || $ship_to_address[Order::COUNTRY] === self::DOMESTIC_CODE3
            || $ship_to_address[Order::COUNTRY] === self::DOMESTIC_NAME)
            return (Order::DOMESTIC_ORDER);

        return (Order::INTERNATIONAL_ORDER);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}