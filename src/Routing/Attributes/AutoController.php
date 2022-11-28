<?php

declare(strict_types=1);

namespace PCore\Init\Routing\Attributes;

use Attribute;
use PCore\HttpMessage\Contracts\RequestMethodInterface;
use PCore\Routing\Contracts\ControllerInterface;

/**
 * Class AutoController
 * @package PCore\Init\Routing\Attributes
 * @github https://github.com/pcore-framework/init
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AutoController implements ControllerInterface
{

    /**
     * @param string $prefix
     * @param array $middlewares
     * @param array $methods
     * @param array $patterns
     */
    public function __construct(
        public string $prefix = '',
        public array  $middlewares = [],
        public array  $methods = [
            RequestMethodInterface::METHOD_GET,
            RequestMethodInterface::METHOD_HEAD,
            RequestMethodInterface::METHOD_POST
        ],
        public array  $patterns = []
    )
    {
    }

}
