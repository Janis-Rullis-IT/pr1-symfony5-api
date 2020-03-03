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
        if ($this->repository->getById($id) != null)
            return (true);
        return (false);
    }
}