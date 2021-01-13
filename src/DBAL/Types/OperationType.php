<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

final class OperationType extends AbstractEnumType
{
    public const WITHDRAWAL = 'W';
    public const RECHARGE   = 'R';
    public const TRANSFER   = 'T';

    protected static $choices = [
        self::WITHDRAWAL => 'Withdrawal',
        self::RECHARGE   => 'Recharge',
        self::TRANSFER   => 'Transfer',
    ];
}