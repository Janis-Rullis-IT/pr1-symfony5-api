<?php

namespace App\Repository\Prod;

use App\Entity\Product;
use App\Interfaces\IProductRepo;
use App\Validators\UserValidators\UidValidator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepo extends ServiceEntityRepository implements IProductRepo
{
    private $em;
    private $userIdValidator;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em, UidValidator $userIdValidator)
    {
        parent::__construct($registry, Product::class);
        $this->em = $em;
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
        $this->em->persist($product);
        $this->em->flush();
        return $product;
    }

    public function getById(int $id_user, int $id)
    {
        if ($this->userIdValidator->validate($id_user))
        {
            return $this->findOneBy([
                "id" => $id,
                "ownerId" => $id_user
            ]);
        }
        return (null);
    }

    public function getAll(int $id_user)
    {
        if ($this->userIdValidator->validate($id_user))
        {
            return $this->findBy([
                "ownerId" => $id_user
            ]);
        }
        return (null);
    }
}
