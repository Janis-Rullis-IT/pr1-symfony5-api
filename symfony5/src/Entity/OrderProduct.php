<?php

namespace App\Entity;

use App\Helper\EnumType;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderProductRepository")
 * @ORM\Table(name="order_product")
 */
class OrderProduct
{
    // #40 Fields.
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const ORDER_ID = 'order_id';
    const SELLER_ID = 'seller_id';
    const SELLER_TITLE = 'seller_title';
    const PRODUCT_ID = 'product_id';
    const PRODUCT_TITLE = 'product_title';
    const PRODUCT_COST = 'product_cost';
    const PRODUCT_TYPE = 'product_type';
    const IS_DOMESTIC = 'is_domestic';
    const IS_ADDITIONAL = 'is_additional';
    const IS_EXPRESS = 'is_express';
    const SHIPPING_COST = 'shipping_cost';
    // #40 Key collections - used for data parsing.
    // #40 Default fields to display to public. Used in repo's `getField()`.
    const PUB_FIELDS = [
        self::ID, self::CUSTOMER_ID, self::ORDER_ID, self::SELLER_ID,
        self::SELLER_TITLE, self::PRODUCT_ID, self::PRODUCT_TITLE,
        self::PRODUCT_COST, self::PRODUCT_TYPE, self::IS_DOMESTIC,
        self::IS_ADDITIONAL, self::IS_EXPRESS, self::SHIPPING_COST,
    ];
    // #40 Required fields when creating a new item.
    const REQUIRED_FIELDS = [self::CUSTOMER_ID, self::PRODUCT_ID];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * #45 Schema annotations implemented.
     * @SWG\Property(property="id", type="integer", example="1")
     * @Groups({"CREATE", "PUB", "ID_ERROR"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="order_id", type="integer", example=1)
     * @Groups({"CREATE", "PUB"})
     */
    private $order_id;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @SWG\Property(property="customer_id", type="integer", example=1)
     * @Groups({"CREATE", "PUB"})
     */
    private $customer_id;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @SWG\Property(property="seller_id", type="integer", example=1)
     * @Groups({"CREATE", "PUB"})
     */
    private $seller_id;

    /**
     * @ORM\Column(type="string", length=250)
     * @SWG\Property(property="seller_title", type="string", example="John Doe")
     * @Groups({"CREATE", "PUB"})
     */
    private $seller_title;

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="product_id", type="integer", example=1)
     * @Groups({"CREATE", "PUB"})
     */
    private $product_id;

    /**
     * @ORM\Column(type="string", length=250)
     */
    private $product_title;

    /**
     * @ORM\Column(type="smallint")
     * @SWG\Property(property="product_cost", type="integer", example=1000)
     * @Groups({"CREATE", "PUB"})
     */
    private $product_cost;

    /**
     * @ORM\Column(type="string", length=20)
     * @SWG\Property(property="product_type", type="string", example="t-shirt")
     * @Groups({"CREATE", "PUB"})
     */
    private $product_type;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @SWG\Property(property="is_domestic", type="string", example="null")
     * @Groups({"CREATE", "PUB"})
     */
    private $is_domestic;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @SWG\Property(property="is_additional", type="string", example="null")
     * @Groups({"CREATE", "PUB"})
     */
    private $is_additional;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @SWG\Property(property="is_express", type="string", example="null")
     * @Groups({"CREATE", "PUB"})
     */
    private $is_express;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @SWG\Property(property="shipping_cost", type="integer", example="null")
     * @Groups({"CREATE", "PUB"})
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

    /**
     * #40 Convert the Entity to array in unified manner.
     */
    public function toArray(?array $fields = []): array
    {
        $return = [];
        // #40 Contains most popular fields. Add a field is necessary.
        $allFields = [
            self::ID => $this->getId(), self::CUSTOMER_ID => $this->getCustomerId(),
            self::ORDER_ID => $this->getOrderId(), self::SELLER_ID => $this->getSellerId(),
            self::SELLER_TITLE => $this->getSellerTitle(), self::PRODUCT_ID => $this->getProductId(),
            self::PRODUCT_TITLE => $this->getProductTitle(), self::PRODUCT_COST => $this->getProductCost(),
            self::PRODUCT_TYPE => $this->getProductType(), self::IS_DOMESTIC => $this->getIsDomestic(),
            self::IS_ADDITIONAL => $this->getIsAdditional(), self::IS_EXPRESS => $this->getIsExpress(),
            self::SHIPPING_COST => $this->getShippingCost(),
        ];
        if (empty($fields)) {
            return $allFields;
        }
        foreach ($fields as $field) {
            $return[$field] = isset($allFields[$field]) ? $allFields[$field] : null;
        }

        return $return;
    }
}
