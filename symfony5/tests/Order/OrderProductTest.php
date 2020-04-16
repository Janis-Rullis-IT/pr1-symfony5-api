<?php

namespace App\Tests\Order;

use App\Entity\OrderProduct;
use App\Exception\ProductIdValidatorException;
use App\Exception\UidValidatorException;
use App\Interfaces\IOrderProductRepo;
use App\Interfaces\IOrderRepo;
use App\Interfaces\IProductRepo;
use App\Interfaces\IUserRepo;
use App\Service\Order\OrderProductCreator;
use App\Service\User\UserWihProductsGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * #40 POST ​/users​/{customerId}​/cart​/{productId}.
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
        $this->productrRepo = $this->c->get('test.'.IProductRepo::class);
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
        $user = $this->userRepo->getUserWithProducts();

        $this->expectException(UidValidatorException::class);
        $this->expectExceptionCode(1);
        $this->orderProductCreator->handle($this->impossibleInt, $user->getProducts()[0]->getId());
    }

    public function testOrderProductCreatorExceptions2()
    {
        $user = $this->userRepo->getUserWithProducts();
        $this->expectException(ProductIdValidatorException::class);
        $this->expectExceptionCode(1);
        $this->orderProductCreator->handle($user->getId(), $this->impossibleInt);
    }

    public function testCreatedOrderProduct()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $orderCreated = $this->orderRepo->insertIfNotExist($user->getId());
        $this->assertNull($orderCreated->getProducts());

        for ($i = 0; $i < 3; ++$i) {
            $validProduct = $this->orderProductCreator->handle($user->getId(), $user->getProducts()[0]->getId());
            $this->assertEquals($validProduct->getCustomerId(), $user->getId());
            $this->assertEquals($validProduct->getSellerId(), $user->getId());
            $this->assertEquals($validProduct->getSellerTitle(), $user->getName().' '.$user->getSurname());
            $this->assertEquals($validProduct->getProductId(), $user->getProducts()[0]->getId());
            $this->assertEquals($validProduct->getProductTitle(), $user->getProducts()[0]->getTitle());
            $this->assertEquals($validProduct->getProductCost(), $user->getProducts()[0]->getCost());
            $this->assertEquals($validProduct->getProductType(), $user->getProducts()[0]->getType());
            $this->assertTrue($validProduct->getId() > 0);
            $this->assertEquals($validProduct->getOrderId(), $orderCreated->getId());
            $this->assertNull($validProduct->getIsAdditional());
            $this->assertNull($validProduct->getIsDomestic());
            $this->assertNull($validProduct->getIsExpress());
            $this->assertNull($validProduct->getShippingCost());
        }
    }

    public function testMakrCartsAdditionalProducts()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $orderCreated = $this->orderRepo->insertIfNotExist($user->getId());

        for ($i = 0; $i < 3; ++$i) {
            $this->orderProductCreator->handle($user->getId(), $user->getProducts()[0]->getId());
        }

        // #39 #33 #34 Mark additional products (ex., 2 pieces of the same t-shirt, 2nd is additional).
        $this->assertTrue($this->orderProductRepo->makrCartsAdditionalProducts($orderCreated));

        $orderCreated = $this->orderRepo->find($orderCreated->getId());
        $this->assertEquals('n', $orderCreated->getProducts()[0]->getIsAdditional());
        $this->assertEquals('y', $orderCreated->getProducts()[1]->getIsAdditional());
        $this->assertEquals('y', $orderCreated->getProducts()[2]->getIsAdditional());
    }

    public function testMarkDomesticShipping()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $orderCreated = $this->orderRepo->insertIfNotExist($user->getId());
        $this->assertEquals(null, $orderCreated->getIsDomestic());

        for ($i = 0; $i < 3; ++$i) {
            $this->orderProductCreator->handle($user->getId(), $user->getProducts()[0]->getId());
        }

        $values = ['y', 'n'];
        foreach ($values as $value) {
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
        $orderCreated = $this->orderRepo->insertIfNotExist($user->getId());
        $this->assertEquals(null, $orderCreated->getIsDomestic());

        for ($i = 0; $i < 3; ++$i) {
            $this->orderProductCreator->handle($user->getId(), $user->getProducts()[0]->getId());
        }

        $values = ['y', 'n'];
        foreach ($values as $value) {
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
        $orderCreated = $this->orderRepo->insertIfNotExist($user->getId());

        for ($i = 0; $i < 3; ++$i) {
            $this->orderProductCreator->handle($user->getId(), $user->getProducts()[0]->getId());
        }

        // #39 #33 #34 Mark additional products (ex., 2 pieces of the same t-shirt, 2nd is additional).
        $this->assertTrue($this->orderProductRepo->makrCartsAdditionalProducts($orderCreated));
        $orderCreated->setIsDomestic('n');
        $orderCreated->setIsExpress('n');
        $this->entityManager->flush();
        $this->assertEquals(true, $this->orderProductRepo->markDomesticShipping($orderCreated));
        $this->assertEquals(true, $this->orderProductRepo->markExpressShipping($orderCreated));
        $this->assertEquals(true, $this->orderProductRepo->setShippingRates($orderCreated));

        // Sum together costs from cart products and store in the order's costs.
        $this->assertEquals(true, $this->orderRepo->setOrderCostsFromCartItems($orderCreated));

        $orderCreated = $this->orderRepo->find($orderCreated->getId());
        // T-shirt / International / Standard / First = 3$.
        $this->assertEquals(300, $orderCreated->getProducts()[0]->getShippingCost());
        // T-shirt / International / Standard / Additional = 1.5$.
        $this->assertEquals(150, $orderCreated->getProducts()[1]->getShippingCost());
        // T-shirt / International / Standard / Additional = 1.5$.
        $this->assertEquals(150, $orderCreated->getProducts()[2]->getShippingCost());
    }

    public function testSetOrderCostsFromCartItemsDomestic()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $orderCreated = $this->orderRepo->insertIfNotExist($user->getId());

        for ($i = 0; $i < 3; ++$i) {
            $this->orderProductCreator->handle($user->getId(), $user->getProducts()[0]->getId());
        }

        // #39 #33 #34 Mark additional products (ex., 2 pieces of the same t-shirt, 2nd is additional).
        $this->assertTrue($this->orderProductRepo->makrCartsAdditionalProducts($orderCreated));
        $orderCreated->setIsDomestic('y');
        $orderCreated->setIsExpress('n');
        $this->entityManager->flush();
        $this->assertEquals(true, $this->orderProductRepo->markDomesticShipping($orderCreated));
        $this->assertEquals(true, $this->orderProductRepo->markExpressShipping($orderCreated));
        $this->assertEquals(true, $this->orderProductRepo->setShippingRates($orderCreated));

        // Sum together costs from cart products and store in the order's costs.
        $this->assertEquals(true, $this->orderRepo->setOrderCostsFromCartItems($orderCreated));

        $orderCreated = $this->orderRepo->find($orderCreated->getId());
        // T-shirt / International / Standard / First = 3$.
        $this->assertEquals(100, $orderCreated->getProducts()[0]->getShippingCost());
        // T-shirt / International / Standard / Additional = 1.5$.
        $this->assertEquals(50, $orderCreated->getProducts()[1]->getShippingCost());
        // T-shirt / International / Standard / Additional = 1.5$.
        $this->assertEquals(50, $orderCreated->getProducts()[2]->getShippingCost());
    }

    public function testSetOrderCostsFromCartItemsExpress()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $orderCreated = $this->orderRepo->insertIfNotExist($user->getId());

        for ($i = 0; $i < 3; ++$i) {
            $this->orderProductCreator->handle($user->getId(), $user->getProducts()[0]->getId());
        }

        // #39 #33 #34 Mark additional products (ex., 2 pieces of the same t-shirt, 2nd is additional).
        $this->assertTrue($this->orderProductRepo->makrCartsAdditionalProducts($orderCreated));
        $orderCreated->setIsDomestic('y');
        $orderCreated->setIsExpress('y');
        $this->entityManager->flush();
        $this->assertEquals(true, $this->orderProductRepo->markDomesticShipping($orderCreated));
        $this->assertEquals(true, $this->orderProductRepo->markExpressShipping($orderCreated));
        $this->assertEquals(true, $this->orderProductRepo->setShippingRates($orderCreated));

        // Sum together costs from cart products and store in the order's costs.
        $this->assertEquals(true, $this->orderRepo->setOrderCostsFromCartItems($orderCreated));

        $orderCreated = $this->orderRepo->find($orderCreated->getId());
        // T-shirt / International / Standard / First = 3$.
        $this->assertEquals(1000, $orderCreated->getProducts()[0]->getShippingCost());
        // T-shirt / International / Standard / Additional = 1.5$.
        $this->assertEquals(1000, $orderCreated->getProducts()[1]->getShippingCost());
        // T-shirt / International / Standard / Additional = 1.5$.
        $this->assertEquals(1000, $orderCreated->getProducts()[2]->getShippingCost());
    }

    public function testInvalidequest()
    {
        $customerId = $this->impossibleInt;
        $productId = $this->impossibleInt;
        $uri = '/users/'.$customerId.'/cart/'.$productId;

        $this->client->request('POST', $uri);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(['id' => 'invalid user'], $responseBody);
    }

    public function testInvalidUser()
    {
        $user = $this->userRepo->getUserWithProducts();

        $customerId = $this->impossibleInt;
        $productId = $user->getProducts()[0]->getId();
        $uri = '/users/'.$customerId.'/cart/'.$productId;

        $this->client->request('POST', $uri);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(['id' => 'invalid user'], $responseBody);
    }

    public function testInvalidProduct()
    {
        $user = $this->userRepo->getUserWithProducts();

        $customerId = $user->getId();
        $productId = $this->impossibleInt;
        $uri = '/users/'.$customerId.'/cart/'.$productId;

        $this->client->request('POST', $uri);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(['id' => 'Invalid product.'], $responseBody);
    }

    public function testValidRequest()
    {
        $user = $this->userRepo->getUserWithProducts();

        $customerId = $user->getId();
        $productId = $user->getProducts()[0]->getId();
        $uri = '/users/'.$customerId.'/cart/'.$productId;

        $this->client->request('POST', $uri);
        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        $responseBody = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertNotEmpty($responseBody['id']);
        $this->assertEquals($productId, $responseBody['product_id']);
        $this->assertEquals($customerId, $responseBody['customer_id']);
    }
}
