<?php

namespace App\Service;

use App\DBAL\Types\CardType;
use App\Entity\Card;
use App\Entity\Transaction;
use App\Repository\CardRepository;
use App\Repository\ExternalCardRepository;
use App\Repository\TransactionRepository;

class BankSystem
{
    private CardRepository $cardRepository;

    private TransactionRepository $transactionRepository;

    private ExternalCardRepository $externalCardRepository;

    public function __construct(
        CardRepository $cardRepository,
        TransactionRepository $transactionRepository,
        ExternalCardRepository $externalCardRepository
    )
    {
        $this->cardRepository        = $cardRepository;
        $this->transactionRepository = $transactionRepository;
        $this->externalCardRepository = $externalCardRepository;
    }

    public function findCard(string $number): ?Card
    {
        return $this->cardRepository->find($number);
    }

    public function saveCard(Card $card): void
    {
        if (CardType::EXTERNAL === $card->getType()) {
            $externalCard = $this->externalCardRepository->find($card->getNumber());
            $externalCard->setBalance($card->getBalance());

            $this->externalCardRepository->save($externalCard);
        }

        $this->cardRepository->save($card);
    }

    public function saveTransaction(Transaction $transaction): void
    {
        $this->transactionRepository->save($transaction);
    }

    /**
     * @param Card $card
     * @return Transaction[]
     */
    public function getTransactions(Card $card): array
    {
        return $this->transactionRepository->findTransactionsByCardNumber($card->getNumber());
    }
}