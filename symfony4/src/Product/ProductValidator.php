<?php


namespace App\Product;


use App\Entity\Product;
use App\ErrorsLoader;
use App\Exception\DuplicateException;
use App\Exception\ProductValidatorException;
use App\Validators\ProductValidators\ProductTypeValidator;
use App\Validators\ProductValidators\SkuValidator;
use App\Validators\ProductValidators\TitleValidator;

class ProductValidator
{
    private $errors;
    private $productTypeValidator;
    private $skuValidator;
    private $titleValidator;
    private $errorsLoader;

    public function __construct(ProductTypeValidator $productTypeV, TitleValidator $titleV, SkuValidator $skuV, ErrorsLoader $errorsLoader)
    {
        $this->errors = [];
        $this->productTypeValidator = $productTypeV;
        $this->skuValidator = $skuV;
        $this->titleValidator = $titleV;
        $this->errorsLoader = $errorsLoader;
    }

    public function validate(array $data, int $id_user): void
    {
        if (!isset($data[Product::TYPE]))
            $this->errorsLoader->load(Product::TYPE, Product::NO_TYPE, $this->errors);
        if (!isset($data[Product::TITLE]))
            $this->errorsLoader->load(Product::TITLE, Product::NO_TITLE, $this->errors);
        if (!isset($data[Product::SKU]))
            $this->errorsLoader->load(Product::SKU, Product::NO_SKU, $this->errors);
        if (!isset($data[Product::COST]))
            $this->errorsLoader->load(Product::COST, Product::NO_COST, $this->errors);

        foreach ($data as $key => $value)
        {
            if ($key === Product::TYPE && !$this->productTypeValidator->validate($value))
                $this->errorsLoader->load(Product::TYPE, Product::INVALID_TYPE, $this->errors);
            elseif ($key === Product::TITLE && !$this->titleValidator->validate($value))
                $this->errorsLoader->load(Product::TITLE, Product::INVALID_TITLE, $this->errors);
            elseif ($key === Product::SKU && !$this->skuValidator->validate($value, $id_user))
                $this->errorsLoader->load(Product::SKU, Product::INVALID_SKU, $this->errors);
            elseif ($key === Product::COST && (!is_int($value) || $value < 1))
                $this->errorsLoader->load(Product::COST, Product::INVALID_COST, $this->errors);
        }

        if (!empty($this->errors))
        {
            if (array_key_exists(Product::SKU, $this->errors) && in_array(Product::INVALID_SKU, $this->errors[Product::SKU]) && count($this->errors) == 1)
                throw new DuplicateException($this->errors);
            throw new ProductValidatorException($this->errors);
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}