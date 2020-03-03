<?php

namespace App\Tests;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetProductTest extends WebTestCase
{
    public function test_valid_owner_valid_product()
    {
        $client = static::createClient();

        $client->request('GET', '/users/1/products/1');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);

        $this->assertIsArray($responseBody);
        /* see if keys exists */
        $this->assertArrayHasKey(Product::ID, $responseBody);
        $this->assertArrayHasKey(Product::OWNER_ID, $responseBody);
        $this->assertArrayHasKey(Product::TYPE, $responseBody);
        $this->assertArrayHasKey(Product::TITLE, $responseBody);
        $this->assertArrayHasKey(Product::SKU, $responseBody);
        $this->assertArrayHasKey(Product::COST, $responseBody);
        /* test key values */
        $this->assertEquals($responseBody[Product::ID], 1);
        $this->assertEquals($responseBody[Product::OWNER_ID], 1);
        $this->assertEquals($responseBody[Product::TYPE], "t-shirt");
        $this->assertEquals($responseBody[Product::TITLE], "much shirt, such style!");
        $this->assertEquals($responseBody[Product::SKU], "100-abc-999");
        $this->assertEquals($responseBody[Product::COST], 1000);
        /* test value types */
        $this->assertIsInt($responseBody[Product::ID]);
        $this->assertIsInt($responseBody[Product::OWNER_ID]);
        $this->assertIsString($responseBody[Product::TYPE]);
        $this->assertIsString($responseBody[Product::TITLE]);
        $this->assertIsString($responseBody[Product::SKU]);
        $this->assertIsInt($responseBody[Product::COST]);
    }

    public function test_valid_owner_invalid_product()
    {
        $client = static::createClient();

        $client->request('GET', '/users/1/products/100000');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);

        $this->assertNull($responseBody);
    }

    public function test_invalid_owner_invalid_product()
    {
        $client = static::createClient();

        $client->request('GET', '/users/100000/products/100000');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);

        $this->assertNull($responseBody);
    }
}
