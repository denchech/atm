<?php

namespace App\Command;

use Symfony\Component\Validator\Constraints as Assert;

class TransactionCommand
{
    /**
     * @Assert\Type(type="integer")
     * @Assert\GreaterThan(value="0", message="positive")
     * @Assert\LessThanOrEqual(value="9999999999", message="cannotBeMore")
     * @Assert\NotBlank(message="notBlank")
     */
    private int $value;

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }
}