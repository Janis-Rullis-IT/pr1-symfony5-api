<?php

namespace App\Tests;

use App\RequestBody\JsonToArray;
use App\RequestBody\RequestBodyStandardizer;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit\Framework\TestCase;
use App\Exception\JsonToArrayException;
use Symfony\Component\HttpFoundation\RequestStack;

class JsonToArrayTest extends TestCase
{
    public function test_valid_json()
    {
        $json_body = "{
            \"name\":\"john\",
            \"surname\":\"doe\"
        }";

        $requestStack = new RequestStack();
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $json_body);
        $requestStack->push($request);

        $json_to_array = new JsonToArray($requestStack, new RequestBodyStandardizer());

        $request_decoded = $json_to_array->retrieve();

        $this->assertArrayHasKey('name', $request_decoded);
        $this->assertArrayHasKey('surname', $request_decoded);
        $this->assertEquals($request_decoded['name'], 'John');
        $this->assertEquals($request_decoded['surname'], 'Doe');
    }

    public function test_invalid_json()
    {
        /* we have comma after "doe" resulting in an invalid json when it is decoded */
        $json_body = "{
            \"name\":\"john\",
            \"surname\":\"doe\",
        }";

        $requestStack = new RequestStack();
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $json_body);
        $requestStack->push($request);

        $json_to_array = new JsonToArray($requestStack, new RequestBodyStandardizer());

        $this->expectException(JsonToArrayException::class);

        $request_decoded = $json_to_array->retrieve();

    }

    public function test_empty_json_body()
    {
        $json_body = "{}";

        $requestStack = new RequestStack();
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $json_body);
        $requestStack->push($request);

        $json_to_array = new JsonToArray($requestStack, new RequestBodyStandardizer());

        $request_decoded = $json_to_array->retrieve();

        $this->assertEmpty($request_decoded);
    }

    public function test_empty_json()
    {
        $json_body = "";

        $requestStack = new RequestStack();
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $json_body);
        $requestStack->push($request);

        $json_to_array = new JsonToArray($requestStack, new RequestBodyStandardizer());

        $this->expectException(JsonToArrayException::class);

        $request_decoded = $json_to_array->retrieve();
    }
}
