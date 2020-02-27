<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ProductRepo")
 */

class Product
{
    const ID = "id";
    const QUANTITY = "quantity";
    const OWNER_ID = "ownerId";
    const TYPE = "type";
    const TITLE = "title";
    const SKU = "sku";
    const COST = "cost";
    const TOTAL_COST = "totalCost";

    const NO_TYPE = "type key not set";
    const NO_TITLE = "title key not set";
    const NO_SKU = "sku key not set";
    const NO_COST = "cost key not set";
    const INVALID_TYPE = "Invalid type";
    const INVALID_TITLE = "Invalid title. It can only consist of letters, digits, dash(-) and space";
    const INVALID_SKU = "Invalid SKU. It must be unique and cannot be empty";
    const INVALID_COST = "Invalid cost. It must be an integer describing price with smallest money unit";

    const NO_LINE_ITEM_ID = "id field not set for line item number ";
    const NO_LINE_ITEM_QUANTITY = "quantity field not set for line item number ";
    const INVALID_LINE_ITEM_ID = "invalid product id for line item number ";
    const INVALID_LINE_ITEM_QUANTITY = "quantity must be at least 1 for line item number ";

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
    private $type;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $sku;

    /**
     * @ORM\Column(type="integer")
     */
    private $cost;

    public function getId(): ?int
    {
        return $this->id;
    }

    /* for testing purposes, so that ProductTestRepo can simulate creation of a product */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getOwnerId(): ?int
    {
        return $this->ownerId;
    }

    public function setOwnerId(int $ownerId): self
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getCost(): ?int
    {
        return $this->cost;
    }

    public function setCost(int $cost): self
    {
        $this->cost = $cost;

        return $this;
    }
}
