<?php
namespace App\User;

/**
 *  #53 Generate dummy users with products. Used in fixtures and tests.
 */
use App\Interfaces\IUserRepo;
use App\Interfaces\IProductRepo;
use Doctrine\ORM\EntityManagerInterface;

class UserWihProductsGenerator
{

	private $userRepo;
	private $productRepo;
	private $em;

	/**
	 * #53
	 * @param IUserRepo $userRepo
	 * @param IProductRepo $productRepo
	 */
	public function __construct(IUserRepo $userRepo, IProductRepo $productRepo, EntityManagerInterface $em)
	{
		$this->userRepo = $userRepo;
		$this->productRepo = $productRepo;
		$this->em = $em;
	}

	/**
	 * #53 Generate dummy users with products. Used in fixtures and tests.
	 * 
	 * @param int $count
	 * @return type
	 */
	public function generate(int $count = 1)
	{
		$userIds = [];
		for ($i = 0; $i < $count; $i++) {

			$user = $this->userRepo->generateDummyUser($i);
			$userIds[] = $user->getId();

			// #38 Create 1 mug and 1 shirt for each user.
			$productTypes = ['t-shirt', 'mug'];
			foreach ($productTypes as $productType) {
				$this->productRepo->generateDummyUserProduct($user, $productType);
			}
		}
		$this->em->clear();

		return $this->userRepo->findById($userIds);
	}
}
