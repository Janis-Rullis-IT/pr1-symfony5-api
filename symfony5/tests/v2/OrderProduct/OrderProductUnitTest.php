<?php
namespace App\Tests\v2\OrderProduct;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use \App\Entity\User;
use \App\Entity\Product;
use \App\Entity\v2\OrderProduct;
use App\v2\OrderProductCreator;

/**
 * #38 Test that the order product data is stored in the database correctly.
 */
class OrderProductUnitTest extends KernelTestCase
{

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $entityManager;
	private $orderProductCreator;

	protected function setUp(): void
	{
		// #38 Using services in tests https://www.tomasvotruba.com/blog/2018/05/17/how-to-test-private-services-in-symfony/ https://symfony.com/doc/current/service_container.html
		// "However, if a service has been marked as private, you can still 
		// alias it to access this service (via the alias)" `config/services.yaml` https://symfony.com/doc/current/service_container/alias_private.html#aliasing
		$kernel = self::bootKernel();
		$container = $kernel->getContainer();
		$this->orderProductCreator = $container->get('test.' . OrderProductCreator::class);

		// Using database in tests https://stackoverflow.com/a/52014145 https://symfony.com/doc/master/testing/database.html#functional-testing-of-a-doctrine-repository
		$this->entityManager = $container->get('doctrine')->getManager();

		// TODO: Truncate specific tables before each run.
	}

	/**
	 * #38 Test that the customer can add products to a cart (`order_product`).
	 */
	public function testAddProductsToCart()
	{
		$users = $this->insertUsersAndProds();

		$this->assertEquals($this->orderProductCreator->handle([]), ['status' => false, 'data' => null, 'errors' => [
				"customer_id" => "'customer_id' field is missing.", "product_id" => "'product_id' field is missing."]]);

		// T-shirt / US / Standard / First.
		dd('ok');

		// `SELECT `order_id`, `customer_id`, `seller_id`, `seller_title`, `product_id`, `product_title`, `product_cost`, `product_type` FROM `v2_order_product` WHERE 1`.
		// `SELECT `order_id`, `customer_id`, `seller_id`, `product_id` FROM `v2_order_product` WHERE 1`.
		$v2_order_product = array(
			array('order_id' => '1', 'customer_id' => $users[0], 'seller_id' => $users[2], 'product_id' => $users[0]->products[0]),
			array('order_id' => '1', 'customer_id' => $users[0], 'seller_id' => $users[2], 'product_id' => $users[0]->products[0]),
			array('order_id' => '1', 'customer_id' => $users[0], 'seller_id' => $users[2], 'product_id' => $users[0]->products[0]),
			array('order_id' => '1', 'customer_id' => $users[0], 'seller_id' => $users[2], 'product_id' => $users[0]->products[0]),
			array('order_id' => '2', 'customer_id' => $users[1], 'seller_id' => $users[0], 'product_id' => $users[1]->products[0]),
			array('order_id' => '2', 'customer_id' => $users[1], 'seller_id' => $users[0], 'product_id' => $users[1]->products[0]),
			array('order_id' => '2', 'customer_id' => $users[1], 'seller_id' => $users[0], 'product_id' => $users[1]->products[0]),
			array('order_id' => '2', 'customer_id' => $users[1], 'seller_id' => $users[0], 'product_id' => $users[1]->products[0]),
			array('order_id' => '3', 'customer_id' => $users[2], 'seller_id' => $users[1], 'product_id' => $users[2]->products[0]),
			array('order_id' => '3', 'customer_id' => $users[2], 'seller_id' => $users[1], 'product_id' => $users[2]->products[0]),
			array('order_id' => '3', 'customer_id' => $users[2], 'seller_id' => $users[1], 'product_id' => $users[2]->products[0]),
			array('order_id' => '3', 'customer_id' => $users[2], 'seller_id' => $users[1], 'product_id' => $users[2]->products[0])
		);

		$product2 = $this->entityManager
			->getRepository(OrderProduct::class)
			->findOneBy(array(), array('id' => 'DESC'), 0, 1);
		$this->assertEquals($product->getId(), $product2->getId());
		$this->assertEquals($product2->getIsDomestic(), 'y');
	}

	protected function tearDown(): void
	{
		parent::tearDown();

		// doing this is recommended to avoid memory leaks
		$this->entityManager->close();
		$this->entityManager = null;
	}

	/**
	 * #38 Create 3 users with 1 mug and 1 shirt.
	 * 
	 * TODO: Replace this approach with fixtures or creators that are designed not just for access from controllers.
	 * 
	 * @return User array
	 */
	private function insertUsersAndProds()
	{
		// #38 Create 3 users.
		$users = [];
		for ($i = 0; $i < 3; $i++) {

			$user = new User();
			$user->setName(rand());
			$user->setSurname($i + 1);
			$user->setBalance(1000);
			$this->entityManager->persist($user);
			$this->entityManager->flush();
			$users[$i] = $user;

			// #38 Create 1 mug and 1 shirt for each user.
			$user->products = [];
			$productTypes = ['t-shirt', 'mug'];
			foreach ($productTypes as $productType) {
				$product = new Product();
				$product->setOwnerId($user->getId());
				$product->setType($productType);
				$product->setTitle($user->getName() . ' ' . $user->getSurname() . ' ' . $productType);
				$product->setSku($user->getName() . ' ' . $user->getSurname() . ' ' . $productType);
				$product->setCost(100);
				$this->entityManager->persist($product);
				$this->entityManager->flush();
				$user->products[] = $product;
			}
		}
		return $users;
	}
}
