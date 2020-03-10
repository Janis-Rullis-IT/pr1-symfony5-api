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
		// persist() works as insert, without it works as update. https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/tutorials/getting-started.html#updating-entities
		$this->em->persist($item);
		$this->em->flush();
		return $item;
	}

	/**
	 * #39 #33 #34 Mark additional products (ex., 2 pieces of the same t-shirt, 2nd is additional).
	 * Done with a SQL query that is faster than checking if is there another product like that before inserting. 
	 * The purpose of this field `is_additional` is to be used for matching a row in the `shipping_rates` table.
	 * https://symfony.com/doc/current/doctrine.html#querying-for-objects-the-repository
	 * https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/dql-doctrine-query-language.html
	 * https://symfony.com/doc/current/doctrine.html#querying-with-sql
	 * https://ourcodeworld.com/articles/read/2/5-simple-tips-for-boost-the-database-handling-with-symfony2-and-doctrine
	 * https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/query-builder.html
	 * 
	 * @param Order $draftOrder
	 * @return bool
	 */
	public function makrCartsAdditionalProducts(Order $draftOrder): bool
	{
		// #39 #33 #34 Reset all cart's products as first. DQL because this is simple.
		$this->createQueryBuilder('p')
			->update()
			->where('p.order_id = :orderId')
			->set('p.is_additional', ':isAdditional')
			->setParameter('orderId', $draftOrder->getId())
			->setParameter('isAdditional', 'n')
			->getQuery()->execute();

		// #39 #33 #34 Raw because this is more complex.
		$tableName = $this->em->getClassMetadata(OrderProduct::class)->getTableName();
		$conn = $this->em->getConnection();
		$sql = '
			UPDATE `' . $tableName . '` n1, `' . $tableName . '` n2
			SET n1.`is_additional` = \'y\'
			WHERE n1.id > n2.id 
			AND n1.`product_id` = n2.`product_id`
			AND n1.`order_id` = n2.`order_id`
			AND n1.`order_id` = :order_id;';
		$stmt = $conn->prepare($sql);
		$return = $stmt->execute(['order_id' => $draftOrder->getId()]);

		// #39 #33 #34 Fix Results are not refreshed when collected later 
		// with `find()` because they are collected by def. from the memory first. 
		// https://github.com/doctrine/orm/issues/6320#issuecomment-581832185
		// https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/working-with-objects.html#detaching-entities
		// https://www.doctrine-project.org/api/orm/latest/Doctrine/ORM/EntityManager.html#method_clear
		// https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/batch-processing.html#iterating-results
		$this->em->flush();
		$this->em->clear();

		return $return;
	}

	/**
	 * #39 #33 #34 Mark cart's products as domestic or international (from the order).
	 * The purpose of this field `is_domestic` is to be used for matching a row in the `shipping_rates` table.
	 * 
	 * @param Order $draftOrder
	 * @return bool
	 */
	public function markDomesticShipping(Order $draftOrder): bool
	{
		$return = $this->createQueryBuilder('p')
				->update()
				->where('p.order_id = :orderId')
				->set('p.is_domestic', ':isDomestic')
				->setParameter('orderId', $draftOrder->getId())
				->setParameter('isDomestic', $draftOrder->getIsDomestic())
				->getQuery()->execute() >= 0;

		$this->em->flush();
		$this->em->clear();

		return $return;
	}

	/**
	 * #39 #33 #34 Mark cart's product shipping as express or standard. 
	 * The purpose of this field `is_express` is to be used for matching a row in the `shipping_rates` table.
	 * 
	 * @param Order $draftOrder
	 * @return bool
	 */
	public function markExpressShipping(Order $draftOrder): bool
	{
		$return = $this->createQueryBuilder('p')
				->update()
				->where('p.order_id = :orderId')
				->set('p.is_express', ':isExpress')
				->setParameter('orderId', $draftOrder->getId())
				->setParameter('isExpress', $draftOrder->getIsExpress())
				->getQuery()->execute() >= 0;

		$this->em->flush();
		$this->em->clear();

		return $return;
	}

	/**
	 * #39 #33 #34 #37 Set order's product shipping costs based on the 
	 * matching rates in the `v2_shipping_rates` table.
	 * https://github.com/janis-rullis/pr1/issues/34#issuecomment-595221093
	 * 
	 * @param Order $draftOrder
	 */
	public function setShippingRates(Order $draftOrder): bool
	{
		// #39 #33 #34 #37 TODO: Rewrite in a query builder format.
		$tableName = $this->em->getClassMetadata(OrderProduct::class)->getTableName();
		$conn = $this->em->getConnection();
		$sql = '
			UPDATE `' . $tableName . '` a
			JOIN v2_shipping_rate b
			ON a.product_type = b.product_type
			AND  a.is_domestic = b.is_domestic
			AND  a.is_additional = b.is_additional
			AND  a.is_express = b.is_express
			SET a.shipping_cost = b.cost
			WHERE a.order_id = :order_id
			AND a.deleted_at IS NULL;';
		$stmt = $conn->prepare($sql);
		$return = $stmt->execute(['order_id' => $draftOrder->getId()]);

		$this->em->flush();
		$this->em->clear();

		return $return;
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
	  return $this->createQueryBuilder('   o')
	  ->andWhere('o.exampleField =  :val')
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
