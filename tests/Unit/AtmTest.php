<?php

namespace App\Tests\Unit;

use App\Entity\Cash;
use App\Repository\CashRepository;
use App\Service\Atm;
use PHPUnit\Framework\TestCase;

class AtmTest extends TestCase
{
    private const CURRENCY      = 'currency';
    private const VALUE         = 5;
    private const BIG_VALUE     = 10;
    private const CASH_FIXTURES = [
        [
            'currency' => self::CURRENCY,
            'value'    => 2,
            'count'    => 1,
        ],
        [
            'currency' => self::CURRENCY,
            'value'    => 1,
            'count'    => 5,
        ],
    ];

    private Atm $atm;

    private CashRepository $cashRepository;

    protected function setUp()
    {
        $this->cashRepository = $this->createMock(CashRepository::class);
        $this->atm            = new Atm($this->cashRepository);
    }

    public function test_prepareCash_enoughCash_cashPrepared(): void
    {
        $returnedArray = $this->givenCardRepository_findByCurrencyLessThanValueDesc_returnsArray();

        $preparedCash = $this->atm->prepareCash(self::VALUE, self::CURRENCY);

        $this->assertSame(['cash' => $returnedArray[0], 'count' => 1], $preparedCash[0]);
        $this->assertSame(['cash' => $returnedArray[1], 'count' => 3], $preparedCash[1]);
    }

    public function test_prepareCash_notEnoughCash_emptyArrayReturned(): void
    {
        $returnedArray = $this->givenCardRepository_findByCurrencyLessThanValueDesc_returnsArray();

        $preparedCash = $this->atm->prepareCash(self::BIG_VALUE, self::CURRENCY);

        $this->assertEmpty($preparedCash);
    }

    public function test_removeCash_preparedCash_cashRemoved(): void
    {
        $cashArray    = $this->givenCashFixtures();
        $preparedCash = $this->givenPreparedCash($cashArray);

        $this->cashRepository->expects($this->exactly(count(self::CASH_FIXTURES)))->method('save');

        $this->atm->removeCash($preparedCash);

        $this->assertEquals(1, $cashArray[0]->getCount());
        $this->assertEquals(1, $cashArray[1]->getCount());
    }

    private function givenCardRepository_findByCurrencyLessThanValueDesc_returnsArray(): array
    {
        $result = $this->givenCashFixtures();

        $this->cashRepository
            ->method('findByCurrencyLessThanValueDesc')
            ->willReturn($result)
        ;

        return $result;
    }

    /**
     * @param Cash[] $cashArray
     */
    private function givenPreparedCash(array $cashArray): array
    {
        $preparedCash = [];

        foreach ($cashArray as $cash) {
            $preparedCash[] = ['cash' => $cash, 'count' => $cash->getCount() - 1];
        }

        return $preparedCash;
    }

    /**
     * @return Cash[]
     */
    private function givenCashFixtures(): array
    {
        $cashArray = [];

        foreach (self::CASH_FIXTURES as $cashFixture) {
            $cash = new Cash();
            $cash->setCurrency($cashFixture['currency']);
            $cash->setValue($cashFixture['value']);
            $cash->setCount($cashFixture['count']);

            $cashArray[] = $cash;
        }

        return $cashArray;
    }
}
