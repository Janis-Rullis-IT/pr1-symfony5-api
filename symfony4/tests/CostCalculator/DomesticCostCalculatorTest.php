<?php

namespace App\Tests;

use App\CostCalculator\DomesticCostCalculator;
use App\Entity\Order;
use App\Entity\Product;
use App\Repository\Test\ProductTestRepo;
use App\Repository\Test\UserTestRepo;
use App\Validators\UserValidators\UidValidator;
use PHPUnit\Framework\TestCase;

class DomesticCostCalculatorTest extends TestCase
{
    public function test_calculate_domestic_cost()
    {
        $id_user = 1;

        $line_items = [
            [Product::ID => 1, Product::QUANTITY => 10], // id 1 is a dummy product created when creating ProductsTestRepository
            [Product::ID => 1, Product::QUANTITY => 1]
        ];

        $repository = new ProductTestRepo(new UidValidator(new UserTestRepo()));
        $costCalculator = new DomesticCostCalculator($repository);
        $cost = $costCalculator->calculate($id_user, $line_items);
        $this->assertIsArray($cost);
        $this->assertArrayHasKey(Order::PRODUCTION_COST, $cost);
        $this->assertArrayHasKey(Order::SHIPPING_COST, $cost);
        $this->assertArrayHasKey(Order::TOTAL_COST, $cost);
        $this->assertEquals($cost[Order::PRODUCTION_COST], 11000);
        $this->assertEquals($cost[Order::SHIPPING_COST], 650);
        $this->assertEquals($cost[Order::TOTAL_COST], 11650);
    }
}
