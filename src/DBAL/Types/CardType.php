<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

final class CardType extends AbstractEnumType
{
    public const DEFAULT  = 'D';
    public const CREDIT   = 'C';
    public const PREMIUM  = 'P';
    public const EXTERNAL = 'E';

    protected static $choices = [
        self::DEFAULT  => 'DEFAULT',
        self::CREDIT   => 'CREDIT',
        self::PREMIUM  => 'PREMIUM',
        self::EXTERNAL => 'EXTERNAL',
    ];
}