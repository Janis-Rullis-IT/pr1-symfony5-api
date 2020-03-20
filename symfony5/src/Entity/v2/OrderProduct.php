<?php
namespace App\Entity\v2;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use \App\Helper\EnumType;

/**
 * @ORM\Entity(repositoryClass="App\Repository\v2\OrderProductRepository")
 * @ORM\Table(name="v2_order_product")
 */
class OrderProduct
{
	const PRODUCT_ID = 'product_id';
	const ORDER_ID = 'order_id';
	const CUSTOMER_ID = 'customer_id';
	

	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $order_id;

	/**
	 * @ORM\Column(type="integer")
	 * @Assert\NotBlank
	 * @Assert\Type("integer")
	 */
	private $customer_id;

	/**
	 * @ORM\Column(type="integer")
	 * @Assert\NotBlank
	 * @Assert\Type("integer")
	 */
	private $seller_id;

	/**
	 * @ORM\Column(type="string", length=250)
	 */
	private $seller_title;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $product_id;

	/**
	 * @ORM\Column(type="string", length=250)
	 */
	private $product_title;

	/**
	 * @ORM\Column(type="smallint")
	 */
	private $product_cost;

	/**
	 * @ORM\Column(type="string", length=20)
	 */
	private $product_type;

	/**
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */
	private $is_domestic;

	/**
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */
	private $is_additional;

	/**
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */
	private $is_express;

	/**
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	private $shipping_cost;

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

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getOrderId(): ?int
	{
		return $this->order_id;
	}

	public function setOrderId(int $order_id): self
	{
		$this->order_id = $order_id;

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

	public function getSellerId(): ?int
	{
		return $this->seller_id;
	}

	public function setSellerId(int $seller_id): self
	{
		$this->seller_id = $seller_id;

		return $this;
	}

	public function getSellerTitle(): ?string
	{
		return $this->seller_title;
	}

	public function setSellerTitle(string $seller_title): self
	{
		$this->seller_title = $seller_title;

		return $this;
	}

	public function getProductId(): ?int
	{
		return $this->product_id;
	}

	public function setProductId(int $product_id): self
	{
		$this->product_id = $product_id;

		return $this;
	}

	public function getProductTitle(): ?string
	{
		return $this->product_title;
	}

	public function setProductTitle(string $product_title): self
	{
		$this->product_title = $product_title;

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

	public function getProductType(): ?string
	{
		return $this->product_type;
	}

	public function setProductType(string $product_type): self
	{
		$this->product_type = $product_type;

		return $this;
	}

	public function getIsDomestic(): ?string
	{
		return $this->is_domestic;
	}

	public function setIsDomestic($is_domestic): self
	{
		$this->is_domestic = EnumType::parse($is_domestic);

		return $this;
	}

	public function getIsAdditional(): ?string
	{
		return $this->is_additional;
	}

	public function setIsAdditional(?string $is_additional): self
	{
		$this->is_additional = $is_additional;

		return $this;
	}

	public function getIsExpress(): ?string
	{
		return $this->is_express;
	}

	public function setIsExpress($is_express): self
	{
		$this->is_express = EnumType::parse($is_express);
		
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

	public static $requireds = ['customer_id', 'product_id'];

}
