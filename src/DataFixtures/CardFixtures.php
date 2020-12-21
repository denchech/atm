<?php

namespace App\DataFixtures;

use App\DBAL\Types\CardType;
use App\Entity\Card;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CardFixtures extends Fixture
{
    public const COUNT = 3;

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
            $debitCard->setType(CardType::getValues()[$i % count(CardType::getValues())]);

            $manager->persist($debitCard);

            $this->addReference(Card::class.'_'.$i, $debitCard);
        }

        $manager->flush();
    }
}
