<?php

namespace App\Tests;

use App\Exception\FundManagerException;
use App\FundManager\FundManager;
use App\Repository\Test\UserTestRepo;
use PHPUnit\Framework\TestCase;

class FundManagerTest extends TestCase
{
    public function test_sufficient_funds()
    {
        $repository = new UserTestRepo();
        $fundManager = new FundManager($repository);
        try {
            $fundManager->userPay(1, 5000);
            $this->assertTrue(true);
        } catch (FundManagerException $e){}
    }

    public function test_expenses_equals_balance()
    {
        $repository = new UserTestRepo();
        $fundManager = new FundManager($repository);
        try {
            $fundManager->userPay(1, 10000);
            $this->assertTrue(true);
        } catch (FundManagerException $e){}
    }

    public function test_insufficient_funds()
    {
        $repository = new UserTestRepo();
        $fundManager = new FundManager($repository);
        $this->expectException(FundManagerException::class);
        $fundManager->userPay(1, 15000);
    }
}
