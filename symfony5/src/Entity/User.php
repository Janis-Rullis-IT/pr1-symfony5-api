<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="UserRepo")
 */
class User
{
    const ID = 'id';
    const NAME = 'name';
    const SURNAME = 'surname';
    const BALANCE = 'balance';
    const INVALID = 'invalid user';
    const NO_NAME = 'name key not set';
    const NO_SURNAME = 'surname key not set';
    const INVALID_NAME = "Invalid name. It can only consist of letters, spaces, dot (.) , comma (,) , apostrophe ('), dash (-) and can not be empty";
    const INVALID_SURNAME = "Invalid surname. It can only consist of letters, spaces, dot (.) , comma (,) , apostrophe ('), dash (-) and can not be empty";
    const INSUFFICIENT_FUNDS = 'Insufficient funds.';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * #45 Schema annotations implemented.
     * @SWG\Property(property="id", type="integer", example="1"),
     * @Groups({ "PUB"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     * @SWG\Property(property="name", type="string", example="John", description="accepts upper and lowercase letters, spaces, dot (.) , comma (,) , apostrophe (') and dash (-)."),
     * @Groups({ "PUB", "CREATE" })
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=30)
     * @SWG\Property(property="surname", type="string", example="Doe", description="accepts upper and lowercase letters, spaces, dot (.) , comma (,) , apostrophe (') and dash (-).")
     * @Groups({ "PUB", "CREATE" })
     */
    private $surname;

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="balance", type="integer", example=10000, description="Each user is assigned 100$ or 10000 cents as a starting balance.")
     * @Groups({ "PUB" })
     */
    private $balance;

    /**
     * #54 Store user's products when called `getProducts()`.
     * #40 Annotation based ManyToMany relation that collects order's products.
     * https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/cookbook/aggregate-fields.html
     * https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/annotations-reference.html#annref_joincolumns
     * https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/association-mapping.html#one-to-many-unidirectional-with-join-table
     * https://www.doctrine-project.org/api/collections/latest/Doctrine/Common/Collections/ArrayCollection.html.
     *
     * @ORM\ManyToMany(targetEntity="Product")
     * @ORM\JoinTable(name="product",
     *      joinColumns={@ORM\JoinColumn(name="owner_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="id", referencedColumnName="id", unique=true)}
     * )
     * @SWG\Property(property="products", type="array", @SWG\Items(@Model(type=Product::class)))
     * @Groups({"PUB"})
     */
    private $products;

    /**
     * #54 Collect user's products.
     * Collected using annotation JOIN. See `$products`.
     */
    public function getProducts()
    {
        return $this->products;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /* for testing purposes, so that UserTestRepo can simulate creation of an user */

    public function setId(int $id): self
    {
        $this->id = $id;

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

    public function getBalance(): ?int
    {
        return $this->balance;
    }

    public function setBalance(int $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function subtractBalance(int $amount): self
    {
        $this->balance -= $amount;

        return $this;
    }
}
