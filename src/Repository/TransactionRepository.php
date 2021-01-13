<?php

namespace App\Repository;

use App\Entity\Card;
use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function save(Transaction $transaction): void
    {
        $manager = $this->getEntityManager();

        $manager->persist($transaction);
        $manager->flush($transaction);
    }

    /**
     * @param string $number
     * @return Transaction[]
     */
    public function findTransactionsByCardNumber(string $number): array
    {
        $query = $this->createQueryBuilder('t')
                      ->select('t')
                      ->join('t.firstCard', 'c1')
                      ->leftJoin('t.secondCard', 'c2')
                      ->where('c1.number = :cardNumber')
                      ->orWhere('c2.number = :cardNumber')
                      ->setParameter('cardNumber', $number)
                      ->getQuery()
        ;

        return $query->getResult();
    }
}
