<?php
namespace App\Tests\Product;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetProductTest extends WebTestCase
{

	public function test_valid_owner_valid_product()
	{
		$client = static::createClient();
		
		// #40 Prepare user and a product.
		$client->request('POST', '/users', [], [], ['CONTENT_TYPE' => 'application/json'], '{"name":"John","surname":"Doe"}');
		$responseUser = json_decode($client->getResponse()->getContent(), TRUE);
		$client->request('POST', '/users/' . $responseUser['id'] . '/products', [], [], ['CONTENT_TYPE' => 'application/json'], '{"type":"t-shirt","title":"aware-wolf", "sku":"100-abc-1000-' . $responseUser['id'] . '", "cost":1000}');
		$responseProduct = json_decode($client->getResponse()->getContent(), TRUE);

		$client->request('GET', '/users/' . $responseProduct['ownerId'] . '/products/' . $responseProduct['id']);
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
		$this->assertEquals($responseBody[Product::ID], $responseProduct['id']);
		$this->assertEquals($responseBody[Product::OWNER_ID], $responseProduct['ownerId']);
		$this->assertEquals($responseBody[Product::TYPE], $responseProduct['type']);
		$this->assertEquals($responseBody[Product::TITLE], $responseProduct['title']);
		$this->assertEquals($responseBody[Product::SKU], $responseProduct['sku']);
		$this->assertEquals($responseBody[Product::COST], $responseProduct['cost']);
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
