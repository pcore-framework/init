<?php

declare(strict_types=1);

namespace PCore\Init\Routing\Attributes;

use Attribute;

/**
 * Class Controller
 * @package PCore\Init\Routing\Attributes
 * @github https://github.com/pcore-framework/init
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{

    /**
     * @param string $prefix
     * @param array $middlewares
     * @param array $patterns
     */
    public function __construct(
        public string $prefix = '/',
        public array  $middlewares = [],
        public array  $patterns = []
    )
    {
    }

}
