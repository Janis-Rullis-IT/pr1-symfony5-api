<?php
namespace App\Repository\v2;

use App\Entity\v2\Order;
use App\Entity\v2\OrderProduct;
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

	/**
	 * #39 #33 #34 Mark order's shipping as express or standard.
	 * The purpose of this field `is_express` is to be used for matching a row in the `shipping_rates` table.
	 * 
	 * @param Order $draftOrder
	 * @param string $isExpress
	 * @return bool
	 */
	public function markExpressShipping(Order $draftOrder, string $isExpress): bool
	{
		// #39 #33 #3 4TODO: Return more descriptive data.
		// #39 #33 #34 Allow only y/n.
		if (!in_array($isExpress, ['y', 'n'])) {
			return false;
		}
		// #39 #33 #34 Forbid setting `is_express` if is_domestic='y'.
		if ($isExpress === 'y' && $draftOrder->getIsDomestic() === 'n') {
			return false;
		}

		$draftOrder->setIsExpress($isExpress);
		$this->em->flush();

		return true;
	}

	/**
	 * #39 #33 #34 #37 Sum together costs from cart products and store in the order's costs.
	 * https://github.com/janis-rullis/pr1/issues/33#issuecomment-595102860
	 * 
	 * @param Order $draftOrder
	 * @return bool
	 */
	public function setOrderCostsFromCartItems(Order $draftOrder): bool
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
		$return = $stmt->execute(['order_id' => $draftOrder->getId()]);

		$this->em->flush();
		$this->em->clear();

		return $return;
	}
}
