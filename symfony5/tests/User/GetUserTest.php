<?php

namespace App\Tests\User;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetUserTest extends WebTestCase
{
    private $impossibleInt = 3147483648;

    public function test_valid_id()
    {
        $client = static::createClient();

        // #40 Prepare a user.
        $client->request('POST', '/users', [], [], ['CONTENT_TYPE' => 'application/json'], '{"name":"John","surname":"Doe"}');
        $responseUser = json_decode($client->getResponse()->getContent(), true);

        $client->request('GET', '/users/'.$responseUser[User::ID]);

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), true);

        /* see if keys exists */
        $this->assertArrayHasKey(User::ID, $responseBody);
        $this->assertArrayHasKey(User::NAME, $responseBody);
        $this->assertArrayHasKey(User::SURNAME, $responseBody);
        $this->assertArrayHasKey(User::BALANCE, $responseBody);
        /* test key values */
        $this->assertEquals($responseBody[User::ID], $responseUser[User::ID]);
        $this->assertEquals($responseBody[User::NAME], $responseUser[User::NAME]);
        $this->assertEquals($responseBody[User::SURNAME], $responseUser[User::SURNAME]);
        $this->assertEquals($responseBody[User::BALANCE], $responseUser[User::BALANCE]);
        /* test value types */
        $this->assertIsInt($responseBody[User::ID]);
        $this->assertIsString($responseBody[User::NAME]);
        $this->assertIsString($responseBody[User::SURNAME]);
        $this->assertIsInt($responseBody[User::BALANCE]);
    }

    public function test_invalid_id()
    {
        $client = static::createClient();

        $client->request('GET', '/users/'.$this->impossibleInt);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), true);

        $this->assertNull($responseBody);
    }
}
