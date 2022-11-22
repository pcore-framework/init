<?php

declare(strict_types=1);

namespace PCore\Init\Cache\Aspects;

use Attribute;
use Closure;
use PCore\Aop\Contracts\AspectInterface;
use PCore\Aop\JoinPoint;
use PCore\Cache\CacheManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Class Cacheable
 * @package PCore\Init\Cache\Aspects
 * @github https://github.com/pcore-framework/init
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Cacheable implements AspectInterface
{

    public function __construct(
        protected ?int    $ttl = null,
        protected string  $prefix = '',
        protected ?string $key = null
    )
    {
    }

    /**
     * @param JoinPoint $joinPoint
     * @param Closure $next
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException|ReflectionException
     */
    public function process(JoinPoint $joinPoint, Closure $next): mixed
    {
        return make(CacheManager::class)
            ->store()
            ->remember($this->getKey($joinPoint), fn() => $next($joinPoint), $this->ttl);
    }

    /**
     * @param JoinPoint $joinPoint
     * @return string
     */
    protected function getKey(JoinPoint $joinPoint): string
    {
        $key = $this->key ?? ($joinPoint->class . ':' . $joinPoint->method . ':' . serialize(
                    array_filter($joinPoint->parameters->getArrayCopy(), fn($item) => !is_object($item))
                ));
        return $this->prefix ? ($this->prefix . ':' . $key) : $key;
    }

}
