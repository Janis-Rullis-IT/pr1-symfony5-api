<?php

namespace App\Service\Order;

/*
 * Complete or re-calculate the order.
 */
use App\Entity\Order;
use App\Interfaces\IOrderProductRepo;
use App\Interfaces\IOrderRepo;
use App\Interfaces\IProductRepo;
use App\Interfaces\IUserRepo;

class OrderService
{
    private $userRepo;
    private $productRepo;
    private $orderRepo;
    private $orderProductRepo;
    private $orderValidator;

    public function __construct(
        IProductRepo $productRepo, IUserRepo $userRepo, IOrderRepo $orderRepo,
        IOrderProductRepo $orderProductRepo, OrderValidator $orderValidator)
    {
        $this->userRepo = $userRepo;
        $this->productRepo = $productRepo;
        $this->orderRepo = $orderRepo;
        $this->orderProductRepo = $orderProductRepo;
        $this->orderValidator = $orderValidator;
    }

    /**
     * #40 Complete the order.
     */
    public function complete(int $customerId): Order
    {
        $customer = $this->userRepo->mustFind($customerId);
        $order = $this->orderRepo->insertIfNotExist($customer->getId());
        $this->recalculateOrder($order);
        $this->orderValidator->mustHaveProducts($order);
        $this->orderValidator->mustHaveMoney($order, $customer);
        $this->orderRepo->markAsCompleted($order);
        $this->userRepo->reduceBalance($customer, $order->getTotalCost());

        return $this->orderRepo->findOneBy(['id' => $order->getId()]);
    }

    /**
     * Recalculate the shipping and product costs.
     */
    public function recalculateOrder(Order $order): void
    {
        $this->orderValidator->mustHaveShippingSet($order);
        $this->orderProductRepo->setShippingValues($order);
        $this->orderRepo->setOrderCostsFromCartItems($order);
    }
}
