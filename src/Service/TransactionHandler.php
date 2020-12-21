<?php

namespace App\Service;

use App\DBAL\Types\OperationType;
use App\DBAL\Types\TransactionStatusType;
use App\Entity\Transaction;

class TransactionHandler
{
    private BankSystem $bankSystem;

    public function __construct(BankSystem $bankSystem)
    {
        $this->bankSystem = $bankSystem;
    }

    public function process(Transaction $transaction): Transaction
    {
        switch ($transaction->getOperation()) {
            case OperationType::RECHARGE:
                $this->recharge($transaction);
                break;
            case OperationType::WITHDRAWAL:
                $this->withdraw($transaction);
                break;
            case OperationType::TRANSFER:
                $this->transfer($transaction);
                break;
            default:
                throw new \InvalidArgumentException("Operation \"{$transaction->getOperation()}\" does not support.");
        }

        if (!$transaction->isCancelled()) {
            $this->bankSystem->saveTransaction($transaction);
            $this->bankSystem->saveCard($transaction->getFirstCard());
            null === $transaction->getSecondCard() ?: $this->bankSystem->saveCard($transaction->getSecondCard());
        }

        return $transaction;
    }

    private function recharge(Transaction $transaction): void
    {
        $value   = $transaction->getValue();
        $card    = $transaction->getFirstCard();
        $balance = $card->getBalance();

        $card->setBalance(bcadd($balance, $value, 2));
        $transaction->setStatus(TransactionStatusType::FINISHED);
    }

    private function withdraw(Transaction $transaction): void
    {
        $value   = $transaction->getValue();
        $card    = $transaction->getFirstCard();
        $balance = $card->getBalance();

        if (-1 === bccomp($balance, $value, 2)) {
            $transaction->setStatus(TransactionStatusType::CANCELLED);
            $transaction->setError(
                $this->createTransactionError(
                    TransactionError::NOT_ENOUGH_WHEREWITHAL,
                    'value'
                )
            );
        } else {
            $card->setBalance(bcsub($balance, $value, 2));
            $transaction->setStatus(TransactionStatusType::FINISHED);
        }
    }

    private function transfer(Transaction $transaction): void
    {
        if (null === $transaction->getSecondCard()) {
            $transaction->setStatus(TransactionStatusType::CANCELLED);
            $transaction->setError(
                $this->createTransactionError(
                    TransactionError::CARD_NOT_FOUND,
                    'secondCard'
                )
            );

            return;
        }

        if ($transaction->getFirstCard() === $transaction->getSecondCard()) {
            $transaction->setStatus(TransactionStatusType::CANCELLED);
            $transaction->setError(
                $this->createTransactionError(
                    TransactionError::CANNOT_TRANSFER_TO_SAME_CARD,
                    'secondCard'
                )
            );

            return;
        }

        $value = $transaction->getValue();

        $firstCard    = $transaction->getFirstCard();
        $firstBalance = $firstCard->getBalance();

        $secondCard    = $transaction->getSecondCard();
        $secondBalance = $secondCard->getBalance();

        if (-1 === bccomp($firstBalance, $value, 2)) {
            $transaction->setStatus(TransactionStatusType::CANCELLED);
            $transaction->setError(
                $this->createTransactionError(
                    TransactionError::NOT_ENOUGH_WHEREWITHAL,
                    'value'
                )
            );
        } else {
            $firstCard->setBalance(bcsub($firstBalance, $value, 2));
            $secondCard->setBalance(bcadd($secondBalance, $value, 2));
            $transaction->setStatus(TransactionStatusType::FINISHED);
        }
    }

    private function createTransactionError(string $message, string $path): TransactionError
    {
        $error = new TransactionError();
        $error->setMessage($message);
        $error->setPath($path);

        return $error;
    }
}