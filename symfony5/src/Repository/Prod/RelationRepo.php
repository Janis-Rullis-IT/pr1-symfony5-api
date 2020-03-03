<?php

namespace App\Repository\Prod;

use App\Entity\Relation;
use App\Entity\Product;
use App\Interfaces\IRelationRepo;
use App\Interfaces\IProductRepo;
use App\Validators\UserValidators\UidValidator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method Relation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Relation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Relation[]    findAll()
 * @method Relation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RelationRepo extends ServiceEntityRepository implements IRelationRepo
{
    private $em;
    private $productsRepository;
    private $userIdValidator;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em, IProductRepo $productsRepository, UidValidator $userIdValidator)
    {
        parent::__construct($registry, Relation::class);
        $this->em = $em;
        $this->productsRepository = $productsRepository;
        $this->userIdValidator = $userIdValidator;
    }

    public function create(int $order_id, array $line_items, int $id_user)
    {
        foreach ($line_items as $item)
        {
            $relation = new Relation();
            $relation->setOwnerId($id_user);
            $relation->setOrderId($order_id);
            $relation->setProductId($item[Product::ID]);
            $relation->setQuantity($item[Product::QUANTITY]);
            $this->em->persist($relation);
            $this->em->flush();
        }
    }

    public function getOrderProducts($id_user, int $id)
    {
        if ($this->userIdValidator->validate($id_user))
        {
            return $this->findBy([
                Relation::ORDER_ID => $id,
                Relation::OWNER_ID => $id_user
            ]);
        }
        return (null);
    }

    public function line_items(int $id_user, int $id)
    {
        $relation_products = $this->getOrderProducts($id_user, $id);
        $line_items = [];
        foreach ($relation_products as $relation_product)
        {
            $item = [];
            $item[Product::ID] = $relation_product->getProductId();
            $item[Product::QUANTITY] = $relation_product->getQuantity();
            $product = $this->productsRepository->getById($id_user, $item[Product::ID]);
            $item[Product::OWNER_ID] = $product->getOwnerId();
            $item[Product::TYPE] = $product->getType();
            $item[Product::TITLE] = $product->getTitle();
            $item[Product::SKU] = $product->getSku();
            $item[Product::COST] = $product->getCost();
            $item[Product::TOTAL_COST] = $item[Product::COST] * $item[Product::QUANTITY];
            $line_items[] = $item;
        }
        return ($line_items);
    }
}
