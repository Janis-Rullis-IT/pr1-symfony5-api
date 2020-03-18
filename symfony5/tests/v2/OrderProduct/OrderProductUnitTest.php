<?php
namespace App\Tests\v2\OrderProduct;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use \App\Entity\User;
use \App\Entity\Product;
use \App\Entity\v2\Order;
use \App\Entity\v2\OrderProduct;
use \App\v2\OrderProductCreator;
use \App\v2\OrderCreator;
use \App\Interfaces\v2\IOrderRepo;
use \App\Interfaces\v2\IOrderProductRepo;
use \App\Exception\OrderValidatorException;
use App\Exception\OrderCreatorException;
use \App\v2\OrderValidator;
use \App\Exception\UidValidatorException;

/**
 * #38 Test that the order product data is stored in the database correctly.
 * Test v2 functionality: `vendor/bin/phpunit tests/v2/`
 */
class OrderProductUnitTest extends KernelTestCase
{

	private $c;
	private $entityManager;
	private $orderProductCreator;
	private $orderCreator;
	private $orderValidator;
	private $orderRepo;
	private $orderProductRepo;

	protected function setUp(): void
	{
		// #38 Using services in tests https://www.tomasvotruba.com/blog/2018/05/17/how-to-test-private-services-in-symfony/ https://symfony.com/doc/current/service_container.html
		// "However, if a service has been marked as private, you can still 
		// alias it to access this service (via the alias)" `config/services.yaml` https://symfony.com/doc/current/service_container/alias_private.html#aliasing
		$kernel = self::bootKernel();
		$this->c = $kernel->getContainer();

		$this->orderProductCreator = $this->c->get('test.' . OrderProductCreator::class);
		$this->orderRepo = $this->c->get('test.' . IOrderRepo::class);
		$this->orderProductRepo = $this->c->get('test.' . IOrderProductRepo::class);
		$this->orderCreator = $this->c->get('test.' . OrderCreator::class);
		$this->orderValidator = $this->c->get('test.' . OrderValidator::class);

		// Using database in tests https://stackoverflow.com/a/52014145 https://symfony.com/doc/master/testing/database.html#functional-testing-of-a-doctrine-repository
		$this->entityManager = $this->c->get('doctrine')->getManager();

		// TODO: Truncate specific tables before each run.
	}

	/**
	 *  #40
	 */
	public function testOrderCreatorExceptions()
	{
		$order = new Order();
		$this->expectException(OrderValidatorException::class);
		$this->expectExceptionCode(1);
		$this->orderCreator->handle(1, []);
	}

	/**
	 *  #40
	 */
	public function testOrderCreatorExceptions2()
	{
		$order = new Order();
		$this->expectException(UidValidatorException::class);
		$this->expectExceptionCode(1);
		$ship_to_address = [
			"name" => "John",
			"surname" => "Doe",
			"street" => "Palm street 25-7",
			"state" => "California",
			"zip" => "60744",
			"country" => "US",
			"phone" => "+1 123 123 123",
			"is_express" => true
		];
		$this->orderCreator->handle(0, $ship_to_address);
	}

	/**
	 * #40
	 */
	public function testOrderAddressValidatorExceptions()
	{
		$this->expectException(OrderValidatorException::class);
		$this->expectExceptionCode(2);
		$this->orderValidator->validateAddress([]);
	}

	/**
	 * #40
	 */
	public function testOrderValidatorExceptions()
	{
		$this->expectException(OrderValidatorException::class);
		$this->expectExceptionCode(1);
		$this->orderValidator->validate([]);
	}

	/**
	 * #40
	 */
	public function testOrderValidation()
	{
		$ship_to_address = [
			"name" => "John",
			"surname" => "Doe",
			"street" => "Palm street 25-7",
			"state" => "California",
			"zip" => "60744",
			"country" => "US",
			"phone" => "+1 123 123 123",
		];
		$this->assertFalse($this->orderValidator->hasRequiredKeys($ship_to_address));
		$this->assertEquals(['is_express' => 'is_express'], $this->orderValidator->getMissingKeys($ship_to_address));
		$ship_to_address['is_express'] = true;
		$this->assertTrue($this->orderValidator->hasRequiredKeys($ship_to_address));

		$this->assertTrue($this->orderValidator->isAddressValid($ship_to_address));
		$this->assertTrue($this->orderValidator->isExpressShippingAllowed($ship_to_address));
		$this->assertTrue($this->orderValidator->isValid($ship_to_address));
		$ship_to_address['country'] = 'Latvia';
		$this->assertTrue($this->orderValidator->isAddressValid($ship_to_address));
		$this->assertFalse($this->orderValidator->isExpressShippingAllowed($ship_to_address));
		$this->assertFalse($this->orderValidator->isValid($ship_to_address));
	}

	public function testOrderEnumExceptions()
	{
		$order = new Order();
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("'aaa' " . \App\Helper\EnumType::INVALID_ENUM_VALUE);
		$this->expectExceptionCode(1);
		$order->setIsDomestic('aaa');
	}

	public function testOrderIsExpressExceptions()
	{
		$order = new Order();
		$this->expectException(OrderValidatorException::class);
		$this->expectExceptionCode(1, '#40 Require the `is_domestic` to be set first');
		$order->setIsExpress('y');
	}

	public function testOrderIsExpressExceptions2()
	{
		$order = new Order();
		$this->expectException(OrderValidatorException::class);
		$this->expectExceptionCode(2, '#40 Express must match the region.');
		$order->setIsDomestic('n');
		$order->setIsExpress('y');
	}

	public function testOrderProductExceptions()
	{
		$orderProduct = new OrderProduct();
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("'aaa' " . \App\Helper\EnumType::INVALID_ENUM_VALUE);
		$orderProduct->setIsExpress('aaa');
	}

	/**
	 * #38 Test that the customer can add products to a cart (`order_product`).
	 */
	public function testAddProductsToCart()
	{
		$users = $this->insertUsersAndProds();

		$this->assertEquals($this->orderProductCreator->handle([]), ['status' => false, 'data' => null, 'errors' => [
				"customer_id" => ["'customer_id' field is missing."], "product_id" => ["'product_id' field is missing."]]]);

		// T-shirt / US / Standard / First.
		$customerId = $users[2]->getId() + 1000000;
		$productId = $users[1]->products[0]->getId() + 100000;
		$invalidCustomerAndProduct = $this->orderProductCreator->handle(['customer_id' => $customerId, "product_id" => $productId]);
		$expected = ['status' => false, 'data' => null, 'errors' => ["customer_id" => ["Invalid 'customer_id'."], "product_id" => ["Invalid 'product_id'."]]];
		$this->assertEquals($invalidCustomerAndProduct, $expected);

		$customerId = $users[2]->getId() + 1000000;
		$productId = $users[1]->products[0]->getId();
		$invalidCustomer = $this->orderProductCreator->handle(['customer_id' => $customerId, "product_id" => $productId]);
		$expected = ['status' => false, 'data' => null, 'errors' => ["customer_id" => ["Invalid 'customer_id'."]]];
		$this->assertEquals($invalidCustomer, $expected);

		$customerId = $users[2]->getId();
		$productId = $users[1]->products[0]->getId() + 1000000;
		$invalidProduct = $this->orderProductCreator->handle(['customer_id' => $customerId, "product_id" => $productId]);
		$expected = ['status' => false, 'data' => null, 'errors' => ["product_id" => ["Invalid 'product_id'."]]];
		$this->assertEquals($invalidProduct, $expected);

		// #38 #36 Create and get customer's draft order.
		// TODO: This probably should be moved to a separate test file.
		$this->assertNull($this->orderRepo->getCurrentDraft($users[2]->getId()), '#36 #38 New customer shouldnt have a draft order.');
		$draftOrder = $this->orderRepo->insertIfNotExist($users[2]->getId());
		$this->assertNotNull($draftOrder, '#36 #38 A draft order should be created if it doesnt exist.');
		$this->assertEquals($this->orderRepo->getCurrentDraft($users[2]->getId())->getId(), $draftOrder->getId(), '#36 #38 Should find an existing one.');
		$this->assertEquals($this->orderRepo->getCurrentDraft($users[2]->getId())->getId(), $this->orderRepo->insertIfNotExist($users[2]->getId())->getId(), "#36 #38 A new draft order shouldnt be created if there is already one.");

		$customerId = $users[2]->getId();
		$productId = $users[1]->products[0]->getId();

		// #39 #33 #34 TODO: MAybe this should be optimized.
		$validProduct = $this->orderProductCreator->handle(['customer_id' => $customerId, "product_id" => $productId]);
		$this->assertEquals($validProduct['errors'], []);
		$this->assertEquals($validProduct['status'], true);
		$this->assertEquals($validProduct['data']->getCustomerId(), $customerId);
		$this->assertEquals($validProduct['data']->getSellerId(), $users[1]->getId());
		$this->assertEquals($validProduct['data']->getSellerTitle(), $users[1]->getName() . ' ' . $users[1]->getSurname());
		$this->assertEquals($validProduct['data']->getProductId(), $productId);
		$this->assertEquals($validProduct['data']->getProductTitle(), $users[1]->products[0]->getTitle());
		$this->assertEquals($validProduct['data']->getProductCost(), $users[1]->products[0]->getCost());
		$this->assertEquals($validProduct['data']->getProductType(), $users[1]->products[0]->getType());
		$this->assertTrue($validProduct['data']->getId() > 0);
		$this->assertEquals($validProduct['data']->getOrderId(), $draftOrder->getId());
		$this->assertEquals($validProduct['data']->getIsAdditional(), NULL);

		// #39 #33 #34 Add additional products  (ex., 2 pieces of the same t-shirt, 2nd is additional).
		$validProduct2 = $this->orderProductCreator->handle(['customer_id' => $customerId, "product_id" => $productId]);
		$this->assertEquals($validProduct2['errors'], []);
		$this->assertEquals($validProduct2['status'], true);
		$this->assertEquals($validProduct2['data']->getCustomerId(), $customerId);
		$this->assertEquals($validProduct2['data']->getSellerId(), $users[1]->getId());
		$this->assertEquals($validProduct2['data']->getSellerTitle(), $users[1]->getName() . ' ' . $users[1]->getSurname());
		$this->assertEquals($validProduct2['data']->getProductId(), $productId);
		$this->assertEquals($validProduct2['data']->getProductTitle(), $users[1]->products[0]->getTitle());
		$this->assertEquals($validProduct2['data']->getProductCost(), $users[1]->products[0]->getCost());
		$this->assertEquals($validProduct2['data']->getProductType(), $users[1]->products[0]->getType());
		$this->assertTrue($validProduct2['data']->getId() > 0);
		$this->assertEquals($validProduct2['data']->getOrderId(), $draftOrder->getId());
		$this->assertNotEquals($validProduct['data']->getId(), $validProduct2['data']->getId());
		$this->assertEquals($validProduct2['data']->getIsAdditional(), NULL);

		$validProduct3 = $this->orderProductCreator->handle(['customer_id' => $customerId, "product_id" => $productId]);
		$this->assertEquals($validProduct3['errors'], []);
		$this->assertEquals($validProduct3['status'], true);
		$this->assertEquals($validProduct3['data']->getCustomerId(), $customerId);
		$this->assertEquals($validProduct3['data']->getSellerId(), $users[1]->getId());
		$this->assertEquals($validProduct3['data']->getSellerTitle(), $users[1]->getName() . ' ' . $users[1]->getSurname());
		$this->assertEquals($validProduct3['data']->getProductId(), $productId);
		$this->assertEquals($validProduct3['data']->getProductTitle(), $users[1]->products[0]->getTitle());
		$this->assertEquals($validProduct3['data']->getProductCost(), $users[1]->products[0]->getCost());
		$this->assertEquals($validProduct3['data']->getProductType(), $users[1]->products[0]->getType());
		$this->assertTrue($validProduct3['data']->getId() > 0);
		$this->assertEquals($validProduct3['data']->getOrderId(), $draftOrder->getId());
		$this->assertNotEquals($validProduct2['data']->getId(), $validProduct3['data']->getId());
		$this->assertEquals($validProduct3['data']->getIsAdditional(), NULL);

		// #39 #33 #34 Mark additional products (ex., 2 pieces of the same t-shirt, 2nd is additional).
		$this->assertTrue($this->orderProductRepo->makrCartsAdditionalProducts($draftOrder));

		// #39 #33 #34 Collect updated items. 
		$validProductUpdated = $this->orderProductRepo->find($validProduct['data']->getId());
		$validProductUpdated2 = $this->orderProductRepo->find($validProduct2['data']->getId());
		$validProductUpdated3 = $this->orderProductRepo->find($validProduct3['data']->getId());

		// #39 #33 #34 Make sure they are marked correctly
		// TODO: Move all assertEquals() values to left side - that's the comparison side.
		$this->assertEquals($validProductUpdated->getIsAdditional(), 'n');
		$this->assertEquals($validProductUpdated2->getIsAdditional(), 'y');
		$this->assertEquals($validProductUpdated3->getIsAdditional(), 'y');

		// #39 #33 #34 Mark the order as domestic or international.
		$this->assertEquals($validProductUpdated->getIsDomestic(), NULL);
		$this->assertEquals($validProductUpdated2->getIsDomestic(), NULL);
		$this->assertEquals($validProductUpdated3->getIsDomestic(), NULL);

		// #39 #33 #34 TODO: Add here the value collection from the address parser.
		$this->assertEquals(NULL, $draftOrder->getIsDomestic());
		$draftOrder->setIsDomestic('y');
		$this->entityManager->flush();

		$this->assertEquals(true, $this->orderProductRepo->markDomesticShipping($draftOrder));
		$this->assertEquals($validProductUpdated->getOrderId(), $draftOrder->getId());

		// #39 #33 #34 Collect updated items. 
		$validProductUpdated = $this->orderProductRepo->find($validProductUpdated->getId());
		$validProductUpdated2 = $this->orderProductRepo->find($validProductUpdated2->getId());
		$validProductUpdated3 = $this->orderProductRepo->find($validProductUpdated3->getId());

		// #39 #33 #34 Make sure they are marked correctly
		$this->assertEquals($validProductUpdated->getIsDomestic(), 'y');
		$this->assertEquals($validProductUpdated2->getIsDomestic(), 'y');
		$this->assertEquals($validProductUpdated3->getIsDomestic(), 'y');

		$draftOrder->setIsDomestic('n');
		$this->entityManager->flush();

		$this->assertEquals(true, $this->orderProductRepo->markDomesticShipping($draftOrder));

		// #39 #33 #34 Collect updated items. 
		$validProductUpdated = $this->orderProductRepo->find($validProductUpdated->getId());
		$validProductUpdated2 = $this->orderProductRepo->find($validProductUpdated2->getId());
		$validProductUpdated3 = $this->orderProductRepo->find($validProductUpdated3->getId());

		// #39 #33 #34 Make sure they are marked correctly
		$this->assertEquals($validProductUpdated->getIsDomestic(), 'n');
		$this->assertEquals($validProductUpdated2->getIsDomestic(), 'n');
		$this->assertEquals($validProductUpdated3->getIsDomestic(), 'n');

		// #39 #33 #34 Mark order's shipping as express or standard.
		$this->assertEquals(NULL, $draftOrder->getIsExpress());
		$this->assertEquals($validProductUpdated->getIsExpress(), NULL);
		$this->assertEquals($validProductUpdated2->getIsExpress(), NULL);
		$this->assertEquals($validProductUpdated3->getIsExpress(), NULL);

		// #39 #33 #34 #37 Set order's product shipping costs based on the matching rates in the `v2_shipping_rates` table.
		$this->assertEquals($validProductUpdated->getShippingCost(), NULL);
		$this->assertEquals($validProductUpdated2->getShippingCost(), NULL);
		$this->assertEquals($validProductUpdated3->getShippingCost(), NULL);

		$this->assertEquals($draftOrder->getShippingCost(), NULL);
		$this->assertEquals($draftOrder->getProductCost(), NULL);
		$this->assertEquals($draftOrder->getTotalCost(), NULL);

		// #40 Can't set this before the domestic is set and throw an execption there if they doesn't match.
		$draftOrder->setIsExpress('n');
		$this->entityManager->flush();
		$this->assertEquals(true, $this->orderProductRepo->markExpressShipping($draftOrder));
		$this->assertEquals(true, $this->orderProductRepo->setShippingRates($draftOrder));

		// #39 #33 #34 #37 Sum together costs from cart products and store in the order's costs.
		$this->assertEquals(true, $this->orderRepo->setOrderCostsFromCartItems($draftOrder));

		// #39 #33 #34 Collect updated items. 
		$draftOrder = $this->orderRepo->find($draftOrder->getId());
		$validProductUpdated = $this->orderProductRepo->find($validProductUpdated->getId());
		$validProductUpdated2 = $this->orderProductRepo->find($validProductUpdated2->getId());
		$validProductUpdated3 = $this->orderProductRepo->find($validProductUpdated3->getId());

		// #39 #33 #34 #37 T-shirt / International / Standard / First = 3$.
		$this->assertEquals(300, $validProductUpdated->getShippingCost());
		// #39 #33 #34 #37 T-shirt / International / Standard / Additional = 1.5$.
		$this->assertEquals(150, $validProductUpdated2->getShippingCost());
		// #39 #33 #34 #37 T-shirt / International / Standard / Additional = 1.5$.
		$this->assertEquals(150, $validProductUpdated3->getShippingCost());

		$shippingCostTotal = $validProductUpdated->getShippingCost() + $validProductUpdated2->getShippingCost() + $validProductUpdated3->getShippingCost();
		$productCostTotal = $validProductUpdated->getProductCost() + $validProductUpdated2->getProductCost() + $validProductUpdated3->getProductCost();
		$costTotal = $shippingCostTotal + $productCostTotal;
		$this->assertEquals($draftOrder->getShippingCost(), $shippingCostTotal);
		$this->assertEquals($draftOrder->getProductCost(), $productCostTotal);
		$this->assertEquals($draftOrder->getTotalCost(), $costTotal);


		$draftOrder->setIsDomestic('y');
		$this->entityManager->flush();
		$this->assertEquals(true, $this->orderProductRepo->markDomesticShipping($draftOrder));
		$draftOrder->setIsExpress('n');
		$this->entityManager->flush();
		$this->assertEquals(true, $this->orderProductRepo->markExpressShipping($draftOrder));

		// #39 #33 #34 Collect updated items. 
		$validProductUpdated = $this->orderProductRepo->find($validProductUpdated->getId());
		$validProductUpdated2 = $this->orderProductRepo->find($validProductUpdated2->getId());
		$validProductUpdated3 = $this->orderProductRepo->find($validProductUpdated3->getId());

		$this->assertEquals('n', $draftOrder->getIsExpress());
		$this->assertEquals($validProductUpdated->getIsExpress(), 'n');
		$this->assertEquals($validProductUpdated2->getIsExpress(), 'n');
		$this->assertEquals($validProductUpdated3->getIsExpress(), 'n');

		$this->assertEquals(true, $this->orderProductRepo->setShippingRates($draftOrder));

		// #39 #33 #34 #37 Sum together costs from cart products and store in the order's costs.
		$this->assertEquals(true, $this->orderRepo->setOrderCostsFromCartItems($draftOrder));

		// #39 #33 #34 Collect updated items. 
		$draftOrder = $this->orderRepo->find($draftOrder->getId());
		$validProductUpdated = $this->orderProductRepo->find($validProductUpdated->getId());
		$validProductUpdated2 = $this->orderProductRepo->find($validProductUpdated2->getId());
		$validProductUpdated3 = $this->orderProductRepo->find($validProductUpdated3->getId());

		// #39 #33 #34 #37 T-shirt / US / Standard / First = 1$.
		$this->assertEquals(100, $validProductUpdated->getShippingCost());
		// #39 #33 #34 #37 T-shirt / US / Standard / Additional = 0.5$.
		$this->assertEquals(50, $validProductUpdated2->getShippingCost());
		// #39 #33 #34 #37 T-shirt / US / Standard / Additional = 0.5$.
		$this->assertEquals(50, $validProductUpdated3->getShippingCost());

		$shippingCostTotal = $validProductUpdated->getShippingCost() + $validProductUpdated2->getShippingCost() + $validProductUpdated3->getShippingCost();
		$productCostTotal = $validProductUpdated->getProductCost() + $validProductUpdated2->getProductCost() + $validProductUpdated3->getProductCost();
		$costTotal = $shippingCostTotal + $productCostTotal;
		$this->assertEquals($draftOrder->getShippingCost(), $shippingCostTotal);
		$this->assertEquals($draftOrder->getProductCost(), $productCostTotal);
		$this->assertEquals($draftOrder->getTotalCost(), $costTotal);

		$draftOrder->setIsExpress('y');
		$this->entityManager->flush();
		$this->assertEquals(true, $this->orderProductRepo->markExpressShipping($draftOrder));

		// #39 #33 #34 Collect updated items. 
		$validProductUpdated = $this->orderProductRepo->find($validProductUpdated->getId());
		$validProductUpdated2 = $this->orderProductRepo->find($validProductUpdated2->getId());
		$validProductUpdated3 = $this->orderProductRepo->find($validProductUpdated3->getId());

		$this->assertEquals('y', $draftOrder->getIsExpress());
		$this->assertEquals($validProductUpdated->getIsExpress(), 'y');
		$this->assertEquals($validProductUpdated2->getIsExpress(), 'y');
		$this->assertEquals($validProductUpdated3->getIsExpress(), 'y');

		$this->assertEquals(true, $this->orderProductRepo->setShippingRates($draftOrder));

		// #39 #33 #34 #37 Sum together costs from cart products and store in the order's costs.
		$this->assertEquals(true, $this->orderRepo->setOrderCostsFromCartItems($draftOrder));

		// #39 #33 #34 Collect updated items. 
		$draftOrder = $this->orderRepo->find($draftOrder->getId());
		$validProductUpdated = $this->orderProductRepo->find($validProductUpdated->getId());
		$validProductUpdated2 = $this->orderProductRepo->find($validProductUpdated2->getId());
		$validProductUpdated3 = $this->orderProductRepo->find($validProductUpdated3->getId());

		// #39 #33 #34 #37 Check that shipping prices are correct.
		// #39 #33 #34 #37 T-shirt / US / Express  / First = 10$.
		$this->assertEquals(1000, $validProductUpdated->getShippingCost());
		// #39 #33 #34 #37 T-shirt / US / Express Additional = 10$.
		$this->assertEquals(1000, $validProductUpdated2->getShippingCost());
		// #39 #33 #34 #37 T-shirt / US / Express Additional = 10$.
		$this->assertEquals(1000, $validProductUpdated3->getShippingCost());

		$shippingCostTotal = $validProductUpdated->getShippingCost() + $validProductUpdated2->getShippingCost() + $validProductUpdated3->getShippingCost();
		$productCostTotal = $validProductUpdated->getProductCost() + $validProductUpdated2->getProductCost() + $validProductUpdated3->getProductCost();
		$costTotal = $shippingCostTotal + $productCostTotal;
		$this->assertEquals($draftOrder->getShippingCost(), $shippingCostTotal);
		$this->assertEquals($draftOrder->getProductCost(), $productCostTotal);
		$this->assertEquals($draftOrder->getTotalCost(), $costTotal);
		
		// #40 Validate that the shipping is set correctly.
		$this->assertEmpty($draftOrder->getName());
		$this->assertEmpty($draftOrder->getSurname());
		$this->assertEmpty($draftOrder->getStreet());
		$this->assertEmpty($draftOrder->getState());
		$this->assertEmpty($draftOrder->getZip());
		$this->assertEmpty($draftOrder->getCountry());
		$this->assertEmpty($draftOrder->getPhone());
		
		$ship_to_address = [
			"name" => "John",
			"surname" => "Doe",
			"street" => "Palm street 25-7",
			"state" => "California",
			"zip" => "60744",
			"country" => "US",
			"phone" => "+1 123 123 123",
			"is_express" => true
		];
		$draftOrder = $this->orderCreator->handle($draftOrder->getCustomerId(), $ship_to_address);
		$this->assertEquals($ship_to_address['name'], $draftOrder->getName());
		$this->assertEquals($ship_to_address['surname'], $draftOrder->getSurname());
		$this->assertEquals($ship_to_address['street'], $draftOrder->getStreet());
		$this->assertEquals($ship_to_address['state'], $draftOrder->getState());
		$this->assertEquals($ship_to_address['zip'], $draftOrder->getZip());
		$this->assertEquals($ship_to_address['country'], $draftOrder->getCountry());
		$this->assertEquals($ship_to_address['phone'], $draftOrder->getPhone());
		$this->assertEquals('y', $draftOrder->getIsDomestic());
		$this->assertEquals('y', $draftOrder->getIsExpress());

		// #39 #33 #34 #37 TODO: Add `shipping_id` to `shipping_rates`.`id`.
		;

		// #38 #36 TODO: Add more use cases when work on the #39.
		// #38 #36 TODO: Decide what to do with the existing tests that doesn't use DB. 
		// On one hand they are currently broken and on the other hand they should be updated
		// to use DB.
	}

	protected function tearDown(): void
	{
		parent::tearDown();

		// doing this is recommended to avoid memory leaks
		$this->entityManager->close();
		$this->entityManager = null;
	}

	/**
	 * #38 Create 3 users with 1 mug and 1 shirt.
	 * 
	 * TODO: Replace this approach with fixtures or creators that are designed not just for access from controllers.
	 * 
	 * @return User array
	 */
	private function insertUsersAndProds()
	{
		// #38 Create 3 users.
		$users = [];
		for ($i = 0; $i < 3; $i++) {

			$user = new User();
			$user->setName(rand());
			$user->setSurname($i + 1);
			$user->setBalance(1000);
			$this->entityManager->persist($user);
			$this->entityManager->flush();
			$users[$i] = $user;

			// #38 Create 1 mug and 1 shirt for each user.
			$user->products = [];
			$productTypes = ['t-shirt', 'mug'];
			foreach ($productTypes as $productType) {
				$product = new Product();
				$product->setOwnerId($user->getId());
				$product->setType($productType);
				$product->setTitle($user->getName() . ' ' . $user->getSurname() . ' ' . $productType);
				$product->setSku($user->getName() . ' ' . $user->getSurname() . ' ' . $productType);
				$product->setCost(100);
				$this->entityManager->persist($product);
				$this->entityManager->flush();
				$user->products[] = $product;
			}
		}
		return $users;
	}
}
