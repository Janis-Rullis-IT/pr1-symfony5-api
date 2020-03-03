<?php

namespace App\Tests;

use App\Validators\AddressValidators\AddressValidator;
use App\Validators\UserValidators\NameSurnameValidator;
use App\Validators\AddressValidators\Modules\CountryValidator;
use App\Validators\AddressValidators\AddressValidatorDomestic;
use App\Entity\Order;
use App\Entity\Product;
use App\ErrorsLoader;
use App\Exception\OrderValidatorException;
use App\Validators\AddressValidators\AddressValidatorInternational;
use App\Order\OrderValidator;
use App\Validators\AddressValidators\Modules\PhoneValidator;
use App\Repository\Test\ProductTestRepo;
use App\Repository\Test\UserTestRepo;
use App\Validators\AddressValidators\ShipmentType;
use App\Validators\AddressValidators\Modules\StateValidator;
use App\Validators\AddressValidators\Modules\StreetValidator;
use App\Validators\UserValidators\UidValidator;
use App\Validators\AddressValidators\Modules\ZipCodeValidator;
use PHPUnit\Framework\TestCase;

class OrderValidatorTest extends TestCase
{
    private $addressValidator;
    private $lineItemsValidator;

    public function __construct()
    {
        parent::__construct();
        /* creating necessary objects to create order validator*/
        $domesticAddressValidator = new AddressValidatorDomestic(new NameSurnameValidator(), new StreetValidator(), new StateValidator(), new ZipCodeValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());
        $internationalAddressValidator = new AddressValidatorInternational(new NameSurnameValidator(), new StreetValidator(), new CountryValidator(), new PhoneValidator(), new ErrorsLoader());
        $this->addressValidator = new AddressValidator(new ShipmentType(), $domesticAddressValidator, $internationalAddressValidator);

        $userValidator = new UidValidator(new UserTestRepo());
    }

    public function test_valid_order()
    {
        $id_user = 1;

        $order = [
            "shipToAddress" => [
                "name" => "John",
                "surname" => "Doe",
                "street" => "Palm street 25-7",
                "state" => "California",
                "zip" => "60744",
                "country" => "US",
                "phone" => "+1 123 123 123"
            ],
            "lineItems" => [
                ["id" => 1, "quantity" => 10],
                ["id" => 1, "quantity" => 1]
            ]
        ];

        $orderValidator = new OrderValidator($this->addressValidator, new ProductTestRepo(new UidValidator(new UserTestRepo())), new ErrorsLoader());
        $orderValidator->validate(1, $order);
        $errors = $orderValidator->getErrors();
        $this->assertEmpty($errors);
    }

    public function test_missing_ship_to_address_key()
    {
        $id_user = 1;

        $order = [
            "XXXX_XX_XXXXXXX" => [
                "name" => "John",
                "surname" => "Doe",
                "street" => "Palm street 25-7",
                "state" => "California",
                "zip" => "60744",
                "country" => "US",
                "phone" => "+1 123 123 123"
            ],
            "lineItems" => [
                ["id" => 1, "quantity" => 10],
                ["id" => 1, "quantity" => 1]
            ]
        ];

        $orderValidator = new OrderValidator($this->addressValidator, new ProductTestRepo(new UidValidator(new UserTestRepo())), new ErrorsLoader());
        try {
            $orderValidator->validate(1, $order);
        } catch (OrderValidatorException $e) {
            $errors = $e->getErrors();
            $this->assertIsArray($errors);
            $this->assertArrayHasKey(Order::SHIPPING_DATA, $errors);
            $this->assertEquals($errors[Order::SHIPPING_DATA], "shipping address not set");
        }
    }

    public function test_missing_line_items_key()
    {
        $id_user = 1;

        $order = [
            "shipToAddress" => [
                "name" => "John",
                "surname" => "Doe",
                "street" => "Palm street 25-7",
                "state" => "California",
                "zip" => "60744",
                "country" => "US",
                "phone" => "+1 123 123 123"
            ],
            "XXXX_XXXXX" => [
                ["id" => 1, "quantity" => 10],
                ["id" => 1, "quantity" => 1]
            ]
        ];

        $orderValidator = new OrderValidator($this->addressValidator, new ProductTestRepo(new UidValidator(new UserTestRepo())), new ErrorsLoader());
        try {
            $orderValidator->validate(1, $order);
        } catch (OrderValidatorException $e) {
            $errors = $e->getErrors();
            $this->assertIsArray($errors);
            $this->assertArrayHasKey(Order::LINE_ITEMS, $errors);
            $this->assertEquals($errors[Order::LINE_ITEMS], "order does not contain any products");
        }
    }

    public function test_invalid_country_within_ship_to_address()
    {
        $id_user = 1;

        $order = [
            "shipToAddress" => [
                "name" => "John",
                "surname" => "Doe",
                "street" => "Palm street 25-7",
                "state" => "California",
                "zip" => "60744",
                "country" => "XXXXXXXXXXXXX",
                "phone" => "+1 123 123 123"
            ],
            "lineItems" => [
                ["id" => 1, "quantity" => 10],
                ["id" => 1, "quantity" => 1]
            ]
        ];

        $orderValidator = new OrderValidator($this->addressValidator, new ProductTestRepo(new UidValidator(new UserTestRepo())), new ErrorsLoader());
        try {
            $orderValidator->validate(1, $order);
        } catch (OrderValidatorException $e) {
            $errors = $e->getErrors();
            $this->assertIsArray($errors);
            $this->assertArrayHasKey(Order::SHIPPING_DATA, $errors);
            $this->assertEquals($errors[Order::SHIPPING_DATA][Order::COUNTRY][0], "invalid country");
        }
    }

    public function test_invalid_line_item_id_within()
    {
        $id_user = 1;

        $order = [
            "shipToAddress" => [
                "name" => "John",
                "surname" => "Doe",
                "street" => "Palm street 25-7",
                "state" => "California",
                "zip" => "60744",
                "country" => "US",
                "phone" => "+1 123 123 123"
            ],
            "lineItems" => [
                ["id" => 100, "quantity" => 10],
                ["id" => 1, "quantity" => 1]
            ]
        ];

        $orderValidator = new OrderValidator($this->addressValidator, new ProductTestRepo(new UidValidator(new UserTestRepo())), new ErrorsLoader());
        try {
            $orderValidator->validate(1, $order);
        } catch (OrderValidatorException $e) {
            $errors = $e->getErrors();
            $this->assertIsArray($errors);
            $this->assertArrayHasKey(Order::LINE_ITEMS, $errors);
            $this->assertEquals($errors[Order::LINE_ITEMS][Product::ID][0], "invalid product id for line item number 1");
        }
    }
}
