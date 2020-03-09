<?php
namespace App\v2;

use \App\Interfaces\IProductRepo;
use \App\Interfaces\IUserRepo;

class OrderProductCreator
{

	private $userRepo;
	private $productRepo;

	public function __construct(IProductRepo $productRepo, IUserRepo $userRepo)
	{
		$this->userRepo = $userRepo;
		$this->productRepo = $productRepo;
	}

	/**
	 * #38 Validate, prepare and write to db.
	 * 
	 * @param array $data
	 * @return type
	 */
	public function handle(array $data)
	{
		$return = ['errors' => [], 'status' => false, 'data' => null];

		// #38 Validate and prepare the item.
		$item = $this->prepare($data);

		// #38 Write data to db only after it's validated and prepare.
		if (empty($item['errors'])) {
			$this->entityManager->persist($item['data']);
			$this->entityManager->flush();

			// #38 TODO: Check and set into `$return` DB errors here.
			$return = $item;
		} else {
			$return = $item;
		}
		return $return;
	}

	/**
	 * #38 Validate and prepare the item.
	 * 
	 * @param array $datas
	 * @return type
	 */
	public function prepare(array $data)
	{
		$return = ['errors' => [], 'status' => false, 'data' => null];
		$validator = new \App\v2\OrderProductValidator;

		// #38 Check if all required fields are passed.
		$status = $validator->hasRequiredKeys($data);
		if ($status === true) {

			// #38 Check if they exist in the database.
			$customer = $this->userRepo->getById($data['customer_id']);
			dd($customer);



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
		} else {
			$return['errors'] = $status;
		}
		return $return;
	}
}
