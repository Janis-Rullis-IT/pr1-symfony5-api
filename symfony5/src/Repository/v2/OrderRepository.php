<?php
namespace App\Repository\v2;

use App\Entity\v2\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use \App\Interfaces\v2\IOrderRepo;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository implements IOrderRepo
{

	public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
	{
		parent::__construct($registry, Order::class);
		$this->em = $em;
	}

	// 
	/**
	 * #38 #36 Collect customer's current 'draft' or create a new one.
	 * 
	 * @param int $customerId
	 * @return Order
	 */
	public function insertIfNotExist(int $customerId): Order
	{
		$item = $this->getCurrentDraft($customerId);

		//  #38 #36 Create if it doesn't exist yet.
		if (empty($item)) {
			$item = new Order();
			$item->setCustomerId($customerId);
			$item->setStatus('draft');
			$this->em->persist($item);
			$this->em->flush();
		}

		return $item;
	}

	/**
	 * #38 #36 Collect customer's current 'draft' order where all the cart's items should be stored.
	 * 
	 * @param int $customerId
	 * @return Order
	 */
	public function getCurrentDraft(int $customerId): ?Order
	{
		return $this->findOneBy([
				"customer_id" => $customerId,
				"status" => 'draft'
		]);
	}
	// /**
	//  * @return Order[] Returns an array of Order objects
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
	  public function findOneBySomeField($value): ?Order
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
