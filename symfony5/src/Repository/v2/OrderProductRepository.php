<?php

namespace App\Repository\v2;

use \App\Entity\v2\OrderProduct;
use \App\Interfaces\v2\IOrderProductRepo;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method OrderProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderProduct[]    findAll()
 * @method OrderProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderProductRepository extends ServiceEntityRepository implements IOrderProductRepo
{
    private $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, OrderProduct::class);
        $this->em = $em;
    }

    public function create(int $id_owner, array $requestBody): Product
    {
        $product = new OrderProduct();
        $product->setOwnerId($id_owner);
        $product->setType($requestBody[Product::TYPE]);
        $product->setTitle($requestBody[Product::TITLE]);
        $product->setSku($requestBody[Product::SKU]);
        $product->setCost($requestBody[Product::COST]);
        $this->em->persist($product);
        $this->em->flush();
        return $product;
    }

    // /**
    //  * @return OrderProduct[] Returns an array of OrderProduct objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OrderProduct
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
