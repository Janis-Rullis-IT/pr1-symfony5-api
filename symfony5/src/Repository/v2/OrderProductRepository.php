<?php
namespace App\Repository\v2;

use \App\Entity\v2\OrderProduct;
use \App\Entity\v2\Order;
use \App\Interfaces\v2\IOrderProductRepo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use \App\Entity\User;
use \App\Entity\Product;

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

	/**
	 * #38 #36 Add a product to cart into the database.
	 * Use OrderProductCreator as a helper to prepare the required data.
	 * 
	 * @param OrderProduct $item
	 * @return OrderProduct
	 */
	public function create(OrderProduct $item): OrderProduct
	{
		$this->em->persist($item);
		$this->em->flush();
		return $item;
	}

	/**
	 * #38 #36 Prepare product to add to cart into the database.
	 * Use OrderProductCreator as a helper to prepare the required data.
	 * 
	 * @param User $customer
	 * @param Product $product
	 * @param User $seller
	 * @param Order $draftOrder
	 * @return OrderProduct
	 */
	public function prepare(User $customer, Product $product, User $seller, Order $draftOrder): OrderProduct
	{
		$item = new OrderProduct();
		$item->setOrderId($draftOrder->getId());

		// #38 TODO: Should this be done better with SQL JOIN UPDATE?
		$item->setCustomerId($customer->getId());
		$item->setSellerId($seller->getId());
		$item->setProductId($product->getId());
		$item->setProductCost($product->getCost());
		$item->setProductType($product->getType());

		$item->setSellerTitle($seller->getName() . ' ' . $seller->getSurname());
		$item->setProductTitle($product->getTitle());
		
		return $item;
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
