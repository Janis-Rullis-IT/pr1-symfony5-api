<?php
namespace App\Order;
/**
 * #40 Validate the order and store errors.
 */
use \App\Entity\Order;
use App\ErrorsLoader;
use \App\Exception\OrderValidatorException;
use \App\Entity\User;
use \App\User\UserValidator;
use \App\Order\OrderShippingValidator;

class OrderValidator
{

	private $errors;
	private $errorsLoader;
	private $userValidator;
	private $shippingValidator;

	public function __construct(ErrorsLoader $errorsLoader, UserValidator $userValidator, OrderShippingValidator $shippingValidator)
	{
		$this->errorsLoader = $errorsLoader;
		$this->errors = [];
		$this->userValidator = $userValidator;
		$this->shippingValidator = $shippingValidator;
	}

	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * #40 Make sure that the shipping is set before completing the order.
	 * 
	 * @param Order $order
	 * @return void
	 */
	public function mustHaveShippingSet(Order $order): void
	{
		$this->shippingValidator->mustHaveShippingSet($order);
	}

	/**
	 * #40 Does the order has at least 1 product?
	 * `product_cost` must greater than 0 because product's minimum price is 1.
	 * Could collect order's products, but this performs better.
	 * 
	 * @param Order $order
	 * @return bool
	 */
	public function hasProducts(Order $order): bool
	{
		return $order->getProductCost() > 0;
	}

	/**
	 * #40 Make sure the order has at least 1 product.
	 * 
	 * @param Order $order
	 * @return void
	 * @throws OrderValidatorException
	 */
	public function mustHaveProducts(Order $order): void
	{
		if (!$this->hasProducts($order)) {
			$this->errorsLoader->load(Order::PRODUCTS, Order::MUST_HAVE_PRODUCTS, $this->errors);
			throw new OrderValidatorException($this->errors, 5);
		}
	}

	/**
	 * #40 Make sure the user has enough money.
	 * 
	 * @param Order $order
	 * @param User $customer
	 * @return void
	 */
	public function mustHaveMoney(Order $order, User $customer): void
	{
		$this->userValidator->mustHaveMoney($customer, $order->getTotalCost());
	}
}
