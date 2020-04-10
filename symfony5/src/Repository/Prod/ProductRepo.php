<?php
namespace App\Repository\Prod;

use App\Entity\Product;
use App\Entity\User;
use App\Interfaces\IProductRepo;
use App\Validators\UserValidators\UidValidator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use \App\Exception\ProductIdValidatorException;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepo extends ServiceEntityRepository implements IProductRepo
{

	private $em;
	private $userIdValidator;

	public function __construct(ManagerRegistry $registry, EntityManagerInterface $em, UidValidator $userIdValidator)
	{
		parent::__construct($registry, Product::class);
		$this->em = $em;
		$this->userIdValidator = $userIdValidator;
	}

	public function create(int $id_owner, array $requestBody): Product
	{
		$product = new Product();
		$product->setOwnerId($id_owner);
		$product->setType($requestBody[Product::TYPE]);
		$product->setTitle($requestBody[Product::TITLE]);
		$product->setSku($requestBody[Product::SKU]);
		$product->setCost($requestBody[Product::COST]);
		$this->em->persist($product);
		$this->em->flush();
		return $product;
	}

	public function getById(int $id_user, int $id)
	{
		if ($this->userIdValidator->validate($id_user)) {
			return $this->findOneBy([
					"id" => $id,
					"ownerId" => $id_user
			]);
		}
		return (null);
	}

	public function getAll(int $id_user)
	{
		if ($this->userIdValidator->validate($id_user)) {
			return $this->findBy([
					"ownerId" => $id_user
			]);
		}
		return (null);
	}

	/**
	 * #40 Find product by id. Throw an exception if not found.
	 * 
	 * @param int $id
	 * @return Product
	 * @throws ProductIdValidatorException
	 */
	public function mustFind(int $id): Product
	{
		$item = $this->findOneBy(["id" => $id]);
		if (empty($item)) {
			throw new ProductIdValidatorException([Product::ID => Product::INVALID], 1);
		}
		return $item;
	}

	/**
	 * #53 Generate a dummy a product for a user. Used in fixtures and tests.
	 * 
	 * @param User $user
	 * @param string $productType
	 * @return Product
	 */
	public function generateDummyUserProduct(User $user, string $productType): Product
	{
		return $this->create($user->getId(), [
				Product::TYPE => $productType,
				Product::TITLE => $user->getName() . ' ' . $user->getSurname() . ' ' . $productType,
				Product::SKU => $user->getName() . ' ' . $user->getSurname() . ' ' . $productType,
				Product::COST => 100
		]);
	}
}
