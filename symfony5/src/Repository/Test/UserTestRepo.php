<?php


namespace App\Repository\Test;

use App\Entity\User;
use App\Interfaces\IUserRepo;

class UserTestRepo implements IUserRepo
{
    private $db;
    private $id;

    public function __construct()
    {
        $this->db = [];
        $this->id = 1;
        $this->create([
            User::NAME => "John",
            User::SURNAME => "Doe"
        ]);
    }

    public function create(array $requestBody): User
    {
        $user = new User();
        $user->setId($this->id);
        $user->setName($requestBody[User::NAME]);
        $user->setSurname($requestBody[User::SURNAME]);
        $user->setBalance(10000);
        $this->db[$this->id] = $user;
        $this->id++;
        return $user;
    }

    public function getById(int $id)
    {
        if (array_key_exists($id, $this->db))
            return $this->db[$id];
        return null;
    }

    public function getAll(): array
    {
        return array_values($this->db);
    }
}