<?php

declare(strict_types=1);

use PCore\Config\Repository;
use PCore\Di\Context;

if (function_exists('base_path') === false) {
    /**
     * @param string $path
     * @return string
     */
    function base_path(string $path = ''): string
    {
        return BASE_PATH . ltrim($path, '/');
    }
}

if (function_exists('config') === false) {
    /**
     * @param string $key
     * @param null|mixed $default
     * @return mixed
     * @throws ReflectionException
     */
    function config(string $key, $default = null): mixed
    {
        /** @var Repository $config */
        $config = Context::getContainer()->make(Repository::class);
        return $config->get($key, $default);
    }
}

if (function_exists('env') === false) {

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    function env(string $key, $default = null): mixed
    {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
            return substr($value, 1, -1);
        }
        return $value;
    }
}