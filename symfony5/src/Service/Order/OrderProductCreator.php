<?php

namespace App\Service\Order;

/*
 * #40 #38 Add a product to customer's cart (draft order).
 */

use App\Entity\OrderProduct;
use App\Interfaces\IOrderProductRepo;
use App\Interfaces\IOrderRepo;
use App\Interfaces\IProductRepo;
use App\Interfaces\IUserRepo;

class OrderProductCreator
{
    private $userRepo;
    private $productRepo;
    private $orderRepo;
    private $orderProductRepo;

    public function __construct(IProductRepo $productRepo, IUserRepo $userRepo, IOrderRepo $orderRepo, IOrderProductRepo $orderProductRepo)
    {
        $this->userRepo = $userRepo;
        $this->productRepo = $productRepo;
        $this->orderRepo = $orderRepo;
        $this->orderProductRepo = $orderProductRepo;
    }

    /**
     * #40 #38 Add a product to customer's cart (draft order).
     */
    public function handle(int $customerId, int $productId): OrderProduct
    {
        $orderProduct = $this->prepare($customerId, $productId);

        return $this->orderProductRepo->create($orderProduct);
    }

    /**
     * #38 Validate and prepare the item.
     */
    public function prepare(int $customerId, int $productId): OrderProduct
    {
        $customer = $this->userRepo->mustFind($customerId);
        $product = $this->productRepo->mustFind($productId);
        $seller = $this->userRepo->mustFind($product->getOwnerId());
        // #38 #36 Collect customer's current 'draft' or create a new one.
        $draftOrder = $this->orderRepo->insertIfNotExist($customer->getId());

        return $this->orderProductRepo->prepare($customer, $product, $seller, $draftOrder);
    }
}
