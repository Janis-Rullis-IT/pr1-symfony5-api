<?php
namespace App\v2;

use \App\Interfaces\IProductRepo;
use \App\Interfaces\IUserRepo;
use \App\Interfaces\v2\IOrderRepo;
use \App\Interfaces\v2\IOrderProductRepo;
use \App\Entity\v2\OrderProduct;

class OrderProductCreator
{

	private $userRepo;
	private $productRepo;
	private $orderRepo;
	private $orderProductRepo;

	public function __construct(IProductRepo $productRepo, IUserRepo $userRepo, IOrderRepo $orderRepo, IOrderProductRepo $orderProductRepo)
	{
		$this->userRepo = $userRepo;
		$this->productRepo = $productRepo;
		$this->orderRepo = $orderRepo;
		$this->orderProductRepo = $orderProductRepo;
	}

	/**
	 * #38 Validate, prepare and write to db.
	 * 
	 * @param array $data
	 * @return array
	 */
	public function handle(array $data): array
	{
		// #38 Validate and prepare the item.
		$return = $this->prepare($data);

		// #38 Write data to db only after it's validated and prepared.
		if (empty($return['errors']) && !empty($return['data'])) {

			$return['data'] = $this->orderProductRepo->create($return['data']);
		}
		return $return;
	}

	/**
	 * #38 Validate and prepare the item.
	 * 
	 * @param array $data
	 * @return array
	 */
	public function prepare(array $data): array
	{
		$return = ['errors' => [], 'status' => false, 'data' => null];
		$validator = new \App\v2\OrderProductValidator;

		// #38 Check if all required fields are passed.
		$status = $validator->hasRequiredKeys($data);
		if ($status !== true) {
			$return['errors'] = $status;
			return $return;
		}

		// TODO: Should this be moved to the Validator?
		// #38 Check if they exist in the database. Collect seller's and product's information.
		$customer = $this->userRepo->find($data['customer_id']);
		if (empty($customer)) {
			$return['errors']['customer_id'] = ["Invalid 'customer_id'."];
		}
		$product = $this->productRepo->find($data['product_id']);
		if (empty($product)) {
			$return['errors']['product_id'] = ["Invalid 'product_id'."];
		}

		// #38 Prepare the data for writing in the database.
		if (empty($return['errors'])) {

			$seller = $this->userRepo->find($product->getOwnerId());
			if (empty($seller)) {
				$return['errors']['seller_id'] = ["Invalid 'seller_id'."];
			}

			// #38 #36 Collect customer's current 'draft' or create a new one.
			$draftOrder = $this->orderRepo->insertIfNotExist($customer->getId());
			if (empty($draftOrder)) {
				$return['errors']['order_id'] = ["Cannot create a draft order. Please, contact our support."];
			}

			$return['data'] = $this->orderProductRepo->prepare($customer, $product, $seller, $draftOrder);
			if (!empty($return['data'])) {
				$return['status'] = true;
			}
		}

		return $return;
	}
}
