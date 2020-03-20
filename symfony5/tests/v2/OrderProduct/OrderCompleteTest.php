<?php
namespace App\Tests;

use \App\Entity\User;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use \App\Entity\v2\Order;
use \App\Entity\v2\OrderProduct;

/**
 * #40 PUT /users/v2/{customerId}/order/complete
 */
class OrderCompleteTest extends WebTestCase
{

	private $impossibleInt = 3147483648;
	private $entityManager;
	private $ship_to_address = [
		"name" => "John",
		"surname" => "Doe",
		"street" => "Palm street 25-7",
		"state" => "California",
		"zip" => "60744",
		"country" => "US",
		"phone" => "+1 123 123 123",
		"is_express" => true
	];

	/**
	 * #40 Invalid customer.
	 */
	public function testInvalidCustomer()
	{
		$client = static::createClient();

		$customerId = $this->impossibleInt;
		$uri = '/users/v2/' . $customerId . '/order/complete';
		$client->request('PUT', $uri);
		$this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
		$responseBody = json_decode($client->getResponse()->getContent(), TRUE);
		$this->assertEquals(['id' => "invalid user"], $responseBody);
	}

	/**
	 * #40 Shipping not set.
	 */
	public function testShippingNotSet()
	{
		$client = static::createClient();
		$user = $this->insertUsersAndProds($client)[0];

		$customerId = $user->getId();
		$productId = $user->products[0]->getId();
		$client->request('POST', '/users/v2/' . $customerId . '/cart/' . $productId);

		$uri = '/users/v2/' . $customerId . '/order/complete';
		$client->request('PUT', $uri);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
		$responseBody = json_decode($client->getResponse()->getContent(), TRUE);
		$this->assertEquals([Order::SHIPPING => [Order::MUST_HAVE_SHIPPING_SET]], $responseBody);
	}

	/**
	 * #40 Not products.
	 */
	public function testNoProducts()
	{
		$client = static::createClient();
		$user = $this->insertUsersAndProds($client)[0];

		$customerId = $user->getId();
		$productId = $user->products[0]->getId();

		$uri = '/users/v2/' . $customerId . '/order/shipping';
		$data = $this->ship_to_address;
		$client->request('PUT', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

		$uri = '/users/v2/' . $customerId . '/order/complete';
		$client->request('PUT', $uri);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
		$responseBody = json_decode($client->getResponse()->getContent(), TRUE);
		$this->assertEquals([Order::PRODUCTS => [Order::MUST_HAVE_PRODUCTS]], $responseBody);
	}

	/**
	 * #40 Insufficient funds.
	 */
	public function testInsufficientFunds()
	{
		$client = static::createClient();
		$balance = 0;
		$user = $this->insertUsersAndProds($client, 1, $balance)[0];

		$customerId = $user->getId();
		$productId = $user->products[0]->getId();
		$client->request('POST', '/users/v2/' . $customerId . '/cart/' . $productId);

		$uri = '/users/v2/' . $customerId . '/order/shipping';
		$data = $this->ship_to_address;
		$client->request('PUT', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

		$uri = '/users/v2/' . $customerId . '/order/complete';
		$client->request('PUT', $uri);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
		$responseBody = json_decode($client->getResponse()->getContent(), TRUE);
		$this->assertEquals([User::BALANCE => [User::INSUFFICIENT_FUNDS]], $responseBody);
	}

	/**
	 * #40 Insufficient funds.
	 */
	public function testInsufficientFunds2()
	{
		$client = static::createClient();
		$balance = 1000;
		$user = $this->insertUsersAndProds($client, 1, $balance)[0];

		$customerId = $user->getId();
		$productId = $user->products[0]->getId();
		$client->request('POST', '/users/v2/' . $customerId . '/cart/' . $productId);

		$uri = '/users/v2/' . $customerId . '/order/shipping';
		$data = $this->ship_to_address;
		$client->request('PUT', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

		$uri = '/users/v2/' . $customerId . '/order/complete';
		$client->request('PUT', $uri);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
		$responseBody = json_decode($client->getResponse()->getContent(), TRUE);
		$this->assertEquals([User::BALANCE => [User::INSUFFICIENT_FUNDS]], $responseBody);
	}

	/**
	 * #40 A valid request.
	 */
	public function testValidRequest()
	{
		$client = static::createClient();
		$user = $this->insertUsersAndProds($client)[0];

		$customerId = $user->getId();
		$product = $user->products[0];
		$productId = $product->getId();
		$client->request('POST', '/users/v2/' . $customerId . '/cart/' . $productId);
		$client->request('PUT', '/users/v2/' . $customerId . '/order/shipping', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($this->ship_to_address));
		$client->request('PUT', '/users/v2/' . $customerId . '/order/complete');
		$this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
		$responseBody = json_decode($client->getResponse()->getContent(), TRUE);
		$this->assertEquals(Order::COMPLETED, $responseBody[Order::STATUS]);
		$this->assertEquals($product->getCost(), $responseBody[Order::PRODUCT_COST]);
		$this->assertEquals(1000, $responseBody[Order::SHIPPING_COST]);
		$totalCost = $product->getCost() + 1000;
		$this->assertEquals($totalCost, $responseBody[Order::TOTAL_COST]);
		$userUpdated = $this->entityManager->find(User::class, $customerId);
		$this->assertEquals($user->getBalance() - $totalCost, $userUpdated->getBalance(), '#40 User\'s balance must be reduced correctly.');

		$data = $this->ship_to_address;
		$data['name'] = 'Hue';
		$client->request('POST', '/users/v2/' . $customerId . '/cart/' . $productId);
		$client->request('PUT', '/users/v2/' . $customerId . '/order/shipping', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
		$responseBody2 = json_decode($client->getResponse()->getContent(), TRUE);
		$this->assertGreaterThan($responseBody[Order::ID], $responseBody2[Order::ID], '#40 Add a new product to the cart and make sure that the order\'s ID is different');

		// #40 Get user's order.
		$client->request('GET', '/users/' . $customerId . '/orders/' . $responseBody2[Order::ID]);
		$this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
		$responseOrder = json_decode($client->getResponse()->getContent(), TRUE);
		$this->assertEquals($responseBody2[Order::ID], $responseOrder[Order::ID]);
		$this->assertEquals($responseOrder[Order::PRODUCTS][0][OrderProduct::PRODUCT_ID], $productId);
	}

	/**
	 * #38 Create 3 users with 1 mug and 1 shirt.
	 * 
	 * TODO: Replace this approach with fixtures or creators that are designed not just for access from controllers.
	 * 
	 * @return User array
	 */
	private function insertUsersAndProds($client, int $count = 1, int $balance = 10000, $productTypes = ['t-shirt', 'mug'])
	{
		$this->c = $client->getContainer();
		$this->entityManager = $this->c->get('doctrine')->getManager();

		// #38 Create 3 users.
		$users = [];
		for ($i = 0; $i < $count; $i++) {

			$users[$i] = $user = $this->createUser($i, $balance);

			// #38 Create 1 mug and 1 shirt for each user.
			$user->products = [];
			foreach ($productTypes as $productType) {
				$user->products[] = $this->createUserProduct($user, $productType);
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
	private function createUser($i, int $balance = 10000): User
	{
		$user = new User();
		$user->setName(rand());
		$user->setSurname($i + 1);
		$user->setBalance($balance);
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
