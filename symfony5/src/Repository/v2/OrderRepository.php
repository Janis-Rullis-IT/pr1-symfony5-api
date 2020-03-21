<?php
namespace App\Repository\v2;

use App\Entity\v2\Order;
use App\Entity\v2\OrderProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use \App\Interfaces\v2\IOrderRepo;
use Doctrine\ORM\EntityManagerInterface;
use \App\Exception\OrderValidatorException;
use \Doctrine\ORM\Query;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository implements IOrderRepo
{

	// #40 TODO: Try to implement this with ORM annotations.
	// #40 TODO: Replace with array and then convert to CSV.
	const SEL_COLUMNS = 'p.id, p.status, p.is_domestic, p.is_express, p.shipping_cost, p.product_cost, p.total_cost, p.name, p.surname, p.street, p.country, p.phone, p.state, p.zip';

	private $orderProductRepo;

	public function __construct(ManagerRegistry $registry, EntityManagerInterface $em, OrderProductRepository $orderProductRepo)
	{
		parent::__construct($registry, Order::class);
		$this->em = $em;
		$this->orderProductRepo = $orderProductRepo;
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
		// #40 A refresh-entity workaround for the field not being updated. 
		// https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/unitofwork.html https://www.doctrine-project.org/api/orm/latest/Doctrine/ORM/EntityManager.html
		// If `persist()` is being used then a naw record is inserted.
		// TODO: Ask someone about this behaviour.
		$order = $this->em->getReference(Order::class, $order->getId());
		$order->setStatus(Order::COMPLETED);
		return $this->write($order);
	}

	/**
	 * #40 Find order by id. Throw an exception if not found.
	 * 
	 * @param int $userId
	 * @param int $orderId
	 * @return Order
	 * @throws OrderValidatorException
	 */
	public function mustFindUsersOrder(int $userId, int $orderId): array
	{
		//$item = $this->findOneBy(["customer_id" => $userId, "id" => $orderId]);
		$item = $this->createQueryBuilder('p')
				// #40 TODO: Replace these keys with cosnt.
				->select('p.id, p.status, p.is_domestic, p.is_express, p.shipping_cost, p.product_cost, p.total_cost, p.name, p.surname, p.street, p.country, p.phone, p.state, p.zip')
				->where('p.id = :orderId')
				->andWhere('p.customer_id = :userId')
				->setParameter('orderId', $orderId)
				->setParameter('userId', $userId)
				->getQuery()->getOneOrNullResult();
		// #40 TODO: Find a way how this can be converted to Entity.
		if (empty($item)) {
			throw new OrderValidatorException([Order::ID => Order::INVALID], 1);
		}
		return $item;
	}

	public function mustFindUsersOrderWithProducts(int $userId, int $orderId): array
	{
		$order = $this->mustFindUsersOrder($userId, $orderId);
		$order[Order::PRODUCTS] = $this->orderProductRepo->findOrderProducts($orderId);
//		$order->setProducts($products);
		return $order;
	}

	public function mustFindUsersOrdersWithProducts(int $userId): array
	{
		// #40 TODO: Save this as a ...WithQueryBuilder
		// #40 Use the Annotation JOIN because it will return Entitites rather than 
		// arrays (as QB does). This approach will give more freedom - choose
		// to work with the Entity or convert to array.
		;
		// #40 Create toArray($keys) methods that will convert the Entity to
		// array in a unified manner. Will give same result in cart/products, 
		// order, orders.
		
		
		
		$return = [];
		$list = $this->createQueryBuilder('p')
				->select(self::SEL_COLUMNS . ',' . OrderProductRepository::SEL_COLUMNS)
				->where('p.customer_id = :userId')->setParameter('userId', $userId)
				->innerJoin(OrderProduct::class, 'r', 'WITH', 'p.id = r.order_id')
				->getQuery()->getResult(Query::HYDRATE_OBJECT);
		// #40 TODO: Try to implement this with relations so it would result in `[{order1:products[]},{order2:[products]}]`.
		// #40 TODO: Find a way how this can be converted to Entity.
		// #40 https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/dql-doctrine-query-language.html#fetching-multiple-from-entities
		if (empty($list)) {
			throw new OrderValidatorException([Order::ID => Order::INVALID], 1);
		} else {
			// #40 This should be replaced with a relation or in worst case a built-in filtering/grouping tool.
			foreach ($list as $item) {
				$return[$item['order_id']][$item['order_product_id']] = $item;
			}
		}
		return $return;
	}
}
