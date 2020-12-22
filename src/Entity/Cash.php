<?php

namespace App\Entity;

use App\Repository\CashRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CashRepository::class)
 */
class Cash
{
    /**
     * @ORM\Id
     * @ORM\Column(type="CurrencyType")
     */
    private string $currency;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private int $value;

    /**
     * @ORM\Column(type="smallint")
     */
    private int $count = 0;

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }
}
