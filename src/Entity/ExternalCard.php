<?php

namespace App\Entity;

use App\DBAL\Types\CardType;
use App\Repository\ExternalCardRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExternalCardRepository::class)
 */
class ExternalCard implements CardInterface
{

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=10)
     */
    private string $number;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $pin;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2)
     */
    private string $balance;

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->pin;
    }

    public function setPin(string $pin): self
    {
        $this->pin = $pin;

        return $this;
    }

    public function getBalance(string $currency): string
    {
        return $this->balance;
    }

    public function setBalance(string $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getType(): string
    {
        return CardType::EXTERNAL;
    }

    public function getRoles()
    {
        return [];
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
        return $this->number;
    }

    public function eraseCredentials()
    {
    }
}
