<?php

namespace App\Service\Order;

/*
 * #40 Validate order's shipping and store errors.
 */
use App\Entity\Order;
use App\ErrorsLoader;
use App\Exception\OrderShippingValidatorException;
use App\Helper\EnumType;
use App\Validators\AddressValidators\AddressValidator;
use App\Validators\AddressValidators\ShipmentType;

class OrderShippingValidator
{
    /**
     * #40 Validation method conventions:
     * - Collection methods. Doesn't store anything in class variables. `getMissingKeys()`
     * - Boolean methods. May rely on calc. methods.  Doesn't store anything in class  variables.
     * Named in a manner that responds with a bool - `hasRequiredKeys()`, `isValidExpressShipping()`.
     * - Action methods. Returns void. Relies on the class' method and variables. Throws exceptions.
     * Named in a manner that implies action - `validate()`, `handle()`.
     */
    private $errors;
    private $addressValidator;
    private $shipmentType;
    private $errorsLoader;
    private $enumType;

    public function __construct(AddressValidator $addressValidator, ShipmentType $shipmentType, ErrorsLoader $errors, EnumType $enumType)
    {
        $this->addressValidator = $addressValidator;
        $this->shipmentType = $shipmentType;
        $this->errors = [];
        $this->errorsLoader = $errors;
        $this->enumType = $enumType;
    }

    /**
     * #40 Validate the order and store errors.
     */
    public function validate(array $data): void
    {
        $this->validateRequiredKeys($data);
        $this->validateAddress($data);
        $this->validateExpressShipping($data);
    }

    /**
     * #40 Is order valid?
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
     */
    public function validateRequiredKeys(array $data): void
    {
        $errors = [];
        $missingKeys = $this->getMissingKeys($data);

        foreach ($missingKeys as $requiredKey) {
            if (!isset($data[$requiredKey])) {
                $errors[$requiredKey] = "'$requiredKey'".Order::FIELD_IS_MISSING;
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $key => $val) {
                $this->errorsLoader->load($key, $val, $this->errors);
            }
            throw new OrderShippingValidatorException($this->errors, 1);
        }
    }

    /**
     * #40 Which keys are missing?
     */
    public function getMissingKeys(array $data): array
    {
        $return = [];
        foreach (Order::REQUIRED_FIELDS as $requiredKey) {
            if (!isset($data[$requiredKey])) {
                $return[$requiredKey] = $requiredKey;
            }
        }

        return $return;
    }

    /**
     * #40 Has all required keys?
     */
    public function hasRequiredKeys(array $data): bool
    {
        return empty($this->getMissingKeys($data));
    }

    /**
     * #40 Is shipping address in the domestic region?
     */
    public function isDomestic(array $data): bool
    {
        return 'domestic' === $this->shipmentType->getType($data);
    }

    /**
     * #40 Is express shipping allowed for this order?
     */
    public function isExpressShippingAllowed(array $data): bool
    {
        return $this->isDomestic($data);
    }

    /**
     * #40 Validate the express shipping and store errors.
     */
    public function validateExpressShipping(array $data): void
    {
        if (!$this->isExpressShippingAllowed($data)) {
            $this->errorsLoader->load(Order::IS_EXPRESS, Order::EXPRESS_ONLY_IN_DOMESTIC_REGION, $this->errors);
            throw new OrderShippingValidatorException($this->errors, 3);
        }
    }

    /**
     * #40 Is address valid?
     */
    public function isAddressValid(array $data): bool
    {
        return $this->addressValidator->validate($data);
    }

    /**
     * #40 Validate the address and store errors.
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
     * #40 Is the shipping set?
     * `is_domestic` field should be set to 'y' or 'n' when calling the `OrderShippingService::set()`
     * based on the address.
     */
    public function IsShippingSet(Order $order): bool
    {
        return !empty($order->getIsDomestic()) && $this->enumType->isValid($order->getIsDomestic());
    }

    /**
     * #40 Make sure that the shipping is set before completing the order.
     *
     * @throws OrderShippingValidatorException
     */
    public function mustHaveShippingSet(Order $order): void
    {
        if (!$this->IsShippingSet($order)) {
            $this->errorsLoader->load(Order::SHIPPING, Order::MUST_HAVE_SHIPPING_SET, $this->errors);
            throw new OrderShippingValidatorException($this->errors, 4);
        }
    }
}
