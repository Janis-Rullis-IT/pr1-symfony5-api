<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetUserTest extends WebTestCase
{
    public function test_valid_id()
    {
        $client = static::createClient();

        $client->request('GET', '/users/1');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);

        /* see if keys exists */
        $this->assertArrayHasKey(User::ID, $responseBody);
        $this->assertArrayHasKey(User::NAME, $responseBody);
        $this->assertArrayHasKey(User::SURNAME, $responseBody);
        $this->assertArrayHasKey(User::BALANCE, $responseBody);
        /* test key values */
        $this->assertEquals($responseBody[User::ID], 1);
        $this->assertEquals($responseBody[User::NAME], "John");
        $this->assertEquals($responseBody[User::SURNAME], "Doe");
        $this->assertEquals($responseBody[User::BALANCE], 10000);
        /* test value types */
        $this->assertIsInt($responseBody[User::ID]);
        $this->assertIsString($responseBody[User::NAME]);
        $this->assertIsString($responseBody[User::SURNAME]);
        $this->assertIsInt($responseBody[User::BALANCE]);
    }

    public function test_invalid_id()
    {
        $client = static::createClient();

        $client->request('GET', '/users/1000000');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);

        $this->assertNull($responseBody);
    }
}
