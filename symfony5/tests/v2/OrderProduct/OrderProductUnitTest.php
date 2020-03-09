<?php
namespace App\Tests\v2\OrderProduct;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use \App\Entity\User;
use \App\Entity\Product;
use \App\Entity\v2\OrderProduct;
use \App\v2\OrderProductCreator;
use \App\Interfaces\v2\IOrderRepo;

/**
 * #38 Test that the order product data is stored in the database correctly.
 * Test v2 functionality: `vendor/bin/phpunit tests/v2/`
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
		$this->orderRepo = $container->get('test.' . IOrderRepo::class);

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
				"customer_id" => ["'customer_id' field is missing."], "product_id" => ["'product_id' field is missing."]]]);

		// T-shirt / US / Standard / First.
		$customerId = $users[2]->getId() + 1000000;
		$productId = $users[1]->products[0]->getId() + 100000;
		$invalidCustomerAndProduct = $this->orderProductCreator->handle(['customer_id' => $customerId, "product_id" => $productId]);
		$expected = ['status' => false, 'data' => null, 'errors' => ["customer_id" => ["Invalid 'customer_id'."], "product_id" => ["Invalid 'product_id'."]]];
		$this->assertEquals($invalidCustomerAndProduct, $expected);

		$customerId = $users[2]->getId() + 1000000;
		$productId = $users[1]->products[0]->getId();
		$invalidCustomer = $this->orderProductCreator->handle(['customer_id' => $customerId, "product_id" => $productId]);
		$expected = ['status' => false, 'data' => null, 'errors' => ["customer_id" => ["Invalid 'customer_id'."]]];
		$this->assertEquals($invalidCustomer, $expected);

		$customerId = $users[2]->getId();
		$productId = $users[1]->products[0]->getId() + 1000000;
		$invalidProduct = $this->orderProductCreator->handle(['customer_id' => $customerId, "product_id" => $productId]);
		$expected = ['status' => false, 'data' => null, 'errors' => ["product_id" => ["Invalid 'product_id'."]]];
		$this->assertEquals($invalidProduct, $expected);

		// #38 #36 Create and get customer's draft order.
		// TODO: This probably should be moved to a separate test file.
		$this->assertNull($this->orderRepo->getCurrentDraft($users[2]->getId()), '#36 #38 New customer shouldnt have a draft order.');
		$draftOrder = $this->orderRepo->insertIfNotExist($users[2]->getId());
		$this->assertNotNull($draftOrder, '#36 #38 A draft order should be created if it doesnt exist.');
		$this->assertEquals($this->orderRepo->getCurrentDraft($users[2]->getId())->getId(), $draftOrder->getId(), '#36 #38 Should find an existing one.');
		$this->assertEquals($this->orderRepo->getCurrentDraft($users[2]->getId())->getId(), $this->orderRepo->insertIfNotExist($users[2]->getId())->getId(), "#36 #38 A new draft order shouldnt be created if there is already one.");

		$customerId = $users[2]->getId();
		$productId = $users[1]->products[0]->getId();
		$validProduct = $this->orderProductCreator->handle(['customer_id' => $customerId, "product_id" => $productId]);
		$this->assertEquals($validProduct['errors'], []);
		$this->assertEquals($validProduct['status'], true);
		$this->assertEquals($validProduct['data']->getCustomerId(), $customerId);
		$this->assertEquals($validProduct['data']->getSellerId(), $users[1]->getId());
		$this->assertEquals($validProduct['data']->getSellerTitle(), $users[1]->getName() . ' ' . $users[1]->getSurname());
		$this->assertEquals($validProduct['data']->getProductId(), $productId);
		$this->assertEquals($validProduct['data']->getProductTitle(), $users[1]->products[0]->getTitle());
		$this->assertEquals($validProduct['data']->getProductCost(), $users[1]->products[0]->getCost());
		$this->assertEquals($validProduct['data']->getProductType(), $users[1]->products[0]->getType());
		$this->assertTrue($validProduct['data']->getId() > 0);
		$this->assertTrue($validProduct['data']->getOrderId() > 0);
		
		// #38 #36 TODO: Add more use cases when work on the #39.
		// #38 #36 TODO: Decide what to do with the existing tests that doesn't use DB. 
		// On one hand they are currently broken and on the other hand they should be updated
		// to use DB.
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
