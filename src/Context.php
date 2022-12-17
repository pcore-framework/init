<?php

declare(strict_types=1);

namespace PCore\Swoole;

use Swoole\Coroutine;
use Swoole\Coroutine\Context as SwooleContext;

/**
 * Class Context
 * @package PCore\Swoole
 * @github https://github.com/pcore-framework/swoole
 */
class Context
{

    /**
     * @var array
     */
    protected static array $container = [];

    /**
     * @param string $key
     * @return mixed
     */
    public static function get(string $key): mixed
    {
        if (($cid = self::getCid()) < 0) {
            return self::$container[$key] ?? null;
        }
        return self::for($cid)[$key] ?? null;
    }

    /**
     * @param string $key
     * @param mixed $item
     * @return void
     */
    public static function put(string $key, mixed $item): void
    {
        if (($cid = self::getCid()) > 0) {
            self::for($cid)[$key] = $item;
        } else {
            self::$container[$key] = $item;
        }
    }

    /**
     * @param string $key
     * @return void
     */
    public static function delete(string $key = ''): void
    {
        if (($cid = self::getCid()) > 0) {
            if (!empty($key)) {
                unset(self::for($cid)[$key]);
            }
        } else {
            if ($key) {
                unset(self::$container[$key]);
            } else {
                self::$container = [];
            }
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        if (($cid = self::getCid()) > 0) {
            return isset(self::for($cid)[$key]);
        }
        return isset(self::$container[$key]);
    }

    /**
     * @param int|null $cid
     * @return SwooleContext|null
     */
    public static function for(?int $cid = null): ?SwooleContext
    {
        return Coroutine::getContext($cid);
    }

    /**
     * @return int
     */
    protected static function getCid(): int
    {
        if (class_exists('Swoole\Coroutine')) {
            return Coroutine::getCid();
        }
        return -1;
    }

    /**
     * @return bool
     */
    public static function inCoroutine(): bool
    {
        return self::getCid() >= 0;
    }

}
