<?php

namespace App\Service\Order;

/*
 * #38 Validate and set order's shipping.
 */
use App\Entity\Order;
use App\Interfaces\IOrderProductRepo;
use App\Interfaces\IOrderRepo;
use App\Interfaces\IProductRepo;
use App\Interfaces\IUserRepo;

class OrderShippingService
{
    private $userRepo;
    private $productRepo;
    private $orderRepo;
    private $orderProductRepo;
    private $orderShippingValidator;
    private $orderService;

    public function __construct(IProductRepo $productRepo, IUserRepo $userRepo, IOrderRepo $orderRepo, IOrderProductRepo $orderProductRepo, OrderShippingValidator $orderShippingValidator, OrderService $orderService)
    {
        $this->userRepo = $userRepo;
        $this->productRepo = $productRepo;
        $this->orderRepo = $orderRepo;
        $this->orderProductRepo = $orderProductRepo;
        $this->orderShippingValidator = $orderShippingValidator;
        $this->orderService = $orderService;
    }

    /**
     * #38 Validate and set order's shipping.
     */
    public function set(int $customerId, array $shippingData): Order
    {
        $order = $this->prepare($customerId, $shippingData);
        $this->orderRepo->save();
        $this->orderService->recalculateOrder($order);

        return $this->orderRepo->findOneBy(['id' => $order->getId()]);
    }

    /**
     * #40 Validate and prepare the item.
     */
    public function prepare(int $customerId, array $shippingData): Order
    {
        $this->orderShippingValidator->validate($shippingData);
        $shippingData['is_domestic'] = $this->orderShippingValidator->isDomestic($shippingData);
        $customer = $this->userRepo->mustFind($customerId);
        // #38 Collect customer's current 'draft' or create a new one.
        $draftOrder = $this->orderRepo->insertDraftIfNotExist($customer->getId());

        return $this->orderRepo->fillShipping($draftOrder, $shippingData);
    }
}
