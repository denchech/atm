<?php

namespace App\Entity;

use App\DBAL\Types\CardType;
use App\Repository\CardRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=CardRepository::class)
 */
class Card implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=10)
     */
    private string $number;

    /**
     * @ORM\Column(type="string")
     */
    private string $pin;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2)
     */
    private string $balance;

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

    public final function getBalance(): string
    {
        return $this->balance;
    }

    public final function setBalance(string $balance): void
    {
        $this->balance = $balance;
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

    public function getRoles(): array
    {
        return ['ROLE_'.CardType::getReadableValue($this->type)];
    }

    public function getPassword(): string
    {
        return $this->pin;
    }

    public function getSalt()
    {
    }

    public function getUsername(): string
    {
        return $this->number;
    }

    public function eraseCredentials()
    {
    }
}
