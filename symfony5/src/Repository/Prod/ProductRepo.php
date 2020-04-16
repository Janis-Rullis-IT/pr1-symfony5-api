<?php

namespace App\Repository\Prod;

use App\Entity\Product;
use App\Entity\User;
use App\Exception\ProductIdValidatorException;
use App\Interfaces\IProductRepo;
use App\Validators\UserValidators\UidValidator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ProductRepo extends ServiceEntityRepository implements IProductRepo
{
    private $userIdValidator;

    public function __construct(ManagerRegistry $registry, UidValidator $userIdValidator)
    {
        parent::__construct($registry, Product::class);
        $this->userIdValidator = $userIdValidator;
    }

    public function create(int $id_owner, array $requestBody): Product
    {
        $product = new Product();
        $product->setOwnerId($id_owner);
        $product->setType($requestBody[Product::TYPE]);
        $product->setTitle($requestBody[Product::TITLE]);
        $product->setSku($requestBody[Product::SKU]);
        $product->setCost($requestBody[Product::COST]);
        $this->_em->persist($product);
        $this->_em->flush();

        return $product;
    }

    public function getById(int $id_user, int $id)
    {
        if ($this->userIdValidator->validate($id_user)) {
            return $this->findOneBy(['id' => $id, 'ownerId' => $id_user]);
        }

        return null;
    }

    public function getAll(int $id_user)
    {
        if ($this->userIdValidator->validate($id_user)) {
            return $this->findBy(['ownerId' => $id_user]);
        }

        return null;
    }

    /**
     * #40 Find product by id. Throw an exception if not found.
     *
     * @throws ProductIdValidatorException
     */
    public function mustFind(int $id): Product
    {
        $item = $this->findOneBy(['id' => $id]);
        if (empty($item)) {
            throw new ProductIdValidatorException([Product::ID => Product::INVALID], 1);
        }

        return $item;
    }

    /**
     * #53 Generate a dummy a product for a user. Used in fixtures and tests.
     */
    public function generateDummyUserProduct(User $user, string $productType): Product
    {
        return $this->create($user->getId(), [
            Product::TYPE => $productType,
            Product::TITLE => $user->getName().' '.$user->getSurname().' '.$productType,
            Product::SKU => $user->getName().' '.$user->getSurname().' '.$productType,
            Product::COST => 100,
        ]);
    }
}
