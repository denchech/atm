<?php

namespace App\DataFixtures;

use App\Entity\ExternalCard;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ExternalCardFixtures extends Fixture
{
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $externalCard = new ExternalCard();
        $externalCard->setNumber('1000000000');
        $externalCard->setBalance('1000.00');
        $externalCard->setPin($this->passwordEncoder->encodePassword($externalCard, '0000'));

        $manager->persist($externalCard);
        $manager->flush();
    }
}
