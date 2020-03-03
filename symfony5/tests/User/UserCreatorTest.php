<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;


class UserCreatorTest extends WebTestCase
{
    public function test_valid_request_body()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"name":"John","surname":"Doe"}'
        );
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        /* see if keys exists */
        $this->assertArrayHasKey(User::ID, $responseBody);
        $this->assertArrayHasKey(User::NAME, $responseBody);
        $this->assertArrayHasKey(User::SURNAME, $responseBody);
        $this->assertArrayHasKey(User::BALANCE, $responseBody);
        /* test key values */
        $this->assertEquals($responseBody[User::NAME], "John");
        $this->assertEquals($responseBody[User::SURNAME], "Doe");
        $this->assertEquals($responseBody[User::BALANCE], 10000);
        /* test value types */
        $this->assertIsInt($responseBody[User::ID]);
        $this->assertIsString($responseBody[User::NAME]);
        $this->assertIsString($responseBody[User::SURNAME]);
        $this->assertIsInt($responseBody[User::BALANCE]);
    }

    public function test_invalid_json_body()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"name":"John",,,,,,,,"surname":"Doe"}'
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
            '/users',
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
            '/users',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(User::NAME, $responseBody);
        $this->assertEquals($responseBody[User::NAME][0], "name key not set");
        $this->assertArrayHasKey(User::SURNAME, $responseBody);
        $this->assertEquals($responseBody[User::SURNAME][0], "surname key not set");
    }

    public function test_missing_name_key()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"xxxx":"John","surname":"Doe"}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(User::NAME, $responseBody);
        $this->assertEquals($responseBody[User::NAME][0], "name key not set");
    }

    public function test_invalid_name_key()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"name":"John00000","surname":"Doe"}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(User::NAME, $responseBody);
        $this->assertEquals($responseBody[User::NAME][0], User::INVALID_NAME);
    }

    public function test_missing_surname_key()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"name":"John","xxxxxxx":"Doe"}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(User::SURNAME, $responseBody);
        $this->assertEquals($responseBody[User::SURNAME][0], "surname key not set");
    }

    public function test_invalid_surname_key()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"name":"John","surname":"Doe00000"}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(User::SURNAME, $responseBody);
        $this->assertEquals($responseBody[User::SURNAME][0], User::INVALID_SURNAME);
    }

    public function test_multiple_errors_missing_both_keys()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"xxxx":"John","xxxxxxx":"Doe"}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(User::NAME, $responseBody);
        $this->assertArrayHasKey(User::SURNAME, $responseBody);
        $this->assertEquals($responseBody[User::NAME][0], "name key not set");
        $this->assertEquals($responseBody[User::SURNAME][0], "surname key not set");
    }

    public function test_multiple_errors_missing_name_key_and_invalid_surname()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/users',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"xxxx":"John","surname":"Doe00000"}'
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $responseBody = json_decode($client->getResponse()->getContent(), TRUE);
        $this->assertArrayHasKey(User::NAME, $responseBody);
        $this->assertArrayHasKey(User::SURNAME, $responseBody);
        $this->assertEquals($responseBody[User::NAME][0], "name key not set");
        $this->assertEquals($responseBody[User::SURNAME][0], User::INVALID_SURNAME);
    }
}
