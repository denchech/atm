<?php

namespace App\DataFixtures;

use App\DBAL\Types\OperationType;
use App\Entity\Card;
use App\Entity\Transaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TransactionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $transactions = [];

        $transactions[] = $this->createWithdrawalOperation(0);
        $transactions[] = $this->createRechargeOperation(1);
        $transactions[] = $this->createTransferOperation(2);

        foreach ($transactions as $transaction) {
            $manager->persist($transaction);
        }

        $manager->flush();
    }

    private function createWithdrawalOperation(int $index): Transaction
    {
        $transaction = new Transaction();
        $transaction->setOperation(OperationType::WITHDRAWAL);

        /** @var Card $card */
        $card = $this->getReference(Card::class.'_'.$index);

        $transaction->setFirstCard($card);
        $transaction->setValue((string) rand(1, 10000) / 100);

        return $transaction;
    }

    private function createRechargeOperation(int $index): Transaction
    {
        $transaction = new Transaction();
        $transaction->setOperation(OperationType::RECHARGE);

        /** @var Card $card */
        $card = $this->getReference(Card::class.'_'.$index);

        $transaction->setFirstCard($card);
        $transaction->setValue((string) rand(1, 10000) / 100);

        return $transaction;
    }

    private function createTransferOperation(int $index): Transaction
    {
        $transaction = new Transaction();
        $transaction->setOperation(OperationType::TRANSFER);

        /** @var Card $firstCard */
        $firstCard = $this->getReference(Card::class.'_'.$index);

        /** @var Card $secondCard */
        $secondCard = $this->getReference(Card::class.'_'.(CardFixtures::COUNT - 1 - $index));

        $transaction->setFirstCard($firstCard);
        $transaction->setSecondCard($secondCard);
        $transaction->setValue((string) rand(1, 10000) / 100);

        return $transaction;
    }
}
