<?php

namespace App\Interfaces;

use App\Entity\User;

interface IUserRepo
{
    public function create(array $characteristics): User;
}