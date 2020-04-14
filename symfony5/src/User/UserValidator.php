<?php

namespace App\User;

use App\Entity\User;
use App\ErrorsLoader;
use App\Exception\UserValidatorException;
use App\Validators\UserValidators\NameSurnameValidator;

class UserValidator
{
    private $errors;
    private $nameSurnameValidator;
    private $errorsLoader;

    public function __construct(NameSurnameValidator $nameSurnameValidator, ErrorsLoader $errorsLoader)
    {
        $this->errors = [];
        $this->nameSurnameValidator = $nameSurnameValidator;
        $this->errorsLoader = $errorsLoader;
    }

    public function validate(array $data): void
    {
        if (!isset($data[User::NAME])) {
            $this->errorsLoader->load(User::NAME, User::NO_NAME, $this->errors);
        }
        if (!isset($data[User::SURNAME])) {
            $this->errorsLoader->load(User::SURNAME, User::NO_SURNAME, $this->errors);
        }

        foreach ($data as $key => $value) {
            if (User::NAME === $key && !$this->nameSurnameValidator->validate($value)) {
                $this->errorsLoader->load(User::NAME, User::INVALID_NAME, $this->errors);
            } elseif (User::SURNAME === $key && !$this->nameSurnameValidator->validate($value)) {
                $this->errorsLoader->load(User::SURNAME, User::INVALID_SURNAME, $this->errors);
            }
        }

        if (!empty($this->errors)) {
            throw new UserValidatorException($this->errors);
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * #40 Has the user enough money?
     */
    public function hasEnoughMoney(User $user, int $money): bool
    {
        return !empty($user->getBalance()) && $user->getBalance() >= $money;
    }

    /**
     * #40 Make sure the user has enough money.
     *
     * @return bool
     *
     * @throws UserValidatorException
     */
    public function mustHaveMoney(User $user, int $money): void
    {
        if (!$this->hasEnoughMoney($user, $money)) {
            $this->errorsLoader->load(User::BALANCE, User::INSUFFICIENT_FUNDS, $this->errors);
            throw new UserValidatorException($this->errors);
        }
    }
}
