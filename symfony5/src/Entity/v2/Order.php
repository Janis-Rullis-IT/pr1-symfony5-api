<?php

namespace App\Entity\v2;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\v2\OrderRepository")
 * @ORM\Table(name="v2_order")
 */
class Order
{
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

    public function setIsDomestic(?string $is_domestic): self
    {
        $this->is_domestic = $is_domestic;

        return $this;
    }

    public function getIsExpress(): ?string
    {
        return $this->is_express;
    }

    public function setIsExpress(?string $is_express): self
    {
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
	
	public static $requireds = ['is_domestic', 'is_express'];
}
