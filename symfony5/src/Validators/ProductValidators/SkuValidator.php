<?php


namespace App\Validators\ProductValidators;

use App\Entity\Product;
use App\Interfaces\IProductRepo;

class SkuValidator
{
    private $repository;

    public function __construct(IProductRepo $repository)
    {
        $this->repository = $repository;
    }

    public function validate(string $sku, int $user_id): bool
    {
        if ($this->repository->findOneBy([Product::SKU => $sku, Product::OWNER_ID => $user_id]))
            return (false);
        if (strlen($sku) < 1)
            return (false);
        return (true);
    }
}