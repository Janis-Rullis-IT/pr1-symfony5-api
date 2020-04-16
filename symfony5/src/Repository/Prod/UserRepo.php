<?php

namespace App\Repository\Prod;

use App\Entity\Product;
use App\Entity\User;
use App\Exception\UidValidatorException;
use App\Interfaces\IUserRepo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class UserRepo extends ServiceEntityRepository implements IUserRepo
{
    private $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, User::class);
        $this->em = $em;
    }

    public function create(array $requestBody): User
    {
        $user = new User();
        $user->setName($requestBody[User::NAME]);
        $user->setSurname($requestBody[User::SURNAME]);
        $user->setBalance(isset($requestBody[User::BALANCE]) ? $requestBody[User::BALANCE] : 10000);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function getById(int $id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function getAll(): array
    {
        return $this->findBy([], ['id' => 'DESC'], 100);
    }

    /**
     * #40 Find user by id. Throw an exception if not found.
     *
     * @throws UidValidatorException
     */
    public function mustFind(int $id): User
    {
        $item = $this->getById($id);
        if (empty($item)) {
            throw new UidValidatorException([User::ID => User::INVALID], 1);
        }

        return $item;
    }

    /**
     * #40 Reduce the passed amount from user's balance.
     */
    public function reduceBalance(User $user, int $money): User
    {
        // #40 A refresh-entity workaround for the field not being updated.https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/unitofwork.html https://www.doctrine-project.org/api/orm/latest/Doctrine/ORM/EntityManager.html
        // TODO: Ask someone about this behaviour.
        $user = $this->em->getReference(User::class, $user->getId());

        // #40 TODO: Maybe this should be better done in SQL? To work with actual DB values and avoid concurrent operations that could impact this data.
        $user->setBalance($user->getBalance() - $money);
        $this->em->flush();

        return $user;
    }

    /**
     * #53 Generate a dummy user. Used in fixtures and tests.
     *
     * @param type $i
     */
    public function generateDummyUser($i, int $balance = 10000): User
    {
        return $this->create([User::NAME => rand(), User::SURNAME => $i + 1, User::BALANCE => $balance]);
    }

    /**
     * #53 Get a QueryBuilder with a LEFT JOIN to Product, Order By and LIMIT.
     */
    public function getUsersQuery(int $count = 3): QueryBuilder
    {
        return $this->createQueryBuilder('User')->select('User')
                        ->leftJoin(Product::class, 'Product', 'WITH', 'User.id = Product.ownerId')
                        ->orderBy('Product.ownerId', 'DESC')->setMaxResults($count);
    }

    /**
     * #53 Get a set amount of users with products ordered by user.id DESC.
     */
    public function getUsers(int $count = 3): array
    {
        $q = $this->getUsersQuery($count)->getQuery();

        return $q->getResult();
    }

    /**
     * #44 #53 Get a QueryBuilder based on getUsersQuery + where.
     */
    public function getUsersWithProductsQuery(int $count = 3): QueryBuilder
    {
        return $this->createQueryBuilder('User')->select('User')
                        ->innerJoin(Product::class, 'Product', 'WITH', 'User.id = Product.ownerId')
                        ->orderBy('Product.ownerId', 'DESC')->setMaxResults($count);
    }

    /**
     * #53 Get a set amount of users that has any product.
     * Necessary for testing purposes.
     */
    public function getUsersWithProducts(int $count = 3): array
    {
        $q = $this->getUsersWithProductsQuery($count)->getQuery();

        return $q->getResult();
    }

    /**
     * #53 Get a user that has any product.
     * Necessary for testing purposes.
     */
    public function getUserWithProducts(): User
    {
        return $this->getUsersWithProducts(1)[0];
    }

    /**
     * #53 Get a set amount of users without any product.
     * Necessary for testing purposes.
     */
    public function getUsersWithoutProducts(int $count = 3): array
    {
        $q = $this->getUsersQuery($count)->where('Product.id IS NULL')->getQuery();

        return $q->getResult();
    }

    /**
     * #53 Get a user without any product.
     * Necessary for testing purposes.
     */
    public function getUserWithoutProducts(): User
    {
        return $this->getUsersWithoutProducts(1)[0];
    }
}
