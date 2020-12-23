<?php

namespace App\Service;

use App\DBAL\Types\CardType;
use App\DBAL\Types\CurrencyType;
use App\DBAL\Types\OperationType;
use App\DBAL\Types\TransactionStatusType;
use App\Entity\Transaction;
use App\Error\TransactionError;

class TransactionHandler
{
    private BankSystem $bankSystem;

    private string $commission;

    public function __construct(BankSystem $bankSystem, string $commission)
    {
        $this->bankSystem = $bankSystem;
        $this->commission = bcmul($commission, '0.01', 2);
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
        $card     = $transaction->getFirstCard();
        $currency = $transaction->getCurrency();
        $balance  = $card->getBalance($currency);

        if (CurrencyType::RUBLES !== $currency && CardType::PREMIUM !== $card->getType()) {
            $transaction->setStatus(TransactionStatusType::CANCELLED);
            $transaction->setError(
                $this->createTransactionError(
                    TransactionError::NOT_ALLOWED_TO_CHOOSE_CURRENCY,
                    'currency'
                )
            );

            return;
        }

        $value = $this->getValueWithCommission($transaction);

        $card->setBalance(bcadd($balance, $value, 2), $currency);
        $transaction->setStatus(TransactionStatusType::FINISHED);
    }

    private function withdraw(Transaction $transaction): void
    {
        $value    = $transaction->getValue();
        $card     = $transaction->getFirstCard();
        $currency = $transaction->getCurrency();
        $balance  = $card->getBalance($currency);

        if (CurrencyType::RUBLES !== $currency && CardType::PREMIUM !== $card->getType()) {
            $transaction->setStatus(TransactionStatusType::CANCELLED);
            $transaction->setError(
                $this->createTransactionError(
                    TransactionError::NOT_ALLOWED_TO_CHOOSE_CURRENCY,
                    'currency'
                )
            );

            return;
        }

        $value = $this->getValueWithCommission($transaction);

        if (-1 === bccomp($balance, $value, 2)) {
            $transaction->setStatus(TransactionStatusType::CANCELLED);
            $transaction->setError(
                $this->createTransactionError(
                    TransactionError::NOT_ENOUGH_WHEREWITHAL,
                    'value'
                )
            );
        } else {
            $card->setBalance(bcsub($balance, $value, 2), $currency);
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

        $currency = $transaction->getCurrency();

        $firstCard    = $transaction->getFirstCard();
        $firstBalance = $firstCard->getBalance($currency);

        $secondCard    = $transaction->getSecondCard();
        $secondBalance = $secondCard->getBalance($currency);

        if (
            CurrencyType::RUBLES !== $currency &&
            (CardType::PREMIUM !== $firstCard->getType() || CardType::PREMIUM !== $secondCard->getType())
        ) {
            $transaction->setStatus(TransactionStatusType::CANCELLED);
            $transaction->setError(
                $this->createTransactionError(
                    TransactionError::NOT_ALLOWED_TO_CHOOSE_CURRENCY,
                    'currency'
                )
            );

            return;
        }

        $value = $transaction->getValue();

        if (-1 === bccomp($firstBalance, $value, 2)) {
            $transaction->setStatus(TransactionStatusType::CANCELLED);
            $transaction->setError(
                $this->createTransactionError(
                    TransactionError::NOT_ENOUGH_WHEREWITHAL,
                    'value'
                )
            );
        } else {
            $firstCard->setBalance(bcsub($firstBalance, $value, 2), $currency);

            $value = $this->getValueWithCommission($transaction);

            $secondCard->setBalance(bcadd($secondBalance, $value, 2), $currency);
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

    private function getValueWithCommission(Transaction $transaction): string
    {
        $firstCard  = $transaction->getFirstCard();
        $secondCard = $transaction->getSecondCard();
        $value      = $transaction->getValue();
        if (
            CardType::EXTERNAL === $firstCard->getType()
            || (null !== $secondCard && CardType::EXTERNAL === $secondCard->getType())
        ) {
            if (OperationType::WITHDRAWAL === $transaction->getOperation()) {
                $commissionMultiplier = bcadd('1.00', $this->commission, 2);
            } else {
                $commissionMultiplier = bcsub('1.00', $this->commission, 2);
            }

            $value = bcmul($value, $commissionMultiplier, 2);
        }

        return $value;
    }
}