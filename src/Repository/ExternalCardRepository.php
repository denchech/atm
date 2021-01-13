<?php

namespace App\Repository;

use App\Entity\ExternalCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExternalCard|null find($id, $lockMode = null, $lockVersion = null)
 */
class ExternalCardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExternalCard::class);
    }

    public function save(ExternalCard $externalCard): void
    {
        $manager = $this->getEntityManager();

        $manager->persist($externalCard);
        $manager->flush($externalCard);
    }
}
