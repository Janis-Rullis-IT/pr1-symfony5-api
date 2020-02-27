<?php

namespace App\Tests;

use App\Entity\Order;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrderCreatorTest extends WebTestCase
{
    public function test_valid_domestic_order()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users/1/orders',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '
            {
                "shipToAddress" : {
                    "name" : "John",
                    "surname" : "Doe",
                    "street" : "Palm street 25-7",
                    "state" : "California",
                    "zip" : "60744",
                    "country" : "US",
                    "phone" : "+1 123 123 123"
                },
                "lineItems" : [
                    {"id" : 1, "quantity" : 2}
                ]
            }'
        );

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        /* see if keys exists */
        $this->assertArrayHasKey(Order::SHIPPING_DATA, $responseBody);
        $this->assertArrayHasKey(Order::LINE_ITEMS, $responseBody);
        $this->assertArrayHasKey(Order::INFO, $responseBody);

        $this->assertArrayHasKey(Order::OWNER_NAME, $responseBody[Order::SHIPPING_DATA]);
        $this->assertArrayHasKey(Order::OWNER_SURNAME, $responseBody[Order::SHIPPING_DATA]);
        $this->assertArrayHasKey(Order::STREET, $responseBody[Order::SHIPPING_DATA]);
        $this->assertArrayHasKey(Order::STATE, $responseBody[Order::SHIPPING_DATA]);
        $this->assertArrayHasKey(Order::ZIP, $responseBody[Order::SHIPPING_DATA]);
        $this->assertArrayHasKey(Order::COUNTRY, $responseBody[Order::SHIPPING_DATA]);
        $this->assertArrayHasKey(Order::PHONE, $responseBody[Order::SHIPPING_DATA]);

        $this->assertArrayHasKey(Product::ID, $responseBody[Order::LINE_ITEMS][0]);
        $this->assertArrayHasKey(Product::QUANTITY, $responseBody[Order::LINE_ITEMS][0]);

        $this->assertArrayHasKey(Order::ID, $responseBody[Order::INFO]);
        $this->assertArrayHasKey(Order::PRODUCTION_COST, $responseBody[Order::INFO]);
        $this->assertArrayHasKey(Order::SHIPPING_COST, $responseBody[Order::INFO]);
        $this->assertArrayHasKey(Order::TOTAL_COST, $responseBody[Order::INFO]);
        /* test key values */
        $this->assertEquals($responseBody[Order::SHIPPING_DATA][Order::OWNER_NAME], "John");
        $this->assertEquals($responseBody[Order::SHIPPING_DATA][Order::OWNER_SURNAME], "Doe");
        $this->assertEquals($responseBody[Order::SHIPPING_DATA][Order::STREET], "Palm Street 25-7");
        $this->assertEquals($responseBody[Order::SHIPPING_DATA][Order::STATE], "California");
        $this->assertEquals($responseBody[Order::SHIPPING_DATA][Order::ZIP], "60744");
        $this->assertEquals($responseBody[Order::SHIPPING_DATA][Order::COUNTRY], "US");
        $this->assertEquals($responseBody[Order::SHIPPING_DATA][Order::PHONE], "+1 123 123 123");
        $this->assertEquals($responseBody[Order::INFO][Order::ID], 2);
        $this->assertEquals($responseBody[Order::INFO][Order::PRODUCTION_COST], 2000);
        $this->assertEquals($responseBody[Order::INFO][Order::SHIPPING_COST], 150);
        $this->assertEquals($responseBody[Order::INFO][Order::TOTAL_COST], 2150);
        /* test value types */
        $this->assertIsString($responseBody[Order::SHIPPING_DATA][Order::OWNER_NAME]);
        $this->assertIsString($responseBody[Order::SHIPPING_DATA][Order::OWNER_SURNAME]);
        $this->assertIsString($responseBody[Order::SHIPPING_DATA][Order::STREET]);
        $this->assertIsString($responseBody[Order::SHIPPING_DATA][Order::STATE]);
        $this->assertIsString($responseBody[Order::SHIPPING_DATA][Order::ZIP]);
        $this->assertIsString($responseBody[Order::SHIPPING_DATA][Order::COUNTRY]);
        $this->assertIsString($responseBody[Order::SHIPPING_DATA][Order::PHONE]);
        $this->assertIsInt($responseBody[Order::INFO][Order::ID]);
        $this->assertIsInt($responseBody[Order::INFO][Order::PRODUCTION_COST]);
        $this->assertIsInt($responseBody[Order::INFO][Order::SHIPPING_COST]);
        $this->assertIsInt($responseBody[Order::INFO][Order::TOTAL_COST]);
    }

    public function test_valid_international_order()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users/1/orders',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '
            {
                "shipToAddress" : {
                    "name" : "John",
                    "surname" : "Doe",
                    "street" : "Palm street 25-7",
                    "state" : "California",
                    "zip" : "60744",
                    "country" : "Latvia",
                    "phone" : "+1 123 123 123"
                },
                "lineItems" : [
                    {"id" : 1, "quantity" : 2}
                ]
            }'
        );
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        /* see if keys exists */
        $this->assertArrayHasKey(Order::SHIPPING_DATA, $responseBody);
        $this->assertArrayHasKey(Order::LINE_ITEMS, $responseBody);
        $this->assertArrayHasKey(Order::INFO, $responseBody);

        $this->assertArrayHasKey(Order::OWNER_NAME, $responseBody[Order::SHIPPING_DATA]);
        $this->assertArrayHasKey(Order::OWNER_SURNAME, $responseBody[Order::SHIPPING_DATA]);
        $this->assertArrayHasKey(Order::STREET, $responseBody[Order::SHIPPING_DATA]);
        $this->assertArrayHasKey(Order::STATE, $responseBody[Order::SHIPPING_DATA]);
        $this->assertArrayHasKey(Order::ZIP, $responseBody[Order::SHIPPING_DATA]);
        $this->assertArrayHasKey(Order::COUNTRY, $responseBody[Order::SHIPPING_DATA]);
        $this->assertArrayHasKey(Order::PHONE, $responseBody[Order::SHIPPING_DATA]);

        $this->assertArrayHasKey(Product::ID, $responseBody[Order::LINE_ITEMS][0]);
        $this->assertArrayHasKey(Product::QUANTITY, $responseBody[Order::LINE_ITEMS][0]);

        $this->assertArrayHasKey(Order::ID, $responseBody[Order::INFO]);
        $this->assertArrayHasKey(Order::PRODUCTION_COST, $responseBody[Order::INFO]);
        $this->assertArrayHasKey(Order::SHIPPING_COST, $responseBody[Order::INFO]);
        $this->assertArrayHasKey(Order::TOTAL_COST, $responseBody[Order::INFO]);
        /* test key values */
        $this->assertEquals($responseBody[Order::SHIPPING_DATA][Order::OWNER_NAME], "John");
        $this->assertEquals($responseBody[Order::SHIPPING_DATA][Order::OWNER_SURNAME], "Doe");
        $this->assertEquals($responseBody[Order::SHIPPING_DATA][Order::STREET], "Palm Street 25-7");
        $this->assertEquals($responseBody[Order::SHIPPING_DATA][Order::STATE], "California");
        $this->assertEquals($responseBody[Order::SHIPPING_DATA][Order::ZIP], "60744");
        $this->assertEquals($responseBody[Order::SHIPPING_DATA][Order::COUNTRY], "Latvia");
        $this->assertEquals($responseBody[Order::SHIPPING_DATA][Order::PHONE], "+1 123 123 123");
        $this->assertEquals($responseBody[Order::INFO][Order::ID], 2);
        $this->assertEquals($responseBody[Order::INFO][Order::PRODUCTION_COST], 2000);
        $this->assertEquals($responseBody[Order::INFO][Order::SHIPPING_COST], 450);
        $this->assertEquals($responseBody[Order::INFO][Order::TOTAL_COST], 2450);
        /* test value types */
        $this->assertIsString($responseBody[Order::SHIPPING_DATA][Order::OWNER_NAME]);
        $this->assertIsString($responseBody[Order::SHIPPING_DATA][Order::OWNER_SURNAME]);
        $this->assertIsString($responseBody[Order::SHIPPING_DATA][Order::STREET]);
        $this->assertIsString($responseBody[Order::SHIPPING_DATA][Order::STATE]);
        $this->assertIsString($responseBody[Order::SHIPPING_DATA][Order::ZIP]);
        $this->assertIsString($responseBody[Order::SHIPPING_DATA][Order::COUNTRY]);
        $this->assertIsString($responseBody[Order::SHIPPING_DATA][Order::PHONE]);
        $this->assertIsInt($responseBody[Order::INFO][Order::ID]);
        $this->assertIsInt($responseBody[Order::INFO][Order::PRODUCTION_COST]);
        $this->assertIsInt($responseBody[Order::INFO][Order::SHIPPING_COST]);
        $this->assertIsInt($responseBody[Order::INFO][Order::TOTAL_COST]);
    }
}
