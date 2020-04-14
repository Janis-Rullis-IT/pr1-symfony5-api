<?php

namespace App\DataFixtures;

/*
 * #43 Fill test tables, before executing tests, using `./test.sh`.`. See `UserWihProductsGenerator`.
 */
use App\Interfaces\IUserRepo;
use App\User\UserWihProductsGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;

class HugeUsersWithProductsFixture extends Fixture implements FixtureGroupInterface
{
    private $userWithProductsGenerator;
    private $userRepo;

    public static function getGroups(): array
    {
        return ['huge', 'users', 'users_with_products'];
    }

    public function __construct(UserWihProductsGenerator $userWithProductsGenerator, IUserRepo $userRepo)
    {
        $this->userWithProductsGenerator = $userWithProductsGenerator;
        $this->userRepo = $userRepo;
    }

    public function load(ObjectManager $manager)
    {
        // #57 Don't execute the data generation if there's already a huge dataset.
        if ($this->userRepo->count([]) > 1000000) {
            return;
        } else {
            // #57 This will take ~3min.
            $conn = $manager->getConnection();
            $stmt = $conn->prepare('CALL generate_data()');

            return $stmt->execute();
        }
    }
}
