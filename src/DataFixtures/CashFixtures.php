<?php

namespace App\DataFixtures;

use App\DBAL\Types\CurrencyType;
use App\Entity\Cash;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CashFixtures extends Fixture
{
    private const FIXTURES = [
        [
            'currency' => CurrencyType::RUBLES,
            'value'    => 100,
            'count'    => 100,
        ],
        [
            'currency' => CurrencyType::RUBLES,
            'value'    => 500,
            'count'    => 100,
        ],
        [
            'currency' => CurrencyType::RUBLES,
            'value'    => 1000,
            'count'    => 100,
        ],
        [
            'currency' => CurrencyType::RUBLES,
            'value'    => 5000,
            'count'    => 100,
        ],
        [
            'currency' => CurrencyType::DOLLARS,
            'value'    => 1,
            'count'    => 10,
        ],
        [
            'currency' => CurrencyType::DOLLARS,
            'value'    => 10,
            'count'    => 10,
        ],
        [
            'currency' => CurrencyType::DOLLARS,
            'value'    => 50,
            'count'    => 10,
        ],
        [
            'currency' => CurrencyType::DOLLARS,
            'value'    => 100,
            'count'    => 10,
        ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::FIXTURES as $fixture) {
            $cash = new Cash();
            $cash->setCurrency($fixture['currency']);
            $cash->setValue($fixture['value']);
            $cash->setCount($fixture['count']);

            $manager->persist($cash);
        }

        $manager->flush();
    }
}
