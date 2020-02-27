<?php

namespace App\Tests;

use App\Entity\Product;
use App\ErrorsLoader;
use App\Exception\DuplicateException;
use App\Exception\ProductValidatorException;
use App\Product\ProductValidator;
use App\Repository\Test\ProductTestRepo;
use App\Repository\Test\UserTestRepo;
use App\Validators\ProductValidators\ProductTypeValidator;
use App\Validators\ProductValidators\SkuValidator;
use App\Validators\ProductValidators\TitleValidator;
use App\Validators\UserValidators\UidValidator;
use PHPUnit\Framework\TestCase;

class ValidateProductTest extends TestCase
{
    public function test_valid_keys_and_values()
    {
        $json_body = [
            Product::TYPE => "t-shirt",
            Product::TITLE => "wow",
            Product::SKU => "100-abc-1000",
            Product::COST => 1000,
        ];

        $errors = [];

        $userIdValidator = new UidValidator(new UserTestRepo());
        $repository = new ProductTestRepo($userIdValidator);

        $validator = new ProductValidator(new ProductTypeValidator(), new TitleValidator(), new SkuValidator($repository), new ErrorsLoader());

        $validator->validate($json_body, 1);

        $errors = $validator->getErrors();

        $this->assertEmpty($errors);
    }

    public function test_type_key_not_set()
    {
        $json_body = [
            "placeholder" => "t-shirt",
            Product::TITLE => "wow",
            Product::SKU => "100-abc-1000",
            Product::COST => 1000,
        ];

        $errors = [];

        $userIdValidator = new UidValidator(new UserTestRepo());
        $repository = new ProductTestRepo($userIdValidator);

        $validator = new ProductValidator(new ProductTypeValidator(), new TitleValidator(), new SkuValidator($repository), new ErrorsLoader());

        try {
            $validator->validate($json_body, 1);
        } catch (ProductValidatorException $e){
            $errors = $validator->getErrors();
            $this->assertArrayHasKey(Product::TYPE, $errors);
            $this->assertEquals($errors[Product::TYPE][0], 'type key not set');
        }
    }

    public function test_title_key_not_set()
    {
        $json_body = [
            Product::TYPE => "t-shirt",
            "placeholder" => "wow",
            Product::SKU => "100-abc-1000",
            Product::COST => 1000,
        ];

        $errors = [];

        $userIdValidator = new UidValidator(new UserTestRepo());
        $repository = new ProductTestRepo($userIdValidator);

        $validator = new ProductValidator(new ProductTypeValidator(), new TitleValidator(), new SkuValidator($repository), new ErrorsLoader());

        try {
            $validator->validate($json_body, 1);
        } catch (ProductValidatorException $e){
            $errors = $validator->getErrors();
            $this->assertArrayHasKey(Product::TITLE, $errors);
            $this->assertEquals($errors[Product::TITLE][0], 'title key not set');
        }
    }

    public function test_sku_key_not_set()
    {
        $json_body = [
            Product::TYPE => "t-shirt",
            Product::TITLE => "wow",
            "placeholder" => "100-abc-1000",
            Product::COST => 1000,
        ];

        $errors = [];

        $userIdValidator = new UidValidator(new UserTestRepo());
        $repository = new ProductTestRepo($userIdValidator);

        $validator = new ProductValidator(new ProductTypeValidator(), new TitleValidator(), new SkuValidator($repository), new ErrorsLoader());

        try {
            $validator->validate($json_body, 1);
        } catch (ProductValidatorException $e){
            $errors = $validator->getErrors();
            $this->assertArrayHasKey(Product::SKU, $errors);
            $this->assertEquals($errors[Product::SKU][0], 'sku key not set');
        }
    }

    public function test_cost_key_not_set()
    {
        $json_body = [
            Product::TYPE => "t-shirt",
            Product::TITLE => "wow",
            Product::SKU => "100-abc-1000",
            "placeholder" => 1000,
        ];

        $errors = [];

        $userIdValidator = new UidValidator(new UserTestRepo());
        $repository = new ProductTestRepo($userIdValidator);

        $validator = new ProductValidator(new ProductTypeValidator(), new TitleValidator(), new SkuValidator($repository), new ErrorsLoader());

        try {
            $validator->validate($json_body, 1);
        } catch (ProductValidatorException $e){
            $errors = $validator->getErrors();
            $this->assertArrayHasKey(Product::COST, $errors);
            $this->assertEquals($errors[Product::COST][0], 'cost key not set');
        }
    }

    public function test_invalid_type()
    {
        $json_body = [
            Product::TYPE => "whatever",
            Product::TITLE => "%%%%%wow%%%%%",
            Product::SKU => "100-abc-100",
            Product::COST => 1000,
        ];

        $errors = [];

        $userIdValidator = new UidValidator(new UserTestRepo());
        $repository = new ProductTestRepo($userIdValidator);

        $validator = new ProductValidator(new ProductTypeValidator(), new TitleValidator(), new SkuValidator($repository), new ErrorsLoader());

        try {
            $validator->validate($json_body, 1);
        } catch (ProductValidatorException $e){
            $productTypeValidator = new ProductTypeValidator();
            $errors = $validator->getErrors();
            $this->assertArrayHasKey(Product::TYPE, $errors);
            $this->assertEquals($errors[Product::TYPE][0], 'Invalid type');
        }
    }

    public function test_invalid_title()
    {
        $json_body = [
            Product::TYPE => "t-shirt",
            Product::TITLE => "%%%%%wow%%%%%",
            Product::SKU => "100-abc-100",
            Product::COST => 1000,
        ];

        $errors = [];

        $userIdValidator = new UidValidator(new UserTestRepo());
        $repository = new ProductTestRepo($userIdValidator);

        $validator = new ProductValidator(new ProductTypeValidator(), new TitleValidator(), new SkuValidator($repository), new ErrorsLoader());

        try {
            $validator->validate($json_body, 1);
        } catch (ProductValidatorException $e){
            $errors = $validator->getErrors();
            $this->assertArrayHasKey(Product::TITLE, $errors);
            $this->assertEquals($errors[Product::TITLE][0], Product::INVALID_TITLE);
        }
    }

    public function test_taken_sku()
    {
        $json_body = [
            Product::TYPE => "t-shirt",
            Product::TITLE => "wow",
            Product::SKU => "100-abc-999",
            Product::COST => 1000,
        ];

        $errors = [];

        $userIdValidator = new UidValidator(new UserTestRepo());
        $repository = new ProductTestRepo($userIdValidator);

        $validator = new ProductValidator(new ProductTypeValidator(), new TitleValidator(), new SkuValidator($repository), new ErrorsLoader());

        try {
            $validator->validate($json_body, 1);
        } catch (DuplicateException $e){
            $errors = $validator->getErrors();
            $this->assertArrayHasKey(Product::SKU, $errors);
            $this->assertEquals($errors[Product::SKU][0], Product::INVALID_SKU);
        }
    }

    public function test_cost_is_float()
    {
        $json_body = [
            Product::TYPE => "t-shirt",
            Product::TITLE => "wow",
            Product::SKU => "100-abc-1000",
            Product::COST => 100.00,
        ];

        $errors = [];

        $userIdValidator = new UidValidator(new UserTestRepo());
        $repository = new ProductTestRepo($userIdValidator);

        $validator = new ProductValidator(new ProductTypeValidator(), new TitleValidator(), new SkuValidator($repository), new ErrorsLoader());

        try {
            $validator->validate($json_body, 1);
        } catch (ProductValidatorException $e){
            $errors = $validator->getErrors();
            $this->assertArrayHasKey(Product::COST, $errors);
            $this->assertEquals($errors[Product::COST][0], 'Invalid cost. It must be an integer describing price with smallest money unit');
        }
    }

    public function test_no_keys()
    {
        $json_body = [

        ];

        $errors = [];

        $userIdValidator = new UidValidator(new UserTestRepo());
        $repository = new ProductTestRepo($userIdValidator);

        $validator = new ProductValidator(new ProductTypeValidator(), new TitleValidator(), new SkuValidator($repository), new ErrorsLoader());

        try {
            $validator->validate($json_body, 1);
        } catch (ProductValidatorException $e){
            $errors = $validator->getErrors();
            $this->assertArrayHasKey(Product::TYPE, $errors);
            $this->assertEquals($errors[Product::TYPE][0], 'type key not set');
            $this->assertArrayHasKey(Product::TITLE, $errors);
            $this->assertEquals($errors[Product::TITLE][0], 'title key not set');
            $this->assertArrayHasKey(Product::SKU, $errors);
            $this->assertEquals($errors[Product::SKU][0], 'sku key not set');
            $this->assertArrayHasKey(Product::COST, $errors);
            $this->assertEquals($errors[Product::COST][0], 'cost key not set');
        }
    }

    public function test_all_keys_have_error()
    {
        $json_body = [
            Product::TYPE => "whatever",
            Product::TITLE => "wow******",
            Product::SKU => "100-abc-999",
            Product::COST => 100.00,
        ];

        $errors = [];

        $userIdValidator = new UidValidator(new UserTestRepo());
        $repository = new ProductTestRepo($userIdValidator);

        $validator = new ProductValidator(new ProductTypeValidator(), new TitleValidator(), new SkuValidator($repository), new ErrorsLoader());

        try {
            $validator->validate($json_body, 1);
        } catch (ProductValidatorException $e){
            $errors = $validator->getErrors();
            $productTypeValidator = new ProductTypeValidator();
            $this->assertArrayHasKey(Product::TYPE, $errors);
            $this->assertEquals($errors[Product::TYPE][0], 'Invalid type');
            $this->assertArrayHasKey(Product::TITLE, $errors);
            $this->assertEquals($errors[Product::TITLE][0], Product::INVALID_TITLE);
            $this->assertArrayHasKey(Product::SKU, $errors);
            $this->assertEquals($errors[Product::SKU][0], Product::INVALID_SKU);
            $this->assertArrayHasKey(Product::COST, $errors);
            $this->assertEquals($errors[Product::COST][0], 'Invalid cost. It must be an integer describing price with smallest money unit');
        }
    }
}
