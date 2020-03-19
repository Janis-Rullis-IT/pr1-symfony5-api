<?php
namespace App\v2;

use \App\Entity\v2\Order;
use App\ErrorsLoader;
use \App\Exception\OrderValidatorException;

class OrderValidator
{

	private $errors;
	private $errorsLoader;

	public function __construct(ErrorsLoader $errors)
	{
		$this->errors = [];
	}

	/**
	 * #40 Validate the order and store errors.
	 * 
	 * @param array $data
	 */
	public function validate(array $data): void
	{
		$this->validateRequiredKeys($data);
		$this->validateAddress($data);
		$this->validateExpressShipping($data);
	}

	/**
	 * #40 Is order valid?
	 * 
	 * @param array $data
	 * @return bool
	 */
	public function isValid(array $data): bool
	{
		return $this->hasRequiredKeys($data) && $this->isAddressValid($data) && $this->isExpressShippingAllowed($data);
	}

	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * #40 Validate the address and store errors.
	 * 
	 * @param array $data
	 */
	public function validateAddress(array $data): void
	{
		if (!$this->isAddressValid($data)) {
			$errors = $this->addressValidator->getErrors();
			foreach ($errors as $key => $val) {
				$this->errorsLoader->load($key, $val, $this->errors);
			}
			throw new OrderShippingValidatorException($this->errors, 2);
		}
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
			throw new OrderValidatorException($this->errors, 5);
		}
	}
}
