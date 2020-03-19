<?php
namespace App\v2;

/**
 * #40 Validation method conventions:
 * - Collection methods. Doesn't store anything in class variables. `getMissingKeys()`
 * - Boolean methods. May rely on calc. methods.  Doesn't store anything in class  variables. 
 * Named in a manner that responds with a bool - `hasRequiredKeys()`, `isValidExpressShipping()`.
 * - Action methods. Returns void. Relies on the class' method and variables. Throws exceptions.
 * Named in a manner that implies action - `validate()`, `handle()`.
 */
use \App\Entity\v2\Order;
use App\Validators\AddressValidators\AddressValidator;
use App\Validators\AddressValidators\ShipmentType;
use App\ErrorsLoader;
use \App\Exception\OrderValidatorException;

class OrderShippingValidator
{

	private $errors;
	private $addressValidator;
	private $shipmentType;
	private $errorsLoader;

	public function __construct(AddressValidator $addressValidator, ShipmentType $shipmentType, ErrorsLoader $errors)
	{
		$this->addressValidator = $addressValidator;
		$this->shipmentType = $shipmentType;
		$this->errors = [];
		$this->errorsLoader = $errors;
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
	 * #40 Validate array keys and store errors.
	 * 
	 * @param array $data
	 */
	public function validateRequiredKeys(array $data): void
	{
		$errors = [];
		$missingKeys = $this->getMissingKeys($data);

		foreach ($missingKeys as $requiredKey) {
			if (!isset($data[$requiredKey])) {
				$errors[$requiredKey] = "'$requiredKey'" . Order::FIELD_IS_MISSING;
			}
		}

		if (!empty($errors)) {
			foreach ($errors as $key => $val) {
				$this->errorsLoader->load($key, $val, $this->errors);
			}
			throw new OrderValidatorException($this->errors, 1);
		}
	}

	/**
	 * #40 Which keys are missing?
	 * 
	 * @param array $data
	 * @return array
	 */
	public function getMissingKeys(array $data): array
	{
		$return = [];
		foreach (Order::$requireds as $requiredKey) {
			if (!isset($data[$requiredKey])) {
				$return[$requiredKey] = $requiredKey;
			}
		}
		return $return;
	}

	/**
	 * #40 Has all required keys?

	 * @param array $data
	 * @return bool
	 */
	public function hasRequiredKeys(array $data): bool
	{
		return empty($this->getMissingKeys($data));
	}

	/**
	 * #40 Is shipping address in the domestic region?
	 * 
	 * @param array $data
	 * @return bool
	 */
	public function isDomestic(array $data): bool
	{
		return $this->shipmentType->getType($data) === 'domestic';
	}

	/**
	 * #40 Is express shipping allowed for this order?
	 * 
	 * @param array $data
	 * @return bool
	 */
	public function isExpressShippingAllowed(array $data): bool
	{
		return $this->isDomestic($data);
	}

	/**
	 * #40 Validate the express shipping and store errors.
	 * 
	 * @param array $data
	 */
	public function validateExpressShipping(array $data): void
	{
		if (!$this->isExpressShippingAllowed($data)) {
			$this->errorsLoader->load(Order::IS_EXPRESS, Order::EXPRESS_ONLY_IN_DOMESTIC_REGION, $this->errors);
			throw new OrderValidatorException($this->errors, 3);
		}
	}

	/**
	 * #40 Is address valid?
	 * 
	 * @param array $data
	 * @return bool
	 */
	public function isAddressValid(array $data): bool
	{
		return $this->addressValidator->validate($data);
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
			throw new OrderValidatorException($this->errors, 2);
		}
	}
}
