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

    public function process(Transaction $transaction): void
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
                throw new \InvalidArgumentException("Operation \"{$transaction->getOperation()}\" is not supported.");
        }

        if (!$transaction->isCancelled()) {
            $this->bankSystem->saveTransaction($transaction);
            $this->bankSystem->saveCard($transaction->getFirstCard());
            null === $transaction->getSecondCard() ?: $this->bankSystem->saveCard($transaction->getSecondCard());
        }
    }

    private function recharge(Transaction $transaction): void
    {
        $this->checkCurrencyPermission($transaction);

        if ($transaction->isCancelled()) {
            return;
        }

        $card     = $transaction->getFirstCard();
        $currency = $transaction->getCurrency();
        $balance  = $card->getBalance($currency);

        $value = $this->getValueWithCommission($transaction);

        $card->setBalance(bcadd($balance, $value, 2), $currency);
        $transaction->setStatus(TransactionStatusType::FINISHED);
    }

    private function withdraw(Transaction $transaction): void
    {
        $this->checkCurrencyPermission($transaction);

        if ($transaction->isCancelled()) {
            return;
        }

        $card     = $transaction->getFirstCard();
        $currency = $transaction->getCurrency();
        $balance  = $card->getBalance($currency);
        $value    = $this->getValueWithCommission($transaction);

        if (-1 === bccomp($balance, $value, 2)) {
            $this->createTransactionError(
                $transaction,
                TransactionError::NOT_ENOUGH_WHEREWITHAL,
                'value'
            );
        } else {
            $card->setBalance(bcsub($balance, $value, 2), $currency);
            $transaction->setStatus(TransactionStatusType::FINISHED);
        }
    }

    private function transfer(Transaction $transaction): void
    {
        if (null === $transaction->getSecondCard()) {
            $this->createTransactionError(
                $transaction,
                TransactionError::CARD_NOT_FOUND,
                'secondCard'
            );

            return;
        }

        if ($transaction->getFirstCard() === $transaction->getSecondCard()) {
            $this->createTransactionError(
                $transaction,
                TransactionError::CANNOT_TRANSFER_TO_SAME_CARD,
                'secondCard'
            );

            return;
        }

        $currency = $transaction->getCurrency();

        $firstCard    = $transaction->getFirstCard();
        $firstBalance = $firstCard->getBalance($currency);

        $secondCard    = $transaction->getSecondCard();
        $secondBalance = $secondCard->getBalance($currency);

        $this->checkCurrencyPermission($transaction);

        if ($transaction->isCancelled()) {
            return;
        }

        $value = $transaction->getValue();

        if (-1 === bccomp($firstBalance, $value, 2)) {
            $this->createTransactionError(
                $transaction,
                TransactionError::NOT_ENOUGH_WHEREWITHAL,
                'value'
            );
        } else {
            $firstCard->setBalance(bcsub($firstBalance, $value, 2), $currency);

            $value = $this->getValueWithCommission($transaction);

            $secondCard->setBalance(bcadd($secondBalance, $value, 2), $currency);
            $transaction->setStatus(TransactionStatusType::FINISHED);
        }
    }

    private function checkCurrencyPermission(Transaction $transaction): void
    {
        $firstCard  = $transaction->getFirstCard();
        $secondCard = $transaction->getSecondCard();
        $currency   = $transaction->getCurrency();

        $firstCardHasNoPermission  = CurrencyType::RUBLES !== $currency && CardType::PREMIUM !== $firstCard->getType();
        $secondCardHasNoPermission = $secondCard
            ? CurrencyType::RUBLES !== $currency && CardType::PREMIUM !== $secondCard->getType()
            : false;

        if ($firstCardHasNoPermission || $secondCardHasNoPermission) {
            $this->createTransactionError(
                $transaction,
                TransactionError::NOT_ALLOWED_TO_CHOOSE_CURRENCY,
                'currency'
            );
        }
    }

    private function createTransactionError(Transaction $transaction, string $message, string $path): void
    {
        $error = new TransactionError();
        $error->setMessage($message);
        $error->setPath($path);

        $transaction->setError($error);
        $transaction->setStatus(TransactionStatusType::CANCELLED);
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