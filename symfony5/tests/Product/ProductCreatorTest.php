<?php

namespace App\Tests;

use App\Entity\Product;
use App\Validators\ProductValidators\ProductTypeValidator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;


class ProductCreatorTest extends WebTestCase
{
    public function test_valid_request_body()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users/1/products',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"type":"t-shirt","title":"aware-wolf", "sku":"100-abc-1000", "cost":1000}'
        );
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        /* see if keys exists */
        $this->assertArrayHasKey(Product::ID, $responseBody);
        $this->assertArrayHasKey(Product::OWNER_ID, $responseBody);
        $this->assertArrayHasKey(Product::TYPE, $responseBody);
        $this->assertArrayHasKey(Product::TITLE, $responseBody);
        $this->assertArrayHasKey(Product::SKU, $responseBody);
        $this->assertArrayHasKey(Product::COST, $responseBody);
        /* test key values */
        $this->assertEquals($responseBody[Product::ID], 2);
        $this->assertEquals($responseBody[Product::OWNER_ID], 1);
        $this->assertEquals($responseBody[Product::TYPE], "t-shirt");
        $this->assertEquals($responseBody[Product::TITLE], "aware-wolf");
        $this->assertEquals($responseBody[Product::SKU], "100-abc-1000");
        $this->assertEquals($responseBody[Product::COST], 1000);
        /* test value types */
        $this->assertIsInt($responseBody[Product::ID]);
        $this->assertIsInt($responseBody[Product::OWNER_ID]);
        $this->assertIsString($responseBody[Product::TYPE]);
        $this->assertIsString($responseBody[Product::TITLE]);
        $this->assertIsString($responseBody[Product::SKU]);
        $this->assertIsInt($responseBody[Product::COST]);
    }

    public function test_invalid_json_body()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users/1/products',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"type":"t-shirt",,,,,,,,,"title":"aware-wolf", "sku":"100-abc-1000", "cost":1000}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey('json', $responseBody);
        $this->assertEquals($responseBody['json'], 'Syntax error');
    }

    public function test_empty_body()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users/1/products',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            ''
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey('json', $responseBody);
        $this->assertEquals($responseBody['json'], 'Syntax error');
    }

    public function test_empty_json_object()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users/1/products',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(Product::TYPE, $responseBody);
        $this->assertEquals($responseBody[Product::TYPE][0], "type key not set");
        $this->assertArrayHasKey(Product::TITLE, $responseBody);
        $this->assertEquals($responseBody[Product::TITLE][0], "title key not set");
        $this->assertArrayHasKey(Product::SKU, $responseBody);
        $this->assertEquals($responseBody[Product::SKU][0], "sku key not set");
        $this->assertArrayHasKey(Product::COST, $responseBody);
        $this->assertEquals($responseBody[Product::COST][0], "cost key not set");
    }

    public function test_missing_type_key()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users/1/products',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"xxxx":"t-shirt","title":"aware-wolf", "sku":"100-abc-1000", "cost":1000}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(Product::TYPE, $responseBody);
        $this->assertEquals($responseBody[Product::TYPE][0], "type key not set");
    }

    public function test_invalid_type_key()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users/1/products',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"type":"invalidproduct","title":"aware-wolf", "sku":"100-abc-1000", "cost":1000}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(Product::TYPE, $responseBody);

        $productTypeValidator = new ProductTypeValidator();
        $this->assertEquals($responseBody[Product::TYPE][0], 'Invalid type');
    }

    public function test_missing_title_key()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users/1/products',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"type":"t-shirt","xxxxx":"aware-wolf", "sku":"100-abc-1000", "cost":1000}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(Product::TITLE, $responseBody);
        $this->assertEquals($responseBody[Product::TITLE][0], "title key not set");
    }

    public function test_invalid_title_key()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users/1/products',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"type":"t-shirt","title":"^^^aware-wolf^^^", "sku":"100-abc-1000", "cost":1000}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(Product::TITLE, $responseBody);
        $this->assertEquals($responseBody[Product::TITLE][0], Product::INVALID_TITLE);
    }

    public function test_missing_sku_key()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users/1/products',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"type":"t-shirt","title":"aware-wolf", "xxx":"100-abc-1000", "cost":1000}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(Product::SKU, $responseBody);
        $this->assertEquals($responseBody[Product::SKU][0], "sku key not set");
    }

    public function test_duplicate_sku_key()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users/1/products',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"type":"t-shirt","title":"aware-wolf", "sku":"100-abc-999", "cost":1000}'
        );

        $this->assertEquals(Response::HTTP_CONFLICT, $client->getResponse()->getStatusCode());
        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(Product::SKU, $responseBody);
        $this->assertEquals($responseBody[Product::SKU][0], Product::INVALID_SKU);
    }

    public function test_missing_cost_key()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users/1/products',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"type":"t-shirt","title":"aware-wolf", "sku":"100-abc-1000", "xxxx":1000}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(Product::COST, $responseBody);
        $this->assertEquals($responseBody[Product::COST][0], "cost key not set");
    }

    public function test_invalid_cost_key()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users/1/products',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"type":"t-shirt","title":"aware-wolf", "sku":"100-abc-1000", "cost":1000.00}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(Product::COST, $responseBody);
        $this->assertEquals($responseBody[Product::COST][0], "Invalid cost. It must be an integer describing price with smallest money unit");
    }

    public function test_multiple_errors_missing_type_key_and_invalid_title()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users/1/products',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"xxxx":"t-shirt","title":"$$$$$$$$$aware-wolf", "sku":"100-abc-999", "cost":1000}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(Product::TYPE, $responseBody);
        $this->assertEquals($responseBody[Product::TYPE][0], "type key not set");
        $this->assertArrayHasKey(Product::TITLE, $responseBody);
        $this->assertEquals($responseBody[Product::TITLE][0], Product::INVALID_TITLE);
    }
}
