<?php

namespace App\Tests\Order;

use App\Entity\OrderProduct;
use App\Exception\ProductIdValidatorException;
use App\Exception\UidValidatorException;
use App\Interfaces\IOrderProductRepo;
use App\Interfaces\IOrderRepo;
use App\Interfaces\IUserRepo;
use App\Service\Order\OrderProductCreator;
use App\Service\User\UserWihProductsGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * #40 ​/users​/{customerId}​/cart​/{productId}.
 */
class OrderProductTest extends WebTestCase
{
    private $impossibleInt = 3147483648;
    private $entityManager;
    private $client;
    private $userWithProductsGenerator;
    private $userRepo;
    private $orderRepo;
    private $orderProductCreator;
    private $orderProductRepo;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->c = $this->client->getContainer();
        $this->entityManager = $this->c->get('doctrine')->getManager();
        $this->userWithProductsGenerator = $this->c->get('test.'.UserWihProductsGenerator::class);
        $this->orderRepo = $this->c->get('test.'.IOrderRepo::class);
        $this->userRepo = $this->c->get('test.'.IUserRepo::class);
        $this->orderProductCreator = $this->c->get('test.'.OrderProductCreator::class);
        $this->orderProductRepo = $this->c->get('test.'.IOrderProductRepo::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testOrderProductExceptions()
    {
        $orderProduct = new OrderProduct();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("'aaa' ".\App\Helper\EnumType::INVALID_ENUM_VALUE);

        $orderProduct->setIsExpress('aaa');
    }

    public function testOrderProductCreatorExceptions()
    {
        $this->expectException(UidValidatorException::class);
        $this->expectExceptionCode(1);

        $this->orderProductCreator->handle($this->impossibleInt, $this->impossibleInt);
    }

    public function testOrderProductCreatorExceptions1()
    {
        $this->expectException(UidValidatorException::class);
        $this->expectExceptionCode(1);

        $this->orderProductCreator->handle($this->impossibleInt, $this->userRepo->getUserWithProducts()->getProducts()[0]->getId());
    }

    public function testOrderProductCreatorExceptions2()
    {
        $this->expectException(ProductIdValidatorException::class);
        $this->expectExceptionCode(1);

        $this->orderProductCreator->handle($this->userRepo->getUserWithProducts()->getId(), $this->impossibleInt);
    }

    public function testCreatedOrderProduct()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $orderCreated = $this->orderRepo->insertIfNotExist($user->getId());

        $this->assertNull($orderCreated->getProducts());

        for ($i = 0; $i < 3; ++$i) {
            $validProductArr = $this->orderProductCreator->handle($user->getId(), $user->getProducts()[0]->getId())->toArray();
            $expected = [
                OrderProduct::CUSTOMER_ID => $user->getId(), OrderProduct::SELLER_ID => $user->getId(),
                OrderProduct::PRODUCT_ID => $user->getProducts()[0]->getId(), OrderProduct::ORDER_ID => $orderCreated->getId(),
                OrderProduct::SELLER_TITLE => $user->getName().' '.$user->getSurname(), OrderProduct::PRODUCT_TITLE => $user->getProducts()[0]->getTitle(),
                OrderProduct::PRODUCT_COST => $user->getProducts()[0]->getCost(), OrderProduct::PRODUCT_TYPE => $user->getProducts()[0]->getType(),
                OrderProduct::IS_ADDITIONAL => null, OrderProduct::IS_DOMESTIC => null,	OrderProduct::IS_EXPRESS => null, OrderProduct::SHIPPING_COST => null, ];

            foreach ($expected  as $field => $val) {
                $this->assertEquals($val, $validProductArr[$field]);
            }
        }
    }

    public function testMakrCartsAdditionalProducts()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $orderCreated = $this->createOrderWithProducts($user);

        $this->assertTrue($this->orderProductRepo->makrCartsAdditionalProducts($orderCreated));

        $orderCreated = $this->orderRepo->find($orderCreated->getId());

        $this->assertEquals('n', $orderCreated->getProducts()[0]->getIsAdditional());
        $this->assertEquals('y', $orderCreated->getProducts()[1]->getIsAdditional());
        $this->assertEquals('y', $orderCreated->getProducts()[2]->getIsAdditional());
    }

    public function testMarkDomesticShipping()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $orderCreated = $this->createOrderWithProducts($user);

        $this->assertEquals(null, $orderCreated->getIsDomestic());

        for ($i = 0; $i < 3; ++$i) {
            $this->orderProductCreator->handle($user->getId(), $user->getProducts()[0]->getId());
        }

        foreach (['y', 'n'] as $value) {
            $orderCreated->setIsDomestic($value);
            $this->entityManager->flush();

            $this->assertEquals(true, $this->orderProductRepo->markDomesticShipping($orderCreated));
            $orderFound = $this->orderRepo->getCurrentDraft($user->getId());
            $this->assertEquals($orderFound->getId(), $orderCreated->getId());

            foreach ($orderFound->getProducts() as $product) {
                $this->assertEquals($value, $product->getIsDomestic());
            }
        }
    }

    public function markExpressShipping()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $orderCreated = $this->createOrderWithProducts($user);

        $this->assertEquals(null, $orderCreated->getIsDomestic());

        foreach (['y', 'n'] as $value) {
            $orderCreated->setIsExpress($value);
            $this->entityManager->flush();

            $this->assertEquals(true, $this->orderProductRepo->markExpressShipping($orderCreated));
            $orderFound = $this->orderRepo->getCurrentDraft($user->getId());
            $this->assertEquals($orderFound->getId(), $orderCreated->getId());

            foreach ($orderFound->getProducts() as $product) {
                $this->assertEquals($value, $product->getIsExpress());
            }
        }
    }

    public function testSetOrderCostsFromCartItemsInternational()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $orderCreated = $this->createAndCompleteOrderWithProducts($user, 'n', 'n');

        $this->assertEquals(300, $orderCreated->getProducts()[0]->getShippingCost(), 'T-shirt / International / Standard / First = 3$.');
        $this->assertEquals(150, $orderCreated->getProducts()[1]->getShippingCost(), 'T-shirt / International / Standard / Additional = 1.5$.');
        $this->assertEquals(150, $orderCreated->getProducts()[2]->getShippingCost(), 'T-shirt / International / Standard / Additional = 1.5$.');
    }

    public function testSetOrderCostsFromCartItemsDomestic()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $orderCreated = $this->createAndCompleteOrderWithProducts($user, 'y', 'n');

        $this->assertEquals(100, $orderCreated->getProducts()[0]->getShippingCost(), 'T-shirt / Domestic / Standard / First = 1$.');
        $this->assertEquals(50, $orderCreated->getProducts()[1]->getShippingCost(), 'T-shirt / Domestic / Standard / Additional = 0.5$.');
        $this->assertEquals(50, $orderCreated->getProducts()[2]->getShippingCost(), 'T-shirt / Domestic / Standard / Additional = 0.5$.');
    }

    public function testSetOrderCostsFromCartItemsExpress()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $orderCreated = $this->createAndCompleteOrderWithProducts($user, 'y', 'y');

        $this->assertEquals(1000, $orderCreated->getProducts()[0]->getShippingCost(), 'T-shirt / Domestic / Express / First = 10$.');
        $this->assertEquals(1000, $orderCreated->getProducts()[1]->getShippingCost(), 'T-shirt / Domestic / Express / Additional = 10$.');
        $this->assertEquals(1000, $orderCreated->getProducts()[2]->getShippingCost(), 'T-shirt / Domestic / Express / Additional = 10$.');
    }

    public function testInvalidequest()
    {
        $this->client->request('POST', '/users/'.$this->impossibleInt.'/cart/'.$this->impossibleInt);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertEquals([OrderProduct::ID => 'invalid user'], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testInvalidUser()
    {
        $this->client->request('POST', '/users/'.$this->impossibleInt.'/cart/'.$this->userRepo->getUserWithProducts()->getProducts()[0]->getId());

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertEquals([OrderProduct::ID => 'invalid user'], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testInvalidProduct()
    {
        $this->client->request('POST', '/users/'.$this->userRepo->getUserWithProducts()->getId().'/cart/'.$this->impossibleInt);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertEquals([OrderProduct::ID => 'Invalid product.'], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testValidRequest()
    {
        $user = $this->userRepo->getUserWithProducts();
        $this->client->request('POST', '/users/'.$user->getId().'/cart/'.$user->getProducts()[0]->getId());

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertNotEmpty($responseBody[OrderProduct::ID]);
        $this->assertEquals($user->getProducts()[0]->getId(), $responseBody[OrderProduct::PRODUCT_ID]);
        $this->assertEquals($user->getId(), $responseBody[OrderProduct::CUSTOMER_ID]);
    }

    private function createAndCompleteOrderWithProducts($user, $isDomestic = 'n', $isExpress = 'n')
    {
        $orderCreated = $this->createOrderWithProducts($user);

        $this->assertTrue($this->orderProductRepo->makrCartsAdditionalProducts($orderCreated));

        $orderCreated->setIsDomestic($isDomestic);
        $orderCreated->setIsExpress($isExpress);
        $this->entityManager->flush();

        $this->assertEquals(true, $this->orderProductRepo->markDomesticShipping($orderCreated));
        $this->assertEquals(true, $this->orderProductRepo->markExpressShipping($orderCreated));
        $this->assertEquals(true, $this->orderProductRepo->setShippingRates($orderCreated));
        // Sum together costs from cart products and store in the order's costs.
        $this->assertEquals(true, $this->orderRepo->setOrderCostsFromCartItems($orderCreated));

        return $this->orderRepo->find($orderCreated->getId());
    }

    private function createOrderWithProducts($user)
    {
        $orderCreated = $this->orderRepo->insertIfNotExist($user->getId());
        $this->addToCart($user->getId(), $user->getProducts()[0]->getId());

        return $orderCreated;
    }

    private function addToCart($userId, $productId, $times = 3)
    {
        for ($i = 0; $i < $times; ++$i) {
            $this->orderProductCreator->handle($userId, $productId);
        }
    }
}
