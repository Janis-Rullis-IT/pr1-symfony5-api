<?php

namespace App\Interfaces;
use App\Entity\Product;

interface IProductRepo
{
    public function create(int $id_owner, array $characteristics): Product;
}