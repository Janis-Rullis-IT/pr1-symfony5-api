<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="OrdersRepo")
 * @ORM\Table(name="`order`")
 */

class Order
{
    const SHIPPING_DATA = "shipToAddress";
    const LINE_ITEMS = "lineItems";
    const INFO = "info";

    const ID = "id";
    const OWNER_ID = "ownerId";
    const OWNER_NAME = "name";
    const OWNER_SURNAME = "surname";
    const STREET = "street";
    const STATE = "state";
    const ZIP = "zip";
    const COUNTRY = "country";
    const PHONE = "phone";
    const PRODUCTION_COST = "productionCost";
    const SHIPPING_COST = "shippingCost";
    const TOTAL_COST = "totalCost";

    const INTERNATIONAL_ORDER = "international";
    const DOMESTIC_ORDER = "domestic";
    const EXPRESS_SHIPPING = "expressShipping";

    const NO_SHIPPING_DATA = "shipping address not set";
    const NO_NAME = "name key not set";
    const NO_SURNAME = "surname key not set";
    const NO_STREET = "street key not set";
    const NO_ZIP = "zip code key not set";
    const NO_STATE = "state key not set";
    const NO_COUNTRY = "country key not set";
    const NO_PHONE = "phone key not set";
    const INVALID_NAME = "name can only consist of letters";
    const INVALID_SURNAME = "surname can only consist of letters";
    const INVALID_STREET = "street can only consist of letters, digits, dash (-) and whitespaces";
    const INVALID_ZIP = "invalid zip code";
    const INVALID_STATE = "invalid state";
    const INVALID_COUNTRY = "invalid country";
    const INVALID_PHONE = "invalid phone number";
    const NO_LINE_ITEMS = "order does not contain any products";
    const EMPTY_ORDER = "empty order";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $ownerId;

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

    /**
     * @ORM\Column(type="integer")
     */
    private $productionCost;

    /**
     * @ORM\Column(type="integer")
     */
    private $shippingCost;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $expressShipping;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalCost;

    public function getId(): ?int
    {
        return $this->id;
    }

    /* for testing purposes, so that OrderTestRepo can simulate creation of an order */
    public function setId(int $id): ?self
    {
        $this->id = $id;

        return $this;
    }

    public function getOwnerId(): ?int
    {
        return $this->ownerId;
    }

    public function setOwnerId(?int $ownerId): ?int
    {
        return $this->ownerId = $ownerId;

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

    public function getProductionCost(): ?int
    {
        return $this->productionCost;
    }

    public function setProductionCost(?int $productionCost): self
    {
        $this->productionCost = $productionCost;

        return $this;
    }

    public function getShippingCost(): ?int
    {
        return $this->shippingCost;
    }

    public function setShippingCost(?int $shippingCost): self
    {
        $this->shippingCost = $shippingCost;

        return $this;
    }

    public function getExpressShipping(): ?int
    {
        return $this->expressShipping;
    }

    public function setExpressShipping(?bool $expressShipping): self
    {
        $this->expressShipping = $expressShipping;

        return $this;
    }

    public function getTotalCost(): ?int
    {
        return $this->totalCost;
    }

    public function setTotalCost(?int $totalCost): self
    {
        $this->totalCost = $totalCost;

        return $this;
    }
}
