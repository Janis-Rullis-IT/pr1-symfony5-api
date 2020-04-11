<?php
namespace App\Tests\Product;

use App\Entity\Product;
use App\Validators\ProductValidators\ProductTypeValidator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProductCreatorTest extends WebTestCase
{

	private $impossibleInt = 3147483648;

	public function test_valid_request_body()
	{
		$client = static::createClient();

		// #40 Prepare a user and a product.
		$client->request('POST', '/users', [], [], ['CONTENT_TYPE' => 'application/json'], '{"name":"John","surname":"Doe"}');
		$responseUser = json_decode($client->getResponse()->getContent(), TRUE);
		$client->request('POST', '/users/' . $responseUser['id'] . '/products', [], [], ['CONTENT_TYPE' => 'application/json'], '{"type":"t-shirt","title":"aware-wolf", "sku":"100-abc-1000-' . $responseUser['id'] . '", "cost":1000}');

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
		$this->assertEquals($responseBody[Product::ID], $responseBody['id']);
		$this->assertEquals($responseBody[Product::OWNER_ID], $responseBody['ownerId']);
		$this->assertEquals($responseBody[Product::TYPE], $responseBody['type']);
		$this->assertEquals($responseBody[Product::TITLE], $responseBody['title']);
		$this->assertEquals($responseBody[Product::SKU], $responseBody['sku']);
		$this->assertEquals($responseBody[Product::COST], $responseBody['cost']);
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

		// #40 Prepare a user and a product.
		$client->request('POST', '/users', [], [], ['CONTENT_TYPE' => 'application/json'], '{"name":"John","surname":"Doe"}');
		$responseUser = json_decode($client->getResponse()->getContent(), TRUE);

		$client->request(
				'POST',
				'/users/' . $responseUser['id'] . '/products',
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

		// #40 Prepare a user and a product.
		$client->request('POST', '/users', [], [], ['CONTENT_TYPE' => 'application/json'], '{"name":"John","surname":"Doe"}');
		$responseUser = json_decode($client->getResponse()->getContent(), TRUE);

		$client->request(
				'POST',
				'/users/' . $responseUser['id'] . '/products',
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

		// #40 Prepare a user and a product.
		$client->request('POST', '/users', [], [], ['CONTENT_TYPE' => 'application/json'], '{"name":"John","surname":"Doe"}');
		$responseUser = json_decode($client->getResponse()->getContent(), TRUE);

		$client->request(
				'POST',
				'/users/' . $responseUser['id'] . '/products',
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

		// #40 Prepare a user and a product.
		$client->request('POST', '/users', [], [], ['CONTENT_TYPE' => 'application/json'], '{"name":"John","surname":"Doe"}');
		$responseUser = json_decode($client->getResponse()->getContent(), TRUE);

		$client->request(
				'POST',
				'/users/' . $responseUser['id'] . '/products',
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

		// #40 Prepare a user and a product.
		$client->request('POST', '/users', [], [], ['CONTENT_TYPE' => 'application/json'], '{"name":"John","surname":"Doe"}');
		$responseUser = json_decode($client->getResponse()->getContent(), TRUE);

		$client->request(
				'POST',
				'/users/' . $responseUser['id'] . '/products',
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

		// #40 Prepare a user and a product.
		$client->request('POST', '/users', [], [], ['CONTENT_TYPE' => 'application/json'], '{"name":"John","surname":"Doe"}');
		$responseUser = json_decode($client->getResponse()->getContent(), TRUE);

		$client->request(
				'POST',
				'/users/' . $responseUser['id'] . '/products',
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

	public function test_duplicate_sku_key()
	{
		$client = static::createClient();

		// #40 Prepare a user and a product.
		$client->request('POST', '/users', [], [], ['CONTENT_TYPE' => 'application/json'], '{"name":"John","surname":"Doe"}');
		$responseUser = json_decode($client->getResponse()->getContent(), TRUE);
		$client->request('POST', '/users/' . $responseUser['id'] . '/products', [], [], ['CONTENT_TYPE' => 'application/json'], '{"type":"t-shirt","title":"aware-wolf", "sku":"100-abc-1000-' . $responseUser['id'] . '", "cost":1000}');
		$client->request('POST', '/users/' . $responseUser['id'] . '/products', [], [], ['CONTENT_TYPE' => 'application/json'], '{"type":"t-shirt","title":"aware-wolf", "sku":"100-abc-1000-' . $responseUser['id'] . '", "cost":1000}');

		$this->assertEquals(Response::HTTP_CONFLICT, $client->getResponse()->getStatusCode());
		$responseBody = json_decode($client->getResponse()->getContent(), TRUE);
		$this->assertArrayHasKey(Product::SKU, $responseBody);
		$this->assertEquals($responseBody[Product::SKU][0], Product::INVALID_SKU);
	}
}
