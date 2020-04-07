<?php


namespace App\Validators\AddressValidators;

use App\Entity\Order;
use App\ErrorsLoader;
use App\Validators\UserValidators\NameSurnameValidator;
use App\Validators\AddressValidators\Modules\CountryValidator;
use App\Validators\AddressValidators\Modules\PhoneValidator;
use App\Validators\AddressValidators\Modules\StateValidator;
use App\Validators\AddressValidators\Modules\StreetValidator;
use App\Validators\AddressValidators\Modules\ZipCodeValidator;

class AddressValidatorDomestic
{
    private $nameSurnameValidator;
    private $streetValidator;
    private $stateValidator;
    private $zipCodeValidator;
    private $countryValidator;
    private $phoneValidator;
    private $errors;
    private $errorsLoader;

    public function __construct(NameSurnameValidator $validator, StreetValidator $street, StateValidator $state, ZipCodeValidator $zip, CountryValidator $country, PhoneValidator $phone, ErrorsLoader $errors)
    {
        $this->nameSurnameValidator = $validator;
        $this->streetValidator = $street;
        $this->stateValidator = $state;
        $this->zipCodeValidator = $zip;
        $this->countryValidator = $country;
        $this->phoneValidator = $phone;
        $this->errors = [];
        $this->errorsLoader = $errors;
    }

    public function validate(array $address): bool
    {
        if (!isset($address[Order::OWNER_NAME]))
            $this->errorsLoader->load(Order::OWNER_NAME, Order::NO_NAME, $this->errors);
        if (!isset($address[Order::OWNER_SURNAME]))
            $this->errorsLoader->load(Order::OWNER_SURNAME, Order::NO_SURNAME, $this->errors);
        if (!isset($address[Order::STREET]))
            $this->errorsLoader->load(Order::STREET, Order::NO_STREET, $this->errors);
        if (!isset($address[Order::STATE]))
            $this->errorsLoader->load(Order::STATE, Order::NO_STATE, $this->errors);
        if (!isset($address[Order::ZIP]))
            $this->errorsLoader->load(Order::ZIP, Order::NO_ZIP, $this->errors);
        if (!isset($address[Order::COUNTRY]))
            $this->errorsLoader->load(Order::COUNTRY, Order::NO_COUNTRY, $this->errors);
        if (!isset($address[Order::PHONE]))
            $this->errorsLoader->load(Order::PHONE, Order::NO_PHONE, $this->errors);

        foreach ($address as $key => $value)
        {
            if ($key === Order::OWNER_NAME && !$this->nameSurnameValidator->validate($value))
                $this->errorsLoader->load(Order::OWNER_NAME, Order::INVALID_NAME, $this->errors);
            elseif ($key === Order::OWNER_SURNAME && !$this->nameSurnameValidator->validate($value))
                $this->errorsLoader->load(Order::OWNER_SURNAME, Order::INVALID_SURNAME, $this->errors);
            elseif ($key === Order::STREET && !$this->streetValidator->validate($value))
                $this->errorsLoader->load(Order::STREET, Order::INVALID_STREET, $this->errors);
            elseif ($key === Order::STATE && !$this->stateValidator->validate($value))
                $this->errorsLoader->load(Order::STATE, Order::INVALID_STATE, $this->errors);
            elseif ($key === Order::ZIP && !$this->zipCodeValidator->validate($value))
                $this->errorsLoader->load(Order::ZIP, Order::INVALID_ZIP, $this->errors);
            elseif ($key === Order::COUNTRY && !$this->countryValidator->validateAlphabetic($value))
                $this->errorsLoader->load(Order::COUNTRY, Order::INVALID_COUNTRY, $this->errors);
            elseif ($key === Order::PHONE && !$this->phoneValidator->validate($value))
                $this->errorsLoader->load(Order::PHONE, Order::INVALID_PHONE, $this->errors);
        }

        if (!empty($this->errors))
            return (false);

        return (true);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}