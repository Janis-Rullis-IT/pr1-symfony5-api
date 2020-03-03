<?php


namespace App\Repository\Test;


use App\Entity\Product;
use App\Interfaces\IProductRepo;
use App\Validators\UserValidators\UidValidator;

class ProductTestRepo implements IProductRepo
{
    public $db;
    private $id;
    private $userIdValidator;

    public function __construct(UidValidator $userIdValidator)
    {
        $this->db = [];
        $this->id = 1;
        $this->userIdValidator = $userIdValidator;
        $this->create(1, [
            Product::TYPE => "t-shirt",
            Product::TITLE => "much shirt, such style!",
            Product::SKU => "100-abc-999",
            Product::COST => 1000
        ]);
    }

    public function create(int $id_owner, array $requestBody): Product
    {
        $product = new Product();
        $product->setId($this->id);
        $product->setOwnerId($id_owner);
        $product->setType($requestBody[Product::TYPE]);
        $product->setTitle($requestBody[Product::TITLE]);
        $product->setSku($requestBody[Product::SKU]);
        $product->setCost($requestBody[Product::COST]);
        $this->db[$this->id] = $product;
        $this->id++;
        return $product;
    }

    public function getById(int $id_user, int $id)
    {
        if ($this->userIdValidator->validate($id_user))
        {
            if (array_key_exists($id, $this->db) && $this->db[$id]->getOwnerId() === $id_user)
            {
                return $this->db[$id];
            }
        }
        return null;
    }

    public function getAll(int $id_user)
    {
        if ($this->userIdValidator->validate($id_user))
        {
            $products = [];
            foreach ($this->db as $product)
            {
                if ($product->getOwnerId() === $id_user)
                    $products[] = $product;
            }
            return $products;
        }
        return (null);
    }

    public function findOneBy(array $requestBody)
    {
        $keysCount = count ($requestBody);
        $keysFound = 0;
        foreach ($this->db as $product)
        {
            foreach ($requestBody as $key => $value)
            {
                if ($key === Product::ID && $product->getId() === $value)
                    $keysFound++;
                elseif($key === Product::OWNER_ID && $product->getOwnerId() === $value)
                    $keysFound++;
                elseif($key === Product::TYPE && $product->getType() === $value)
                    $keysFound++;
                elseif($key === Product::TITLE && $product->getTitle() === $value)
                    $keysFound++;
                elseif($key === Product::SKU && $product->getSku() === $value)
                    $keysFound++;
                elseif($key === Product::COST && $product->getCost() === $value)
                    $keysFound++;
            }
            if ($keysFound === $keysCount)
                return (true);
        }
        return (null);
    }
}