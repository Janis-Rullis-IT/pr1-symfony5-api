<?php
namespace App\v2;

use App\RequestBody\JsonToArray;

class OrderProductCreator
{

	private $converter;

	public function __construct(JsonToArray $converter)
	{
		$this->converter = $converter;
	}

	/**
	 * #38 Validate, prepare and write to db.
	 * 
	 * @param array $data
	 * @return type
	 */
	public function handle(array $data)
	{
		// TODO: To Validator: Check if all required fields are passed and they exist in the database.
		// TODO: To Validator: Check if the customer can afford this product.
		// TODO: First the order, user, seller and product must exist.
		$isValid = false;
		$return = [];
		$requireds = ['customer_id', 'product_id'];

		if ($isValid) {
			$item = $this->prepare($requireds);
			if (!empty($item)) {

				// #38 Write data to db only after it's validated and prepare.
				$this->entityManager->persist($item);
				$this->entityManager->flush();
				$return = $item;
			}
		}
		return $return;
	}

	/**
	 * #38 Prepare the item.
	 * @return \App\v2\OrderProduct
	 */
	public function prepare(array $datas)
	{
		// TODO: To `prepareItem()` Collect seller's and product's information.
		// TODO: To `prepareItem()` Prepare the data for writing in the database.

		$item = new OrderProduct();
		$item->setOrderId(1);
		$item->setCustomerId(1);
		$item->setSellerId(1);
		$item->setSellerTitle('US');
		$item->setProductId(1);
		$item->setProductTitle('T-shirt / US / Standard / First');
		$item->setProductCost(1);
		$item->setProductType('t-shirt');
		$item->setIsDomestic('y');

		return $item;
	}
}
