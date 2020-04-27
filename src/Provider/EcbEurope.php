<?php
declare(strict_types=1);

namespace ArrayIterator\ProjectModule\CurrencyRates\Provider;

use ArrayIterator\ProjectModule\CurrencyRates\AbstractProvider;
use ArrayIterator\ProjectModule\CurrencyRates\Rate;
use ArrayIterator\ProjectModule\CurrencyRates\Rates;

/**
 * Class EcbEurope
 * @package ArrayIterator\ProjectModule\CurrencyRates\Provider
 */
class EcbEurope extends AbstractProvider
{
    /**
     * @var string
     */
    protected $providerCode = 'ECB';

    /**
     * @var string
     */
    protected $providerName = 'Europan Central Bank';

    /**
     * @var string
     */
    protected $providerUrl = 'https://www.ecb.europa.eu/';

    /**
     * @var string
     */
    protected $ratesUrl = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

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

        foreach ($xml->Cube->Cube->Cube as $item) {
            if (! isset($item['currency']) || ! isset($item['rate'])) {
                return $this->rates;
            }

            $currency        = $this->normalizeCurrency((string)$item['currency']);
            $rate            = (float) ((string)$item['rate']);
            $this->rates->addRate(new Rate($currency, $rate, $rate, $rate));
        }

        return $this->rates;
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseCode(): string
    {
        return 'EUR';
    }
}
