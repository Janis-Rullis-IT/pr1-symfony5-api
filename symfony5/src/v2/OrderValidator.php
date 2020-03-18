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

class OrderValidator
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
		// #40 Error, Exceptions and PHPUNit best practices
		// https://www.guru99.com/error-handling-and-exceptions.html
		// https://dev.to/anastasionico/good-practices-handling-error-and-exceptions-in-php-5d8c
		// https://guh.me/how-to-assert-that-an-exception-is-not-thrown-on-phpunit
		// https://riptutorial.com/phpunit/example/23271/assert-an-exception-is-thrown
		// https://www.greycampus.com/codelabs/php/exception-handling
		// https://blog.eleven-labs.com/en/fr/php7-throwable-error-exception/
		// https://code-boxx.com/php-error-handling-best-practices/
		// https://phpunit.readthedocs.io/en/9.0/writing-tests-for-phpunit.html#testing-exceptions
		// When to use Exception and when not to? https://www.startutorial.com/articles/view/modern-php-developer-exception
		// 
		// http://bestpractices.thecodingmachine.com/php/error_handling.html
		// - Return vs exception "Developers tend to forget to add required checks. 
		// So throw an exception that the error will be noticed even wo the check."
		// - Always extend Exceptions so they could be differenetiated one from another.
		// 
		// https://www.nikolaposa.in.rs/blog/2016/08/17/exceptional-behavior-best-practices/ 
		// try/catch block which is a more clearer and meaningful way of expressing our intents in a cod
		//
		// void vs bool return
		// https://symfony.com/doc/master/contributing/code/standards.html
		// https://wiki.php.net/rfc/void_return_type 
		// "In C, a void function can't be used in an expression, only as a statement.
		// void signifies an unimportant return value that won't be used.".
		// #40 Personal note: Couldn't find a case where a return value could do a harm.		
		// My current thought is - return always, use if want.
		// 
		// Update about void: void is a good note for action methods that doesn't returns values
		// but stores in the class.
		//
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
