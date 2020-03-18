<?php
namespace App\Tests;

use \App\Entity\User;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * #40 POST ​/users​/{customerId}​/v2​/cart​/{productId}
 */
class OrderProductTest extends WebTestCase
{

	private $impossibleInt = 3147483648;
	private $entityManager;

	/**
	 * #40 Invalid parameters.
	 */
	public function testInvalidequest()
	{
		$client = static::createClient();

		$customerId = $this->impossibleInt;
		$productId = $this->impossibleInt;
		$uri = '/users/' . $customerId . '/v2/cart/' . $productId;
		
		$client->request('POST', $uri);
		$this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
		$responseBody = json_decode($client->getResponse()->getContent(), TRUE);
		$this->assertEquals(['id' => 'invalid user'], $responseBody);
	}

	/**
	 * #40 Invalid user, valid product.
	 */
	public function testInvalidUser()
	{
		$client = static::createClient();
		$user = $this->insertUsersAndProds($client)[0];

		$customerId = $this->impossibleInt;
		$productId = $user->products[0]->getId();
		$uri = '/users/' . $customerId . '/v2/cart/' . $productId;
		
		$client->request('POST', $uri);
		$this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
		$responseBody = json_decode($client->getResponse()->getContent(), TRUE);
		$this->assertEquals(['id' => 'invalid user'], $responseBody);
	}

	/**
	 * #40 Invalid product, valid user.
	 */
	public function testInvalidProduct()
	{
		$client = static::createClient();
		$user = $this->insertUsersAndProds($client)[0];

		$customerId = $user->getId();
		$productId = $this->impossibleInt;
		$uri = '/users/' . $customerId . '/v2/cart/' . $productId;
		
		$client->request('POST', $uri);
		$this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
		$responseBody = json_decode($client->getResponse()->getContent(), TRUE);
		$this->assertEquals(['id' => 'Invalid product.'], $responseBody);
	}
	
	/**
	 * #40 Valid request.
	 */
	public function testValidRequest()
	{
		$client = static::createClient();
		$user = $this->insertUsersAndProds($client)[0];

		$customerId = $user->getId();
		$productId = $user->products[0]->getId();
		$uri = '/users/' . $customerId . '/v2/cart/' . $productId;
		
		$client->request('POST', $uri);
		$this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
		$responseBody = json_decode($client->getResponse()->getContent(), TRUE);
		$this->assertEquals(['id' => 'Invalid product.'], $responseBody);
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
