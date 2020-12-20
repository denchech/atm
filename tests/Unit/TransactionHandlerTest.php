<?php

namespace App\Tests\Unit;

use App\DBAL\Types\OperationType;
use App\DBAL\Types\TransactionStatusType;
use App\Entity\Card;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use App\Service\TransactionHandler;
use PHPUnit\Framework\TestCase;


class TransactionHandlerTest extends TestCase
{

    private const FIRST_CARD_NUMBER  = '0000000000';
    private const SECOND_CARD_NUMBER = '0000000000';
    private const VALUE              = '10.50';

    private TransactionRepository $transactionRepository;
    private TransactionHandler $transactionHandler;

    protected function setUp(): void
    {
        $this->transactionRepository = $this->createMock(TransactionRepository::class);
        $this->transactionHandler = new TransactionHandler($this->transactionRepository);
    }

    public function test_process_validRechargeOperation_finishedTransaction(): void
    {
        $firstCard   = $this->givenCard(self::FIRST_CARD_NUMBER, '100.00');
        $transaction = $this->givenTransaction(OperationType::RECHARGE, $firstCard, self::VALUE);

        /** @noinspection PhpParamsInspection */
        $this->transactionRepository->expects($this->once())->method('save')->with($transaction);

        $result = $this->transactionHandler->process($transaction);

        TestCase::assertEquals(TransactionStatusType::FINISHED, $result->getStatus());
        TestCase::assertEquals('110.50', $firstCard->getBalance());

    }

    public function test_process_validWithdrawalOperation_finishedTransaction(): void
    {
        $firstCard   = $this->givenCard(self::FIRST_CARD_NUMBER, '100.00');
        $transaction = $this->givenTransaction(OperationType::WITHDRAWAL, $firstCard, self::VALUE);

        /** @noinspection PhpParamsInspection */
        $this->transactionRepository->expects($this->once())->method('save')->with($transaction);

        $result = $this->transactionHandler->process($transaction);

        TestCase::assertEquals(TransactionStatusType::FINISHED, $result->getStatus());
        TestCase::assertEquals('89.50', $firstCard->getBalance());

    }

    public function test_process_notValidWithdrawalOperation_cancelledTransaction(): void
    {
        $firstCard   = $this->givenCard(self::FIRST_CARD_NUMBER, '10.00');
        $transaction = $this->givenTransaction(OperationType::WITHDRAWAL, $firstCard, self::VALUE);

        /** @noinspection PhpParamsInspection */
        $this->transactionRepository->expects($this->once())->method('save')->with($transaction);

        $result = $this->transactionHandler->process($transaction);

        TestCase::assertEquals(TransactionStatusType::CANCELLED, $result->getStatus());
        TestCase::assertEquals('10.00', $firstCard->getBalance());

    }

    public function test_process_validTransferOperation_finishedTransaction(): void
    {
        $firstCard   = $this->givenCard(self::FIRST_CARD_NUMBER, '100.00');
        $secondCard = $this->givenCard(self::SECOND_CARD_NUMBER, '100.00');
        $transaction = $this->givenTransaction(OperationType::TRANSFER, $firstCard, self::VALUE, $secondCard);

        /** @noinspection PhpParamsInspection */
        $this->transactionRepository->expects($this->once())->method('save')->with($transaction);

        $result = $this->transactionHandler->process($transaction);

        TestCase::assertEquals(TransactionStatusType::FINISHED, $result->getStatus());
        TestCase::assertEquals('89.50', $firstCard->getBalance());
        TestCase::assertEquals('110.50', $secondCard->getBalance());

    }

    public function test_process_unsupportedOperation_exceptionThrown(): void
    {
        $firstCard   = $this->givenCard(self::FIRST_CARD_NUMBER, '0.0');
        $transaction = $this->givenTransaction('operation', $firstCard, self::VALUE);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Operation "operation" does not support.');

        $this->transactionHandler->process($transaction);
    }

    private function givenTransaction(
        string $operation,
        Card $firstCard,
        string $value,
        Card $secondCard = null
    ): Transaction {
        $transaction = new Transaction();
        $transaction->setOperation($operation);
        $transaction->setFirstCard($firstCard);
        $transaction->setValue($value);
        $transaction->setSecondCard($secondCard);

        return $transaction;
    }

    private function givenCard(string $number, string $balance): Card
    {
        $card = new Card();
        $card->setNumber($number);
        $card->setBalance($balance);
        $card->setPin('0000');

        return $card;
    }
}
