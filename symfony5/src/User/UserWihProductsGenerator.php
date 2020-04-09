<?php
namespace App\User;

/**
 *  #53 Generate dummy users with products. Used in fixtures and tests.
 */
use App\Interfaces\IUserRepo;
use App\Interfaces\IProductRepo;

class UserWihProductsGenerator
{

	private $userRepo;
	private $productRepo;

	/**
	 * #53
	 * @param IUserRepo $userRepo
	 * @param IProductRepo $productRepo
	 */
	public function __construct(IUserRepo $userRepo, IProductRepo $productRepo)
	{
		$this->userRepo = $userRepo;
		$this->productRepo = $productRepo;
	}

	/**
	 * #53 Generate dummy users with products. Used in fixtures and tests.
	 * 
	 * @param int $count
	 * @return type
	 */
	public function generate(int $count = 1)
	{
		// #38 Create 3 users.
		$users = [];
		for ($i = 0; $i < $count; $i++) {

			$users[$i] = $user = $this->userRepo->generateDummyUser($i);

			// #38 Create 1 mug and 1 shirt for each user.
			$productTypes = ['t-shirt', 'mug'];
			foreach ($productTypes as $productType) {
				$this->productRepo->generateDummyUserProduct($user, $productType);
			}
		}
		return $users;
	}
}
