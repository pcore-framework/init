<?php

declare(strict_types=1);

namespace PCore\Init\Config\Attributes;

use Attribute;
use PCore\Aop\Contracts\PropertyAttribute;
use PCore\Aop\Exceptions\PropertyHandleException;
use PCore\Config\Repository;
use PCore\Di\Reflection;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use Throwable;

/**
 * Class Config
 * @package PCore\Init\Config\Attributes
 * @github https://github.com/pcore-framework/init
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Config implements PropertyAttribute
{

    /**
     * @param string $key ключ
     * @param null|mixed $default значение по умолчанию
     */
    public function __construct(
        protected string $key,
        protected mixed  $default = null
    )
    {
    }

    /**
     * @param object $object
     * @param string $property
     */
    public function handle(object $object, string $property): void
    {
        try {
            $reflectionProperty = Reflection::property($object::class, $property);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($object, $this->getConfigValue());
        } catch (Throwable $throwable) {
            throw new PropertyHandleException('Не удалось назначить свойство.' . $throwable->getMessage());
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    protected function getValue()
    {
        return make(Repository::class)->get($this->key, $this->default);
    }

}
