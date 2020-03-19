<?php
namespace App\v2;

use \App\Interfaces\IProductRepo;
use \App\Interfaces\IUserRepo;
use \App\Interfaces\v2\IOrderRepo;
use \App\Interfaces\v2\IOrderProductRepo;
use App\Entity\v2\Order;

class OrderShipping
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
	 * #38 Set order's shipping.
	 * 
	 * @param int $customerId
	 * @param array $data
	 * @return array
	 */
	public function set(int $customerId, array $data): Order
	{
		// #38 Validate and prepare the item.
		$order = $this->prepare($customerId, $data);
		$order = $this->orderRepo->write($order);

		$this->orderProductRepo->makrCartsAdditionalProducts($order);
		$this->orderProductRepo->markDomesticShipping($order);
		$this->orderProductRepo->markExpressShipping($order);
		$this->orderProductRepo->setShippingRates($order);
		$this->orderRepo->setOrderCostsFromCartItems($order);

		return $this->orderRepo->findOneBy(["id" => $order->getId()]);
	}

	/**
	 * #40 Validate and prepare the item.
	 * 
	 * @param int $customerId
	 * @param array $data
	 * @return array
	 */
	public function prepare(int $customerId, array $data): Order
	{
		$this->orderShippingValidator->validate($data);
		$data['is_domestic'] = $this->orderShippingValidator->isDomestic($data);
		$customer = $this->userRepo->mustFind($customerId);
		// #38 #36 Collect customer's current 'draft' or create a new one.
		$draftOrder = $this->orderRepo->insertIfNotExist($customer->getId());
		return $this->orderRepo->prepare($draftOrder, $data);
	}
}
