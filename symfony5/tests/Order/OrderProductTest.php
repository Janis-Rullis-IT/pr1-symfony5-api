<?php

namespace App\Tests\Order;

use App\Interfaces\IUserRepo;
use App\Service\User\UserWihProductsGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * #40 POST ​/users​/{customerId}​/cart​/{productId}.
 */
class OrderProductTest extends WebTestCase
{
    private $impossibleInt = 3147483648;
    private $entityManager;
    private $client;
    private $userWithProductsGenerator;
    private $userRepo;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->c = $this->client->getContainer();
        $this->entityManager = $this->c->get('doctrine')->getManager();
        $this->userWithProductsGenerator = $this->c->get('test.'.UserWihProductsGenerator::class);
        $this->userRepo = $this->c->get('test.'.IUserRepo::class);
    }

    /**
     * #40 Invalid parameters.
     */
    public function testInvalidequest()
    {
        $customerId = $this->impossibleInt;
        $productId = $this->impossibleInt;
        $uri = '/users/'.$customerId.'/cart/'.$productId;

        $this->client->request('POST', $uri);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(['id' => 'invalid user'], $responseBody);
    }

    /**
     * #40 Invalid user, valid product.
     */
    public function testInvalidUser()
    {
        $user = $this->userRepo->getUserWithProducts();

        $customerId = $this->impossibleInt;
        $productId = $user->getProducts()[0]->getId();
        $uri = '/users/'.$customerId.'/cart/'.$productId;

        $this->client->request('POST', $uri);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(['id' => 'invalid user'], $responseBody);
    }

    /**
     * #40 Invalid product, valid user.
     */
    public function testInvalidProduct()
    {
        $user = $this->userRepo->getUserWithProducts();

        $customerId = $user->getId();
        $productId = $this->impossibleInt;
        $uri = '/users/'.$customerId.'/cart/'.$productId;

        $this->client->request('POST', $uri);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(['id' => 'Invalid product.'], $responseBody);
    }

    /**
     * #40 Valid request.
     */
    public function testValidRequest()
    {
        $user = $this->userRepo->getUserWithProducts();

        $customerId = $user->getId();
        $productId = $user->getProducts()[0]->getId();
        $uri = '/users/'.$customerId.'/cart/'.$productId;

        $this->client->request('POST', $uri);
        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertNotEmpty($responseBody['id']);
        $this->assertEquals($productId, $responseBody['product_id']);
        $this->assertEquals($customerId, $responseBody['customer_id']);

        // #40 More thorough tests regarding this are located in OrderProductUnitTest.
    }
}
