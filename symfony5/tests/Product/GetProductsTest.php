<?php

namespace App\Tests\Product;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetProductsTest extends WebTestCase
{
    private $impossibleInt = 3147483648;

    public function test_get_products_valid_user()
    {
        $client = static::createClient();

        // #40 Prepare a user and a product.
        $client->request('POST', '/users', [], [], ['CONTENT_TYPE' => 'application/json'], '{"name":"John","surname":"Doe"}');
        $responseUser = json_decode($client->getResponse()->getContent(), true);
        $client->request('POST', '/users/'.$responseUser['id'].'/products', [], [], ['CONTENT_TYPE' => 'application/json'], '{"type":"t-shirt","title":"aware-wolf", "sku":"100-abc-1000-'.$responseUser['id'].'", "cost":1000}');
        $responseProduct = json_decode($client->getResponse()->getContent(), true);

        $client->request('GET', '/users/'.$responseProduct['ownerId'].'/products');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseBody);
        $this->assertIsArray($responseBody[0]);
        $this->assertEquals($responseBody[0][Product::ID], $responseProduct['id']);
        $this->assertEquals($responseBody[0][Product::OWNER_ID], $responseProduct['ownerId']);
        $this->assertEquals($responseBody[0][Product::TYPE], $responseProduct['type']);
        $this->assertEquals($responseBody[0][Product::TITLE], $responseProduct['title']);
        $this->assertEquals($responseBody[0][Product::SKU], $responseProduct['sku']);
        $this->assertEquals($responseBody[0][Product::COST], $responseProduct['cost']);
    }

    public function test_get_products_invalid_user()
    {
        $client = static::createClient();

        $client->request('GET', '/users/'.$this->impossibleInt.'/products');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), true);
        $this->assertNull($responseBody);
    }
}
