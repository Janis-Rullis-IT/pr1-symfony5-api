<?php

namespace App\Tests\Order;

use App\User\UserWihProductsGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * #40 PUT /users/{customerId}/order/shipping .
 */
class OrderShippingTest extends WebTestCase
{
    private $impossibleInt = 3147483648;
    private $entityManager;
    private $client;
    private $userWithProductsGenerator;
    private $ship_to_address = [
            'name' => 'John',
            'surname' => 'Doe',
            'street' => 'Palm street 25-7',
            'state' => 'California',
            'zip' => '60744',
            'country' => 'US',
            'phone' => '+1 123 123 123',
            'is_express' => true,
    ];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->c = $this->client->getContainer();
        $this->entityManager = $this->c->get('doctrine')->getManager();
        $this->userWithProductsGenerator = $this->c->get('test.'.UserWihProductsGenerator::class);
    }

    /**
     * #40 Invalid customer.
     */
    public function testInvalidCustomer()
    {
        $customerId = $this->impossibleInt;
        $uri = '/users/'.$customerId.'/order/shipping';
        $data = $this->ship_to_address;
        $this->client->request('PUT', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(['id' => 'invalid user'], $responseBody);
    }

    /**
     * #40 Invalid missing data.
     */
    public function testMissingData()
    {
        $customerId = $this->impossibleInt;
        $uri = '/users/'.$customerId.'/order/shipping';
        $data = [];
        $this->client->request('PUT', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        foreach (\App\Entity\Order::$requireds as $key => $val) {
            $this->assertEquals(["'".$val."' field is missing."], $responseBody[$val]);
        }
    }

    /**
     * #40 Invalid missing field.
     */
    public function testMissingField()
    {
        $customerId = $this->impossibleInt;
        $uri = '/users/'.$customerId.'/order/shipping';
        $data = $this->ship_to_address;
        unset($data['is_express']);
        $this->client->request('PUT', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(['is_express' => ["'is_express' field is missing."]], $responseBody);
    }

    /**
     * #40 Valid request.
     */
    public function testValidRequest()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];

        $customerId = $user->getId();
        $productId = $user->getProducts()[0]->getId();
        $this->client->request('POST', '/users/'.$customerId.'/cart/'.$productId);

        $uri = '/users/'.$customerId.'/order/shipping';
        $data = $this->ship_to_address;
        $this->client->request('PUT', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('y', $responseBody['is_domestic']);
        $this->assertEquals('y', $responseBody['is_express']);
        $this->assertEquals(1000, $responseBody['shipping_cost'], '#40 Express costs 10$.');
        $this->assertEquals($user->getProducts()[0]->getCost(), $responseBody['product_cost']);
        $this->assertEquals(1000 + $user->getProducts()[0]->getCost(), $responseBody['total_cost']);

        unset($data['is_express']);
        foreach ($data as $key => $val) {
            $this->assertEquals($val, $responseBody[$key]);
        }
    }
}
