<?php

namespace App\Tests;

use App\Repository\Test\UserTestRepo;
use App\Validators\UserValidators\UidValidator;
use PHPUnit\Framework\TestCase;

class UserIdValidatorTest extends TestCase
{
    public function test_valid_id()
    {
        $userIdValidator = new UidValidator(new UserTestRepo());
        $valid = $userIdValidator->validate(1);
        $this->assertEquals($valid, true);
    }

    public function test_invalid_id()
    {
        $userIdValidator = new UidValidator(new UserTestRepo());
        $valid = $userIdValidator->validate(99999);
        $this->assertEquals($valid, false);
    }
}
