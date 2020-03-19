<?php
namespace App\v2;

use \App\Interfaces\IProductRepo;
use \App\Interfaces\IUserRepo;
use \App\Interfaces\v2\IOrderRepo;
use \App\Interfaces\v2\IOrderProductRepo;
use App\Entity\v2\Order;

class OrderService
{

	private $userRepo;
	private $productRepo;
	private $orderRepo;
	private $orderProductRepo;
	private $orderShippingValidator;

	public function __construct(IProductRepo $productRepo, IUserRepo $userRepo, IOrderRepo $orderRepo, IOrderProductRepo $orderProductRepo, OrderShippingValidator $orderShippingValidator)
	{
		$this->userRepo = $userRepo;
		$this->productRepo = $productRepo;
		$this->orderRepo = $orderRepo;
		$this->orderProductRepo = $orderProductRepo;
		$this->orderShippingValidator = $orderShippingValidator;
	}

	/**
	 * #40 Complete the order.
	 * 
	 * @param int $customerId
	 * @param array $data
	 * @return array
	 */
	public function complete(int $customerId, array $data): Order
	{
		$customer = $this->userRepo->mustFind($customerId);
		$order = $this->orderRepo->insertIfNotExist($customer->getId());
		$this->orderShippingValidator->mustHaveShippingSet($order);
		$this->recalculateOrder($order);
//		$this->orderValidator->mustHaveProducts($order);

		// #40 TODO: has at least 1 product - product_cost > 0.
		// #40 TODO User has enough money to contine if not suggest to change shipping or remove items from the cart.
		// #40 TODO Change order's status.
		// #40 TODO Reduce customers balance.

		return $this->orderRepo->findOneBy(["id" => $order->getId()]);
	}

	public function recalculateOrder($order): void
	{
		$this->orderProductRepo->setShippingValues($order);
		$this->orderRepo->setOrderCostsFromCartItems($order);
	}
}
