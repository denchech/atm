<?php

namespace App\Entity;

use App\DBAL\Types\CardType;
use App\DBAL\Types\CurrencyType;
use App\Repository\CardRepository;
use Doctrine\ORM\Mapping as ORM;
use Fresh\DoctrineEnumBundle\Exception\EnumType\EnumTypeIsNotRegisteredException;
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
    private string $balanceRub = '0';

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2)
     */
    private string $balanceUsd = '0';

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

    public final function getBalance(string $currency = CurrencyType::RUBLES): string
    {
        switch ($currency) {
            case CurrencyType::RUBLES:
                return $this->balanceRub;
            case CurrencyType::DOLLARS:
                return $this->balanceUsd;
            default:
                throw new EnumTypeIsNotRegisteredException("Currency $currency does not exist.");
        }
    }

    public final function setBalance(string $balance, string $currency = CurrencyType::RUBLES): void
    {
        switch ($currency) {
            case CurrencyType::RUBLES:
                $this->balanceRub = $balance;
                break;
            case CurrencyType::DOLLARS:
                $this->balanceUsd = $balance;
                break;
            default:
                throw new EnumTypeIsNotRegisteredException("Currency '$currency' does not exist.");
        }
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

    public function getBalanceRub(): string
    {
        return $this->balanceRub;
    }

    public function setBalanceRub(string $balanceRub): void
    {
        $this->balanceRub = $balanceRub;
    }

    public function getBalanceUsd(): string
    {
        return $this->balanceUsd;
    }

    public function setBalanceUsd(string $balanceUsd): void
    {
        $this->balanceUsd = $balanceUsd;
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
