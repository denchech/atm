<?php

namespace App\Service;

use App\DBAL\Types\CardType;
use App\DBAL\Types\CurrencyType;
use App\Entity\CardInterface;
use App\Entity\Cash;
use App\Repository\CashRepository;

class Atm
{
    private CashRepository $cashRepository;

    public function __construct(CashRepository $cashRepository)
    {
        $this->cashRepository = $cashRepository;
    }

    public function saveCash(Cash $cash): void
    {
        $this->cashRepository->save($cash);
    }

    /**
     * @return Cash[]
     */
    public function findAllCashSorted(): array
    {
        return $this->cashRepository->findAllSorted();
    }

    public function prepareCash(int $value, string $currency = CurrencyType::RUBLES): array
    {
        $preparedCash = [];
        $allCash      = $this->cashRepository->findByCurrencyLessThanValueDesc($currency, $value);

        foreach ($allCash as $cash) {
            if ($value === 0) {
                break;
            }

            $requiredCount = intdiv($value, $cash->getValue());
            if ($requiredCount > $cash->getCount()) {
                $requiredCount = $cash->getCount();
            }

            $value -= $requiredCount * $cash->getValue();

            $preparedCash[] = ['cash' => $cash, 'count' => $requiredCount];
        }

        if ($value !== 0) {
            return [];
        }

        return $preparedCash;
    }

    public function removeCash(array $preparedCashArray): void
    {
        foreach ($preparedCashArray as $preparedCash) {
            /** @var Cash $cash */
            $cash          = $preparedCash['cash'];
            $requiredCount = $preparedCash['count'];

            $newCount = $cash->getCount() - $requiredCount;
            $cash->setCount($newCount);

            $this->cashRepository->save($cash);
        }
    }

    public function findCashByCurrencyAndValue(string $currency, int $value): ?Cash
    {
        return $this->cashRepository->findCashByCurrencyAndValue($currency, $value);
    }

    public function findAllForUser(CardInterface $card): array
    {
        return $this->cashRepository->findAllForUser(CardType::PREMIUM === $card->getType());
    }
}
