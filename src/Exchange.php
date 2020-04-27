<?php
declare(strict_types=1);

namespace ArrayIterator\ProjectModule\CurrencyRates;

use InvalidArgumentException;
use ReflectionClass;
use Throwable;

/**
 * Class Exchange
 * @package ArrayIterator\ProjectModule\CurrencyRates
 */
class Exchange
{
    /**
     * @var AbstractProvider[]
     */
    private $providers = [];

    /**
     * @var array
     */
    private static $cachedProvider = [];

    /**
     * @return array
     */
    public function getRegisteredProviders()
    {
        return $this->providers;
    }

    /**
     * @param string $provider
     * @return AbstractProvider
     * @throws Throwable
     */
    public function provider(string $provider) : AbstractProvider
    {
        $cacheProvider = self::$cachedProvider[$provider]??$provider;
        if (isset($this->providers[$cacheProvider])) {
            return $this->providers[$cacheProvider];
        }

        try {
            $ref = new ReflectionClass($provider);
            if (!$ref->isSubclassOf(AbstractProvider::class)) {
                throw new InvalidArgumentException(
                    sprintf('Provider Argument must be sub class of %s', $ref->getName())
                );
            }
            $cacheProvider = $ref->getName();
            self::$cachedProvider[$provider] = $cacheProvider;
            $provider = $cacheProvider;
            unset($ref);
        } catch (Throwable $e) {
            throw $e;
        }

        return $this->providers[$provider] = new $provider($this);
    }
}
