<?php declare(strict_types=1);

namespace TiendaNube\Throttler;

use TiendaNube\Throttler\Provider\ProviderInterface;
use TiendaNube\Throttler\Storage\InMemory;
use TiendaNube\Throttler\Storage\StorageInterface;

/**
 * Class Throttler
 *
 * @package TiendaNube\Throttler
 */
class Throttler
{
    /**
     * The current provider instance
     *
     * @var ProviderInterface
     */
    private $provider;

    /**
     * Throttler constructor.
     *
     * @param ProviderInterface $provider
     * @param StorageInterface|null $storage
     */
    public function __construct(ProviderInterface $provider, StorageInterface $storage = null)
    {
        $this->provider = $provider;

        if (is_null($provider->getStorage())) {
            $storage = $storage ?: new InMemory();
            $this->provider->setStorage($storage);
        }
    }

    /**
     * Throttle a request
     *
     * @param string $namespace
     * @param bool $sleep
     * @param int $increment
     * @return bool
     */
    public function throttle(string $namespace, bool $sleep = false, int $increment = 1): bool
    {
        if ($this->provider->hasLimit($namespace)) {
            $this->provider->incrementUsage($namespace,$increment);
            return false;
        } else if ($sleep) {
            $time = $this->provider->getEstimate($namespace);
            usleep($time * 1000);

            return $this->throttle($namespace,$sleep,$increment);
        }

        return true;
    }

    /**
     * Get the current ratio.
     *
     * @param string $namespace
     * @param int $factor
     * @return int|mixed
     */
    public function getRatio(string $namespace, int $factor = ProviderInterface::RATIO_FACTOR_BY_SECOND)
    {
        return $this->provider->getRatio($namespace,$factor);
    }

    /**
     * Get the current usage.
     *
     * @param string $namespace
     * @return int
     */
    public function getUsage(string $namespace)
    {
        return $this->provider->getUsage($namespace);
    }

    /**
     * Get the current limit.
     *
     * @param string $namespace
     * @return int
     */
    public function getLimit(string $namespace)
    {
        return $this->provider->getLimit($namespace);
    }

    /**
     * Check if the current provider has limit.
     *
     * @param string $namespace
     * @return bool
     */
    public function hasLimit(string $namespace)
    {
        return $this->provider->hasLimit($namespace);
    }

    /**
     * Get the number of remaining requests available.
     *
     * @param string $namespace
     * @return int
     */
    public function getRemaining(string $namespace)
    {
        return $this->provider->getRemaining($namespace);
    }

    /**
     * Get the estimated time (in milliseconds) to perform the next request.
     *
     * @param string $namespace
     * @return int
     */
    public function getEstimate(string $namespace)
    {
        return $this->provider->getEstimate($namespace);
    }

    /**
     * Get the estimated time (in milliseconds) to fully reset the bucket.
     *
     * @param string $namespace
     * @return int
     */
    public function getReset(string $namespace)
    {
        return $this->provider->getReset($namespace);
    }
}
