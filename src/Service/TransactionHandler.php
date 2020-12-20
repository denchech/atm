<?php

namespace App\Service;

use App\DBAL\Types\OperationType;
use App\DBAL\Types\TransactionStatusType;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;

class TransactionHandler
{
    private TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
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

        $this->transactionRepository->save($transaction);

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
        } else {
            $card->setBalance(bcsub($balance, $value, 2));
            $transaction->setStatus(TransactionStatusType::FINISHED);
        }
    }

    private function transfer(Transaction $transaction): void
    {
        $value = $transaction->getValue();

        $firstCard    = $transaction->getFirstCard();
        $firstBalance = $firstCard->getBalance();

        $secondCard = $transaction->getSecondCard();
        $secondBalance = $secondCard->getBalance();

        if (-1 === bccomp($firstBalance, $value, 2)) {
            $transaction->setStatus(TransactionStatusType::CANCELLED);
        } else {
            $firstCard->setBalance(bcsub($firstBalance, $value, 2));
            $secondCard->setBalance(bcadd($secondBalance, $value, 2));
            $transaction->setStatus(TransactionStatusType::FINISHED);
        }
    }
}