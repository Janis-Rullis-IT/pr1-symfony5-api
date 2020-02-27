<?php

namespace App\Tests;

use App\ErrorsLoader;
use PHPUnit\Framework\TestCase;

class ErrorsLoaderTest extends TestCase
{
    public function test_load_in_empty()
    {
        $errors = [];
        $key = 'name';
        $error = 'name key not set';

        $errorsLoader = new ErrorsLoader();

        $errorsLoader->load($key, $error, $errors);

        $this->assertArrayHasKey($key, $errors);
        $this->assertEquals($errors[$key][0], 'name key not set');
    }

    public function test_load_in_existing_key()
    {
        $errors = [];
        $key = 'name';
        $error = 'name key not set';
        $errors[$key] = array();
        array_push($errors[$key], $error);

        $error2 = 'name error number 2';

        $errorsLoader = new ErrorsLoader();

        $errorsLoader->load($key, $error2, $errors);

        $this->assertArrayHasKey($key, $errors);
        $this->assertEquals($errors[$key][0], 'name key not set');
        $this->assertEquals($errors[$key][1], 'name error number 2');
    }
}
