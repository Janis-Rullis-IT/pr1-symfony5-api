<?php

namespace App\Tests;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetProductsTest extends WebTestCase
{
    public function test_get_products_valid_user()
    {
        $client = static::createClient();

        $client->request('GET', '/users/1/products');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertIsArray($responseBody);
        $this->assertIsArray($responseBody[0]);
        $this->assertEquals($responseBody[0][Product::ID], 1);
        $this->assertEquals($responseBody[0][Product::OWNER_ID], 1);
        $this->assertEquals($responseBody[0][Product::TYPE], "t-shirt");
        $this->assertEquals($responseBody[0][Product::TITLE], "much shirt, such style!");
        $this->assertEquals($responseBody[0][Product::SKU], "100-abc-999");
        $this->assertEquals($responseBody[0][Product::COST], 1000);
    }

    public function test_get_products_invalid_user()
    {
        $client = static::createClient();

        $client->request('GET', '/users/10000/products');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertNull($responseBody);
    }
}
