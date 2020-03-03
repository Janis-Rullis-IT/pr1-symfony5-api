<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="UserRepo")
 */
class User
{
    const ID = "id";
    const NAME = "name";
    const SURNAME = "surname";
    const BALANCE = "balance";
    const INVALID = "invalid user";

    const NO_NAME = "name key not set";
    const NO_SURNAME = "surname key not set";
    const INVALID_NAME = "Invalid name. It can only consist of letters, spaces, dot (.) , comma (,) , apostrophe ('), dash (-) and can not be empty";
    const INVALID_SURNAME = "Invalid surname. It can only consist of letters, spaces, dot (.) , comma (,) , apostrophe ('), dash (-) and can not be empty";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $surname;

    /**
     * @ORM\Column(type="integer")
     */
    private $balance;

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
