<?php

namespace App\Repository\Prod;

use App\CostCalculator\CostCalculator;
use App\Entity\Order;
use App\Interfaces\IRelationRepo;
use App\Validators\AddressValidators\ShipmentType;
use App\Validators\UserValidators\UidValidator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use App\Interfaces\IOrderRepo;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepo extends ServiceEntityRepository implements IOrderRepo
{
    private $em;
    private $userIdValidator;
    private $relationRepository;
    private $shipmentType;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em, UidValidator $userIdValidator, IRelationRepo $relationRepository, ShipmentType $shipmentType)
    {
        parent::__construct($registry, Order::class);
        $this->em = $em;
        $this->userIdValidator = $userIdValidator;
        $this->relationRepository = $relationRepository;
        $this->shipmentType = $shipmentType;
    }

    public function create(array $requestBody, array $costs, int $id_user)
    {
        $order = new Order();
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


        if ($this->shipmentType->getType($requestBody[Order::SHIPPING_DATA]) === Order::DOMESTIC_ORDER)
        {
            if (CostCalculator::express_shipping($requestBody))
                $order->setExpressShipping(true);
            else
                $order->setExpressShipping(false);
        }

        $order->setTotalCost($costs[Order::TOTAL_COST]);
        $this->em->persist($order);
        $this->em->flush();
        return $order;
    }

    private function getEntityById(int $id)
    {
        return $this->findOneBy([
            Order::ID => $id,
        ]);
    }

    public function getById(int $id_user, int $id)
    {
        if ($this->userIdValidator->validate($id_user) && $this->relationRepository->getOrderProducts($id_user, $id))
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
            $order[Order::INFO][Order::OWNER_ID] = $orderEntity->getOwnerId();
            $order[Order::INFO][Order::PRODUCTION_COST] = $orderEntity->getProductionCost();
            $order[Order::INFO][Order::SHIPPING_COST] = $orderEntity->getShippingCost();

            $expressShipping = $orderEntity->getExpressShipping();
            if ($expressShipping != null && $expressShipping === 1)
                $order[Order::INFO][Order::EXPRESS_SHIPPING] = true;
            elseif ($expressShipping != null && $expressShipping === 0)
                $order[Order::INFO][Order::EXPRESS_SHIPPING] = false;

            $order[Order::INFO][Order::TOTAL_COST] = $orderEntity->getTotalCost();
            return ($order);
        }
        return (null);
    }

    public function getAll(int $id_user)
    {
        if ($this->userIdValidator->validate($id_user))
        {
            $ordersEntities = $this->findBy([
                Order::OWNER_ID => $id_user
            ]);
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
