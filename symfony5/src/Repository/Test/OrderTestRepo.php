<?php


namespace App\Repository\Test;

use App\CostCalculator\CostCalculator;
use App\Entity\Order;
use App\Interfaces\IRelationRepo;
use App\Interfaces\IOrderRepo;
use App\Validators\AddressValidators\ShipmentType;
use App\Validators\UserValidators\UidValidator;

class OrderTestRepo implements IOrderRepo
{
    private $db;
    private $id;
    private $userIdValidator;
    private $relationRepository;
    private $shipmentType;

    public function __construct(UidValidator $userIdValidator, IRelationRepo $relationRepository, ShipmentType $shipmentType)
    {
        $this->db = [];
        $this->id = 1;
        $this->userIdValidator = $userIdValidator;
        $this->relationRepository = $relationRepository;
        $this->shipmentType = $shipmentType;

        $requestBody = [
            "shipToAddress" => [
                "name" => "John",
                "surname" => "Doe",
                "street" => "Palm street 25-7",
                "state" => "California",
                "zip" => "60744",
                "country" => "US",
                "phone" => "+1 123 123 123"
            ],
            "lineItems" => [
                ["id" => 1, "quantity" => 10],
                ["id" => 1, "quantity" => 1]
            ]
        ];
        $costs = [
            "productionCost" => 11000,
            "shippingCost" => 650,
            "totalCost" => 11650
        ];

        $this->create($requestBody, $costs, 1);
    }

    public function create(array $requestBody, array $costs, int $id_user)
    {
        $order = new Order();
        $order->setId($this->id);
        $order->setOwnerId($id_user);
        $order->setName($requestBody[Order::SHIPPING_DATA][Order::OWNER_NAME]);
        $order->setSurname($requestBody[Order::SHIPPING_DATA][Order::OWNER_SURNAME]);
        $order->setStreet($requestBody[Order::SHIPPING_DATA][Order::STREET]);

        if (array_key_exists(Order::STATE, $requestBody[Order::SHIPPING_DATA]))
            $order->setState($requestBody[Order::SHIPPING_DATA][Order::STATE]);
        else
            $order->setState(null);

        if (array_key_exists(Order::ZIP, $requestBody[Order::SHIPPING_DATA]))
            $order->setZip($requestBody[Order::SHIPPING_DATA][Order::ZIP]);
        else
            $order->setZip(null);

        $order->setCountry($requestBody[Order::SHIPPING_DATA][Order::COUNTRY]);
        $order->setPhone($requestBody[Order::SHIPPING_DATA][Order::PHONE]);
        $order->setProductionCost($costs[Order::PRODUCTION_COST]);
        $order->setShippingCost($costs[Order::SHIPPING_COST]);

        if ($this->shipmentType->getType($requestBody[Order::SHIPPING_DATA]) === Order::INTERNATIONAL_ORDER)
        {
            if (CostCalculator::express_shipping($requestBody))
                $order->setExpressShipping(true);
            else
                $order->setExpressShipping(false);
        }

        $order->setTotalCost($costs[Order::TOTAL_COST]);
        $this->db[$this->id] = $order;
        $this->id++;
        return $order;
    }

    private function getEntityById(int $id)
    {
        foreach ($this->db as $order)
        {
            if ($order->getId() === $id)
                return ($order);
        }
        return (null);
    }

    public function getById(int $id_user, int $id)
    {
        if ($this->userIdValidator->validate($id_user))
        {
            $order = [];
            $orderEntity = $this->getEntityById($id);
            if (!$orderEntity)
                return (null);

            $order[Order::SHIPPING_DATA] = array();
            $order[Order::SHIPPING_DATA][Order::OWNER_NAME] = $orderEntity->getName();
            $order[Order::SHIPPING_DATA][Order::OWNER_SURNAME] = $orderEntity->getSurname();
            $order[Order::SHIPPING_DATA][Order::STREET] = $orderEntity->getStreet();
            if ($orderEntity->getState())
                $order[Order::SHIPPING_DATA][Order::STATE] = $orderEntity->getState();
            if ($orderEntity->getZip())
                $order[Order::SHIPPING_DATA][Order::ZIP] = $orderEntity->getZip();
            $order[Order::SHIPPING_DATA][Order::COUNTRY] = $orderEntity->getCountry();
            $order[Order::SHIPPING_DATA][Order::PHONE] = $orderEntity->getPhone();

            $order[Order::LINE_ITEMS] = $this->relationRepository->line_items($id_user, $id);

            $order[Order::INFO] = array();
            $order[Order::INFO][Order::ID] = $orderEntity->getId();
            $order[Order::INFO][Order::PRODUCTION_COST] = $orderEntity->getProductionCost();
            $order[Order::INFO][Order::SHIPPING_COST] = $orderEntity->getShippingCost();
            $order[Order::INFO][Order::TOTAL_COST] = $orderEntity->getTotalCost();
            return ($order);
        }
        return (null);
    }

    public function getAll(int $id_user)
    {
        if ($this->userIdValidator->validate($id_user))
        {
            $ordersEntities = [];
            foreach ($this->db as $order)
            {
                if ($order->getOwnerId() === $id_user)
                    $ordersEntities[] = $order;
            }
            $orders = [];
            foreach ($ordersEntities as $order)
            {
                $orders[] = $this->getById($id_user, $order->getId());
            }
            return $orders;
        }
        return (null);
    }
}