<?php

namespace App\Tests;

use App\Validators\ProductValidators\ProductTypeValidator;
use PHPUnit\Framework\TestCase;

class ProductTypeValidatorTest extends TestCase
{
    public function test_mug()
    {
        $productTypeValidator = new ProductTypeValidator();
        $valid = $productTypeValidator->validate("mug");
        $this->assertEquals($valid, true);
    }

    public function test_t_shirt()
    {
        $productTypeValidator = new ProductTypeValidator();
        $valid = $productTypeValidator->validate("t-shirt");
        $this->assertEquals($valid, true);
    }

    public function test_invalid_product_armour()
    {
        $productTypeValidator = new ProductTypeValidator();
        $valid = $productTypeValidator->validate("armour");
        $this->assertEquals($valid, false);
    }
}
