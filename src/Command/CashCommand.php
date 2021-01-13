<?php

namespace App\Command;

use App\DBAL\Types\CurrencyType;
use Fresh\DoctrineEnumBundle\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

class CashCommand
{
    /**
     * @Assert\NotBlank()
     * @DoctrineAssert\Enum(entity=CurrencyType::class)
     */
    private string $currency;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @Assert\Positive(message="positive")
     */
    private int $value;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @Assert\Positive(message="positive")
     * @Assert\LessThanOrEqual(value="100")
     */
    private int $count;

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