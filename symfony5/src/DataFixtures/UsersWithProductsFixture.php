<?php

namespace App\DataFixtures;

/*
 * #43 Fill test tables, before executing tests, using `./test.sh`.`. See `UserWihProductsGenerator`.
 */
use App\User\UserWihProductsGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;

class UsersWithProductsFixture extends Fixture implements FixtureGroupInterface
{
    private $userWithProductsGenerator;

    public function __construct(UserWihProductsGenerator $userWithProductsGenerator)
    {
        $this->userWithProductsGenerator = $userWithProductsGenerator;
    }

    public static function getGroups(): array
    {
        return ['regular', 'users', 'users_with_products'];
    }

    public function load(ObjectManager $manager)
    {
        $this->userWithProductsGenerator->generate(10);
    }
}
