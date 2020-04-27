<?php
declare(strict_types=1);

namespace ArrayIterator\ProjectModule\CurrencyRates\Provider;

use ArrayIterator\ProjectModule\CurrencyRates\AbstractProvider;
use ArrayIterator\ProjectModule\CurrencyRates\Rate;
use ArrayIterator\ProjectModule\CurrencyRates\Rates;

/**
 * Class UOBIndonesia
 * @package ArrayIterator\ProjectModule\CurrencyRates\Provider
 */
class UOBIndonesia extends AbstractProvider
{
    /**
     * @var string
     */
    protected $providerCode = 'UOB';

    /**
     * @var string
     */
    protected $providerName = 'UOB Indonesia';

    /**
     * @var string
     */
    protected $providerUrl = 'https://uob.co.id/';

    /**
     * @var string
     */
    protected $ratesUrl = 'https://uob.co.id/web-resources/personal/kurs.xml';

    /**
     * @var Rates
     */
    protected $rates;

    /**
     * @return bool
     */
    public function isBank(): bool
    {
        return true;
    }

    /**
     * @return Rates
     */
    public function getRates(): Rates
    {
        if ($this->rates instanceof Rates) {
            return $this->rates;
        }

        $this->rates = new Rates($this);
        $this->rates->addRate(new Rate($this->getBaseCode(), 1.0, 1.0, 1.0));
        $xml = @simplexml_load_file($this->ratesUrl);
        if (!$xml) {
            return $this->rates;
        }
        foreach ($xml as $item) {
            $item = (array) $item;
            if (! isset($item['matauang']) || ! isset($item['beli']) || ! isset($item['jual'])) {
                return $this->rates;
            }
            $currency = $this->normalizeCurrency((string)$item['matauang']);
            $buy = trim((string)$item['beli']);
            $sell = trim((string)$item['jual']);
            $buy = abs(str_replace(',', '', $buy));
            $sell = abs(str_replace(',', '', $sell));
            $this->rates->addRate(new Rate($currency, $sell, $sell, $buy));
        }

        return $this->rates;
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseCode(): string
    {
        return 'IDR';
    }
}
