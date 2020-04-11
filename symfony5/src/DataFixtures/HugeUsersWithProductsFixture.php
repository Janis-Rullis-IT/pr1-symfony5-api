<?php
namespace App\DataFixtures;

/**
 * #43 Fill test tables, before executing tests, using `./test.sh`.`. See `UserWihProductsGenerator`.
 */
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;
use \App\User\UserWihProductsGenerator;

class HugeUsersWithProductsFixture extends Fixture implements FixtureGroupInterface
{

	private $userWithProductsGenerator;

	public static function getGroups(): array
	{
		return ['huge', 'users', 'users_with_products'];
	}

	public function __construct(UserWihProductsGenerator $userWithProductsGenerator)
	{
		$this->userWithProductsGenerator = $userWithProductsGenerator;
	}

	public function load(ObjectManager $manager)
	{
		
		// #57 Fill tables using a stored procedure.
		//TRUNCATE `user`;
		//TRUNCATE `product`;
		//TRUNCATE `order`;
		//TRUNCATE `order_product`;
		//
		//CALL generate_data();
//		$this->userWithProductsGenerator->generate(100000);
	}
}
