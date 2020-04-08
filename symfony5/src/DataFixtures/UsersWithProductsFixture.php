<?php
/**
 * #43 Fill test tables. before executing tests, using `./test.sh`.
 */
namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use \App\Entity\User;
use \App\Entity\Product;

class UsersWithProductsFixture extends Fixture
{

	private $entityManager;

	public function load(ObjectManager $manager)
	{
		$this->entityManager = $manager;
		$user = $this->insertUsersAndProds(10);
	}

	/**
	 * #38 Create 3 users with 1 mug and 1 shirt.
	 * #43 Moved from /home/j/Desktop/www/pr1-symfony5-api/symfony5/tests/Order/OrderUnitTest.php
	 * 
	 * @return User array
	 */
	private function insertUsersAndProds(int $count = 1)
	{
		// #38 Create 3 users.
		$users = [];
		for ($i = 0; $i < $count; $i++) {

			$users[$i] = $user = $this->createUser($i);

			// #38 Create 1 mug and 1 shirt for each user.
			$productTypes = ['t-shirt', 'mug'];
			foreach ($productTypes as $productType) {
				$this->createUserProduct($user, $productType);
			}
		}
		return $users;
	}

	/**
	 * #40 Create a user.
	 * 
	 * @param type $i
	 * @return User
	 */
	private function createUser($i): User
	{
		$user = new User();
		$user->setName(rand());
		$user->setSurname($i + 1);
		$user->setBalance(10000);
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		return $user;
	}

	/**
	 * #40 Create a product.
	 * 
	 * @param User $user
	 * @param string $productType
	 * @return Product
	 */
	private function createUserProduct(User $user, string $productType): Product
	{
		$product = new Product();
		$product->setOwnerId($user->getId());
		$product->setType($productType);
		$product->setTitle($user->getName() . ' ' . $user->getSurname() . ' ' . $productType);
		$product->setSku($user->getName() . ' ' . $user->getSurname() . ' ' . $productType);
		$product->setCost(100);
		$this->entityManager->persist($product);
		$this->entityManager->flush();
		return $product;
	}
}
