<?php
declare(strict_types=1);

namespace ArrayIterator\ProjectModule\CurrencyRates;

use JsonSerializable;

/**
 * Class Rates
 * @package ArrayIterator\ProjectModule\CurrencyRates
 */
class Rates implements JsonSerializable
{
    /**
     * @var Rate[]
     */
    protected $rates = [];

    /**
     * @var AbstractProvider
     */
    protected $exchangeProvider;

    /**
     * Rates constructor.
     * @param AbstractProvider $provider
     */
    public function __construct(AbstractProvider $provider)
    {
        $this->exchangeProvider = $provider;
    }

    /**
     * @return Rate[]
     */
    public function getRates(): array
    {
        return $this->rates;
    }

    /**
     * @return AbstractProvider
     */
    public function getExchangeProvider(): AbstractProvider
    {
        return $this->exchangeProvider;
    }

    /**
     * @param Rate $rate
     */
    public function addRate(Rate $rate)
    {
        $code = $this->getExchangeProvider()->normalizeCurrency($rate->getCode());
        $this->rates[$code] = $rate;
    }

    /**
     * @param Rate $rate
     */
    public function removeByRate(Rate $rate)
    {
        $code = $this->getExchangeProvider()->normalizeCurrency($rate->getCode());
        unset($this->rates[$code]);
    }

    /**
     * @param string $rate
     */
    public function remove(string $rate)
    {
        unset($this->rates[$rate]);
    }

    /**
     * @param string $code
     * @return Rate|null
     */
    public function get(string $code)
    {
        $code = $this->getExchangeProvider()->normalizeCurrency($code);
        return $this->rates[$code]??null;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function has(string $code)
    {
        $code = $this->getExchangeProvider()->normalizeCurrency($code);
        return isset($this->rates[$code]);
    }

    /**
     * @return array
     */
    public function jsonSerialize() : array
    {
        $exchange = $this->getExchangeProvider();
        $rates = [];
        foreach ($this->getRates() as $key => $rate) {
            $rates[$key] = $rate->toArray();
            $rates[$key]['info'] = $rate->getCurrencyInfo();
        }
        return [
            'provider' => [
                'name' => $exchange->getProviderName(),
                'code' => $exchange->getProviderCode(),
                'url' => $exchange->getProviderUrl(),
            ],
            'code' => $exchange->getBaseCode(),
            'rates' => $rates
        ];
    }

    /**
     * @param Rate $rate
     * @param int $round
     * @return array
     */
    public function fromRate(Rate $rate, int $round = 4)
    {
        $exchange = $this->getExchangeProvider();
        $rates = [];
        $baseCode = $rate->getCode();
        $baseRateArray = $rate->toArray();
        $currentArray  = $this->get($exchange->getBaseCode())->toArray();
        foreach ($baseRateArray as $key => $v) {
            if ($v === null || $currentArray[$key]) {
                $baseRateArray[$key] = null;
                continue;
            }
            $baseRateArray[$key] = $currentArray[$key]/$v;
        }

        /**
         * @var Rate $rate
         */
        foreach ($this->getRates() as $key => $rate) {
            $array = $rate->toArray();
            if ($baseCode === $rate->getCode()) {
                $array = array_map(function () {
                    return 1.0;
                }, $array);
            } else {
                foreach ($array as $c => $v) {
                    if ($v === null || $currentArray[$c] === null || $baseRateArray[$c]) {
                        $array[$c] = null;
                        continue;
                    }
                    $array[$c] = round($currentArray[$c] / $v / $baseRateArray[$c], $round);
                }
            }

            $rates[$key] = $array;
            $rates[$key]['info'] = $rate->getCurrencyInfo();
        }
        return [
            'provider' => [
                'name' => $exchange->getProviderName(),
                'code' => $exchange->getProviderCode(),
                'url' => $exchange->getProviderUrl(),
            ],
            'code'  => $baseCode,
            'rates' => $rates
        ];
    }
}
