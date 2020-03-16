<?php
namespace App\v2;

use \App\Interfaces\IProductRepo;
use \App\Interfaces\IUserRepo;
use \App\Interfaces\v2\IOrderRepo;
use \App\Interfaces\v2\IOrderProductRepo;

class OrderCreator
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

//			$return['data'] = $this->orderRepo->create($return['data']);
		}
		return $return;
	}

	/**
	 * #40 Validate and prepare the item.
	 * 
	 * @param array $data
	 * @return array
	 */
	public function prepare(array $data): array
	{
		$return = self::isDomestic($data);

		// #38 Prepare the data for writing in the database.
		if (empty($return['errors'])) {

			// TODO: Should this be moved to the Validator?
			// #38 Check if they exist in the database. Collect seller's and product's information.
			$customer = $this->userRepo->find($data['customer_id']);
			if (empty($customer)) {
				$return['errors']['customer_id'] = ["Invalid 'customer_id'."];
			}

			// #38 #36 Collect customer's current 'draft' or create a new one.
			$draftOrder = $this->orderRepo->insertIfNotExist($customer->getId());
			if (empty($draftOrder)) {
				$return['errors']['order_id'] = ["Cannot create a draft order. Please, contact our support."];
			}

			$return['data'] = $this->orderRepo->prepare($customer, $data);
			if (!empty($return['data'])) {
				$return['status'] = true;
			}
		}

		return $return;
	}

	/**
	 * #40 Check if the passed address is in the domestic region (has lower shipping rates).
	 * Previously, this was implemented in `App\Validators\AddressValidators\ShipmentType`
	 * 
	 * @param array $address
	 * @return array
	 */
	public static function isDomestic(array $address): array
	{
		$return = ['errors' => [], 'status' => false, 'data' => null];
		if (empty($address['country'])) {
			$return['errors']['country'] = ["Invalid 'country' field."];
			return $return;
		}

		// #40 TODO: This list should be moved to a separate config or DB so it would be
		// easier to update.
		if (in_array(trim(strtolower($address['country'])), ['us', 'usa', 'united states of america'])) {
			$return['data'] = true;
		} else {
			$return['data'] = false;
		}

		$return['status'] = true;

		return $return;
	}
}
