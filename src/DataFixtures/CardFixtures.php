<?php

namespace App\DataFixtures;

use App\DBAL\Types\CardType;
use App\Entity\Card;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CardFixtures extends Fixture
{
    private const COUNT = 3;
    private const TYPES = [
        CardType::DEFAULT,
        CardType::PREMIUM,
        CardType::CREDIT,
    ];

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < self::COUNT; ++$i) {
            $debitCard = new Card();
            $debitCard->setNumber(str_repeat('0', 9 - intdiv($i, 10)).$i);
            $debitCard->setPin('0000');
            $debitCard->setBalance(0.0);
            $debitCard->setType(self::TYPES[$i % count(self::TYPES)]);

            $manager->persist($debitCard);
        }

        $manager->flush();
    }
}
