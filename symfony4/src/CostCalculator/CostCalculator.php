<?php


namespace App\CostCalculator;

use App\Entity\Order;
use App\Validators\AddressValidators\ShipmentType;

class CostCalculator
{
    private $shipmentType;
    private $domesticCostCalculator;
    private $internationalCostCalculator;

    public function __construct(ShipmentType $shipmentType, DomesticCostCalculator $domesticCostCalculator, InternationalCostCalculator $internationalCostCalculator)
    {
        $this->shipmentType = $shipmentType;
        $this->domesticCostCalculator = $domesticCostCalculator;
        $this->internationalCostCalculator = $internationalCostCalculator;
    }

    public function calculate(int $id_user, array $request_body): array
    {
        $shipmentType = $this->shipmentType->getType($request_body[Order::SHIPPING_DATA]);
        if ($shipmentType === Order::DOMESTIC_ORDER)
        {
            if ($this->express_shipping($request_body))
                return ($this->domesticCostCalculator->calculate_express($id_user, $request_body[Order::LINE_ITEMS]));
            return ($this->domesticCostCalculator->calculate($id_user, $request_body[Order::LINE_ITEMS]));
        }
        elseif ($shipmentType === Order::INTERNATIONAL_ORDER)
            return ($this->internationalCostCalculator->calculate($id_user, $request_body[Order::LINE_ITEMS]));
    }

    public static function express_shipping(array $request_body): bool
    {
        if (array_key_exists(Order::INFO, $request_body)
            && array_key_exists(Order::EXPRESS_SHIPPING, $request_body[Order::INFO])
            && $request_body[Order::INFO][Order::EXPRESS_SHIPPING] === true)
                return (true);
        return (false);
    }
}