<?php
namespace App\Repository\v2;

use App\Entity\v2\Order;
use App\Entity\v2\OrderProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use \App\Interfaces\v2\IOrderRepo;
use Doctrine\ORM\EntityManagerInterface;
use \App\Exception\OrderValidatorException;

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
			$item->setStatus(Order::DRAFT);
			$this->em->persist($item);
			$this->em->flush();
		}

		if (empty($item)) {
			throw new OrderValidatorException([Order::ORDER_ID => Order::CANT_CREATE]);
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
				"status" => Order::DRAFT
		]);
	}

	/**
	 * #39 #33 #34 #37 Sum together costs from cart products and store in the order's costs.
	 * https://github.com/janis-rullis/pr1/issues/33#issuecomment-595102860
	 * 
	 * @param Order $order
	 * @return bool
	 */
	public function setOrderCostsFromCartItems(Order $order): bool
	{
		// #39 #33 #34 #37 TODO: Rewrite in a query builder format.
		$orderProductTableName = $this->em->getClassMetadata(OrderProduct::class)->getTableName();
		$orderTableName = $this->em->getClassMetadata(Order::class)->getTableName();

		$conn = $this->em->getConnection();
		$sql = '
			UPDATE `' . $orderTableName . '` a
			JOIN (
				SELECT b.order_id, SUM(b.product_cost) as product_cost, SUM(b.shipping_cost) as shipping_cost, SUM(product_cost + shipping_cost) as  total_cost
				FROM `' . $orderProductTableName . '` b
				WHERE b.order_id = :order_id
				GROUP BY b.order_id
			) b
			ON a.id = b.order_id
			SET a.shipping_cost = b.shipping_cost, a.product_cost = b.product_cost, a.total_cost = b.total_cost;';
		$stmt = $conn->prepare($sql);
		$return = $stmt->execute(['order_id' => $order->getId()]);

		$this->em->flush();
		$this->em->clear();

		return $return;
	}

	// #40
	public function prepare(Order $order, array $data): Order
	{
		$order->setIsDomestic($data['is_domestic']);

		// #39 #33 #34 Mark order's shipping as express or standard.
		// The purpose of this field `is_express` is to be used for matching a row in the `shipping_rates` table.
		$order->setIsExpress($data['is_express']);

		$order->setName($data[Order::OWNER_NAME]);
		$order->setSurname($data[Order::OWNER_SURNAME]);
		$order->setStreet($data[Order::STREET]);

		if (array_key_exists(Order::STATE, $data))
			$order->setState($data[Order::STATE]);
		else
			$order->setState(null);

		if (array_key_exists(Order::ZIP, $data))
			$order->setZip($data[Order::ZIP]);
		else
			$order->setZip(null);

		$order->setCountry($data[Order::COUNTRY]);
		$order->setPhone($data[Order::PHONE]);

		return $order;
	}

	/**
	 * #40 Write to database.
	 * Shorthand helper so wouldn't need to init the em when there's a repo.
	 * 
	 * @param Order $order
	 * @return Order
	 */
	public function write(Order $order): Order
	{
		// #40 https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/working-with-objects.html#persisting-entities
		$this->em->persist($order);
		$this->em->flush();
		$this->em->clear();
		return $order;
	}

	/**
	 * #40 Mark the order as completed.
	 * New products added to the cart won't be attached to this one anymore.
	 *  
	 * @param Order $order
	 * @return Order
	 */
	public function markAsCompleted(Order $order): Order
	{
		$order->setStatus(Order::COMPLETED);
		return $this->write($order);
	}
}
