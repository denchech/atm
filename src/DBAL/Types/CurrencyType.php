<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class CurrencyType extends AbstractEnumType
{
    public const RUBLES  = 'R';
    public const DOLLARS = 'D';

    protected static $choices = [
        self::RUBLES  => '₽',
        self::DOLLARS => '$',
    ];
}
