<?php

namespace App\Validators\UserValidators;

use App\Interfaces\IUserRepo;

class UidValidator
{
    private $repository;

    public function __construct(IUserRepo $repository)
    {
        $this->repository = $repository;
    }

    public function validate(int $id): bool
    {
        if (null != $this->repository->getById($id)) {
            return true;
        }

        return false;
    }
}
