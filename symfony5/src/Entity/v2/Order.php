<?php
namespace App\Entity\v2;

use Doctrine\ORM\Mapping as ORM;
use \App\Helper\EnumType;
use \App\Exception\OrderShippingValidatorException;

/**
 * @ORM\Entity(repositoryClass="App\Repository\v2\OrderRepository")
 * @ORM\Table(name="v2_order")
 */
class Order
{

	const IS_EXPRESS = "is_express";
	const IS_DOMESTIC = "is_domestic";
	const REQUIRE_IS_DOMESTIC = 'Set `is_domestic` before `is_express`.';
	const EXPRESS_ONLY_IN_DOMESTIC_REGION = "Express shipping is allowed only in domestic regions.";
	const ID = "id";
	const ORDER_ID = "order_id";
	const OWNER_NAME = "name";
	const OWNER_SURNAME = "surname";
	const STREET = "street";
	const STATE = "state";
	const ZIP = "zip";
	const COUNTRY = "country";
	const PHONE = "phone";
	const CANT_CREATE = "Cannot create a draft order. Please, contact our support.";
	const FIELD_IS_MISSING = ' field is missing.';
	const PRODUCTS = "products";
	const MUST_HAVE_PRODUCTS = "Must have at least 1 product.";
	const SHIPPING = "shipping";
	const MUST_HAVE_SHIPPING_SET = "The shipping must be set before completing the order.";
	const STATUS = 'status';
	const COMPLETED = 'completed';
	const DRAFT = 'draft';
	
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=20, nullable=true)
	 */
	private $status;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $customer_id;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $product_cost;

	/**
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */
	private $is_domestic;

	/**
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */
	private $is_express;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $shipping_cost;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $total_cost;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $created_at;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $updated_at;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $deleted_at;

	/**
	 * @ORM\Column(type="string", length=20, nullable=true)
	 */
	private $sys_info;

	/**
	 * @ORM\Column(type="string", length=30)
	 */
	private $name;

	/**
	 * @ORM\Column(type="string", length=30)
	 */
	private $surname;

	/**
	 * @ORM\Column(type="string", length=50)
	 */
	private $street;

	/**
	 * @ORM\Column(type="string", length=40)
	 */
	private $country;

	/**
	 * @ORM\Column(type="string", length=30)
	 */
	private $phone;

	/**
	 * @ORM\Column(type="string", length=30, nullable=true)
	 */
	private $state;

	/**
	 * @ORM\Column(type="string", length=20, nullable=true)
	 */
	private $zip;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getStatus(): ?string
	{
		return $this->status;
	}

	public function setStatus(?string $status): self
	{
		$this->status = $status;

		return $this;
	}

	public function getCustomerId(): ?int
	{
		return $this->customer_id;
	}

	public function setCustomerId(int $customer_id): self
	{
		$this->customer_id = $customer_id;

		return $this;
	}

	public function getProductCost(): ?int
	{
		return $this->product_cost;
	}

	public function setProductCost(int $product_cost): self
	{
		$this->product_cost = $product_cost;

		return $this;
	}

	public function getIsDomestic(): ?string
	{
		return $this->is_domestic;
	}

	public function setIsDomestic($is_domestic): self
	{
		 #40 Convert the value to defined enum values.
		$this->is_domestic = EnumType::parse($is_domestic);

		return $this;
	}

	public function getIsExpress(): ?string
	{
		return $this->is_express;
	}

	public function setIsExpress($is_express): self
	{
		// #40 Convert the value to defined enum values.
		$is_express = EnumType::parse($is_express);

		// #40 Require the `is_domestic` to be set first and to match the region.
		if (empty($this->getIsDomestic())) {
			throw new OrderShippingValidatorException([self::IS_EXPRESS => self::REQUIRE_IS_DOMESTIC], 1);
		}
		if ($this->getIsDomestic() === 'n' && $is_express === 'y') {
			throw new OrderShippingValidatorException([self::IS_EXPRESS => self::EXPRESS_ONLY_IN_DOMESTIC_REGION], 2);
		}

		$this->is_express = $is_express;

		return $this;
	}

	public function getShippingCost(): ?int
	{
		return $this->shipping_cost;
	}

	public function setShippingCost(?int $shipping_cost): self
	{
		$this->shipping_cost = $shipping_cost;

		return $this;
	}

	public function getTotalCost(): ?int
	{
		return $this->total_cost;
	}

	public function setTotalCost(?int $total_cost): self
	{
		$this->total_cost = $total_cost;

		return $this;
	}

	public function getCreatedAt(): ?\DateTimeInterface
	{
		return $this->created_at;
	}

	public function setCreatedAt(?\DateTimeInterface $created_at): self
	{
		$this->created_at = $created_at;

		return $this;
	}

	public function getUpdatedAt(): ?\DateTimeInterface
	{
		return $this->updated_at;
	}

	public function setUpdatedAt(?\DateTimeInterface $updated_at): self
	{
		$this->updated_at = $updated_at;

		return $this;
	}

	public function getDeletedAt(): ?\DateTimeInterface
	{
		return $this->deleted_at;
	}

	public function setDeletedAt(?\DateTimeInterface $deleted_at): self
	{
		$this->deleted_at = $deleted_at;

		return $this;
	}

	public function getSysInfo(): ?string
	{
		return $this->sys_info;
	}

	public function setSysInfo(?string $sys_info): self
	{
		$this->sys_info = $sys_info;

		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(string $name): self
	{
		$this->name = $name;

		return $this;
	}

	public function getSurname(): ?string
	{
		return $this->surname;
	}

	public function setSurname(string $surname): self
	{
		$this->surname = $surname;

		return $this;
	}

	public function getStreet(): ?string
	{
		return $this->street;
	}

	public function setStreet(string $street): self
	{
		$this->street = $street;

		return $this;
	}

	public function getCountry(): ?string
	{
		return $this->country;
	}

	public function setCountry(string $country): self
	{
		$this->country = $country;

		return $this;
	}

	public function getPhone(): ?string
	{
		return $this->phone;
	}

	public function setPhone(string $phone): self
	{
		$this->phone = $phone;

		return $this;
	}

	public function getState(): ?string
	{
		return $this->state;
	}

	public function setState(?string $state): self
	{
		$this->state = $state;

		return $this;
	}

	public function getZip(): ?string
	{
		return $this->zip;
	}

	public function setZip(?string $zip): self
	{
		$this->zip = $zip;

		return $this;
	}

	public static $requireds = [
		self::OWNER_NAME,
		self::OWNER_SURNAME,
		self::STREET,
		self::COUNTRY,
		self::PHONE,
		self::IS_EXPRESS
	];

}
