<?php

namespace App\DataFixtures;

use App\DBAL\Types\CardType;
use App\Entity\Card;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CardFixtures extends Fixture
{
    public const COUNT = 3;

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < self::COUNT; ++$i) {
            $debitCard = new Card();
            $debitCard->setNumber(str_repeat('0', 9 - intdiv($i, 10)).$i);
            $debitCard->setPin('0000');
            $debitCard->setBalance((string) rand(10000, 100000) / 100);
            $debitCard->setType(CardType::getValues()[$i % count(CardType::getValues())]);

            $manager->persist($debitCard);

            $this->addReference(Card::class . '_' . $i, $debitCard);
        }

        $manager->flush();
    }
}
