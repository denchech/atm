<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

final class CardType extends AbstractEnumType
{
    public const DEFAULT = 'D';
    public const CREDIT  = 'C';
    public const PREMIUM = 'P';

    protected static $choices = [
        self::DEFAULT => 'Default',
        self::CREDIT  => 'Credit',
        self::PREMIUM => 'Premium',
    ];
}