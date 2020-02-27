<?php


namespace App\Validators\ProductValidators;

class ProductTypeValidator
{
    const MUG = "mug";
    const TSHIRT = "t-shirt";

    private $allowedProducts;

    public function __construct()
    {
        $this->allowedProducts = [
            self::MUG,
            self::TSHIRT
        ];
    }

    public function validate(string $product): bool
    {
        if (in_array($product, $this->allowedProducts))
            return (true);
        return (false);
    }
}