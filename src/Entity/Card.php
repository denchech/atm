<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CardRepository::class)
 */
class Card
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=10)
     */
    private string $number;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private string $pin;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2)
     */
    private float $balance;

    /**
     * @ORM\Column(type="CardType", nullable=false)
     */
    private string $type;

    public final function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

    public final function getBalance(): ?string
    {
        return $this->balance;
    }

    public final function setBalance(string $balance): void
    {
        $this->balance = $balance;
    }

    public final function getPin(): ?string
    {
        return $this->pin;
    }

    public final function setPin(string $pin): void
    {
        $this->pin = $pin;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
