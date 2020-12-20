<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class TransactionStatusType extends AbstractEnumType
{
    public const STARTED  = 'S';
    public const FINISHED = 'F';
    public const CANCELED = 'C';
}