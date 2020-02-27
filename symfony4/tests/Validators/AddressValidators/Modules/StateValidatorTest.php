<?php

namespace App\Tests;

use App\Validators\AddressValidators\Modules\StateValidator;
use PHPUnit\Framework\TestCase;

class StateValidatorTest extends TestCase
{
    public function test_valid_code()
    {
        $stateValidator = new StateValidator();
        $state = "NJ";
        $valid = $stateValidator->validate($state);
        $this->assertTrue($valid);
    }

    public function test_invalid_code()
    {
        $stateValidator = new StateValidator();
        $state = "XX";
        $valid = $stateValidator->validate($state);
        $this->assertFalse($valid);
    }

    public function test_valid_name()
    {
        $stateValidator = new StateValidator();
        $state = "New Jersey";
        $valid = $stateValidator->validate($state);
        $this->assertTrue($valid);
    }

    public function test_invalid_name()
    {
        $stateValidator = new StateValidator();
        $state = "New XXXXXX";
        $valid = $stateValidator->validate($state);
        $this->assertFalse($valid);
    }
}
