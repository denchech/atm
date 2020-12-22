<?php

namespace App\Command;

use App\DBAL\Types\CurrencyType;
use Fresh\DoctrineEnumBundle\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

class TransferTransactionCommand
{
    /**
     * @Assert\Regex(pattern="/^[0]+/", match=false, message="mustContainNumber")
     * @Assert\Regex(pattern="/^[0-9]{1,10}([.]\d{1,2})?$/", message="mustContainNumber")
     * @Assert\NotBlank(message="notBlank")
     */
    private string $value;

    /**
     * @Assert\Regex(pattern="/^\d+$/", message="mustContainNumber")
     * @Assert\Length(10)
     */
    private string $secondCard;

    /**
     * @Assert\NotBlank()
     * @DoctrineAssert\Enum(entity=CurrencyType::class)
     */
    private string $currency;

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getSecondCard(): string
    {
        return $this->secondCard;
    }

    public function setSecondCard(string $secondCard): void
    {
        $this->secondCard = $secondCard;
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