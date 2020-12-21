<?php

namespace App\Tests\Unit;

use App\DBAL\Types\CardType;
use App\DBAL\Types\OperationType;
use App\DBAL\Types\TransactionStatusType;
use App\Entity\Card;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use App\Service\BankSystem;
use App\Service\TransactionError;
use App\Service\TransactionHandler;
use PHPUnit\Framework\TestCase;

class TransactionHandlerTest extends TestCase
{
    private const FIRST_CARD_NUMBER  = '0000000000';
    private const SECOND_CARD_NUMBER = '0000000000';
    private const VALUE              = '10.50';

    private TransactionRepository $transactionRepository;

    private BankSystem $bankSystem;

    private TransactionHandler $transactionHandler;

    protected function setUp(): void
    {
        $this->bankSystem         = $this->createMock(BankSystem::class);
        $this->transactionHandler = new TransactionHandler($this->bankSystem);
    }

    /** @noinspection PhpParamsInspection */
    public function test_process_validRechargeOperation_finishedTransaction(): void
    {
        $firstCard   = $this->givenCard(self::FIRST_CARD_NUMBER, '100.00');
        $transaction = $this->givenTransaction(OperationType::RECHARGE, $firstCard, self::VALUE);

        $this->bankSystem->expects($this->once())->method('saveTransaction')->with($transaction);
        $this->bankSystem->expects($this->once())->method('saveCard')->with($firstCard);

        $result = $this->transactionHandler->process($transaction);

        TestCase::assertEquals(TransactionStatusType::FINISHED, $result->getStatus());
        TestCase::assertEquals('110.50', $firstCard->getBalance());
    }

    /** @noinspection PhpParamsInspection */
    public function test_process_validWithdrawalOperation_finishedTransaction(): void
    {
        $firstCard   = $this->givenCard(self::FIRST_CARD_NUMBER, '100.00');
        $transaction = $this->givenTransaction(OperationType::WITHDRAWAL, $firstCard, self::VALUE);

        $this->bankSystem->expects($this->once())->method('saveTransaction')->with($transaction);
        $this->bankSystem->expects($this->once())->method('saveCard')->with($firstCard);

        $result = $this->transactionHandler->process($transaction);

        TestCase::assertEquals(TransactionStatusType::FINISHED, $result->getStatus());
        TestCase::assertEquals('89.50', $firstCard->getBalance());
    }

    public function test_process_notValidWithdrawalOperation_cancelledTransaction(): void
    {
        $firstCard   = $this->givenCard(self::FIRST_CARD_NUMBER, '10.00');
        $transaction = $this->givenTransaction(OperationType::WITHDRAWAL, $firstCard, self::VALUE);

        $this->bankSystem->expects($this->never())->method('saveTransaction');

        $result = $this->transactionHandler->process($transaction);

        $this->assertCancelledOperation($result, TransactionError::NOT_ENOUGH_WHEREWITHAL, 'value');
        TestCase::assertEquals('10.00', $firstCard->getBalance());
    }

    /** @noinspection PhpParamsInspection */
    public function test_process_validTransferOperation_finishedTransaction(): void
    {
        $firstCard   = $this->givenCard(self::FIRST_CARD_NUMBER, '100.00');
        $secondCard  = $this->givenCard(self::SECOND_CARD_NUMBER, '100.00');
        $transaction = $this->givenTransaction(OperationType::TRANSFER, $firstCard, self::VALUE, $secondCard);

        $this->bankSystem->expects($this->exactly(2))->method('saveCard');
        $this->bankSystem->expects($this->once())->method('saveTransaction')->with($transaction);

        $result = $this->transactionHandler->process($transaction);

        TestCase::assertEquals(TransactionStatusType::FINISHED, $result->getStatus());
        TestCase::assertEquals('89.50', $firstCard->getBalance());
        TestCase::assertEquals('110.50', $secondCard->getBalance());
    }

    public function test_process_transferOperationToSameCard_cancelledTransaction(): void
    {
        $firstCard   = $this->givenCard(self::FIRST_CARD_NUMBER, '100.00');
        $transaction = $this->givenTransaction(OperationType::TRANSFER, $firstCard, self::VALUE, $firstCard);

        $this->bankSystem->expects($this->never())->method('saveTransaction');
        $this->bankSystem->expects($this->never())->method('saveCard');

        $result = $this->transactionHandler->process($transaction);

        $this->assertCancelledOperation($result, TransactionError::CANNOT_TRANSFER_TO_SAME_CARD, 'secondCard');
        TestCase::assertEquals('100.00', $firstCard->getBalance());
    }

    public function test_process_transferOperationToNotFoundCard_cancelledTransaction(): void
    {
        $firstCard   = $this->givenCard(self::FIRST_CARD_NUMBER, '100.00');
        $transaction = $this->givenTransaction(OperationType::TRANSFER, $firstCard, self::VALUE);

        $this->bankSystem->expects($this->never())->method('saveTransaction');
        $this->bankSystem->expects($this->never())->method('saveCard');

        $result = $this->transactionHandler->process($transaction);

        $this->assertCancelledOperation($result, TransactionError::CARD_NOT_FOUND, 'secondCard');
        TestCase::assertEquals('100.00', $firstCard->getBalance());
    }

    public function test_process_transferOperationWithoutEnoughWherewithal_cancelledTransaction(): void
    {
        $firstCard   = $this->givenCard(self::FIRST_CARD_NUMBER, '10.00');
        $secondCard  = $this->givenCard(self::SECOND_CARD_NUMBER, '1.00');
        $transaction = $this->givenTransaction(OperationType::TRANSFER, $firstCard, self::VALUE, $secondCard);

        $result = $this->transactionHandler->process($transaction);

        $this->assertCancelledOperation($result, TransactionError::NOT_ENOUGH_WHEREWITHAL, 'value');
        TestCase::assertEquals('10.00', $firstCard->getBalance());
    }

    public function test_process_unsupportedOperation_exceptionThrown(): void
    {
        $firstCard   = $this->givenCard(self::FIRST_CARD_NUMBER, '0.0');
        $transaction = $this->givenTransaction('operation', $firstCard, self::VALUE);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Operation "operation" does not support.');

        $this->transactionHandler->process($transaction);
    }

    private function assertCancelledOperation(Transaction $result, string $message, string $path): void
    {
        TestCase::assertEquals(TransactionStatusType::CANCELLED, $result->getStatus());
        TestCase::assertEquals($message, $result->getError()->getMessage());
        TestCase::assertEquals($path, $result->getError()->getPath());
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

    private function givenCard(string $number, string $balance, string $type = CardType::DEFAULT): Card
    {
        $card = new Card();
        $card->setNumber($number);
        $card->setBalance($balance);
        $card->setPin('0000');

        return $card;
    }
}
