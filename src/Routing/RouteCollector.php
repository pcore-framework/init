<?php

declare(strict_types=1);

namespace PCore\Init\Routing;

use PCore\Aop\Collectors\AbstractCollector;
use PCore\Di\Context;
use PCore\Di\Exceptions\NotFoundException;
use PCore\Init\Routing\Attributes\{Controller, RequestMapping};
use PCore\Routing\{Router};
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

/**
 * Class RouteCollector
 * @package PCore\Init\Routing\Attributes
 * @github https://github.com/pcore-framework/init
 */
class RouteCollector extends AbstractCollector
{

    /**
     * Соответствующий текущему контроллеру
     *
     * @var Router|null
     */
    protected static ?Router $router = null;

    /**
     * Имя класса текущего контроллера
     */
    protected static string $class = '';

    /**
     * @param string $class
     * @param object $attribute
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     * @throws NotFoundExceptionInterface
     */
    public static function collectClass(string $class, object $attribute): void
    {
        if ($attribute instanceof Controller) {
            $routeCollection = Context::getContainer()->make(\PCore\Routing\RouteCollection::class);
            $router = new Router(
                $attribute->prefix,
                $attribute->patterns,
                middlewares: $attribute->middlewares,
                routeCollection: $routeCollection
            );
            self::$router = $router;
            self::$class = $class;
        }
    }

    /**
     * @param string $class
     * @param string $method
     * @param object $attribute
     * @throws NotFoundException
     */
    public static function collectMethod(string $class, string $method, object $attribute): void
    {
        if ($attribute instanceof RequestMapping && self::$class === $class && !is_null(self::$router)) {
            self::$router
                ->request($attribute->path, [$class, $method], $attribute->methods)
                ->middleware(...$attribute->middlewares);
        }
    }

}
