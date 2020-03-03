<?php

namespace App\Tests;

use App\Repository\Test\ProductTestRepo;
use App\Repository\Test\UserTestRepo;
use App\Validators\ProductValidators\SkuValidator;
use App\Validators\UserValidators\UidValidator;
use PHPUnit\Framework\TestCase;

class SkuValidatorTest extends TestCase
{
    public function test_available_sku()
    {
        $userIdValidator = new UidValidator(new UserTestRepo());
        $skuValidator = new SkuValidator(new ProductTestRepo($userIdValidator));
        $valid = $skuValidator->validate("100-abc-1000", 1);
        $this->assertEquals($valid, true);
    }

    public function test_unavailable_sku()
    {
        $userIdValidator = new UidValidator(new UserTestRepo());
        $skuValidator = new SkuValidator(new ProductTestRepo($userIdValidator));
        $valid = $skuValidator->validate("100-abc-999", 1);
        $this->assertEquals($valid, false);
    }
}
