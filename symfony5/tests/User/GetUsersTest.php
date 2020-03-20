<?php
namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetUsersTest extends WebTestCase
{

	public function test_get_users()
	{
		$client = static::createClient();

		// #40 Prepare a user.
		$client->request('POST', '/users', [], [], ['CONTENT_TYPE' => 'application/json'], '{"name":"John","surname":"Doe"}');
		$responseUser = json_decode($client->getResponse()->getContent(), TRUE);

		$client->request('GET', '/users');

		$this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

		$responseBody = json_decode($client->getResponse()->getContent(), TRUE);
		$lastUser = $responseBody[count($responseBody) - 1];
		$this->assertIsArray($responseBody);
		$this->assertIsArray($lastUser);
		$this->assertEquals($lastUser[User::ID], $lastUser[User::ID]);
		$this->assertEquals($lastUser[User::NAME], $lastUser[User::NAME]);
		$this->assertEquals($lastUser[User::SURNAME], $lastUser[User::SURNAME]);
		$this->assertEquals($lastUser[User::BALANCE], $lastUser[User::BALANCE]);
	}
}
