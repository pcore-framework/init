<?php

declare(strict_types=1);

namespace PCore\Init\Routing\Attributes;

use Attribute;
use PCore\HttpMessage\Contracts\RequestMethodInterface;

/**
 * Class RequestMapping
 * @package PCore\Init\Routing\Attributes
 * @github https://github.com/pcore-framework/init
 */
#[Attribute(Attribute::TARGET_METHOD)]
class RequestMapping
{

    /**
     * Метод по умолчанию
     *
     * @var array|string[]
     */
    public array $methods = [
        RequestMethodInterface::METHOD_GET,
        RequestMethodInterface::METHOD_HEAD,
        RequestMethodInterface::METHOD_POST
    ];


    /**
     * @param string $path путь
     * @param array|string[] $methods метод
     * @param array $middlewares промежуточный слой
     */
    public function __construct(
        public string $path = '/',
        array         $methods = [],
        public array  $middlewares = []
    )
    {
        if (!empty($methods)) {
            $this->methods = $methods;
        }
    }

}
