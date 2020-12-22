<?php

namespace App\Command;

use App\DBAL\Types\CurrencyType;
use Fresh\DoctrineEnumBundle\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

class WithdrawTransactionCommand
{
    /**
     * @Assert\Type(type="integer")
     * @Assert\Positive(message="positive")
     * @Assert\LessThanOrEqual(value="9999999999")
     * @Assert\NotBlank(message="notBlank")
     */
    private int $value;

    /**
     * @Assert\NotBlank()
     * @DoctrineAssert\Enum(entity=CurrencyType::class)
     */
    private string $currency;

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }
}