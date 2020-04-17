<?php

namespace App\Tests\Order;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Exception\OrderShippingValidatorException;
use App\Interfaces\IOrderProductRepo;
use App\Interfaces\IOrderRepo;
use App\Service\Order\OrderProductCreator;
use App\Service\User\UserWihProductsGenerator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OrderUnitTest extends KernelTestCase
{
    private $c;
    private $entityManager;
    private $orderProductCreator;
    private $userWithProductsGenerator;
    private $orderRepo;
    private $orderProductRepo;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->c = $kernel->getContainer();
        $this->orderProductCreator = $this->c->get('test.'.OrderProductCreator::class);
        $this->userWithProductsGenerator = $this->c->get('test.'.UserWihProductsGenerator::class);
        $this->orderRepo = $this->c->get('test.'.IOrderRepo::class);
        $this->orderProductRepo = $this->c->get('test.'.IOrderProductRepo::class);
        $this->entityManager = $this->c->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testOrderEnumExceptions()
    {
        $order = new Order();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("'aaa' ".\App\Helper\EnumType::INVALID_ENUM_VALUE);
        $this->expectExceptionCode(1);

        $order->setIsDomestic('aaa');
    }

    public function testOrderIsExpressExceptions()
    {
        $order = new Order();

        $this->expectException(OrderShippingValidatorException::class);
        $this->expectExceptionCode(1, '#40 Require the `is_domestic` to be set first');

        $order->setIsExpress('y');
    }

    public function testOrderIsExpressExceptions2()
    {
        $order = new Order();

        $this->expectException(OrderShippingValidatorException::class);
        $this->expectExceptionCode(2, '#40 Express must match the region.');

        $order->setIsDomestic('n');
        $order->setIsExpress('y');
    }

    public function testDrafAndCompletedtOrder()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $this->assertNull($this->orderRepo->getCurrentDraft($user->getId()), '#36 #38 New customer shouldnt have a draft order.');
        $orderCreated = $this->orderRepo->insertDraftIfNotExist($user->getId());

        $this->assertNull($orderCreated->getIsExpress());
        $this->assertNull($orderCreated->getShippingCost());
        $this->assertNull($orderCreated->getProductCost());
        $this->assertNull($orderCreated->getTotalCost());
        $this->assertEquals(Order::DRAFT, $orderCreated->getStatus(), 'A created order should be a draft.');

        $this->orderRepo->markAsCompleted($orderCreated);
        $orderFound = $this->orderRepo->find($orderCreated->getId());

        $this->assertEquals(Order::COMPLETED, $orderFound->getStatus(), 'markAsCompleted() should change status to completed.');

        $orderCreated2 = $this->orderRepo->insertDraftIfNotExist($user->getId());

        $this->assertNotEquals($orderCreated->getId(), $orderCreated2->getId(), '#40 A new order should be created after the previous is completed.');

        $orderFound2 = $this->orderRepo->getCurrentDraft($user->getId());

        $this->assertEquals($orderFound2->getId(), $orderCreated2->getId(), '#36 #38 Should find an existing one.');
        $this->assertEquals($orderFound2->getId(), $this->orderRepo->insertDraftIfNotExist($user->getId())->getId(), '#36 #38 A new draft order should not be created if there is already one.');
    }

    public function testOrderTotals()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];
        $orderCreated = $this->orderRepo->insertDraftIfNotExist($user->getId());

        for ($i = 0; $i < 3; ++$i) {
            $this->orderProductCreator->handle($user->getId(), $user->getProducts()[0]->getId());
        }

        // #39 #33 #34 Mark additional products (ex., 2 pieces of the same t-shirt, 2nd is additional).
        $this->assertTrue($this->orderProductRepo->markCartsAdditionalProducts($orderCreated));

        $orderCreated->setIsDomestic('n');
        $orderCreated->setIsExpress('n');
        $this->entityManager->flush();

        $this->assertEquals(true, $this->orderProductRepo->markAsDomesticShipping($orderCreated));
        $this->assertEquals(true, $this->orderProductRepo->markAsExpressShipping($orderCreated));
        $this->assertEquals(true, $this->orderProductRepo->setShippingRates($orderCreated));
        $this->assertEquals(true, $this->orderRepo->setOrderCostsFromCartItems($orderCreated));

        $orderFound = $this->orderRepo->find($orderCreated->getId());
        $productCostTotal = $shippingCostTotal = 0;
        foreach ($orderFound->getProducts() as $product) {
            $shippingCostTotal += $product->getShippingCost();
            $productCostTotal += $product->getProductCost();
        }
        $costTotal = $shippingCostTotal + $productCostTotal;

        $this->assertEquals($orderFound->getShippingCost(), $shippingCostTotal);
        $this->assertEquals($orderFound->getProductCost(), $productCostTotal);
        $this->assertEquals($orderFound->getTotalCost(), $costTotal);
    }

    public function testToArray()
    {
        $user = $this->userWithProductsGenerator->generate(1)[0];

        $this->assertNull($this->orderRepo->getCurrentDraft($user->getId()), '#36 #38 New customer shouldnt have a draft order.');

        $this->orderRepo->insertDraftIfNotExist($user->getId());
        $this->orderProductCreator->handle($user->getId(), $user->getProducts()[0]->getId());
        $this->entityManager->clear();
        $orderFound = $this->orderRepo->getCurrentDraft($user->getId());
        $orderWithProducts = $this->orderRepo->mustFindUsersOrder($orderFound->getCustomerId(), $orderFound->getId())->toArray([], [Order::PRODUCTS]);
        $firstProduct = $orderWithProducts[Order::PRODUCTS][0];

        $this->assertEquals($orderFound->getId(), $firstProduct[OrderProduct::ORDER_ID]);
        $this->assertEquals($orderFound->getCustomerId(), $firstProduct[OrderProduct::CUSTOMER_ID]);

        $products = $orderFound->getProducts()->toArray()[0];

        foreach ($products as $product) {
            $this->assertEquals($orderFound->getId(), $product->getOrderId());
        }
    }
}
