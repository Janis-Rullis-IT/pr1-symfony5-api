<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="ProductRepo")
 */
class Product
{
    const ID = 'id';
    const QUANTITY = 'quantity';
    const OWNER_ID = 'ownerId';
    const TYPE = 'type';
    const TITLE = 'title';
    const SKU = 'sku';
    const COST = 'cost';
    const TOTAL_COST = 'totalCost';

    const NO_TYPE = 'type key not set';
    const NO_TITLE = 'title key not set';
    const NO_SKU = 'sku key not set';
    const NO_COST = 'cost key not set';
    const INVALID = 'Invalid product.';
    const INVALID_TYPE = 'Invalid type';
    const INVALID_TITLE = 'Invalid title. It can only consist of letters, digits, dash(-) and space';
    const INVALID_SKU = 'Invalid SKU. It must be unique and cannot be empty';
    const INVALID_COST = 'Invalid cost. It must be an integer describing price with smallest money unit';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * #45 Schema annotations implemented.
     * @SWG\Property(property="id", type="integer", example="1")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="ownerId", type="integer", example="1")
     */
    private $ownerId;

    /**
     * @ORM\Column(type="string", length=30)
     * @SWG\Property(property="type", type="string", example="t-shirt", description="valid product types are 't-shirt' and 'mug'. They can be written in upper or lowercase e.g. 'T-Shirt' is valid.")
     * @Groups({"CREATE", "PUB"})
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=50)
     * @SWG\Property(property="title", type="string", example="just do it", description="Can consist of upper and lowercase letters, digits, dash (-) and space ( ). It is stored as a string later on.")
     * @Groups({"CREATE", "PUB"})
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=20)
     * @SWG\Property(property="sku", type="string", example="100-abc-999", description="(stock keeping unit) must be unique among products user has submitted. It is stored as a string later on.")
     * @Groups({"CREATE", "PUB"})
     */
    private $sku;

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="cost", type="integer", example=1000, description="Must be an integer representing cents.")
     * @Groups({"CREATE", "PUB"})
     */
    private $cost;

    public function getId(): ?int
    {
        return $this->id;
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
