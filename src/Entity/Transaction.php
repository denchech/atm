<?php

namespace App\Entity;

use App\DBAL\Types\TransactionStatusType;
use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TransactionRepository::class)
 */
class Transaction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Card::class)
     * @ORM\JoinColumn(nullable=false, referencedColumnName="number")
     */
    private Card $firstCard;

    /**
     * @ORM\ManyToOne(targetEntity=Card::class)
     * @ORM\JoinColumn (referencedColumnName="number")
     */
    private ?Card $secondCard = null;

    /**
     * @ORM\Column(type="OperationType", nullable=false)
     */
    private string $operation;

    /**
     * @ORM\Column(type="TransactionStatusType", nullable=false)
     */
    private string $status = TransactionStatusType::STARTED;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2)
     */
    private string $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstCard(): Card
    {
        return $this->firstCard;
    }

    public function setFirstCard(Card $firstCard): void
    {
        $this->firstCard = $firstCard;
    }

    public function getSecondCard(): ?Card
    {
        return $this->secondCard;
    }

    public function setSecondCard(?Card $secondCard): void
    {
        $this->secondCard = $secondCard;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function setOperation(string $operation): void
    {
        $this->operation = $operation;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
