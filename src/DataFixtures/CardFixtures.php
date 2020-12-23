<?php

namespace App\DataFixtures;

use App\DBAL\Types\CardType;
use App\DBAL\Types\CurrencyType;
use App\Entity\Card;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CardFixtures extends Fixture
{
    public const COUNT = 6;
    private const TYPES = [
        CardType::DEFAULT,
        CardType::CREDIT,
        CardType::PREMIUM,
    ];

    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < self::COUNT; ++$i) {
            $debitCard = new Card();
            $debitCard->setNumber(str_repeat('0', 9 - intdiv($i, 10)).$i);
            $debitCard->setPin($this->passwordEncoder->encodePassword($debitCard, '0000'));
            $debitCard->setBalance('1000.00');
            $debitCard->setType(self::TYPES[$i % count(self::TYPES)]);

            if (CardType::PREMIUM === $debitCard->getType()) {
                $debitCard->setBalance('1000.0', CurrencyType::DOLLARS);
            }

            $manager->persist($debitCard);

            $this->addReference(Card::class.'_'.$i, $debitCard);
        }

        $manager->flush();
    }
}
