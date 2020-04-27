<?php
declare(strict_types=1);

namespace ArrayIterator\ProjectModule\CurrencyRates;

use InvalidArgumentException;
use JsonSerializable;
use Serializable;

/**
 * Class Rate
 * @package ArrayIterator\ProjectModule\CurrencyRates
 */
class Rate implements Serializable, JsonSerializable
{
    /**
     * @var string
     */
    protected $code;
    /**
     * @var float
     */
    protected $rate;
    /**
     * @var float|null
     */
    protected $sell;
    /**
     * @var float|null
     */
    protected $buy;

    /**
     * Rate constructor.
     * @param string $currencyCode
     * @param float $rate
     * @param float|null $sellRate
     * @param float|null $buyRate
     */
    public function __construct(
        string $currencyCode,
        float $rate,
        ?float $sellRate,
        ?float $buyRate
    ) {
        $this->code = $currencyCode;
        $this->rate = $rate;
        $this->sell = $sellRate;
        $this->buy = $buyRate;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return float
     */
    public function getRate(): float
    {
        return $this->rate;
    }

    /**
     * @param float $rate
     */
    public function setRate(float $rate): void
    {
        $this->rate = $rate;
    }

    /**
     * @return float|null
     */
    public function getSell(): ?float
    {
        return $this->sell;
    }

    /**
     * @param float|null $sell
     */
    public function setSell(?float $sell): void
    {
        $this->sell = $sell;
    }

    /**
     * @return float|null
     */
    public function getBuy(): ?float
    {
        return $this->buy;
    }

    /**
     * @param float|null $buy
     */
    public function setBuy(?float $buy): void
    {
        $this->buy = $buy;
    }

    /**
     * @return string
     */
    public function serialize() : string
    {
        return serialize([
            'code' => $this->getCode(),
            'rate' => $this->getRate(),
            'buy'  => $this->getBuy(),
            'sell' => $this->getSell(),
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        if (!is_string($serialized)) {
            throw new InvalidArgumentException(
                sprintf('Argument 1 must be as a string, %s given', gettext($serialized))
            );
        }
        $array = @unserialize($serialized);
        if (!is_array($array)) {
            throw new InvalidArgumentException(
                sprintf('Argument 1 must be string serialized of array, %s given.', gettype($array))
            );
        }

        $this->setBuy($array['buy']??null);
        $this->setSell($array['sell']??null);
        $this->setRate($array['rate']);
        $this->setCode($array['code']);
    }

    /**
     * @return array|null
     */
    public function getCurrencyInfo() : ?array
    {
        return Currencies::get($this->getCode());
    }

    /**
     * @return float[]|null[]
     */
    public function toArray() : array
    {
        return [
            'rate' => $this->getRate(),
            'buy'  => $this->getBuy(),
            'sell' => $this->getSell(),
        ];
    }

    public function jsonSerialize() : array
    {
        return [
            'code' => $this->getCode(),
            'rate' => $this->getRate(),
            'buy'  => $this->getBuy(),
            'sell' => $this->getSell(),
            'info' => $this->getCurrencyInfo()
        ];
    }
}
