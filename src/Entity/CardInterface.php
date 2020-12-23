<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

interface CardInterface extends UserInterface
{
    public function getNumber(): string;

    public function getBalance(string $currency): string;

    public function getType(): string;
}