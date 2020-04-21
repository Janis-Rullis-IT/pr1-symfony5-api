<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Entity\User;
use App\Interfaces\IOrderProductRepo;
use Doctrine\ORM\EntityManagerInterface;

final class OrderProductRepository extends BaseRepository implements IOrderProductRepo
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, OrderProduct::class);
    }

    /**
     * #38 Add a product to cart into the database.
     */
    public function create(OrderProduct $item): OrderProduct
    {
        $this->em->persist($item);
        $this->em->flush();

        return $item;
    }

    /**
     * #39 Mark additional products (ex., 2 pieces of the same t-shirt, 2nd is additional).
     * The purpose of this field `is_additional` is to be used for matching a row in the `shipping_rates` table.1.
     */
    public function markCartsAdditionalProducts(Order $draftOrder): bool
    {
        $this->markCartsProductsAsFirst($draftOrder);
        $tableName = $this->em->getClassMetadata(OrderProduct::class)->getTableName();
        $conn = $this->em->getConnection();
        $sql = '
			UPDATE `'.$tableName.'` n1, `'.$tableName.'` n2
			SET n1.`is_additional` = :isAdditional
			WHERE n1.id > n2.id 
			AND n1.`product_id` = n2.`product_id`
			AND n1.`order_id` = n2.`order_id`
			AND n1.`order_id` = :order_id;';
        $stmt = $conn->prepare($sql);
        $return = $stmt->execute(['isAdditional' => 'y', 'order_id' => $draftOrder->getId()]);
        $this->em->flush();
        $this->em->clear();

        return $return;
    }

    /**
     * #39 Reset `is_additional` field for all cart's products to 'n' (means first).
     */
    public function markCartsProductsAsFirst(Order $draftOrder): bool
    {
        return $this->em->createQueryBuilder()->update(OrderProduct::class, 'p')->where('p.order_id = :orderId')->set('p.is_additional', ':isAdditional')
            ->setParameter('orderId', $draftOrder->getId())->setParameter('isAdditional', 'n')
            ->getQuery()->execute() >= 0;
    }

    /**
     * #39 Mark cart's products as domestic or international (from the order).
     * The purpose of this field `is_domestic` is to be used for matching a row in the `shipping_rates` table.
     */
    public function markAsDomesticShipping(Order $draftOrder): bool
    {
        $return = $this->em->createQueryBuilder()->update(OrderProduct::class, 'p')->where('p.order_id = :orderId')->set('p.is_domestic', ':isDomestic')
            ->setParameter('orderId', $draftOrder->getId())->setParameter('isDomestic', $draftOrder->getIsDomestic())
                ->getQuery()->execute() >= 0;
        $this->em->flush();
        $this->em->clear();

        return $return;
    }

    /**
     * #39 Mark cart's product shipping as express or standard.
     * The purpose of this field `is_express` is to be used for matching a row in the `shipping_rate` table.
     */
    public function markAsExpressShipping(Order $draftOrder): bool
    {
        $return = $this->em->createQueryBuilder()->update(OrderProduct::class, 'p')->where('p.order_id = :orderId')->set('p.is_express', ':isExpress')
                ->setParameter('orderId', $draftOrder->getId())->setParameter('isExpress', $draftOrder->getIsExpress())
                ->getQuery()->execute() >= 0;
        $this->em->flush();
        $this->em->clear();

        return $return;
    }

    /**
     * #39 Set order's product shipping costs based on the matching rates in the `shipping_rate` table https://github.com/janis-rullis/pr1/issues/34#issuecomment-595221093.
     */
    public function setShippingRates(Order $draftOrder): bool
    {
        $tableName = $this->em->getClassMetadata(OrderProduct::class)->getTableName();
        $conn = $this->em->getConnection();
        $sql = '
			UPDATE `'.$tableName.'` a
			JOIN shipping_rate b
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
     * #38 Prepare product to add to cart into the database.
     */
    public function prepare(User $customer, Product $product, User $seller, Order $draftOrder): OrderProduct
    {
        // #68 JOIN with seller and product would be faster but less control when update did not happen (was it because of productId or sellerId?).
        $item = new OrderProduct();
        $item->setOrderId($draftOrder->getId());
        $item->setCustomerId($customer->getId());
        $item->setSellerId($seller->getId());
        $item->setProductId($product->getId());
        $item->setProductCost($product->getCost());
        $item->setProductType($product->getType());

        $item->setSellerTitle($seller->getName().' '.$seller->getSurname());
        $item->setProductTitle($product->getTitle());

        return $item;
    }

    /**
     * #40 Mark additional products, domestic regions and set rates.
     */
    public function setShippingValues(Order $order): void
    {
        $this->markCartsAdditionalProducts($order);
        $this->markAsDomesticShipping($order);
        $this->markAsExpressShipping($order);
        $this->setShippingRates($order);
    }
}
