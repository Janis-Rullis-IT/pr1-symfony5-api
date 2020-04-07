<?php


namespace App\Validators\AddressValidators;

use App\Entity\Order;

class AddressValidator
{
    private $shipmentType;
    private $domesticShipmentValidator;
    private $internationalShipmentValidator;
    private $errors;

    public function __construct(ShipmentType $shipmentType, AddressValidatorDomestic $domesticAddressValidator, AddressValidatorInternational $internationalAddressValidator)
    {
        $this->shipmentType = $shipmentType;
        $this->domesticShipmentValidator = $domesticAddressValidator;
        $this->internationalShipmentValidator = $internationalAddressValidator;
        $this->errors = [];
    }

    public function validate(array $ship_to_address): bool
    {
        $shipmentType = $this->shipmentType->getType($ship_to_address);
        if ($shipmentType === null)
            $this->errors = $this->shipmentType->getErrors();

        if ($shipmentType === Order::INTERNATIONAL_ORDER && !$this->internationalShipmentValidator->validate($ship_to_address))
            $this->errors = $this->internationalShipmentValidator->getErrors();
        elseif ($shipmentType === Order::DOMESTIC_ORDER && !$this->domesticShipmentValidator->validate($ship_to_address))
            $this->errors = $this->domesticShipmentValidator->getErrors();

        if (!empty($this->errors))
            return (false);
        return (true);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}