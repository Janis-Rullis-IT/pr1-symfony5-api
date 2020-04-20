<?php
namespace App\Interfaces;
use App\Entity\OrderProduct;
use App\Entity\Order;
use App\Entity\User;
use App\Entity\Product;

interface IOrderProductRepo
{

	/**
	 * #38 Add a product to cart into the database.
	 */
	public function create(OrderProduct $item): OrderProduct;

	/**
	 * #39 Mark additional products (ex., 2 pieces of the same t-shirt, 2nd is additional).
	 * The purpose of this field `is_additional` is to be used for matching a row in the `shipping_rates` table.1.
	 */
	public function markCartsAdditionalProducts(Order $draftOrder): bool;

	/**
	 * #39 Reset `is_additional` field for all cart's products to 'n' (means first).
	 */
	public function markCartsProductsAsFirst(Order $draftOrder): bool;

	/**
	 * #39 Mark cart's products as domestic or international (from the order).
	 * The purpose of this field `is_domestic` is to be used for matching a row in the `shipping_rates` table.
	 */
	public function markAsDomesticShipping(Order $draftOrder): bool;

	/**
	 * #39 Mark cart's product shipping as express or standard.
	 * The purpose of this field `is_express` is to be used for matching a row in the `shipping_rate` table.
	 */
	public function markAsExpressShipping(Order $draftOrder): bool;

	/**
	 * #39 Set order's product shipping costs based on the matching rates in the `shipping_rate` table https://github.com/janis-rullis/pr1/issues/34#issuecomment-595221093.
	 */
	public function setShippingRates(Order $draftOrder): bool;

	/**
	 * #38 Prepare product to add to cart into the database.
	 */
	public function prepare(User $customer, Product $product, User $seller, Order $draftOrder): OrderProduct;

	/**
	 * #40 Mark additional products, domestic regions and set rates.
	 */
	public function setShippingValues(Order $order): void;
}
