<?php
namespace App\Tests\Order;

use \App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use \App\Entity\Order;
use \App\Entity\OrderProduct;
use App\Interfaces\IUserRepo;
use App\User\UserWihProductsGenerator;

/**
 * #40 PUT /users/{customerId}/order/complete
 */
class OrderCompleteTest extends WebTestCase
{

	private $impossibleInt = 3147483648;
	private $entityManager;
	private $userWithProductsGenerator;
	private $userRepo;
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

	protected function setUp(): void
	{
		$this->client = static::createClient();
		$this->c = $this->client->getContainer();
		$this->entityManager = $this->c->get('doctrine')->getManager();
		$this->userWithProductsGenerator = $this->c->get('test.' . UserWihProductsGenerator::class);
		$this->userRepo = $this->c->get('test.' . IUserRepo::class);
	}

	/**
	 * #40 Invalid customer.
	 */
	public function testInvalidCustomer()
	{
		$customerId = $this->impossibleInt;
		$uri = '/users/' . $customerId . '/order/complete';
		$this->client->request('PUT', $uri);
		$this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
		$responseBody = json_decode($this->client->getResponse()->getContent(), TRUE);
		$this->assertEquals(['id' => "invalid user"], $responseBody);
	}

	/**
	 * #40 Shipping not set.
	 */
	public function testShippingNotSet()
	{
		$user = $this->userWithProductsGenerator->generate(1)[0];

		$customerId = $user->getId();
		$productId = $user->getProducts()[0]->getId();
		$this->client->request('POST', '/users/' . $customerId . '/cart/' . $productId);

		$uri = '/users/' . $customerId . '/order/complete';
		$this->client->request('PUT', $uri);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
		$responseBody = json_decode($this->client->getResponse()->getContent(), TRUE);
		$this->assertEquals([Order::SHIPPING => [Order::MUST_HAVE_SHIPPING_SET]], $responseBody);
	}

	/**
	 * #40 Not products.
	 */
	public function testNoProducts()
	{
		$user = $this->userWithProductsGenerator->generate(1)[0];
		$customerId = $user->getId();
		$productId = $user->getProducts()[0]->getId();

		$uri = '/users/' . $customerId . '/order/shipping';
		$data = $this->ship_to_address;
		$this->client->request('PUT', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

		$uri = '/users/' . $customerId . '/order/complete';
		$this->client->request('PUT', $uri);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
		$responseBody = json_decode($this->client->getResponse()->getContent(), TRUE);
		$this->assertEquals([Order::PRODUCTS => [Order::MUST_HAVE_PRODUCTS]], $responseBody);
	}

	/**
	 * #40 Insufficient funds.
	 */
	public function testInsufficientFunds()
	{
		$balance = 0;
		$user = $this->userWithProductsGenerator->generate(1, $balance)[0];

		$customerId = $user->getId();
		$productId = $user->getProducts()[0]->getId();
		$this->client->request('POST', '/users/' . $customerId . '/cart/' . $productId);

		$uri = '/users/' . $customerId . '/order/shipping';
		$data = $this->ship_to_address;
		$this->client->request('PUT', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

		$uri = '/users/' . $customerId . '/order/complete';
		$this->client->request('PUT', $uri);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
		$responseBody = json_decode($this->client->getResponse()->getContent(), TRUE);
		$this->assertEquals([User::BALANCE => [User::INSUFFICIENT_FUNDS]], $responseBody);
	}

	/**
	 * #40 Insufficient funds.
	 */
	public function testInsufficientFunds2()
	{
		$balance = 1000;
		$user = $this->userWithProductsGenerator->generate(1, $balance)[0];

		$customerId = $user->getId();
		$productId = $user->getProducts()[0]->getId();
		$this->client->request('POST', '/users/' . $customerId . '/cart/' . $productId);

		$uri = '/users/' . $customerId . '/order/shipping';
		$data = $this->ship_to_address;
		$this->client->request('PUT', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

		$uri = '/users/' . $customerId . '/order/complete';
		$this->client->request('PUT', $uri);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
		$responseBody = json_decode($this->client->getResponse()->getContent(), TRUE);
		$this->assertEquals([User::BALANCE => [User::INSUFFICIENT_FUNDS]], $responseBody);
	}

	/**
	 * #40 A valid request.
	 */
	public function testValidRequest()
	{
		$user = $this->userWithProductsGenerator->generate(1)[0];

		$customerId = $user->getId();
		$product = $user->getProducts()[0];
		$productId = $product->getId();
		$this->client->request('POST', '/users/' . $customerId . '/cart/' . $productId);
		$this->client->request('PUT', '/users/' . $customerId . '/order/shipping', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($this->ship_to_address));
		$this->client->request('PUT', '/users/' . $customerId . '/order/complete');
		$this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
		$responseBody = json_decode($this->client->getResponse()->getContent(), TRUE);
		$this->assertEquals(Order::COMPLETED, $responseBody[Order::STATUS]);
		$this->assertEquals($product->getCost(), $responseBody[Order::PRODUCT_COST]);
		$this->assertEquals(1000, $responseBody[Order::SHIPPING_COST]);
		$totalCost = $product->getCost() + 1000;
		$this->assertEquals($totalCost, $responseBody[Order::TOTAL_COST]);
		$userUpdated = $this->entityManager->find(User::class, $customerId);
		$this->assertEquals($user->getBalance() - $totalCost, $userUpdated->getBalance(), '#40 User\'s balance must be reduced correctly.');

		$data = $this->ship_to_address;
		$data['name'] = 'Hue';
		$this->client->request('POST', '/users/' . $customerId . '/cart/' . $productId);
		$this->client->request('PUT', '/users/' . $customerId . '/order/shipping', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
		$responseBody2 = json_decode($this->client->getResponse()->getContent(), TRUE);
		$this->assertGreaterThan($responseBody[Order::ID], $responseBody2[Order::ID], '#40 Add a new product to the cart and make sure that the order\'s ID is different');

		// #40 Get user's order.
		$this->client->request('GET', '/users/' . $this->impossibleInt . '/orders/' . $responseBody2[Order::ID]);
		$this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
		$responseInvalidOrder = json_decode($this->client->getResponse()->getContent(), TRUE);
		$this->assertEquals(['id' => "Invalid order."], $responseInvalidOrder);

		$this->client->request('GET', '/users/' . $customerId . '/orders/' . $this->impossibleInt);
		$this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
		$responseInvalidOrder = json_decode($this->client->getResponse()->getContent(), TRUE);
		$this->assertEquals(['id' => "Invalid order."], $responseInvalidOrder);

		$this->client->request('GET', '/users/' . $customerId . '/orders/' . $responseBody2[Order::ID]);
		$this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
		$responseOrder = json_decode($this->client->getResponse()->getContent(), TRUE);
		$this->assertEquals($responseBody2[Order::ID], $responseOrder[Order::ID]);
		$this->assertEquals($responseOrder[Order::PRODUCTS][0][OrderProduct::PRODUCT_ID], $productId);

		$this->client->request('GET', '/users/' . $customerId . '/orders');
		$this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
		$responseOrders = json_decode($this->client->getResponse()->getContent(), TRUE);
		$responseOrder = $responseOrders[0];
		$this->assertEquals($responseOrder[Order::PRODUCTS][0][OrderProduct::PRODUCT_ID], $productId);
	}
}
