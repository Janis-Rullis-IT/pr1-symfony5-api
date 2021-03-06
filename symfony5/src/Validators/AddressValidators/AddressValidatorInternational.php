<?php

namespace App\Validators\AddressValidators;

use App\Entity\Order;
use App\ErrorsLoader;
use App\Validators\AddressValidators\Modules\CountryValidator;
use App\Validators\AddressValidators\Modules\PhoneValidator;
use App\Validators\AddressValidators\Modules\StreetValidator;
use App\Validators\UserValidators\NameSurnameValidator;

class AddressValidatorInternational
{
    private $nameSurnameValidator;
    private $streetValidator;
    private $countryValidator;
    private $phoneValidator;
    private $errors;
    private $errorsLoader;

    public function __construct(NameSurnameValidator $nameSurnameValidator, StreetValidator $street, CountryValidator $country, PhoneValidator $phone, ErrorsLoader $errors)
    {
        $this->nameSurnameValidator = $nameSurnameValidator;
        $this->streetValidator = $street;
        $this->countryValidator = $country;
        $this->phoneValidator = $phone;
        $this->errors = [];
        $this->errorsLoader = $errors;
    }

    public function validate(array $address): bool
    {
        if (!isset($address[Order::OWNER_NAME])) {
            $this->errorsLoader->load(Order::OWNER_NAME, Order::NO_NAME, $this->errors);
        }
        if (!isset($address[Order::OWNER_SURNAME])) {
            $this->errorsLoader->load(Order::OWNER_SURNAME, Order::NO_SURNAME, $this->errors);
        }
        if (!isset($address[Order::STREET])) {
            $this->errorsLoader->load(Order::STREET, Order::NO_STREET, $this->errors);
        }
        if (!isset($address[Order::COUNTRY])) {
            $this->errorsLoader->load(Order::COUNTRY, Order::NO_COUNTRY, $this->errors);
        }
        if (!isset($address[Order::PHONE])) {
            $this->errorsLoader->load(Order::PHONE, Order::NO_PHONE, $this->errors);
        }

        foreach ($address as $key => $value) {
            if (Order::OWNER_NAME === $key && !$this->nameSurnameValidator->validate($value)) {
                $this->errorsLoader->load(Order::OWNER_NAME, Order::INVALID_NAME, $this->errors);
            } elseif (Order::OWNER_SURNAME === $key && !$this->nameSurnameValidator->validate($value)) {
                $this->errorsLoader->load(Order::OWNER_SURNAME, Order::INVALID_SURNAME, $this->errors);
            } elseif (Order::STREET === $key && !$this->streetValidator->validate($value)) {
                $this->errorsLoader->load(Order::STREET, Order::INVALID_STREET, $this->errors);
            } elseif (Order::COUNTRY === $key && !$this->countryValidator->validateAlphabetic($value)) {
                $this->errorsLoader->load(Order::COUNTRY, Order::INVALID_COUNTRY, $this->errors);
            } elseif (Order::PHONE === $key && !$this->phoneValidator->validate($value)) {
                $this->errorsLoader->load(Order::PHONE, Order::INVALID_PHONE, $this->errors);
            }
        }

        if (!empty($this->errors)) {
            return false;
        }

        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
