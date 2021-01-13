<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

final class TransactionStatusType extends AbstractEnumType
{
    public const STARTED   = 'S';
    public const FINISHED  = 'F';
    public const CANCELLED = 'C';

    protected static $choices = [
        self::FINISHED  => 'Finished',
        self::CANCELLED => 'Cancelled',
    ];
}