<?php

namespace Fhp\Model\StatementOfHoldings;

use Fhp\MT535\MT535;

class StatementOfHoldings
{
    /**
     * @var Holding[]
     */
    protected $holdings = [];

    /**
     * Get statements
     *
     * @return Holding[]
     */
    public function getHoldings(): array
    {
        return $this->holdings;
    }

    private static function parseDate(string $date): \DateTime
    {
        try {
            return new \DateTime($date);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid date: $date", 0, $e);
        }
    }

    /**
     * @param array $array A parsed MT535 dataset, as returned from {@link MT535::parse()}.
     * @return StatementOfHoldings A new instance that contains the given data.
     */
    public static function fromMT535Object($array): StatementOfHoldings
    {
        $result = new StatementOfHoldings();
        foreach ($array as $h) {
            $holdingModel = new Holding();
            if (property_exists($h, 'date')) {
                $holdingModel->setDate(static::parseDate($h->date));
            }
            if (property_exists($h, 'isin')) {
                $holdingModel->setISIN($h->isin);
            }
            if (property_exists($h, 'wkn')) {
                $holdingModel->setWKN($h->wkn);
            }
            if (property_exists($h, 'name')) {
                $holdingModel->setName($h->name);
            }
            if (property_exists($h, 'price')) {
                if ($h->currency == '%') {
                    $holdingModel->setPrice($h->price / 100);
                } else {
                    $holdingModel->setPrice($h->price);
                }
            }
            if (property_exists($h, 'amount')) {
                $holdingModel->setAmount($h->amount);
            }
            if (property_exists($h, 'price') && property_exists($h, 'amount')) {
                if ($h->currency == '%') {
                    $holdingModel->setValue($h->price / 100);
                } else {
                    $holdingModel->setValue($h->price * $h->amount);
                }
            }
            if (property_exists($h, 'currency')) {
                $holdingModel->setCurrency($h->currency);
            }
            if (property_exists($h, 'time')) {
                $holdingModel->setTime($h->time);
            }

            $result->statements[] = $holdingModel;
        }
        return $result;
    }
}
