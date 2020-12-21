<?php

namespace App\Service;

class TransactionError
{
    public const NOT_ENOUGH_WHEREWITHAL       = 'notEnoughWherewithal';
    public const CARD_NOT_FOUND               = 'cardNotFound';
    public const CANNOT_TRANSFER_TO_SAME_CARD = 'cannotTransferToSameCard';

    private string $message;

    private string $path;

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }
}