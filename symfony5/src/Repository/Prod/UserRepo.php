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
		$user->setBalance(10000);
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
}
