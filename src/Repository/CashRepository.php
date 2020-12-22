<?php

namespace App\Repository;

use App\Entity\Cash;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cash|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cash|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cash[]    findAll()
 * @method Cash[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CashRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cash::class);
    }

    public function save(Cash $cash): void
    {
        $manager = $this->getEntityManager();

        $manager->persist($cash);
        $manager->flush($cash);
    }

    /**
     * @return Cash[]
     */
    public function findAllSorted(): array
    {
        return $this->createQueryBuilder('c')
                    ->addOrderBy('c.currency')
                    ->addOrderBy('c.count')
                    ->getQuery()
                    ->getArrayResult()
            ;
    }

    public function findCashByCurrencyAndValue(string $currency, int $value): ?Cash
    {
        return $this->createQueryBuilder('c')
                    ->andWhere('c.currency = :currency')
                    ->andWhere('c.value = :value')
                    ->setParameter('currency', $currency)
                    ->setParameter('value', $value)
                    ->getQuery()
                    ->getOneOrNullResult()
            ;
    }

    /**
     * @param string $currency
     * @param int $value
     * @return Cash[]
     */
    public function findByCurrencyLessThanValueDesc(string $currency, int $value): array
    {
        return $this->createQueryBuilder('c')
                    ->andWhere('c.currency = :currency')
                    ->andWhere('c.value <= :value')
                    ->andWhere('c.count != 0')
                    ->orderBy('c.value', 'DESC')
                    ->setParameter('currency', $currency)
                    ->setParameter('value', $value)
                    ->getQuery()
                    ->getResult()
            ;
    }
}
