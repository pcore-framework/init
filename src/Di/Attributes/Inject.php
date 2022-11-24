<?php

declare(strict_types=1);

namespace PCore\Init\Di\Attributes;

use Attribute;
use PCore\Aop\Contracts\PropertyAttribute;
use PCore\Aop\Exceptions\PropertyHandleException;
use PCore\Di\Reflection;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use Throwable;

/**
 * Class Inject
 * @package PCore\Init\Di\Attributes
 * @github https://github.com/pcore-framework/init
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Inject implements PropertyAttribute
{

    /**
     * @param string $id тип инъекции
     */
    public function __construct(protected string $id = '')
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
            if ((!is_null($type = $reflectionProperty->getType()) && $type = $type->getName()) || $type = $this->id) {
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($object, $this->getBinding($type));
            }
        } catch (Throwable $throwable) {
            throw new PropertyHandleException('Не удалось назначить свойство.' . $throwable->getMessage());
        }
    }

    /**
     * @param string $type
     * @return object
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    protected function getBinding(string $type): object
    {
        return make($type);
    }

}
