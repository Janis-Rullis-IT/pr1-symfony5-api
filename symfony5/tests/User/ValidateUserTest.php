<?php

namespace App\Tests;

use App\Entity\User;
use App\ErrorsLoader;
use App\Exception\UserValidatorException;
use App\User\UserValidator;
use App\Validators\UserValidators\NameSurnameValidator;
use PHPUnit\Framework\TestCase;

class ValidateUserTest extends TestCase
{
    public function test_valid_keys_and_values()
    {
        $json_body = [
            User::NAME => "John",
            User::SURNAME => "Doe"
        ];

        $errors = [];

        $validator = new UserValidator(new NameSurnameValidator(), new ErrorsLoader());

        $validator->validate($json_body);

        $errors = $validator->getErrors();

        $this->assertEmpty($errors);
    }

    public function test_name_key_not_set()
    {
        $json_body = [
            "nameblablabla" => "John",
            User::SURNAME => "Doe"
        ];

        $errors = [];

        $validator = new UserValidator(new NameSurnameValidator(), new ErrorsLoader());

        try {
            $validator->validate($json_body);
        } catch (UserValidatorException $e){
            $errors = $validator->getErrors();
            $this->assertArrayHasKey(User::NAME, $errors);
            $this->assertEquals($errors[User::NAME][0], 'name key not set');
        }
    }

    public function test_name_key_invalid()
    {
        $json_body = [
            User::NAME => "John55",
            User::SURNAME => "Doe"
        ];

        $errors = [];

        $validator = new UserValidator(new NameSurnameValidator(), new ErrorsLoader());

        try {
            $validator->validate($json_body);
        } catch (UserValidatorException $e){
            $errors = $validator->getErrors();
            $this->assertArrayHasKey(User::NAME, $errors);
            $this->assertEquals($errors[User::NAME][0], User::INVALID_NAME);
        }
    }

    public function test_surname_key_not_set()
    {
        $json_body = [
            User::NAME => "John",
            "surnameblablabla" => "Doe"
        ];

        $errors = [];

        $validator = new UserValidator(new NameSurnameValidator(), new ErrorsLoader());

        try {
            $validator->validate($json_body);
        } catch (UserValidatorException $e){
            $errors = $validator->getErrors();
            $this->assertArrayHasKey(User::SURNAME, $errors);
            $this->assertEquals($errors[User::SURNAME][0], 'surname key not set');
        }
    }

    public function test_surname_key_invalid()
    {
        $json_body = [
            User::NAME => "John",
            User::SURNAME => "Doe55"
        ];

        $errors = [];

        $validator = new UserValidator(new NameSurnameValidator(), new ErrorsLoader());

        try {
            $validator->validate($json_body);
        } catch (UserValidatorException $e){
            $errors = $validator->getErrors();
            $this->assertArrayHasKey(User::SURNAME, $errors);
            $this->assertEquals($errors[User::SURNAME][0], User::INVALID_SURNAME);
        }
    }

    public function test_name_key_empty()
    {
        $json_body = [
            User::NAME => "",
            User::SURNAME => "Doe"
        ];

        $errors = [];

        $validator = new UserValidator(new NameSurnameValidator(), new ErrorsLoader());

        try {
            $validator->validate($json_body);
        } catch (UserValidatorException $e){
            $errors = $validator->getErrors();
            $this->assertArrayHasKey(User::NAME, $errors);
            $this->assertEquals($errors[User::NAME][0], User::INVALID_NAME);
        }
    }

    public function test_surname_key_empty()
    {
        $json_body = [
            User::NAME => "John",
            User::SURNAME => ""
        ];

        $errors = [];

        $validator = new UserValidator(new NameSurnameValidator(), new ErrorsLoader());

        try {
            $validator->validate($json_body);
        } catch (UserValidatorException $e){
            $errors = $validator->getErrors();
            $this->assertArrayHasKey(User::SURNAME, $errors);
            $this->assertEquals($errors[User::SURNAME][0], User::INVALID_SURNAME);
        }
    }

    public function test_no_keys()
    {
        $json_body = [

        ];

        $errors = [];

        $validator = new UserValidator(new NameSurnameValidator(), new ErrorsLoader());

        try {
            $validator->validate($json_body);
        } catch (UserValidatorException $e){
            $errors = $validator->getErrors();
            $this->assertArrayHasKey(User::NAME, $errors);
            $this->assertArrayHasKey(User::SURNAME, $errors);
            $this->assertEquals($errors[User::NAME][0], 'name key not set');
            $this->assertEquals($errors[User::SURNAME][0], 'surname key not set');
        }
    }
}
