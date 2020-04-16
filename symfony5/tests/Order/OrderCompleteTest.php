<?php

namespace App\Tests\Order;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\User;
use App\Service\User\UserWihProductsGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * #40 /users/{customerId}/order/complete.
 */
class OrderCompleteTest extends WebTestCase
{
    private $impossibleInt = 3147483648;
    private $entityManager;
    private $userWithProductsGenerator;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->c = $this->client->getContainer();
        $this->entityManager = $this->c->get('doctrine')->getManager();
        $this->userWithProductsGenerator = $this->c->get('test.'.UserWihProductsGenerator::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testInvalidCustomer()
    {
        $uri = '/users/'.$this->impossibleInt.'/order/complete';
        $this->client->request('PUT', $uri);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertEquals([Order::ID => 'invalid user'], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testShippingNotSet()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $this->client->request('POST', '/users/'.$user->getId().'/cart/'.$user->getProducts()[0]->getId());
        $this->client->request('PUT', '/users/'.$user->getId().'/order/complete');

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals([Order::SHIPPING => [Order::MUST_HAVE_SHIPPING_SET]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testNoProducts()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $data = Order::VALID_SHIPPING_EXAMPLE;
        $this->client->request('PUT', '/users/'.$user->getId().'/order/shipping', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->client->request('PUT', '/users/'.$user->getId().'/order/complete');

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals([Order::PRODUCTS => [Order::MUST_HAVE_PRODUCTS]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testInsufficientFunds()
    {
        $balance = 0;
        $user = $this->userWithProductsGenerator->generate(1, $balance)[0];
        $this->client->request('POST', '/users/'.$user->getId().'/cart/'.$user->getProducts()[0]->getId());
        $data = Order::VALID_SHIPPING_EXAMPLE;
        $this->client->request('PUT', '/users/'.$user->getId().'/order/shipping', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->client->request('PUT', '/users/'.$user->getId().'/order/complete');

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals([User::BALANCE => [User::INSUFFICIENT_FUNDS]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testInsufficientFunds2()
    {
        $balance = 1000;
        $user = $this->userWithProductsGenerator->generate(1, $balance)[0];
        $this->client->request('POST', '/users/'.$user->getId().'/cart/'.$user->getProducts()[0]->getId());
        $data = Order::VALID_SHIPPING_EXAMPLE;
        $this->client->request('PUT', '/users/'.$user->getId().'/order/shipping', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->client->request('PUT', $uri = '/users/'.$user->getId().'/order/complete');

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals([User::BALANCE => [User::INSUFFICIENT_FUNDS]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testValidRequest()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $product = $user->getProducts()[0];
        $productId = $product->getId();
        $this->client->request('POST', '/users/'.$user->getId().'/cart/'.$productId);
        $this->client->request('PUT', '/users/'.$user->getId().'/order/shipping', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(Order::VALID_SHIPPING_EXAMPLE));
        $this->client->request('PUT', '/users/'.$user->getId().'/order/complete');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(Order::COMPLETED, $responseBody[Order::STATUS]);
        $this->assertEquals($product->getCost(), $responseBody[Order::PRODUCT_COST]);
        $this->assertEquals(1000, $responseBody[Order::SHIPPING_COST]);

        $totalCost = $product->getCost() + 1000;
        $this->assertEquals($totalCost, $responseBody[Order::TOTAL_COST]);
        $userUpdated = $this->entityManager->find(User::class, $user->getId());
        $this->assertEquals($user->getBalance() - $totalCost, $userUpdated->getBalance(), '#40 User\'s balance must be reduced correctly.');

        $data = Order::VALID_SHIPPING_EXAMPLE;
        $data['name'] = 'Hue';
        $this->client->request('POST', '/users/'.$user->getId().'/cart/'.$productId);
        $this->client->request('PUT', '/users/'.$user->getId().'/order/shipping', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $responseBody2 = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan($responseBody[Order::ID], $responseBody2[Order::ID], '#40 Add a new product to the cart and make sure that the order\'s ID is different');

        $this->client->request('GET', '/users/'.$this->impossibleInt.'/orders/'.$responseBody2[Order::ID]);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertEquals([Order::ID => 'Invalid order.'], json_decode($this->client->getResponse()->getContent(), true));

        $this->client->request('GET', '/users/'.$user->getId().'/orders/'.$this->impossibleInt);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertEquals([Order::ID => 'Invalid order.'], json_decode($this->client->getResponse()->getContent(), true));

        $this->client->request('GET', '/users/'.$user->getId().'/orders/'.$responseBody2[Order::ID]);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseOrder = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($responseBody2[Order::ID], $responseOrder[Order::ID]);
        $this->assertEquals($responseOrder[Order::PRODUCTS][0][OrderProduct::PRODUCT_ID], $productId);

        $this->client->request('GET', '/users/'.$user->getId().'/orders');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseOrder = json_decode($this->client->getResponse()->getContent(), true)[0];
        $this->assertEquals($responseOrder[Order::PRODUCTS][0][OrderProduct::PRODUCT_ID], $productId);
    }
}
