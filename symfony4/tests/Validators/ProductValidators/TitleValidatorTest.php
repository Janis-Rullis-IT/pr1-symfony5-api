<?php

namespace App\Tests;

use App\Validators\ProductValidators\TitleValidator;
use PHPUnit\Framework\TestCase;

class TitleValidatorTest extends TestCase
{
    public function test_valid_title()
    {
        $titleValidator = new TitleValidator();
        $valid = $titleValidator->validate("happy-panda66");
        $this->assertEquals($valid, true);
    }

    public function test_valid_title_with_space()
    {
        $titleValidator = new TitleValidator();
        $valid = $titleValidator->validate("happy panda66");
        $this->assertEquals($valid, true);
    }

    public function test_invalid_title()
    {
        $titleValidator = new TitleValidator();
        $valid = $titleValidator->validate("happy-panda66_____");
        $this->assertEquals($valid, false);
    }

    public function test_empty_title()
    {
        $titleValidator = new TitleValidator();
        $valid = $titleValidator->validate("");
        $this->assertEquals($valid, false);
    }
}
