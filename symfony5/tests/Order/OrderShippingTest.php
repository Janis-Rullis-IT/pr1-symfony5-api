<?php

namespace App\Tests\Order;

use App\Entity\Order;
use App\Exception\OrderShippingValidatorException;
use App\Exception\UidValidatorException;
use App\Interfaces\IOrderRepo;
use App\Service\Order\OrderShippingService;
use App\Service\Order\OrderShippingValidator;
use App\Service\User\UserWihProductsGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * #40 /users/{customerId}/order/shipping.
 */
class OrderShippingTest extends WebTestCase
{
    private $impossibleInt = 3147483648;
    private $entityManager;
    private $client;
    private $userWithProductsGenerator;
    private $orderShippingService;
    private $orderShippingValidator;
    private $orderRepo;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->c = $this->client->getContainer();
        $this->entityManager = $this->c->get('doctrine')->getManager();
        $this->userWithProductsGenerator = $this->c->get('test.'.UserWihProductsGenerator::class);
        $this->orderShippingService = $this->c->get('test.'.OrderShippingService::class);
        $this->orderShippingValidator = $this->c->get('test.'.OrderShippingValidator::class);
        $this->orderRepo = $this->c->get('test.'.IOrderRepo::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testOrderShippingExceptions()
    {
        $order = new Order();

        $this->expectException(OrderShippingValidatorException::class);
        $this->expectExceptionCode(1);

        $this->orderShippingService->set(1, []);
    }

    public function testOrderShippingExceptions2()
    {
        $order = new Order();

        $this->expectException(UidValidatorException::class);
        $this->expectExceptionCode(1);

        $this->orderShippingService->set(0, Order::VALID_SHIPPING_EXAMPLE);
    }

    public function testOrderAddressValidatorExceptions()
    {
        $this->expectException(OrderShippingValidatorException::class);
        $this->expectExceptionCode(2);

        $this->orderShippingValidator->validateAddress([]);
    }

    public function testOrderValidatorExceptions()
    {
        $this->expectException(OrderShippingValidatorException::class);
        $this->expectExceptionCode(1);

        $this->orderShippingValidator->validate([]);
    }

    public function testOrderShippingValidator()
    {
        $ship_to_address = Order::VALID_SHIPPING_EXAMPLE;
        unset($ship_to_address[Order::IS_EXPRESS]);

        $this->assertFalse($this->orderShippingValidator->hasRequiredKeys($ship_to_address));
        $this->assertEquals([Order::IS_EXPRESS => Order::IS_EXPRESS], $this->orderShippingValidator->getMissingKeys($ship_to_address));

        $ship_to_address[Order::IS_EXPRESS] = true;

        $this->assertTrue($this->orderShippingValidator->hasRequiredKeys($ship_to_address));
        $this->assertTrue($this->orderShippingValidator->isAddressValid($ship_to_address));
        $this->assertTrue($this->orderShippingValidator->isExpressShippingAllowed($ship_to_address));
        $this->assertTrue($this->orderShippingValidator->isValid($ship_to_address));

        $ship_to_address[Order::COUNTRY] = 'Latvia';

        $this->assertTrue($this->orderShippingValidator->isAddressValid($ship_to_address));
        $this->assertFalse($this->orderShippingValidator->isExpressShippingAllowed($ship_to_address));
        $this->assertFalse($this->orderShippingValidator->isValid($ship_to_address));
    }

    public function testShippingSet()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $draftOrder = $this->orderRepo->insertIfNotExist($user->getId());

        // #40 Validate that the shipping is set correctly.
        $this->assertEmpty($draftOrder->getName());
        $this->assertEmpty($draftOrder->getSurname());
        $this->assertEmpty($draftOrder->getStreet());
        $this->assertEmpty($draftOrder->getState());
        $this->assertEmpty($draftOrder->getZip());
        $this->assertEmpty($draftOrder->getCountry());
        $this->assertEmpty($draftOrder->getPhone());

        $ship_to_address = Order::VALID_SHIPPING_EXAMPLE;
        $draftOrder = $this->orderShippingService->set($draftOrder->getCustomerId(), $ship_to_address);

        $this->assertEquals($ship_to_address[Order::OWNER_NAME], $draftOrder->getName());
        $this->assertEquals($ship_to_address[Order::OWNER_SURNAME], $draftOrder->getSurname());
        $this->assertEquals($ship_to_address[Order::STREET], $draftOrder->getStreet());
        $this->assertEquals($ship_to_address[Order::STATE], $draftOrder->getState());
        $this->assertEquals($ship_to_address[Order::ZIP], $draftOrder->getZip());
        $this->assertEquals($ship_to_address[Order::COUNTRY], $draftOrder->getCountry());
        $this->assertEquals($ship_to_address[Order::PHONE], $draftOrder->getPhone());
        $this->assertEquals('y', $draftOrder->getIsDomestic());
        $this->assertEquals('y', $draftOrder->getIsExpress());
    }

    public function testInvalidCustomer()
    {
        $this->client->request('PUT', '/users/'.$this->impossibleInt.'/order/shipping', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(Order::VALID_SHIPPING_EXAMPLE));

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertEquals([Order::ID => 'invalid user'], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testMissingData()
    {
        $this->client->request('PUT', '/users/'.$this->impossibleInt.'/order/shipping', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());

        $responseBody = json_decode($this->client->getResponse()->getContent(), true);

        foreach (\App\Entity\Order::REQUIRED_FIELDS as $key => $val) {
            $this->assertEquals(["'".$val."' field is missing."], $responseBody[$val]);
        }
    }

    public function testMissingField()
    {
        $data = Order::VALID_SHIPPING_EXAMPLE;
        unset($data[Order::IS_EXPRESS]);
        $this->client->request('PUT', '/users/'.$this->impossibleInt.'/order/shipping', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals([Order::IS_EXPRESS => ["'is_express' field is missing."]], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testValidRequest()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $this->client->request('POST', '/users/'.$user->getId().'/cart/'.$user->getProducts()[0]->getId());
        $data = Order::VALID_SHIPPING_EXAMPLE;
        $this->client->request('PUT', '/users/'.$user->getId().'/order/shipping', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('y', $responseBody[Order::IS_DOMESTIC]);
        $this->assertEquals('y', $responseBody[Order::IS_EXPRESS]);
        $this->assertEquals(1000, $responseBody[Order::SHIPPING_COST], '#40 Express costs 10$.');
        $this->assertEquals($user->getProducts()[0]->getCost(), $responseBody[Order::PRODUCT_COST]);
        $this->assertEquals(1000 + $user->getProducts()[0]->getCost(), $responseBody[Order::TOTAL_COST]);

        unset($data[Order::IS_EXPRESS]);
        foreach ($data as $key => $val) {
            $this->assertEquals($val, $responseBody[$key]);
        }
    }
}
