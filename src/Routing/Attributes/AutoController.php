<?php

declare(strict_types=1);

namespace PCore\Init\Routing\Attributes;

use Attribute;
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
        public array  $methods = ['GET', 'HEAD', 'POST'],
        public array  $patterns = []
    )
    {
    }

}
