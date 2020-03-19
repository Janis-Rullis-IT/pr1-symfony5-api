<?php
namespace App\Tests;

use \App\Entity\User;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * #40 POST /users/v2/{customerId}/order/shipping .
 */
class OrdertTest extends WebTestCase
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
		$uri = '/users/v2/' . $customerId . '/order/shipping';
		$data = $this->ship_to_address;
		$client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
		$this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
		$responseBody = json_decode($client->getResponse()->getContent(), TRUE);
		$this->assertEquals(['id' => "invalid user"], $responseBody);
	}

	/**
	 * #40 Invalid missing data.
	 */
	public function testMissingData()
	{
		$client = static::createClient();

		$customerId = $this->impossibleInt;
		$uri = '/users/v2/' . $customerId . '/order/shipping';
		$data = [];
		$client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
		$responseBody = json_decode($client->getResponse()->getContent(), TRUE);
		foreach (\App\Entity\v2\Order::$requireds as $key => $val) {
			$this->assertEquals(["'" . $val . "' field is missing."], $responseBody[$val]);
		}
	}

	/**
	 * #40 Invalid missing field.
	 */
	public function testMissingField()
	{
		$client = static::createClient();

		$customerId = $this->impossibleInt;
		$uri = '/users/v2/' . $customerId . '/order/shipping';
		$data = $this->ship_to_address;
		unset($data['is_express']);
		$client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
		$responseBody = json_decode($client->getResponse()->getContent(), TRUE);
		$this->assertEquals(['is_express' => ["'is_express' field is missing."]], $responseBody);
	}

	/**
	 * #40 Valid request.
	 */
	public function testValidRequest()
	{
		$client = static::createClient();
		$user = $this->insertUsersAndProds($client)[0];

		$customerId = $user->getId();
		$uri = '/users/v2/' . $customerId . '/order/shipping';
		$data = $this->ship_to_address;
		$client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
		$this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
		$responseBody = json_decode($client->getResponse()->getContent(), TRUE);

		$this->assertEquals($responseBody['is_domestic'], 'y');
		$this->assertEquals($responseBody['is_express'], 'y');
		foreach (["shipping_cost", "product_cost", "total_cost"] as $val) {
			$this->assertEquals(null, $responseBody[$val]);
		}

		unset($data['is_express']);
		foreach ($data as $key => $val) {
			$this->assertEquals($val, $responseBody[$key]);
		}
	}

	/**
	 * #38 Create 3 users with 1 mug and 1 shirt.
	 * 
	 * TODO: Replace this approach with fixtures or creators that are designed not just for access from controllers.
	 * 
	 * @return User array
	 */
	private function insertUsersAndProds($client, int $count = 1)
	{
		$this->c = $client->getContainer();
		$this->entityManager = $this->c->get('doctrine')->getManager();

		// #38 Create 3 users.
		$users = [];
		for ($i = 0; $i < $count; $i++) {

			$users[$i] = $user = $this->createUser($i);

			// #38 Create 1 mug and 1 shirt for each user.
			$user->products = [];
			$productTypes = ['t-shirt', 'mug'];
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
	private function createUser($i): User
	{
		$user = new User();
		$user->setName(rand());
		$user->setSurname($i + 1);
		$user->setBalance(1000);
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
