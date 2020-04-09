<?php
namespace App\Repository\Prod;

use App\Entity\User;
use App\Interfaces\IUserRepo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use \App\Exception\UidValidatorException;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
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
		$user->setBalance(empty($requestBody[User::BALANCE]) ? 10000 : $requestBody[User::BALANCE]);
		$this->em->persist($user);
		$this->em->flush();
		return $user;
	}

	public function getById(int $id)
	{
		return $this->findOneBy([
				"id" => $id
		]);
	}

	public function getAll(): array
	{
		return $this->findAll();
	}

	/**
	 * #40 Find user by id. Throw an exception if not found.
	 * 
	 * @param int $id
	 * @return User
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
	 * 
	 * @param User $user
	 * @param int $money
	 * @return User
	 */
	public function reduceBalance(User $user, int $money): User
	{
		// #40 A refresh-entity workaround for the field not being updated. 
		// https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/unitofwork.html https://www.doctrine-project.org/api/orm/latest/Doctrine/ORM/EntityManager.html
		// If `persist()` is being used then a naw record is inserted.
		// TODO: Ask someone about this behaviour.
		$user = $this->em->getReference(User::class, $user->getId());

		// #40 TODO: Maybe this should be better done in SQL? To work with actual DB
		// values and avoid concurrent operations that could impact this data.
		$user->setBalance($user->getBalance() - $money);
		$this->em->flush();

		return $user;
	}

	/**
	 * #53 Generate a dummy user. Used in fixtures and tests.
	 * 
	 * @param type $i
	 * @return User
	 */
	public function generateDummyUser($i): User
	{
		return $this->create([User::NAME => rand(), User::SURNAME => $i + 1]);
	}
}
