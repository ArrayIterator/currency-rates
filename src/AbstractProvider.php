<?php
declare(strict_types=1);

namespace ArrayIterator\ProjectModule\CurrencyRates;

/**
 * Class AbstractExchange
 * @package ArrayIterator\ProjectModule\CurrencyRates
 */
abstract class AbstractProvider
{
    /**
     * @var string
     */
    protected $providerCode;

    /**
     * @var string
     */
    protected $providerName;

    /**
     * @var string
     */
    protected $providerUrl;

    /**
     * @var Exchange
     */
    protected $exchange;

    /**
     * AbstractProvider constructor.
     * @param Exchange $exchange
     */
    public function __construct(Exchange $exchange)
    {
        $this->exchange = $exchange;
    }

    /**
     * @return Exchange
     */
    public function getExchange(): Exchange
    {
        return $this->exchange;
    }

    /**
     * @return string
     */
    public function getProviderCode(): string
    {
        return $this->providerCode;
    }

    /**
     * @return string|null
     */
    public function getProviderName() : ?string
    {
        return  $this->providerName;
    }

    /**
     * @return string
     */
    public function getProviderUrl(): ?string
    {
        return $this->providerUrl;
    }

    /**
     * @param string $currencyCode
     * @return bool
     */
    public function isCurrencySupported(string $currencyCode) : bool
    {
        return in_array($this->normalizeCurrency($currencyCode), $this->getSupportedCurrencies());
    }

    /**
     * @param string $currencyCode
     * @return string
     */
    public function normalizeCurrency(string $currencyCode) : string
    {
        return trim(strtoupper($currencyCode));
    }

    /**
     * @param $rate
     * @param int $roundAdd
     *
     * @return string
     */
    public function sanityNumber($rate, int $roundAdd = 1)
    {
        $roundAdd = $roundAdd < 0 ? 0 : $roundAdd;
        preg_match('/[\-\+]([0-9]+)$/', (string) $rate, $match);
        if (!empty($match[1])) {
            $rate = number_format((float) $rate, (int) $match[1]+$roundAdd, '.', '');
        } elseif (strpos((string) $rate, '.')) {
            $ex = explode('.', (string) $rate);
            $ex = array_pop($ex);
            if (strlen($ex) > 5) {
                $rate = number_format((float) $rate, 5, '.', '');
            }
        }

        return preg_replace('~\.[0]+$~', '', (string) $rate);
    }

    /**
     * @return bool
     */
    abstract public function isBank() : bool;

    /**
     * @return Rates
     */
    abstract public function getRates() : Rates;

    /**
     * @return string
     */
    abstract public function getBaseCode() : string;

    /**
     * @return array
     */
    public function getSupportedCurrencies(): array
    {
        return array_keys($this->getRates()->getRates());
    }

    /**
     * @param string $code
     * @return Rate|null
     */
    public function get(string $code) : ?Rate
    {
        return $this->getRates()->get($code);
    }

    /**
     * @param Rate $from
     * @param Rate $to
     * @return string|null
     */
    public function convert(Rate $from, Rate $to): ?string
    {
        if ($from->getCode() === $to->getCode()) {
            return $this->sanityNumber($to->getRate());
        }

        $from = $from->getRate();
        $to   = $to->getRate();
        if ($from === $to) {
            return $this->sanityNumber($to);
        }
        if ($from === null || $to === null) {
            return null;
        }
        return $this->sanityNumber($from/$to);
    }
}
