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

//    /**
//     * @param Card $card
//     * @return Transaction[]
//     */
//    public function findTransactionByCard(Card $card): array
//    {
//        $query = $this->createQueryBuilder('t')
//                      ->select('t')
//                      ->join('t.firstCard', 'c1')
//                      ->join('t.secondCard', 'c2')
//                      ->where('c1.number = :cardNumber or c2.number = :cardNumber')
//                      ->setParameter('cardNumber', $card->getNumber())
//                      ->getQuery()
//        ;
//
//        return $query->getArrayResult();
//    }
}
