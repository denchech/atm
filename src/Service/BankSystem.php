<?php

namespace App\Service;

use App\Entity\Card;
use App\Entity\Transaction;
use App\Repository\CardRepository;
use App\Repository\TransactionRepository;

class BankSystem
{
    private CardRepository $cardRepository;

    private TransactionRepository $transactionRepository;

    public function __construct(CardRepository $cardRepository, TransactionRepository $transactionRepository)
    {
        $this->cardRepository        = $cardRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function findCard(string $number): ?Card
    {
        return $this->cardRepository->find($number);
    }

    public function saveCard(Card $card): void
    {
        $this->cardRepository->save($card);
    }

    public function saveTransaction(Transaction $transaction): void
    {
        $this->transactionRepository->save($transaction);
    }
}