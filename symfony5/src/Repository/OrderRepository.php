<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Exception\OrderValidatorException;
use App\Interfaces\IOrderRepo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * #68 Repo best practices https://www.thinktocode.com/2018/03/05/repository-pattern-symfony/.
 */
class OrderRepository extends ServiceEntityRepository implements IOrderRepo
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * #38 Collect customer's current 'draft' or create a new one.
     */
    public function insertIfNotExist(int $customerId): Order
    {
        $item = $this->getCurrentDraft($customerId);

        // #38 Create if it doesn't exist yet.
        if (empty($item)) {
            $item = new Order();
            $item->setCustomerId($customerId);
            $item->setStatus(Order::DRAFT);
            $this->_em->persist($item);
            $this->_em->flush();
        }

        if (empty($item)) {
            throw new OrderValidatorException([Order::ORDER_ID => Order::CANT_CREATE]);
        }

        return $item;
    }

    /**
     * #38 Collect customer's current 'draft' order where all the cart's items should be stored.
     */
    public function getCurrentDraft(int $customerId): ?Order
    {
        return $this->findOneBy(['customer_id' => $customerId, 'status' => Order::DRAFT]);
    }

    /**
     * #37 Sum together costs from cart products and store in the order's costs https://github.com/janis-rullis/pr1/issues/33#issuecomment-595102860.
     */
    public function setOrderCostsFromCartItems(Order $order): bool
    {
        $orderProductTableName = $this->_em->getClassMetadata(OrderProduct::class)->getTableName();
        $orderTableName = $this->_em->getClassMetadata(Order::class)->getTableName();

        $conn = $this->_em->getConnection();
        $sql = '
			UPDATE `'.$orderTableName.'` a
			JOIN (
				SELECT b.order_id, SUM(b.product_cost) as product_cost, SUM(b.shipping_cost) as shipping_cost, SUM(product_cost + shipping_cost) as  total_cost
				FROM `'.$orderProductTableName.'` b
				WHERE b.order_id = :order_id
				GROUP BY b.order_id
			) b
			ON a.id = b.order_id
			SET a.shipping_cost = b.shipping_cost, a.product_cost = b.product_cost, a.total_cost = b.total_cost;';
        $stmt = $conn->prepare($sql);
        $return = $stmt->execute(['order_id' => $order->getId()]);

        $this->_em->flush();
        $this->_em->clear();

        return $return;
    }

    /**
     * #40 Fill the shipping data into the order.
     */
    public function fillShipping(Order $order, array $shippingData): Order
    {
        // #39 Mark order's shipping values to be used for matching a row in the `shipping_rates` table.
        $order->setIsDomestic($shippingData['is_domestic']);
        $order->setIsExpress($shippingData['is_express']);

        $order->setName($shippingData[Order::OWNER_NAME]);
        $order->setSurname($shippingData[Order::OWNER_SURNAME]);
        $order->setStreet($shippingData[Order::STREET]);
        $order->setState(array_key_exists(Order::STATE, $shippingData) ? $shippingData[Order::STATE] : null);
        $order->setZip(array_key_exists(Order::ZIP, $shippingData) ? $shippingData[Order::ZIP] : null);
        $order->setCountry($shippingData[Order::COUNTRY]);
        $order->setPhone($shippingData[Order::PHONE]);

        return $order;
    }

    /**
     * #40 Shorthand to write to the database.
     */
    public function save()
    {
        $this->_em->flush();
        $this->_em->clear();
    }

    /**
     * #40 Mark the order as completed. New products added to the cart won't be attached to this one anymore.
     */
    public function markAsCompleted(Order $order): Order
    {
        // #40 A refresh-entity workaround for the field not being updated. https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/unitofwork.html https://www.doctrine-project.org/api/orm/latest/Doctrine/ORM/EntityManager.html
        // TODO: Ask someone about this behaviour.
        $order = $this->_em->getReference(Order::class, $order->getId());
        $order->setStatus(Order::COMPLETED);
        $this->save($order);

        return $order;
    }

    /**
     * #40 Find order by id. Throw an exception if not found.
     */
    public function mustFindUsersOrder(int $userId, int $orderId): Order
    {
        $order = $this->findOneBy(['customer_id' => $userId, 'id' => $orderId]);
        if (empty($order)) {
            throw new OrderValidatorException([Order::ID => Order::INVALID], 1);
        }

        return $order;
    }

    /**
     * #40 Find user's orders.
     */
    public function mustFindUsersOrders(int $userId): array
    {
        return $this->findBy(['customer_id' => $userId]);
    }
}
